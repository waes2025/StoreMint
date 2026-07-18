<?php

namespace Modules\Support\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class SupportServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Support';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'support';

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        RouteServiceProvider::class,
    ];
}
