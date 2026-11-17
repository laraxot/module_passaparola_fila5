<?php

declare(strict_types=1);

namespace Modules\Passaparola\Providers;

use Modules\Xot\Providers\XotBaseRouteServiceProvider;

class RouteServiceProvider extends XotBaseRouteServiceProvider
{
    public string $name = 'Passaparola';

    protected string $moduleNamespace = 'Modules\Passaparola\Http\Controllers';
}
