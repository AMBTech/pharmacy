<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ========== BACKUP SCHEDULES ==========

        // 1. Daily backup at 2:00 AM
        $schedule->command('backup:run')->dailyAt('02:00')
            ->onSuccess(function () {
                \Log::info('Daily backup completed successfully');
            })
            ->onFailure(function () {
                \Log::error('Daily backup failed');
            });

        // 2. Clean old backups - keep only latest 7 daily backups
        $schedule->command('backup:clean')->dailyAt('03:00');

        // 3. Monitor backup health (optional)
        $schedule->command('backup:monitor')->dailyAt('04:00');

        // ========== DATABASE MAINTENANCE ==========

        // 4. Optimize database tables weekly
        $schedule->command('db:optimize')->weeklyOn(1, '03:30'); // Monday at 3:30 AM

        // 5. Backup only database (without files) every 6 hours
        $schedule->command('backup:run --only-db')->everySixHours();

        // ========== CUSTOM BACKUP COMMANDS ==========

        // 6. Weekly full backup (with files) on Sunday at 1:00 AM
        $schedule->command('backup:run --only-files')->sundays()->at('01:00');

        // 7. Monthly backup to cloud (if configured)
        $schedule->command('backup:run --only-db --disable-notifications')
            ->monthlyOn(1, '02:00'); // 1st of month at 2 AM
//            ->emailOutputOnFailure('admin@yourdomain.com');

        // ========== APPLICATION MAINTENANCE ==========

        // 8. Clear temporary files daily
        $schedule->command('cache:clear')->dailyAt('05:00');
        $schedule->command('view:clear')->dailyAt('05:05');
        $schedule->command('route:clear')->dailyAt('05:10');

        // 9. Queue worker monitoring (if using queues)
        // $schedule->command('queue:work --stop-when-empty')->everyMinute();

        // 10. Log cleanup - keep logs for 30 days only
        $schedule->command('log:clear --days=30')->monthly();

        // 11. Send backup success notification to admin (optional)
        $schedule->call(function () {
            $lastBackup = \Spatie\Backup\Helpers\Backup::create()
                ->getLatestBackupDate();

            if ($lastBackup && $lastBackup->gt(now()->subDay())) {
                // Backup is recent, send success notification
                // \App\Models\User::where('role', 'admin')->each->notify(new BackupSuccessNotification());
            } else {
                // Backup is old, send warning
                // \App\Models\User::where('role', 'admin')->each->notify(new BackupWarningNotification());
            }
        })->dailyAt('06:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // Load custom commands
        $this->load(__DIR__.'/Commands');

        // Load all console commands
        require base_path('routes/console.php');
    }

    /**
     * Configure the application for production.
     */
    protected function configureForProduction(): void
    {
        // These commands will be available in production
        $this->commands([
            \App\Console\Commands\BackupCreate::class,
//            \App\Console\Commands\BackupRestore::class,
            \App\Console\Commands\BackupList::class,
            \App\Console\Commands\DatabaseOptimize::class,
//            \App\Console\Commands\DatabaseCheck::class,
        ]);
    }
}
