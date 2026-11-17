<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Resources\WorkspaceResource\Pages;

use Modules\Passaparola\Filament\Resources\WorkspaceResource;
use Modules\Xot\Filament\Resources\Pages\XotBaseCreateRecord;

class CreateWorkspace extends XotBaseCreateRecord
{
    protected static string $resource = WorkspaceResource::class;

    protected function getRedirectUrl(): string
    {
        /** @var string */
        return $this->getResource()::getUrl('index');
    }
}
