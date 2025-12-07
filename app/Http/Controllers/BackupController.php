<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        $backups = [];
        $backpath = storage_path('app/backups');

        if (file_exists($backpath)) {
            $files = glob($backpath . '/*.sql.gz');
            foreach ($files as $file) {
                $filename = basename($file);

                $backups[] = [
                    'name' => $filename,
                    'size' => $this->formatBytes(filesize($file)),
                    'date' => date('Y-m-d H:i:s', filemtime($file)),
                    'path' => $file,
                ];
            }

            // Sort by date (newest first)
            usort($backups, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
        }

        return view('backups.index', compact('backups'));
    }

    public function create()
    {
        $timestamp = date('Ymd_His');
        $filename = "backup_{$timestamp}.sql";
        $path = storage_path("app/backups/{$filename}");

        // Ensure directory exists
        if (!file_exists(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $config = config('database.connections.mysql');

        // Export using mysqldump
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
            escapeshellarg($config['host']),
            escapeshellarg($config['port']),
            escapeshellarg($config['username']),
            escapeshellarg($config['password']),
            escapeshellarg($config['database']),
            escapeshellarg($path)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            return back()->with('error', 'Backup failed!');
        }

        // Compress the file
        $compressedPath = $path . '.gz';
        $gz = gzopen($compressedPath, 'w9');
        gzwrite($gz, file_get_contents($path));
        gzclose($gz);

        // Delete uncompressed file
        unlink($path);

        return back()->with('success', 'Backup created successfully!');
    }

    public function download($filename)
    {
        $path = storage_path("app/backups/{$filename}");

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,gz,sql.gz',
        ]);

        // Get the uploaded file
        $file = $request->file('backup_file');
        $tempPath = $file->getPathname(); // Use getPathname() instead of getRealPath()

        // Debug logging
        \Log::info('Restore initiated', [
            'filename' => $file->getClientOriginalName(),
            'extension' => $file->getClientOriginalExtension(),
            'temp_path' => $tempPath,
            'file_exists' => file_exists($tempPath),
            'file_size' => filesize($tempPath),
        ]);

        $config = config('database.connections.mysql');

        // Debug database config (without password)
        \Log::info('Database config', [
            'host' => $config['host'],
            'port' => $config['port'],
            'database' => $config['database'],
            'username' => $config['username'],
        ]);

        $sqlFile = null;
        $decompressed = null;

        try {
            // Check if file is gzipped
            $originalExtension = $file->getClientOriginalExtension();
            $fileName = $file->getClientOriginalName();

            if ($originalExtension === 'gz' || str_ends_with($fileName, '.sql.gz')) {
                // Decompress first
                $decompressed = storage_path('app/temp_restore_' . time() . '.sql');

                \Log::info('Decompressing gzip file', [
                    'source' => $tempPath,
                    'destination' => $decompressed,
                ]);

                // Method 1: Using PHP gz functions
                $success = $this->decompressGzFile($tempPath, $decompressed);

                if (!$success) {
                    // Method 2: Using shell command
                    $command = "gunzip -c " . escapeshellarg($tempPath) . " > " . escapeshellarg($decompressed) . " 2>&1";
                    \Log::info('Shell command: ' . $command);

                    exec($command, $output, $returnCode);

                    if ($returnCode !== 0 || !file_exists($decompressed) || filesize($decompressed) < 100) {
                        \Log::error('Decompression failed', [
                            'return_code' => $returnCode,
                            'output' => $output,
                            'decompressed_exists' => file_exists($decompressed),
                            'decompressed_size' => file_exists($decompressed) ? filesize($decompressed) : 0,
                        ]);
                        return back()->with('error', 'Failed to decompress backup file. Check if gzip is installed on server.');
                    }
                }

                $sqlFile = $decompressed;
            } else {
                $sqlFile = $tempPath;
            }

            // Verify SQL file
            if (!file_exists($sqlFile) || filesize($sqlFile) < 100) {
                \Log::error('SQL file invalid', [
                    'sql_file' => $sqlFile,
                    'exists' => file_exists($sqlFile),
                    'size' => file_exists($sqlFile) ? filesize($sqlFile) : 0,
                ]);
                return back()->with('error', 'Invalid SQL file or file too small.');
            }

            // Read first few lines to verify it's an SQL file
            $handle = fopen($sqlFile, 'r');
            $firstLines = fread($handle, 500);
            fclose($handle);

            if (!str_contains($firstLines, 'MySQL dump') &&
                !str_contains($firstLines, 'CREATE TABLE') &&
                !str_contains($firstLines, 'INSERT INTO')) {
                \Log::error('File does not appear to be a valid SQL dump', [
                    'first_500_chars' => $firstLines,
                ]);
                return back()->with('error', 'The file does not appear to be a valid SQL database dump.');
            }

            \Log::info('SQL file verified', [
                'size' => filesize($sqlFile),
                'first_chars' => substr($firstLines, 0, 100),
            ]);

            // Restore database
            // First, disable foreign key checks to avoid issues
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

            $command = sprintf(
                'mysql --host=%s --port=%s --user=%s --password=%s %s < %s 2>&1',
                escapeshellarg($config['host']),
                escapeshellarg($config['port']),
                escapeshellarg($config['username']),
                escapeshellarg($config['password']),
                escapeshellarg($config['database']),
                escapeshellarg($sqlFile)
            );

            \Log::info('Restore command (password hidden)', [
                'command' => str_replace($config['password'], '***', $command),
            ]);

            exec($command, $output, $returnVar);

            \Log::info('Restore execution result', [
                'return_var' => $returnVar,
                'output' => $output,
            ]);

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

            // Cleanup
            if ($decompressed && file_exists($decompressed)) {
                unlink($decompressed);
            }

            if ($returnVar !== 0) {
                $errorMessage = 'Restore failed! ';
                if (!empty($output)) {
                    $errorMessage .= 'Error: ' . implode(' ', $output);
                }

                \Log::error('Database restore failed', [
                    'return_var' => $returnVar,
                    'output' => $output,
                    'command' => $command,
                ]);

                return back()->with('error', $errorMessage);
            }

            // Verify restoration was successful
            try {
                // Try to query the database to verify it's working
                $tableCount = DB::select('SHOW TABLES');
                \Log::info('Restore verification', [
                    'tables_restored' => count($tableCount),
                ]);
            } catch (\Exception $e) {
                \Log::error('Restore verification failed', [
                    'error' => $e->getMessage(),
                ]);
                return back()->with('warning', 'Database restored but verification failed. Please check manually.');
            }

            return back()->with('success', 'Database restored successfully! ' . count($tableCount) . ' tables restored.');

        } catch (\Exception $e) {
            \Log::error('Restore exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Cleanup on error
            if ($decompressed && file_exists($decompressed)) {
                unlink($decompressed);
            }

            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    private function decompressGzFile($source, $destination)
    {
        try {
            // Open gzipped file
            $gz = gzopen($source, 'rb');
            if (!$gz) {
                \Log::error('Failed to open gzip file: ' . $source);
                return false;
            }

            // Open destination file
            $dest = fopen($destination, 'wb');
            if (!$dest) {
                \Log::error('Failed to open destination file: ' . $destination);
                gzclose($gz);
                return false;
            }

            // Copy content
            while (!gzeof($gz)) {
                fwrite($dest, gzread($gz, 4096));
            }

            // Close files
            gzclose($gz);
            fclose($dest);

            \Log::info('GZ decompression successful', [
                'source_size' => filesize($source),
                'dest_size' => filesize($destination),
            ]);

            return file_exists($destination) && filesize($destination) > 100;

        } catch (\Exception $e) {
            \Log::error('GZ decompression error', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function delete($filename)
    {
        $path = storage_path("app/backups/{$filename}");

        if (file_exists($path)) {
            unlink($path);
        }

        return back()->with('success', 'Backup deleted!');
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
