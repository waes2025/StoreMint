<?php

declare(strict_types=1);

namespace Modules\Shipment\Services\Shipment\Pathao;

use App\Models\Contact;
use Modules\Cart\Models\Transaction;
use Modules\Shipment\Repositories\ShipmentRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\Cart\Models\TransactionPayment;
use Exception;

class PathaoService
{
    protected PathaoApi $api;
    protected ShipmentRepositoryInterface $repository;

    public function __construct(PathaoApi $api, ShipmentRepositoryInterface $repository)
    {
        $this->api = $api;
        $this->repository = $repository;
    }

    /**
     * Get list of cities (Cached for 24 hours).
     */
    public function getCities(int $businessId): array
    {
        return Cache::remember("pathao_cities_{$businessId}", now()->addHours(24), function () use ($businessId) {
            $response = $this->api->getCities($businessId);
            return $response['data']['data'] ?? $response['data'] ?? [];
        });
    }

    /**
     * Get list of zones for a city (Cached for 24 hours).
     */
    public function getZones(int $businessId, int $cityId): array
    {
        return Cache::remember("pathao_zones_{$businessId}_{$cityId}", now()->addHours(24), function () use ($businessId, $cityId) {
            $response = $this->api->getZones($businessId, $cityId);
            return $response['data']['data'] ?? $response['data'] ?? [];
        });
    }

    /**
     * Get list of areas for a zone (Cached for 24 hours).
     */
    public function getAreas(int $businessId, int $zoneId): array
    {
        return Cache::remember("pathao_areas_{$businessId}_{$zoneId}", now()->addHours(24), function () use ($businessId, $zoneId) {
            $response = $this->api->getAreas($businessId, $zoneId);
            return $response['data']['data'] ?? $response['data'] ?? [];
        });
    }

    /**
     * Get list of stores (Cached for 24 hours).
     */
    public function getStores(int $businessId): array
    {
        return Cache::remember("pathao_stores_{$businessId}", now()->addHours(24), function () use ($businessId) {
            $response = $this->api->getStores($businessId);
            return $response['data']['data'] ?? $response['data'] ?? [];
        });
    }

    /**
     * Estimate delivery charge.
     */
    public function estimateCharge(int $businessId, array $data): array
    {
        // Require minimal validation parameters
        if (empty($data['recipient_city']) || empty($data['recipient_zone'])) {
            throw new Exception("City ID and Zone ID are required to calculate delivery charge.");
        }

        $payload = [
            'store_id' => (int) ($data['store_id'] ?? setting($businessId, 'pathao_store_id')),
            'item_type' => (int) ($data['item_type'] ?? setting($businessId, 'pathao_default_item_type', '2')),
            'delivery_type' => (int) ($data['delivery_type'] ?? 48), // Default standard
            'item_weight' => (float) ($data['item_weight'] ?? setting($businessId, 'pathao_default_item_weight', '0.5')),
            'recipient_city' => (int) $data['recipient_city'],
            'recipient_zone' => (int) $data['recipient_zone'],
        ];

        $response = $this->api->calculatePrice($businessId, $payload);
        return $response['data'] ?? [];
    }

