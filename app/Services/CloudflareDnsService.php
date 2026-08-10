<?php

namespace App\Services;

use App\Models\ApiLog;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;

class CloudflareDnsService
{
    /**
     * Get authentication headers for Cloudflare Global API Key or Token.
     */
    private function getHeaders(): array
    {
        $email = SystemSetting::where('key', 'cf_email')->value('value') ?? '';
        $apiKey = SystemSetting::where('key', 'cf_api_key')->value('value') ?? (SystemSetting::where('key', 'cf_api_token')->value('value') ?? '');

        if (!empty($email)) {
            // Global API Key mode (X-Auth-Email & X-Auth-Key)
            return [
                'X-Auth-Email' => $email,
                'X-Auth-Key' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];
        }

        // Fallback to Bearer Token mode
        return [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Dynamically get existing Cloudflare Zone Info or create a brand new domain zone in Cloudflare.
     */
    public function getOrCreateZoneInfo(string $domainName, array $headers): array
    {
        if (empty($headers['X-Auth-Key']) && empty($headers['Authorization'])) {
            return ['id' => null, 'status' => 'error', 'name_servers' => []];
        }

        $startTime = microtime(true);

        // Step 1: Check if domain or its parent zone already exists in user's Cloudflare account
        try {
            // Coba cari domain exact match dulu untuk menghindari masalah limit 50 per_page
            $response = Http::withHeaders($headers)
                ->timeout(15)
                ->get('https://api.cloudflare.com/client/v4/zones', [
                    'name' => strtolower($domainName)
                ]);

            if ($response->successful() && !empty($response->json('result'))) {
                $zone = $response->json('result.0');
                return [
                    'id' => $zone['id'],
                    'status' => $zone['status'] ?? 'pending',
                    'name_servers' => $zone['name_servers'] ?? [],
                    'name' => $zone['name']
                ];
            }

            // Jika tidak ketemu exact, coba fallback ambil semua (limit 50) untuk cari subdomain
            $response = Http::withHeaders($headers)
                ->timeout(15)
                ->get('https://api.cloudflare.com/client/v4/zones', [
                    'per_page' => 50
                ]);

            if ($response->successful()) {
                $zones = $response->json('result', []);
                foreach ($zones as $zone) {
                    $zoneName = $zone['name'];
                    if (strtolower($domainName) === strtolower($zoneName) || str_ends_with(strtolower($domainName), '.' . strtolower($zoneName))) {
                        return [
                            'id' => $zone['id'],
                            'status' => $zone['status'] ?? 'pending',
                            'name_servers' => $zone['name_servers'] ?? [],
                            'name' => $zone['name']
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            // Continue to creation attempt if inspection fails
        }

        // Step 2: Since domain is new and not found in Cloudflare, we must add/register it as a new Zone!
        $accountId = SystemSetting::where('key', 'cf_account_id')->value('value');
        if (empty($accountId) || str_contains($accountId, 'mock')) {
            try {
                $accRes = Http::withHeaders($headers)->timeout(15)->get('https://api.cloudflare.com/client/v4/accounts');
                if ($accRes->successful() && !empty($accRes->json('result.0.id'))) {
                    $accountId = $accRes->json('result.0.id');
                    SystemSetting::updateOrCreate(['key' => 'cf_account_id'], ['value' => $accountId]);
                }
            } catch (\Exception $e) {
                // Continue with whatever accountId exists
            }
        }

        if (empty($accountId)) {
            ApiLog::create([
                'method' => 'POST',
                'endpoint' => 'https://api.cloudflare.com/client/v4/zones',
                'cluster_name' => 'Cloudflare API',
                'tenant_name' => $domainName . ' (New Zone Registration)',
                'status_code' => 400,
                'status_text' => '400 Missing Cloudflare Account ID',
                'latency_ms' => '0ms',
                'request_body' => json_encode(['name' => $domainName], JSON_PRETTY_PRINT),
                'response_body' => json_encode(['error' => 'Gagal menambahkan domain baru ke Cloudflare karena Cloudflare Account ID tidak ditemukan atau Global API Key invalid.'], JSON_PRETTY_PRINT),
            ]);
            return ['id' => null, 'status' => 'error', 'name_servers' => []];
        }

        // Step 3: Send POST request to add the domain to Cloudflare
        $zonePayload = [
            'name' => strtolower($domainName),
            'account' => ['id' => $accountId],
            'jump_start' => true
        ];

        try {
            $createRes = Http::withHeaders($headers)
                ->timeout(30)
                ->post('https://api.cloudflare.com/client/v4/zones', $zonePayload);

            // If Cloudflare rejects because target is a subdomain (e.g., toko01.mywebsite.com), extract root domain and retry
            if (!$createRes->successful() && str_contains($createRes->body(), 'not a registered domain')) {
                $parts = explode('.', strtolower($domainName));
                if (count($parts) >= 3) {
                    $rootDomain = implode('.', array_slice($parts, -2));
                    $zonePayload['name'] = $rootDomain;
                    $createRes = Http::withHeaders($headers)->timeout(30)->post('https://api.cloudflare.com/client/v4/zones', $zonePayload);
                    
                    if (!$createRes->successful() && count($parts) >= 4 && str_contains($createRes->body(), 'not a registered domain')) {
                        $rootDomain = implode('.', array_slice($parts, -3));
                        $zonePayload['name'] = $rootDomain;
                        $createRes = Http::withHeaders($headers)->timeout(30)->post('https://api.cloudflare.com/client/v4/zones', $zonePayload);
                    }
                }
            }

            $latency = round((microtime(true) - $startTime) * 1000) . 'ms';
            $statusCode = $createRes->status();
            $statusText = $statusCode . ' ' . ($createRes->successful() ? 'Created New Domain Zone' : 'Zone Registration Failed');
            $responseBody = json_decode($createRes->body(), true) ? json_encode($createRes->json(), JSON_PRETTY_PRINT) : $createRes->body();

            ApiLog::create([
                'method' => 'POST',
                'endpoint' => 'https://api.cloudflare.com/client/v4/zones',
                'cluster_name' => 'Cloudflare API',
                'tenant_name' => $zonePayload['name'] . ' (New Domain Zone Registration)',
                'status_code' => $statusCode,
                'status_text' => $statusText,
                'latency_ms' => $latency,
                'request_body' => json_encode($zonePayload, JSON_PRETTY_PRINT),
                'response_body' => $responseBody,
            ]);

            if ($createRes->successful()) {
                $result = $createRes->json('result', []);
                return [
                    'id' => $result['id'] ?? null,
                    'status' => $result['status'] ?? 'pending',
                    'name_servers' => $result['name_servers'] ?? [],
                    'name' => $result['name'] ?? $domainName
                ];
            }
        } catch (\Exception $e) {
            $latency = round((microtime(true) - $startTime) * 1000) . 'ms';
            ApiLog::create([
                'method' => 'POST',
                'endpoint' => 'https://api.cloudflare.com/client/v4/zones',
                'cluster_name' => 'Cloudflare API',
                'tenant_name' => $domainName . ' (New Zone Exception)',
                'status_code' => 500,
                'status_text' => '500 Zone Connection Exception',
                'latency_ms' => $latency,
                'request_body' => json_encode($zonePayload, JSON_PRETTY_PRINT),
                'response_body' => json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT),
            ]);
        }

        return ['id' => null, 'status' => 'error', 'name_servers' => []];
    }

    /**
     * Get zone ID shortcut for deletion methods.
     */
    public function getOrCreateZoneId(string $domainName, array $headers): ?string
    {
        $info = $this->getOrCreateZoneInfo($domainName, $headers);
        return $info['id'] ?? null;
    }

    /**
     * Check live Cloudflare status and name servers for a specific domain/zone.
     */
    public function checkZoneStatus(?string $zoneId, string $domainName): array
    {
        $headers = $this->getHeaders();
        if (empty($zoneId)) {
            $info = $this->getOrCreateZoneInfo($domainName, $headers);
            $info['success'] = !empty($info['id']);
            return $info;
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout(15)
                ->get("https://api.cloudflare.com/client/v4/zones/{$zoneId}");

            if ($response->successful()) {
                $result = $response->json('result', []);
                return [
                    'id' => $result['id'] ?? $zoneId,
                    'status' => $result['status'] ?? 'pending',
                    'name_servers' => $result['name_servers'] ?? [],
                    'name' => $result['name'] ?? $domainName,
                    'success' => true
                ];
            }
        } catch (\Exception $e) {
            // Ignore timeout/connection error
        }

        return ['success' => false, 'error' => 'Gagal berkomunikasi dengan Cloudflare API saat memeriksa status propagasi Name Server.'];
    }

    /**
     * Register an A or CNAME Record in Cloudflare for a Tenant or Domain Alias.
     */
    public function syncRecord(string $recordName, string $recordType, string $targetContent, bool $proxied = true, string $tenantReference = 'System DNS', ?string $zoneId = null): array
    {
        $headers = $this->getHeaders();
        
        if ($zoneId) {
            $zoneInfo = ['id' => $zoneId];
        } else {
            $zoneInfo = $this->getOrCreateZoneInfo($recordName, $headers);
        }

        if (empty($zoneInfo['id'])) {
            return ['success' => false, 'error' => 'Gagal mendaftar atau mendapatkan Zone ID dari Cloudflare.'];
        }

        $zoneId = $zoneInfo['id'];
        $endpoint = "https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records";

        $payload = [
            'type' => strtoupper($recordType),
            'name' => $recordName,
            'content' => $targetContent,
            'ttl' => 1, // Auto
            'proxied' => $proxied
        ];

        $startTime = microtime(true);

        try {
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->post($endpoint, $payload);

            $latency = round((microtime(true) - $startTime) * 1000) . 'ms';
            $isSuccess = $response->successful();
            $statusCode = $response->status();
            $statusText = $statusCode . ' ' . ($isSuccess ? 'OK (DNS Record Created)' : 'API Error');

            if (!$isSuccess) {
                $errJson = $response->json();
                if (!empty($errJson['errors']) && is_array($errJson['errors'])) {
                    foreach ($errJson['errors'] as $error) {
                        if (($error['code'] ?? null) == 81058) {
                            $isSuccess = true;
                            $statusText = $statusCode . ' OK (Identical Record Exists)';
                            break;
                        }
                    }
                }
            }

            $responseBody = json_decode($response->body(), true) ? json_encode($response->json(), JSON_PRETTY_PRINT) : $response->body();

            ApiLog::create([
                'method' => 'POST',
                'endpoint' => $endpoint,
                'cluster_name' => 'Cloudflare API',
                'tenant_name' => $tenantReference . " ({$recordType} Record)",
                'status_code' => $statusCode,
                'status_text' => $statusText,
                'latency_ms' => $latency,
                'request_body' => json_encode($payload, JSON_PRETTY_PRINT),
                'response_body' => $responseBody,
            ]);

            return [
                'success' => $isSuccess,
                'zone_id' => $zoneId,
                'zone_status' => $zoneInfo['status'] ?? 'pending',
                'name_servers' => $zoneInfo['name_servers'] ?? [],
            ];

        } catch (\Exception $e) {
            $latency = round((microtime(true) - $startTime) * 1000) . 'ms';
            
            ApiLog::create([
                'method' => 'POST',
                'endpoint' => $endpoint,
                'cluster_name' => 'Cloudflare API',
                'tenant_name' => $tenantReference . ' (Connection Error)',
                'status_code' => 500,
                'status_text' => '500 Connection Exception',
                'latency_ms' => $latency,
                'request_body' => json_encode($payload, JSON_PRETTY_PRINT),
                'response_body' => json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Remove a DNS Record from Cloudflare.
     */
    public function deleteRecord(string $recordName, string $tenantReference = 'System DNS'): bool
    {
        $headers = $this->getHeaders();
        $zoneId = $this->getOrCreateZoneId($recordName, $headers);

        if (empty($zoneId)) {
            return false;
        }

        $startTime = microtime(true);

        try {
            // Step 1: Inspect the zone to check if the tenant domain IS the root domain zone in Cloudflare
            $zoneInfoRes = Http::withHeaders($headers)
                               ->timeout(15)
                               ->get("https://api.cloudflare.com/client/v4/zones/{$zoneId}");

            if ($zoneInfoRes->successful()) {
                $zoneName = $zoneInfoRes->json('result.name');
                if (strtolower($zoneName) === strtolower($recordName)) {
                    // Delete the ENTIRE DOMAIN (ZONE) from user's Cloudflare account!
                    $delZoneRes = Http::withHeaders($headers)
                                      ->timeout(30)
                                      ->delete("https://api.cloudflare.com/client/v4/zones/{$zoneId}");

                    $latency = round((microtime(true) - $startTime) * 1000) . 'ms';
                    ApiLog::create([
                        'method' => 'DELETE',
                        'endpoint' => "https://api.cloudflare.com/client/v4/zones/{$zoneId}",
                        'cluster_name' => 'Cloudflare API',
                        'tenant_name' => $tenantReference . ' (Remove Full Domain Zone)',
                        'status_code' => $delZoneRes->status(),
                        'status_text' => $delZoneRes->status() . ' ' . ($delZoneRes->successful() ? 'Domain Removed from Cloudflare' : 'Zone Deletion Error'),
                        'latency_ms' => $latency,
                        'request_body' => json_encode(['zone_id' => $zoneId, 'domain' => $recordName], JSON_PRETTY_PRINT),
                        'response_body' => json_decode($delZoneRes->body(), true) ? json_encode($delZoneRes->json(), JSON_PRETTY_PRINT) : $delZoneRes->body(),
                    ]);

                    return $delZoneRes->successful();
                }
            }

            // Step 2: If it's a subdomain (e.g. client01.site.com under zone site.com), delete only the specific DNS record
            $searchEndpoint = "https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records";
            $searchRes = Http::withHeaders($headers)
                             ->timeout(20)
                             ->get($searchEndpoint, ['name' => $recordName]);

            if (!$searchRes->successful()) {
                return false;
            }

            $records = $searchRes->json('result', []);
            $deletedCount = 0;

            foreach ($records as $record) {
                $recId = $record['id'];
                $delEndpoint = "https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records/{$recId}";
                
                $delRes = Http::withHeaders($headers)
                              ->timeout(20)
                              ->delete($delEndpoint);

                if ($delRes->successful()) {
                    $deletedCount++;
                }

                $latency = round((microtime(true) - $startTime) * 1000) . 'ms';
                ApiLog::create([
                    'method' => 'DELETE',
                    'endpoint' => $delEndpoint,
                    'cluster_name' => 'Cloudflare API',
                    'tenant_name' => $tenantReference . " (Delete {$record['type']} Record)",
                    'status_code' => $delRes->status(),
                    'status_text' => $delRes->status() . ' ' . ($delRes->successful() ? 'Terminated' : 'Error'),
                    'latency_ms' => $latency,
                    'request_body' => json_encode(['record_id' => $recId, 'domain' => $recordName], JSON_PRETTY_PRINT),
                    'response_body' => json_decode($delRes->body(), true) ? json_encode($delRes->json(), JSON_PRETTY_PRINT) : $delRes->body(),
                ]);
            }

            return $deletedCount > 0;

        } catch (\Exception $e) {
            $latency = round((microtime(true) - $startTime) * 1000) . 'ms';
            ApiLog::create([
                'method' => 'DELETE',
                'endpoint' => "https://api.cloudflare.com/client/v4/zones/{$zoneId}",
                'cluster_name' => 'Cloudflare API',
                'tenant_name' => $tenantReference . ' (Delete Exception)',
                'status_code' => 500,
                'status_text' => '500 Exception',
                'latency_ms' => $latency,
                'request_body' => json_encode(['domain' => $recordName], JSON_PRETTY_PRINT),
                'response_body' => json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT),
            ]);
            return false;
        }
    }
}
