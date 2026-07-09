<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GatewaysController extends Controller
{
    /**
     * Show the payment gateways settings form.
     */
    public function edit(Request $request): Response
    {
        // Only admins can access gateways
        abort_if(! $request->user()->isAdmin(), 403);

        $systemSetting = DB::table('system')->where('key', 'payment_gateways')->first();
        $storageFormat = 'json';
        $savedGateways = [];

        if ($systemSetting) {
            $val = $systemSetting->value;
            if ($this->isSerialized($val)) {
                $storageFormat = 'serialize';
                $savedGateways = @unserialize($val);
            } else {
                $savedGateways = json_decode($val, true);
            }
        }

        // Default structure for payment gateways if not set
        $defaultGateways = [
            'stripe' => [
                'enabled' => false,
                'publishable_key' => '',
                'secret_key' => '',
            ],
            'sslcommerz' => [
                'enabled' => false,
                'store_id' => '',
                'store_password' => '',
                'merchant_id' => '',
                'mode' => 'live',
            ],
            'cod' => [
                'enabled' => true,
            ],
        ];

        $gateways = array_merge($defaultGateways, is_array($savedGateways) ? $savedGateways : []);

        return Inertia::render('settings/Gateways', [
            'gateways' => $gateways,
            'storage_format' => $storageFormat,
        ]);
    }

    /**
     * Update the payment gateways settings.
     */
    public function update(Request $request): RedirectResponse
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $request->validate([
            'storage_format' => 'required|in:json,serialize',
            'stripe.enabled' => 'required|boolean',
            'stripe.publishable_key' => 'nullable|string|required_if:stripe.enabled,true',
            'stripe.secret_key' => 'nullable|string|required_if:stripe.enabled,true',
            'sslcommerz.enabled' => 'required|boolean',
            'sslcommerz.store_id' => 'nullable|string|required_if:sslcommerz.enabled,true',
            'sslcommerz.store_password' => 'nullable|string|required_if:sslcommerz.enabled,true',
            'sslcommerz.merchant_id' => 'nullable|string',
            'sslcommerz.mode' => 'nullable|string|required_if:sslcommerz.enabled,true|in:live,sandbox',
            'cod.enabled' => 'required|boolean',
        ]);

        $gatewaysData = [
            'stripe' => [
                'enabled' => (bool) $request->input('stripe.enabled'),
                'publishable_key' => $request->input('stripe.publishable_key', ''),
                'secret_key' => $request->input('stripe.secret_key', ''),
            ],
            'sslcommerz' => [
                'enabled' => (bool) $request->input('sslcommerz.enabled'),
                'store_id' => $request->input('sslcommerz.store_id', ''),
                'store_password' => $request->input('sslcommerz.store_password', ''),
                'merchant_id' => $request->input('sslcommerz.merchant_id', ''),
                'mode' => $request->input('sslcommerz.mode', 'live'),
            ],
            'cod' => [
                'enabled' => (bool) $request->input('cod.enabled'),
            ],
        ];

        $storageFormat = $request->input('storage_format', 'json');
        $storedValue = $storageFormat === 'serialize' ? serialize($gatewaysData) : json_encode($gatewaysData);

        DB::table('system')->updateOrInsert(
            ['key' => 'payment_gateways'],
            ['value' => $storedValue]
        );

        return back()->with('toast', [
            'type' => 'success',
            'message' => '💳 Payment gateways updated successfully!',
        ]);
    }

    /**
     * Check if a value is serialized PHP data.
     */
    private function isSerialized($value): bool
    {
        if (! is_string($value)) {
            return false;
        }
        $data = trim($value);
        if ('N;' === $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        $lastc = substr($data, -1);
        if (';' !== $lastc && '}' !== $lastc) {
            return false;
        }
        $token = $data[0];
        switch ($token) {
            case 's':
                if ('"' !== substr($data, -2, 1)) {
                    return false;
                }
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                return (bool) preg_match("/^{$token}:[0-9.E-]+;/s", $data);
        }
        return false;
    }
}
