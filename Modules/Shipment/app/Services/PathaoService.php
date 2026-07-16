<?php

declare(strict_types=1);

namespace Modules\Shipment\Services;

use Modules\Shipment\Services\Shipment\Pathao\PathaoService as BasePathaoService;

/**
 * Legacy wrapper for compatibility.
 * Redirects to the main clean architecture PathaoService.
 */
class PathaoService extends BasePathaoService
{
}