    /**
     * Auto-detect and resolve city, zone, and area IDs based on customer address fields.
     */
    public function autoDetectLocation(int $businessId, Contact $customer): array
    {
        // First check custom fields
        if ($customer->shipping_custom_field_details) {
            $custom = json_decode($customer->shipping_custom_field_details, true);
            if (is_array($custom) && isset($custom['city_id'], $custom['zone_id'], $custom['area_id'])) {
                return [
                    'city_id' => (int) $custom['city_id'],
                    'zone_id' => (int) $custom['zone_id'],
                    'area_id' => (int) $custom['area_id'],
                ];
            }
        }

        // Try to match city
        $cityId = null;
        $zoneId = null;
        $areaId = null;

        $shippingCity = trim((string) $customer->shipping_city);
        $shippingState = trim((string) $customer->shipping_state);
        $shippingAddress = trim((string) $customer->shipping_address);

        if ($shippingCity || $shippingState) {
            $cities = $this->getCities($businessId);
            // Case-insensitive check
            foreach ($cities as $city) {
                $name = strtolower($city['city_name'] ?? '');
                if (strtolower($shippingCity) === $name || strtolower($shippingState) === $name || str_contains(strtolower($shippingCity), $name)) {
                    $cityId = (int) $city['city_id'];
                    break;
                }
            }
        }

        // Fallback to default settings city
        if (!$cityId) {
            $cityId = (int) setting($businessId, 'pathao_city_id');
        }

        if ($cityId) {
            $zones = $this->getZones($businessId, $cityId);
            foreach ($zones as $zone) {
                $name = strtolower($zone['zone_name'] ?? '');
                if (str_contains(strtolower($shippingAddress), $name) || str_contains(strtolower($shippingCity), $name)) {
                    $zoneId = (int) $zone['zone_id'];
                    break;
                }
            }

            // Fallback to default zone
            if (!$zoneId) {
                $zoneId = (int) setting($businessId, 'pathao_zone_id');
            }

            if ($zoneId) {
                $areas = $this->getAreas($businessId, $zoneId);
                foreach ($areas as $area) {
                    $name = strtolower($area['area_name'] ?? '');
                    if (str_contains(strtolower($shippingAddress), $name)) {
                        $areaId = (int) $area['area_id'];
                        break;
                    }
                }

                // Fallback to default area
                if (!$areaId) {
                    $areaId = (int) setting($businessId, 'pathao_area_id');
                }
            }
        }

        // Save back resolved locations to contact if detected
        if ($cityId && $zoneId && $areaId) {
            $this->repository->updateCustomerCustomFields($customer->id, [
                'city_id' => $cityId,
                'zone_id' => $zoneId,
                'area_id' => $areaId
            ]);
        }

        return [
            'city_id' => $cityId,
            'zone_id' => $zoneId,
            'area_id' => $areaId,
        ];
    }

    /**
     * Book a shipment order on Pathao.
     */
    public function bookShipment(int $businessId, int $transactionId, array $inputData = []): array
    {
        if (!setting($businessId, 'pathao_enabled')) {
            throw new Exception("Pathao integration is disabled in business settings.");
        }

        $transaction = $this->repository->findTransaction($transactionId);
        if (!$transaction) {
            throw new Exception("Transaction not found.");
        }

        if ($transaction->courier === 'Pathao' && $transaction->tracking_number) {
            throw new Exception("Duplicate Shipment: This transaction is already booked with Pathao Consignment ID: {$transaction->tracking_number}");
        }

        $customer = $this->repository->findCustomer((int) $transaction->contact_id);
        if (!$customer) {
            throw new Exception("Customer contact not found or not of customer type.");
        }

        // Customer Name priority
        $customerName = trim((string) ($customer->name ?: "{$customer->prefix} {$customer->first_name} {$customer->middle_name} {$customer->last_name}"));
        if (!$customerName) {
            throw new Exception("Customer name is required to book a shipment.");
        }

        // Customer Mobile validation
        $customerPhone = preg_replace('/[^0-9+]/', '', trim((string) $customer->mobile));
        if (empty($customerPhone)) {
            throw new Exception("Customer mobile number is required to book a shipment.");
        }

        // Customer Address validation
        $customerAddress = trim((string) ($transaction->shipping_address ?: $customer->shipping_address));
        if (empty($customerAddress)) {
            throw new Exception("Recipient shipping address is required.");
        }

        // Resolve location IDs (auto-detect or use inputs)
        if (!empty($inputData['recipient_city']) && !empty($inputData['recipient_zone']) && !empty($inputData['recipient_area'])) {
            $cityId = (int) $inputData['recipient_city'];
            $zoneId = (int) $inputData['recipient_zone'];
            $areaId = (int) $inputData['recipient_area'];

            // Save back to customer custom fields
            $this->repository->updateCustomerCustomFields($customer->id, [
                'city_id' => $cityId,
                'zone_id' => $zoneId,
                'area_id' => $areaId
            ]);
        } else {
            $location = $this->autoDetectLocation($businessId, $customer);
            $cityId = $location['city_id'];
            $zoneId = $location['zone_id'];
            $areaId = $location['area_id'];
        }

        if (!$cityId || !$zoneId || !$areaId) {
            throw new Exception("Could not map or auto-detect a valid Pathao delivery location (City, Zone, Area). Please fill them manually.");
        }

        // Determine collection amount (if transaction is already paid, collect 0. Else collect final_total or unpaid balance)
        $amountToCollect = (float) ($transaction->final_total ?? 0);
        if ($transaction->payment_status === 'paid') {
            $amountToCollect = 0.0;
        }

        // Override collection amount from input if provided
        if (isset($inputData['amount_to_collect'])) {
            $amountToCollect = (float) $inputData['amount_to_collect'];
        }

        $storeId = (int) ($inputData['store_id'] ?? setting($businessId, 'pathao_store_id'));
        if (!$storeId) {
            // Retrieve first available store if not configured
            $stores = $this->getStores($businessId);
            if (!empty($stores)) {
                $storeId = (int) $stores[0]['store_id'];
                updateSetting($businessId, 'pathao_store_id', (string) $storeId);
            } else {
                throw new Exception("No Pathao Store ID found. Please create/configure a store in your Pathao Merchant Dashboard.");
            }
        }

        $itemType = (int) ($inputData['item_type'] ?? setting($businessId, 'pathao_default_item_type', '2'));
        $itemWeight = (float) ($inputData['item_weight'] ?? setting($businessId, 'pathao_default_item_weight', '0.5'));
        $specialInstruction = trim((string) ($inputData['special_instruction'] ?? setting($businessId, 'pathao_default_special_instruction', '')));
        
        // Build descriptions from transaction items if possible
        $itemDescription = "Order #" . ($transaction->ref_no ?: $transaction->invoice_no);

        $payload = [
            'store_id' => $storeId,
            'merchant_order_id' => (string) ($transaction->ref_no ?: $transaction->id),
            'recipient_name' => $customerName,
            'recipient_phone' => $customerPhone,
            'recipient_address' => $customerAddress,
            'recipient_city' => $cityId,
            'recipient_zone' => $zoneId,
            'recipient_area' => $areaId,
            'delivery_type' => 48, // Standard delivery
            'item_type' => $itemType,
            'special_instruction' => $specialInstruction,
            'item_quantity' => 1,
            'item_weight' => $itemWeight,
            'amount_to_collect' => $amountToCollect,
            'item_description' => $itemDescription,
        ];

        // Send booking request
        $response = $this->api->createOrder($businessId, $payload);
        $orderData = $response['data'] ?? [];

        if (empty($orderData['consignment_id'])) {
            throw new Exception("Pathao booking succeeded but consignment_id is missing in response: " . json_encode($response));
        }

        $consignmentId = (string) $orderData['consignment_id'];
        
        // Generate tracking URL
        $trackingUrl = "https://merchant.pathao.com/tracking?consignment_id=" . urlencode($consignmentId);

        // Map status
        $pathaoStatus = $orderData['order_status'] ?? 'Pending';
        $internalStatus = $this->mapPathaoStatusToInternal($pathaoStatus);

        $updateData = [
            'courier' => 'Pathao',
            'tracking_number' => $consignmentId,
            'tracking_url' => $trackingUrl,
            'shipping_status' => $internalStatus,
            'delivered_to' => $customerName,
            'shipped_at' => now(),
            'shipping_details' => json_encode($response),
        ];

        $this->repository->updateTransactionShipping($transactionId, $updateData);

        // Handle auto-completion logic if order is shipped/delivered
        $this->handleStatusStateTransitions($transactionId, $internalStatus);

        return $orderData;
    }

