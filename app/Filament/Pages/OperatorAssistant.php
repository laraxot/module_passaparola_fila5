<?php

declare(strict_types=1);

namespace Modules\Passaparola\Filament\Pages;

use Illuminate\Support\Str;
use Modules\Passaparola\Actions\Agent\RunOperatorAssistantAction;
use Modules\Xot\Contracts\UserContract;
use Modules\Xot\Filament\Pages\XotBasePage;

/**
 * Chat con l'assistente operatore (AG-UI): il client JS in `public/js/operator-assistant.js`
 * parla con RunOperatorAssistantAction. Ogni apertura della pagina inizia una conversazione nuova.
 */
class OperatorAssistant extends XotBasePage
{
    protected string $view = 'passaparola::filament.pages.operator-assistant';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?int $navigationSort = 30;

    public string $threadId = '';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof UserContract && RunOperatorAssistantAction::canUse($user);
    }

    public static function getNavigationLabel(): string
    {
        return (string) __('passaparola::operator_assistant.navigation.label');
    }

    public static function getNavigationGroup(): string
    {
        return (string) __('passaparola::operator_assistant.navigation.group');
    }

    public function getTitle(): string
    {
        return (string) __('passaparola::operator_assistant.title');
    }

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user instanceof UserContract, 403);

        $this->threadId = RunOperatorAssistantAction::threadPrefix($user).Str::uuid()->toString();
    }
}
