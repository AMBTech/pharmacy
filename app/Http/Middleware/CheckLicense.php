<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckLicense
{
    public function handle($request, $next)
    {
        // Skip license check for license activation routes
        if ($request->routeIs('license.*')) {
            return $next($request);
        }

        if (!$this->validateLicense()) {
            return redirect()->route('license.required');
        }

        return $next($request);
    }

    /**
     * Main license validation
     */
    protected function validateLicense()
    {
        // Cache result to avoid checking on every request
        return Cache::remember('license_valid', 3600, function () {
            try {
                // 1. ionCube loader check
                if (!extension_loaded('ionCube Loader')) {
                    Log::error('ionCube Loader not installed');
                    return false;
                }

                // 2. Read ionCube license properties
                $license = ioncube_license_properties();

                if (!$license || !isset($license['properties'])) {
                    Log::error('Invalid or missing ionCube license');
                    return false;
                }

                $props = $license['properties'];

                // 3. Validate expiration date
                if (!$this->validateExpiry($props)) {
                    Log::warning('License expired');
                    return false;
                }

                // 4. Validate domain or IP
                if (!$this->validateDomainOrIP($props)) {
                    Log::warning('Domain/IP mismatch');
                    return false;
                }

                // 5. Validate hardware fingerprint (optional but strong)
                if (!$this->validateHardware($props)) {
                    Log::warning('Hardware fingerprint mismatch');
                    return false;
                }

                // 6. Optional online validation
                $this->pingLicenseServer($props);

                return true;

            } catch (\Throwable $e) {
                Log::error('License validation exception: ' . $e->getMessage());
                return false;
            }
        });
    }

    /**
     * Expiration validation
     */
    protected function validateExpiry(array $props): bool
    {
        if (!isset($props['expiry_date'])) {
            return true; // perpetual license
        }

        return strtotime($props['expiry_date']) >= time();
    }

    /**
     * Domain / IP validation
     */
    protected function validateDomainOrIP(array $props): bool
    {
        $currentDomain = request()->getHost();
        $currentIP     = request()->server('SERVER_ADDR');

        if (isset($props['allowed_domain'])) {
            return $props['allowed_domain'] === $currentDomain;
        }

        if (isset($props['allowed_ip'])) {
            return $props['allowed_ip'] === $currentIP;
        }

        return true;
    }

    /**
     * Hardware fingerprint validation
     */
    protected function validateHardware(array $props): bool
    {
        if (!isset($props['hardware_id'])) {
            return true;
        }

        $currentFingerprint = $this->generateHardwareFingerprint();

        return hash_equals($props['hardware_id'], $currentFingerprint);
    }

    /**
     * Generate hardware fingerprint
     */
    protected function generateHardwareFingerprint(): string
    {
        $data = [];

        $data[] = php_uname('n'); // hostname
        $data[] = php_uname('m'); // machine type
        $data[] = PHP_OS;

        // Linux MAC address
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $mac = shell_exec("cat /sys/class/net/*/address 2>/dev/null");
            if ($mac) {
                $data[] = trim($mac);
            }
        }

        return hash('sha256', implode('|', $data));
    }

    /**
     * Optional online validation (fails silently)
     */
    protected function pingLicenseServer(array $props): void
    {
        if (!isset($props['license_key'])) {
            return;
        }

        try {
            @file_get_contents(
                'https://your-license-server.com/api/validate?key=' .
                urlencode($props['license_key'])
            );
        } catch (\Throwable $e) {
            // Silent fail – do NOT block app
        }
    }
}