    /**
     * Track an order and sync its status in database.
     */
    public function syncShipmentStatus(int $businessId): int
    {
        $pendingShipments = $this->repository->getPendingShipments(50);
        $updatedCount = 0;

        foreach ($pendingShipments as $transaction) {
            try {
                $response = $this->api->trackOrder($businessId, $transaction->tracking_number);
                $orderData = $response['data'] ?? [];
                
                if (empty($orderData)) {
                    Log::warning("Pathao trackOrder returned empty data for consignment: {$transaction->tracking_number}");
                    continue;
                }

                $pathaoStatus = $orderData['order_status'] ?? '';
                if (!$pathaoStatus) {
                    continue;
                }

                $internalStatus = $this->mapPathaoStatusToInternal($pathaoStatus);
                
                // Only update if status has changed
                if ($transaction->shipping_status !== $internalStatus || json_encode($response) !== $transaction->shipping_details) {
                    $updateData = [
                        'shipping_status' => $internalStatus,
                        'shipping_details' => json_encode($response),
                    ];

                    if ($internalStatus === 'delivered' && !$transaction->delivered_at) {
                        $updateData['delivered_at'] = now();
                    }

                    $this->repository->updateTransactionShipping($transaction->id, $updateData);
                    $this->handleStatusStateTransitions($transaction->id, $internalStatus);
                    $updatedCount++;
                }

            } catch (Exception $e) {
                Log::error("Failed to sync Pathao shipment status for tracking #{$transaction->tracking_number}: " . $e->getMessage());
            }
        }

        return $updatedCount;
    }

