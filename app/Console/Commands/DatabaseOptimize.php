<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseOptimize extends Command
{
    protected $signature = 'db:optimize';
    protected $description = 'Optimize database tables';

    public function handle()
    {
        $this->info('Starting database optimization...');

        // Get all tables
        $tables = DB::select('SHOW TABLES');

        $progressBar = $this->output->createProgressBar(count($tables));
        $progressBar->start();

        foreach ($tables as $table) {
            $tableName = reset($table);

            try {
                DB::statement("OPTIMIZE TABLE `{$tableName}`");
                $progressBar->advance();
            } catch (\Exception $e) {
                $this->warn("Failed to optimize table {$tableName}: " . $e->getMessage());
            }
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('Database optimization completed!');

        return 0;
    }
}
