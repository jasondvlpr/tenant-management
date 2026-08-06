<?php

namespace App\Http\Controllers;

use App\Models\ApiLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class CloudflareController extends Controller
{
    public function index()
    {
        $email = SystemSetting::where('key', 'cf_email')->value('value') ?? '';
        $apiKey = SystemSetting::where('key', 'cf_api_key')->value('value') ?? (SystemSetting::where('key', 'cf_api_token')->value('value') ?? '');
        $accountId = SystemSetting::where('key', 'cf_account_id')->value('value') ?? '';
        
        // Filter API logs for Cloudflare specific mutations
        $logs = ApiLog::where('cluster_name', 'like', '%Cloudflare%')->latest()->take(30)->get();

        return view('admin.cloudflare.index', compact('email', 'apiKey', 'accountId', 'logs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cf_email' => 'required|email',
            'cf_api_key' => 'required|string',
            'cf_account_id' => 'nullable|string',
        ]);

        SystemSetting::updateOrCreate(['key' => 'cf_email'], ['value' => $request->input('cf_email')]);
        SystemSetting::updateOrCreate(['key' => 'cf_api_key'], ['value' => $request->input('cf_api_key')]);
        SystemSetting::updateOrCreate(['key' => 'cf_api_token'], ['value' => $request->input('cf_api_key')]); // Fallback synchronization
        SystemSetting::updateOrCreate(['key' => 'cf_account_id'], ['value' => $request->input('cf_account_id') ?? '']);

        return redirect()->back()->with('success', 'Kredensial Cloudflare Global API Key dan Email berhasil disimpan!');
    }
}
