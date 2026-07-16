<?php

namespace Modules\Shipment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\BusinessContextService;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ShipmentController extends Controller
{
    protected \Modules\Shipment\Services\Shipment\Pathao\PathaoService $pathaoService;

    public function __construct(\Modules\Shipment\Services\Shipment\Pathao\PathaoService $pathaoService)
    {
        $this->pathaoService = $pathaoService;
    }

    /**
     * Update shipping status with optional tracking information.
     * Supports transitions: ordered → packed → shipped → delivered → cancelled
     */
    public function updateShipping(Request $request, $currentTeam, $id): RedirectResponse
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $request->validate([
            'shipping_status' => 'required|in:ordered,packed,shipped,delivered,cancelled',
            'tracking_number' => 'nullable|string|max:100',
            'tracking_url' => 'nullable|url|max:500',
            'courier' => 'nullable|string|max:100',
        ]);

        $transaction = Transaction::findOrFail($id);
        $newStatus = $request->input('shipping_status');

        $updateData = [
            'shipping_status' => $newStatus,
            'tracking_number' => $request->input('tracking_number') ?: $transaction->tracking_number,
            'tracking_url' => $request->input('tracking_url') ?: $transaction->tracking_url,
            'courier' => $request->input('courier') ?: $transaction->courier,
        ];

        // Auto-set timestamps on key transitions
        if ($newStatus === 'shipped' && ! $transaction->shipped_at) {
            $updateData['shipped_at'] = now();
        }
        if ($newStatus === 'delivered' && ! $transaction->delivered_at) {
            $updateData['delivered_at'] = now();
        }

