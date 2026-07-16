<?php

namespace Modules\Shipment\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class ShipmentServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Shipment';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'shipment';

    protected array $commands = [
        \Modules\Shipment\Console\Commands\PathaoSyncCommand::class,
    ];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        parent::register();
        
        $this->app->bind(
            \Modules\Shipment\Repositories\ShipmentRepositoryInterface::class,
            \Modules\Shipment\Repositories\ShipmentRepository::class
        );
    }

    /**
     * Define module schedules.
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
