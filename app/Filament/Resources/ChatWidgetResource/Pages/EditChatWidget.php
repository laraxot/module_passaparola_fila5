<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Resources\ChatWidgetResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Modules\Passaparola\Filament\Resources\ChatWidgetResource;
use Modules\Xot\Filament\Resources\Pages\XotBaseEditRecord;

class EditChatWidget extends XotBaseEditRecord
{
    protected static string $resource = ChatWidgetResource::class;

    /**
     * @return array<string, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            'delete' => DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        /** @var string */
        return $this->getResource()::getUrl('index');
    }
}
