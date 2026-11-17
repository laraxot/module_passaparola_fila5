<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Resources\WorkspaceResource\Pages;

use Modules\Passaparola\Filament\Resources\WorkspaceResource;
use Modules\Xot\Filament\Resources\Pages\XotBaseListRecords;

class ListWorkspaces extends XotBaseListRecords
{
    protected static string $resource = WorkspaceResource::class;
}