    /**
     * Synchronize status of a single transaction shipment.
     */
    public function syncSingleShipment(int $businessId, int $transactionId): array
    {
        $transaction = $this->repository->findTransaction($transactionId);
        if (!$transaction) {
            throw new Exception("Transaction not found.");
        }

        if ($transaction->courier !== 'Pathao' || !$transaction->tracking_number) {
            throw new Exception("This transaction is not booked with Pathao.");
        }

        $response = $this->api->trackOrder($businessId, $transaction->tracking_number);
        $orderData = $response['data'] ?? [];

        if (empty($orderData)) {
            throw new Exception("Could not retrieve tracking details from Pathao.");
        }

        $pathaoStatus = $orderData['order_status'] ?? '';
        if ($pathaoStatus) {
            $internalStatus = $this->mapPathaoStatusToInternal($pathaoStatus);

            $updateData = [
                'shipping_status' => $internalStatus,
                'shipping_details' => json_encode($response),
            ];

            if ($internalStatus === 'delivered' && !$transaction->delivered_at) {
                $updateData['delivered_at'] = now();
            }

            $this->repository->updateTransactionShipping($transaction->id, $updateData);
            $this->handleStatusStateTransitions($transaction->id, $internalStatus);
        }

        return $orderData;
    }

    /**
     * Map Pathao status codes to internal Shipping Statuses.
     */
    public function mapPathaoStatusToInternal(string $pathaoStatus): string
    {
        $normalized = strtolower(trim($pathaoStatus));
        switch ($normalized) {
            case 'pending':
            case 'booked':
                return 'ordered';
            case 'picked':
            case 'arrived_at_sorting_hub':
                return 'packed';
            case 'in_transit':
            case 'in transit':
            case 'dispatched':
            case 'on_the_way':
            case 'on the way':
                return 'shipped';
            case 'delivered':
            case 'delivery_success':
                return 'delivered';
            case 'cancelled':
            case 'returned':
            case 'return_success':
                return 'cancelled';
            default:
                return 'ordered';
        }
    }

    /**
     * Auto-completes payments, orders, and sales generation when status moves to shipped/delivered/cancelled.
     */
    protected function handleStatusStateTransitions(int $transactionId, string $newStatus): void
    {
        $transaction = $this->repository->findTransaction($transactionId);
        if (!$transaction) {
            return;
        }

        $updateData = [];

        // Auto-complete the order when delivered
        if ($newStatus === 'delivered') {
            $updateData['status'] = 'completed';
            $updateData['payment_status'] = 'paid';

            // Ensure a payment record exists
            $payment = DB::table('transaction_payments')->where('transaction_id', $transaction->id)->first();
            if (!$payment) {
                $amount = (float) $transaction->final_total;
                $paymentType = TransactionPayment::determinePaymentType($amount);
                DB::table('transaction_payments')->insert([
                    'transaction_id' => $transaction->id,
                    'business_id' => $transaction->business_id ?? 1,
                    'amount' => $amount,
                    'method' => 'cash',
                    'payment_type' => $paymentType,
                    'paid_on' => now(),
                    'created_by' => $transaction->created_by ?? 1,
                    'status' => 'success',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $transaction->update($updateData);
            Transaction::checkAndGenerateSell($transaction->id);
        }

        // Shipped → also mark payment if COD
        if ($newStatus === 'shipped') {
            $updateData['status'] = 'completed';
            $updateData['payment_status'] = 'paid';

            $payment = DB::table('transaction_payments')->where('transaction_id', $transaction->id)->first();
            if (!$payment) {
                $amount = (float) $transaction->final_total;
                $paymentType = TransactionPayment::determinePaymentType($amount);
                DB::table('transaction_payments')->insert([
                    'transaction_id' => $transaction->id,
                    'business_id' => $transaction->business_id ?? 1,
                    'amount' => $amount,
                    'method' => 'cash',
                    'payment_type' => $paymentType,
                    'paid_on' => now(),
                    'created_by' => $transaction->created_by ?? 1,
                    'status' => 'success',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('transaction_payments')
                    ->where('transaction_id', $transaction->id)
                    ->update(['status' => 'success', 'updated_at' => now()]);
            }
            $transaction->update($updateData);
            Transaction::checkAndGenerateSell($transaction->id);
        }

        if ($newStatus === 'cancelled') {
            $updateData['status'] = 'cancelled';
            $updateData['payment_status'] = 'cancelled';
            $transaction->update($updateData);
        }
    }
}
