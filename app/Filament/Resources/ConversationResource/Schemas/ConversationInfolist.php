<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Resources\ConversationResource\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Component;
use Modules\Passaparola\Actions\Widget\ListConversationMessagesAction;
use Modules\Passaparola\Models\Conversation;
use Modules\Xot\Filament\Resources\Schemas\XotBaseResourceInfolist;

class ConversationInfolist extends XotBaseResourceInfolist
{
    /** @return array<string, Component> */
    public function getInfolistSchema(): array
    {
        return [
            'workspace.name' => TextEntry::make('workspace.name'),
            'status' => TextEntry::make('status')->badge(),
            'last_message_at' => TextEntry::make('last_message_at')->dateTime('d/m/Y H:i'),
            'transcript' => ViewEntry::make('transcript')
                ->view('passaparola::filament.conversation-transcript')
                ->state(static function (Conversation $record): array {
                    $widget = $record->chatWidget;

                    return $widget === null
                        ? []
                        : app(ListConversationMessagesAction::class)->execute($widget, $record->visitor_token)['messages'];
                })
                ->columnSpanFull(),
        ];
    }
}
