<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Resources;

use Filament\Widgets\Widget;
use Modules\Passaparola\Filament\Resources\ChatWidgetResource\Pages;
use Modules\Passaparola\Models\ChatWidget;
use Modules\Passaparola\Filament\Concerns\ScopesToAccessibleWorkspaces;
use Modules\Xot\Filament\Resources\XotBaseResource;

/**
 * Widget chat delle attività: siti ammessi, saluto e codice da incollare nel sito.
 */
class ChatWidgetResource extends XotBaseResource
{
    use ScopesToAccessibleWorkspaces;

    protected static ?string $model = ChatWidget::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    /** @return array<class-string<Widget>> */
    public static function getHeaderWidgets(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return array_merge(parent::getPages(), [
            'index' => Pages\ListChatWidgets::route('/'),
            'create' => Pages\CreateChatWidget::route('/create'),
            'view' => Pages\ViewChatWidget::route('/{record}'),
            'edit' => Pages\EditChatWidget::route('/{record}/edit'),
        ]);
    }
}
