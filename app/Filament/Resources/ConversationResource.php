<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Resources;

use Filament\Widgets\Widget;
use Modules\Passaparola\Enums\ConversationStatusEnum;
use Modules\Passaparola\Filament\Resources\ConversationResource\Pages;
use Modules\Passaparola\Models\Conversation;
use Modules\Xot\Filament\Resources\XotBaseResource;

/**
 * Coda dell'operatore: conversazioni del widget, con quelle passate a una persona in evidenza.
 * Le conversazioni nascono solo dal widget: niente creazione o modifica a mano.
 */
class ConversationResource extends XotBaseResource
{
    protected static ?string $model = Conversation::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-stack';

    /** @return array<class-string<Widget>> */
    public static function getHeaderWidgets(): array
    {
        return [];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $waiting = Conversation::query()->where('status', ConversationStatusEnum::HANDED_OFF->value)->count();

        return $waiting > 0 ? (string) $waiting : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConversations::route('/'),
            'view' => Pages\ViewConversation::route('/{record}'),
        ];
    }
}
