<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupList extends Command
{
    protected $signature = 'backup:list';
    protected $description = 'List all available backups';

    public function handle()
    {
        $disk = Storage::disk('backups');

        if (!$disk->exists('backups')) {
            $this->warn('No backup directory found.');
            return;
        }

        $files = $disk->files('backups');
        $backupFiles = array_filter($files, function ($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'gz';
        });

        if (empty($backupFiles)) {
            $this->warn('No backup files found.');
            return;
        }

        $this->info('Available Backups:');
        $this->info(str_repeat('-', 80));

        $headers = ['Filename', 'Size', 'Date Modified'];
        $rows = [];

        foreach ($backupFiles as $file) {
            $rows[] = [
                basename($file),
                $this->formatBytes($disk->size($file)),
                date('Y-m-d H:i:s', $disk->lastModified($file)),
            ];
        }

        $this->table($headers, $rows);

        $this->info("\nTotal backups: " . count($backupFiles));

        $totalSize = array_sum(array_map(function($file) use ($disk) {
            return $disk->size($file);
        }, $backupFiles));

        $this->info("Total size: " . $this->formatBytes($totalSize));
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
