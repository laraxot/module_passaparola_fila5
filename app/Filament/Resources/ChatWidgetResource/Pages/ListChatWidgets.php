<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Resources\ChatWidgetResource\Pages;

use Modules\Passaparola\Filament\Resources\ChatWidgetResource;
use Modules\Xot\Filament\Resources\Pages\XotBaseListRecords;

class ListChatWidgets extends XotBaseListRecords
{
    protected static string $resource = ChatWidgetResource::class;
}
