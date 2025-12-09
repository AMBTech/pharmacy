<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Validator;
use Exception;

class UpdateSystemSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Only updates guarded fields listed in the SystemSetting model.
     */
    protected $signature = 'settings:update
                            {--company-name= : Company name}
                            {--company-address= : Company address}
                            {--company-phone= : Company phone}
                            {--license-number= : License / registration number}
                            {--company-email= : Company email}
                            {--yes : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * Updates the guarded fields in the SystemSetting model using SystemSetting::getSettings().
     */
    protected $description = 'Update guarded system settings (company_name, company_address, company_phone, license_number, company_email)';

    public function handle()
    {
        // Collect provided options
        $inputs = [
            'company_name'    => $this->option('company-name'),
            'company_address' => $this->option('company-address'),
            'company_phone'   => $this->option('company-phone'),
            'license_number'  => $this->option('license-number'),
            'company_email'   => $this->option('company-email'),
        ];

        // Filter out nulls (only keep provided options)
        $toUpdate = array_filter($inputs, fn($v) => $v !== null);

        if (empty($toUpdate)) {
            $this->info('No fields provided. Use --company-name="..." or other options to update settings.');
            $this->line('Options: --company-name --company-address --company-phone --license-number --company-email');
            return 0;
        }

        // Validation rules for the provided fields
        $rules = [
            'company_name'    => 'sometimes|string|max:255',
            'company_address' => 'sometimes|string|max:1000',
            'company_phone'   => ['sometimes','string','max:50'],
            'license_number'  => 'sometimes|string|max:255',
            'company_email'   => 'sometimes|email|max:255',
        ];

        $validator = Validator::make($toUpdate, $rules);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $err) {
                $this->error('  - ' . $err);
            }
            return 1;
        }

        // Show preview and ask for confirmation (unless --yes)
        $this->info('About to update the following guarded settings:');
        foreach ($toUpdate as $k => $v) {
            $this->line("  - {$k} => {$v}");
        }

        if (!$this->option('yes')) {
            if (!$this->confirm('Proceed with updating these settings?')) {
                $this->info('Aborted.');
                return 0;
            }
        }

        try {
            $settings = SystemSetting::getSettings(); // ensures record exists

            // Show previous values (for logging/visibility)
            $this->line("\nCurrent values (before update):");
            foreach ($toUpdate as $k => $_) {
                $current = data_get($settings, $k);
                $this->line("  - {$k}: " . ($current === null ? '(null)' : $current));
            }

            // Update only the guarded fields provided.
            foreach ($toUpdate as $k => $v) {
                // Use direct assignment to avoid mass-assignment restrictions (guarded fields)
                $settings->{$k} = $v;
            }

            $settings->save();

            $this->info("\nSettings updated successfully. New values:");
            foreach ($toUpdate as $k => $_) {
                $this->line("  - {$k}: " . $settings->{$k});
            }

            return 0;
        } catch (Exception $e) {
            $this->error('Failed to update settings: ' . $e->getMessage());
            return 1;
        }
    }
}
