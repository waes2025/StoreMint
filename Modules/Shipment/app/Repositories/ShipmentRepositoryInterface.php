<?php

declare(strict_types=1);

namespace Modules\Shipment\Repositories;

use App\Models\Contact;
use Modules\Cart\Models\Transaction;

interface ShipmentRepositoryInterface
{
    /**
     * Get a transaction by ID with loaded relations if necessary.
     */
    public function findTransaction(int $id): ?Transaction;

    /**
     * Get a customer contact by ID.
     */
    public function findCustomer(int $id): ?Contact;

    /**
     * Update a transaction's shipping details.
     */
    public function updateTransactionShipping(int $id, array $data): bool;

    /**
     * Get pending shipments that need tracking sync.
     */
    public function getPendingShipments(int $limit = 50): \Illuminate\Support\Collection;

    /**
     * Update customer address custom fields.
     */
    public function updateCustomerCustomFields(int $contactId, array $customFields): bool;
}
