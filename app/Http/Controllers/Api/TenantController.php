<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TenantController extends Controller
{
    public function show(Request $request, $id)
    {
        $tenant = Tenant::with('clusterNode')->where('remote_tenant_id', $id)->orWhere('id', $id)->first();

        if (!$tenant) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tenant not found'
            ], 404);
        }

        $node = $tenant->clusterNode;
        if (!$node) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tenant is not assigned to any master node'
            ], 400);
        }

        $baseUrl = rtrim($node->endpoint_url, '/');
        $remoteId = $tenant->remote_tenant_id ?: $tenant->id;

        if (str_ends_with($baseUrl, '/api/central/v1')) {
            $endpoint = $baseUrl . '/tenants/' . urlencode($remoteId);
        } elseif (str_contains($baseUrl, '/api/')) {
            $endpoint = preg_replace('#/api/.*$#', '/api/central/v1/tenants/' . urlencode($remoteId), $baseUrl);
        } else {
            $endpoint = $baseUrl . '/api/central/v1/tenants/' . urlencode($remoteId);
        }

        $queryParams = [];
        if ($request->boolean('with_stats')) {
            $queryParams['with_stats'] = 'true';
        }

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $node->api_secret,
                'Accept' => 'application/json',
            ])->timeout(15)->get($endpoint, $queryParams);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data dari server master.',
                'master_response' => $response->json()
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Koneksi ke server master terputus atau timeout: ' . $e->getMessage()
            ], 500);
        }
    }
}
