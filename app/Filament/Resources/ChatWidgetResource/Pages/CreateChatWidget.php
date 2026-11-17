<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Resources\ChatWidgetResource\Pages;

use Modules\Passaparola\Filament\Resources\ChatWidgetResource;
use Modules\Xot\Filament\Resources\Pages\XotBaseCreateRecord;

class CreateChatWidget extends XotBaseCreateRecord
{
    protected static string $resource = ChatWidgetResource::class;

    protected function getRedirectUrl(): string
    {
        /** @var string */
        return $this->getResource()::getUrl('index');
    }
}
