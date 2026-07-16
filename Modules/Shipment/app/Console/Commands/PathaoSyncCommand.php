<?php

declare(strict_types=1);

namespace Modules\Shipment\Console\Commands;

use Illuminate\Console\Command;
use Modules\Shipment\Services\Shipment\Pathao\PathaoService;
use App\Models\Business;
use Exception;

class PathaoSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipment:pathao-sync {--business_id= : Sync only for a specific business}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize pending Pathao shipment statuses with the local database';

    protected PathaoService $pathaoService;

    public function __construct(PathaoService $pathaoService)
    {
        parent::__construct();
        $this->pathaoService = $pathaoService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("Starting Pathao Shipment Status Sync...");

        $businessIdOption = $this->option('business_id');

        if ($businessIdOption) {
            $businessIds = [(int) $businessIdOption];
        } else {
            // Find all businesses that have Pathao enabled
            $businessIds = \Illuminate\Support\Facades\DB::table('settings')
                ->where('key', 'pathao_enabled')
                ->where('value', '1')
                ->pluck('business_id')
                ->map(fn($id) => (int) $id)
                ->toArray();

            if (empty($businessIds)) {
                // Fallback to all businesses if none explicitly has pathao_enabled key
                $businessIds = Business::pluck('id')->toArray();
            }
        }

        if (empty($businessIds)) {
            $this->warn("No businesses found to sync.");
            return Command::SUCCESS;
        }

        foreach ($businessIds as $businessId) {
            $isEnabled = setting($businessId, 'pathao_enabled');
            if (!$isEnabled && !$businessIdOption) {
                // Skip if not explicitly enabled
                continue;
            }

            $this->info("Syncing Pathao shipments for Business ID: {$businessId}...");

            try {
                $updatedCount = $this->pathaoService->syncShipmentStatus($businessId);
                $this->success("Successfully synced. Updated {$updatedCount} pending shipments for Business ID: {$businessId}.");
            } catch (Exception $e) {
                $this->error("Failed to sync shipments for Business ID {$businessId}: " . $e->getMessage());
            }
        }

        $this->info("Pathao Shipment Sync Completed!");
        return Command::SUCCESS;
    }
}
