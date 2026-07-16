<?php

declare(strict_types=1);

namespace Modules\Shipment\Repositories;

use App\Models\Contact;
use Modules\Cart\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShipmentRepository implements ShipmentRepositoryInterface
{
    /**
     * Get a transaction by ID.
     */
    public function findTransaction(int $id): ?Transaction
    {
        return Transaction::find($id);
    }

    /**
     * Get a customer contact by ID.
     */
    public function findCustomer(int $id): ?Contact
    {
        return Contact::where('id', $id)
            ->where('type', 'customer')
            ->first();
    }

    /**
     * Update a transaction's shipping details.
     */
    public function updateTransactionShipping(int $id, array $data): bool
    {
        $transaction = $this->findTransaction($id);
        if (!$transaction) {
            return false;
        }

        return $transaction->update($data);
    }

    /**
     * Get pending shipments that need tracking sync.
     * These are transactions with courier 'Pathao', a tracking number (consignment_id),
     * and a shipping status that is NOT 'delivered' or 'cancelled'.
     */
    public function getPendingShipments(int $limit = 50): Collection
    {
        return Transaction::where('courier', 'Pathao')
            ->whereNotNull('tracking_number')
            ->whereNotIn('shipping_status', ['delivered', 'cancelled'])
            ->limit($limit)
            ->get();
    }

    /**
     * Update customer address custom fields.
     */
    public function updateCustomerCustomFields(int $contactId, array $customFields): bool
    {
        $contact = Contact::find($contactId);
        if (!$contact) {
            return false;
        }

        // Merge or overwrite shipping_custom_field_details
        $existing = [];
        if ($contact->shipping_custom_field_details) {
            $decoded = json_decode($contact->shipping_custom_field_details, true);
            if (is_array($decoded)) {
                $existing = $decoded;
            }
        }

        $merged = array_merge($existing, $customFields);

        return $contact->update([
            'shipping_custom_field_details' => json_encode($merged)
        ]);
    }
}
