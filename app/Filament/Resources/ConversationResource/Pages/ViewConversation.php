<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Resources\ConversationResource\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Modules\Passaparola\Actions\Operator\CloseConversationAction;
use Modules\Passaparola\Actions\Operator\ReplyAsOperatorAction;
use Modules\Passaparola\Enums\ConversationStatusEnum;
use Modules\Passaparola\Filament\Resources\ConversationResource;
use Modules\Passaparola\Models\Conversation;
use Modules\Xot\Filament\Resources\Pages\XotBaseViewRecord;

class ViewConversation extends XotBaseViewRecord
{
    protected static string $resource = ConversationResource::class;

    /** @return array<string, Action> */
    protected function getHeaderActions(): array
    {
        return [
            'reply' => Action::make('reply')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn (Conversation $record): bool => $record->status !== ConversationStatusEnum::CLOSED)
                ->schema([
                    Textarea::make('body')
                        ->required()
                        ->maxLength(2000)
                        ->rows(5),
                ])
                ->action(function (Conversation $record, array $data): void {
                    $body = $data['body'] ?? '';
                    app(ReplyAsOperatorAction::class)->execute($record, (int) Auth::id(), is_string($body) ? $body : '');
                    Notification::make()->success()->title(\trans_string('passaparola::conversation.notifications.reply_sent'))->send();
                }),

            'close' => Action::make('close')
                ->icon('heroicon-o-check-circle')
                ->color('gray')
                ->requiresConfirmation()
                ->visible(fn (Conversation $record): bool => $record->status !== ConversationStatusEnum::CLOSED)
                ->action(function (Conversation $record): void {
                    app(CloseConversationAction::class)->execute($record);
                    Notification::make()->success()->title(\trans_string('passaparola::conversation.notifications.closed'))->send();
                }),
        ];
    }
}