        // Auto-complete the order when delivered
        if ($newStatus === 'delivered') {
            $updateData['status'] = 'completed';
            $updateData['payment_status'] = 'paid';

            // Ensure a payment record exists
            $payment = DB::table('transaction_payments')->where('transaction_id', $transaction->id)->first();
            if (! $payment) {
                $amount = (float) $transaction->final_total;
                $paymentType = TransactionPayment::determinePaymentType($amount);
                DB::table('transaction_payments')->insert([
                    'transaction_id' => $transaction->id,
                    'business_id' => $transaction->business_id ?? 1,
                    'amount' => $amount,
                    'method' => 'cash',
                    'payment_type' => $paymentType,
                    'paid_on' => now(),
                    'created_by' => $request->user()->id,
                    'status' => 'success',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            Transaction::checkAndGenerateSell($transaction->id);
        }

        // Shipped → also mark payment if COD
        if ($newStatus === 'shipped') {
            $updateData['status'] = 'completed';
            $updateData['payment_status'] = 'paid';

            $payment = DB::table('transaction_payments')->where('transaction_id', $transaction->id)->first();
            if (! $payment) {
                $amount = (float) $transaction->final_total;
                $paymentType = TransactionPayment::determinePaymentType($amount);
                DB::table('transaction_payments')->insert([
                    'transaction_id' => $transaction->id,
                    'business_id' => $transaction->business_id ?? 1,
                    'amount' => $amount,
                    'method' => 'cash',
                    'payment_type' => $paymentType,
                    'paid_on' => now(),
                    'created_by' => $request->user()->id,
                    'status' => 'success',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('transaction_payments')
                    ->where('transaction_id', $transaction->id)
                    ->update(['status' => 'success', 'updated_at' => now()]);
            }
            Transaction::checkAndGenerateSell($transaction->id);
        }

        if ($newStatus === 'cancelled') {
            $updateData['status'] = 'cancelled';
            $updateData['payment_status'] = 'cancelled';
        }

        $transaction->update($updateData);

        $statusLabels = [
            'ordered' => '📋 Order Confirmed',
            'packed' => '📦 Order Packed',
            'shipped' => '🚚 Order Shipped',
            'delivered' => '✅ Order Delivered',
            'cancelled' => '❌ Order Cancelled',
        ];

        return back()->with('toast', [
            'type' => 'success',
            'message' => ($statusLabels[$newStatus] ?? '✅ Updated')." — Order #{$transaction->ref_no}",
        ]);
    }

    /**
     * Ship and mark order as paid.
     */
    public function shipOrder(Request $request, $currentTeam, $id): RedirectResponse
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $transaction = Transaction::findOrFail($id);
        $transaction->update([
            'status' => 'completed',
            'payment_status' => 'paid',
            'shipping_status' => 'shipped',
        ]);

        // Insert or update transaction payment record
        $payment = DB::table('transaction_payments')->where('transaction_id', $transaction->id)->first();
        if (! $payment) {
            $amount = (float) $transaction->final_total;
            $paymentType = TransactionPayment::determinePaymentType($amount);

            DB::table('transaction_payments')->insert([
                'transaction_id' => $transaction->id,
                'business_id' => $transaction->business_id ?? 1,
                'amount' => $amount,
                'method' => 'cash',
                'payment_type' => $paymentType,
                'paid_on' => now(),
                'created_by' => $request->user()->id,
                'status' => 'success',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('transaction_payments')
                ->where('transaction_id', $transaction->id)
                ->update([
                    'status' => 'success',
                    'updated_at' => now(),
                ]);
        }

        Transaction::checkAndGenerateSell($transaction->id);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "🚚 Order #{$transaction->ref_no} marked as Shipped and Paid!",
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('shipment::index');
    }

    /**
     * Show the shipping settings form.
     */
    public function edit(Request $request): Response
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $businessId = BusinessContextService::getCurrentBusinessId() ?: ($request->user()->business_id ?? 1);
        $setting = DB::table('settings')
            ->where('business_id', $businessId)
            ->where('key', 'shipping_settings')
            ->first();
        $saved = $setting ? json_decode($setting->value, true) : [];

        $defaults = [
            'free_shipping_enabled' => false,
            'free_shipping_threshold' => 0.00,
            'flat_rate_enabled' => true,
            'flat_rate_amount' => 5.00,
            'local_pickup_enabled' => false,
            'local_pickup_label' => 'Local Pickup',
            'default_courier' => '',
            'estimated_delivery_days' => 3,
            'tracking_base_url' => '',
            'zones' => [
                ['name' => 'Domestic', 'rate' => 5.00,  'enabled' => true],
                ['name' => 'International', 'rate' => 20.00, 'enabled' => false],
            ],
        ];

        $settings = array_merge($defaults, is_array($saved) ? $saved : []);

        $pathaoKeys = [
            'pathao_client_id',
            'pathao_client_secret',
            'pathao_username',
            'pathao_password',
            'pathao_store_id',
            'pathao_base_url',
            'pathao_city_id',
            'pathao_zone_id',
            'pathao_area_id',
            'pathao_default_item_type',
            'pathao_default_item_weight',
            'pathao_default_special_instruction',
            'pathao_enabled',
        ];

        $pathaoSettings = [];
        foreach ($pathaoKeys as $key) {
            $pathaoSettings[$key] = setting($businessId, $key, '');
        }
        $pathaoSettings['pathao_enabled'] = filter_var($pathaoSettings['pathao_enabled'], FILTER_VALIDATE_BOOLEAN);

        $cities = [];
        $zones = [];
        $areas = [];

        try {
            if ($pathaoSettings['pathao_enabled'] && $pathaoSettings['pathao_client_id']) {
                $cities = $this->pathaoService->getCities((int) $businessId);
                if ($pathaoSettings['pathao_city_id']) {
                    $zones = $this->pathaoService->getZones((int) $businessId, (int) $pathaoSettings['pathao_city_id']);
                }
                if ($pathaoSettings['pathao_zone_id']) {
                    $areas = $this->pathaoService->getAreas((int) $businessId, (int) $pathaoSettings['pathao_zone_id']);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to prefetch locations for Pathao settings: " . $e->getMessage());
        }

        return Inertia::render('Shipment::settings/Shipping', [
            'shippingSettings' => $settings,
            'pathaoSettings' => $pathaoSettings,
            'pathaoCities' => $cities,
            'pathaoZones' => $zones,
            'pathaoAreas' => $areas,
        ]);
    }

    /**
     * Save the shipping settings.
     */
    public function update(Request $request): RedirectResponse
    {
        abort_if(! $request->user()->isAdmin(), 403);

        $request->validate([
            'free_shipping_enabled' => 'required|boolean',
            'free_shipping_threshold' => 'required|numeric|min:0',
            'flat_rate_enabled' => 'required|boolean',
            'flat_rate_amount' => 'required|numeric|min:0',
            'local_pickup_enabled' => 'required|boolean',
            'local_pickup_label' => 'nullable|string|max:80',
            'default_courier' => 'nullable|string|max:100',
            'estimated_delivery_days' => 'required|integer|min:0|max:365',
            'tracking_base_url' => 'nullable|url|max:500',
            'zones' => 'nullable|array|max:20',
            'zones.*.name' => 'required_with:zones|string|max:80',
            'zones.*.rate' => 'required_with:zones|numeric|min:0',
            'zones.*.enabled' => 'required_with:zones|boolean',
            
            // Pathao validations
            'pathao_enabled' => 'nullable|boolean',
            'pathao_client_id' => 'nullable|string|max:255',
            'pathao_client_secret' => 'nullable|string|max:255',
            'pathao_username' => 'nullable|string|max:255',
            'pathao_password' => 'nullable|string|max:255',
            'pathao_store_id' => 'nullable|string|max:255',
            'pathao_base_url' => 'nullable|url|max:255',
            'pathao_city_id' => 'nullable|integer',
            'pathao_zone_id' => 'nullable|integer',
            'pathao_area_id' => 'nullable|integer',
            'pathao_default_item_type' => 'nullable|string|max:50',
            'pathao_default_item_weight' => 'nullable|numeric|min:0',
            'pathao_default_special_instruction' => 'nullable|string|max:500',
        ]);

        $businessId = BusinessContextService::getCurrentBusinessId() ?: ($request->user()->business_id ?? 1);

        $data = [
            'free_shipping_enabled' => (bool) $request->input('free_shipping_enabled'),
            'free_shipping_threshold' => (float) $request->input('free_shipping_threshold'),
            'flat_rate_enabled' => (bool) $request->input('flat_rate_enabled'),
            'flat_rate_amount' => (float) $request->input('flat_rate_amount'),
            'local_pickup_enabled' => (bool) $request->input('local_pickup_enabled'),
            'local_pickup_label' => $request->input('local_pickup_label', 'Local Pickup'),
            'default_courier' => $request->input('default_courier', ''),
            'estimated_delivery_days' => (int) $request->input('estimated_delivery_days', 3),
            'tracking_base_url' => $request->input('tracking_base_url', ''),
            'zones' => collect($request->input('zones', []))->map(fn ($z) => [
                'name' => $z['name'] ?? '',
                'rate' => (float) ($z['rate'] ?? 0),
                'enabled' => (bool) ($z['enabled'] ?? false),
            ])->values()->all(),
        ];

        DB::table('settings')->updateOrInsert(
            ['business_id' => $businessId, 'key' => 'shipping_settings'],
            ['value' => json_encode($data), 'updated_at' => now()]
        );

        // Update Pathao settings
        updateSetting($businessId, 'pathao_enabled', $request->boolean('pathao_enabled') ? '1' : '0');
        
        $pathaoKeys = [
            'pathao_client_id',
            'pathao_client_secret',
            'pathao_username',
            'pathao_password',
            'pathao_store_id',
            'pathao_base_url',
            'pathao_city_id',
            'pathao_zone_id',
            'pathao_area_id',
            'pathao_default_item_type',
            'pathao_default_item_weight',
            'pathao_default_special_instruction',
        ];

        foreach ($pathaoKeys as $key) {
            if ($request->has($key)) {
                updateSetting($businessId, $key, (string) $request->input($key));
            }
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => '🚚 Shipping settings saved successfully!',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('shipment::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('shipment::show');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}

    public function getPathaoCities(Request $request)
    {
        $businessId = BusinessContextService::getCurrentBusinessId() ?: ($request->user()->business_id ?? 1);
        try {
            $cities = $this->pathaoService->getCities((int) $businessId);
            return response()->json(['success' => true, 'data' => $cities]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function getPathaoZones(Request $request, $currentTeam, $cityId)
    {
        $businessId = BusinessContextService::getCurrentBusinessId() ?: ($request->user()->business_id ?? 1);
        try {
            $zones = $this->pathaoService->getZones((int) $businessId, (int) $cityId);
            return response()->json(['success' => true, 'data' => $zones]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function getPathaoAreas(Request $request, $currentTeam, $zoneId)
    {
        $businessId = BusinessContextService::getCurrentBusinessId() ?: ($request->user()->business_id ?? 1);
        try {
            $areas = $this->pathaoService->getAreas((int) $businessId, (int) $zoneId);
            return response()->json(['success' => true, 'data' => $areas]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function estimatePathaoCharge(Request $request, $currentTeam, $transactionId)
    {
        $businessId = BusinessContextService::getCurrentBusinessId() ?: ($request->user()->business_id ?? 1);
        try {
            $data = $request->validate([
                'recipient_city' => 'required|integer',
                'recipient_zone' => 'required|integer',
                'item_weight' => 'nullable|numeric',
                'item_type' => 'nullable|integer',
                'store_id' => 'nullable|integer',
            ]);
            
            $estimate = $this->pathaoService->estimateCharge((int) $businessId, $data);
            return response()->json(['success' => true, 'data' => $estimate]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function bookPathaoShipment(Request $request, $currentTeam, $transactionId)
    {
        $businessId = BusinessContextService::getCurrentBusinessId() ?: ($request->user()->business_id ?? 1);
        try {
            $data = $request->validate([
                'recipient_city' => 'nullable|integer',
                'recipient_zone' => 'nullable|integer',
                'recipient_area' => 'nullable|integer',
                'item_weight' => 'nullable|numeric',
                'item_type' => 'nullable|integer',
                'amount_to_collect' => 'nullable|numeric',
                'special_instruction' => 'nullable|string|max:500',
            ]);

            $order = $this->pathaoService->bookShipment((int) $businessId, (int) $transactionId, $data);
            return response()->json(['success' => true, 'data' => $order]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function syncPathaoShipment(Request $request, $currentTeam, $transactionId)
    {
        $businessId = BusinessContextService::getCurrentBusinessId() ?: ($request->user()->business_id ?? 1);
        try {
            $orderData = $this->pathaoService->syncSingleShipment((int) $businessId, (int) $transactionId);
            return response()->json(['success' => true, 'data' => $orderData]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function cancelPathaoShipment(Request $request, $currentTeam, $transactionId)
    {
        $businessId = BusinessContextService::getCurrentBusinessId() ?: ($request->user()->business_id ?? 1);
        try {
            $transaction = \Modules\Cart\Models\Transaction::findOrFail($transactionId);
            if ($transaction->courier !== 'Pathao' || !$transaction->tracking_number) {
                throw new \Exception("This transaction is not booked with Pathao.");
            }

            $api = app(\Modules\Shipment\Services\Shipment\Pathao\PathaoApi::class);
            $response = $api->cancelOrder((int) $businessId, $transaction->tracking_number);

            $transaction->update([
                'shipping_status' => 'cancelled',
                'status' => 'cancelled',
                'payment_status' => 'cancelled',
                'shipping_details' => json_encode($response),
            ]);

            return response()->json(['success' => true, 'message' => 'Shipment cancelled successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
