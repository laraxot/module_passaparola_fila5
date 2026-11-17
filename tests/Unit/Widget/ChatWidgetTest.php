<?php

declare(strict_types=1);

namespace Modules\Passaparola\Tests\Unit\Widget;

use Modules\AI\Enums\AiMessageRoleEnum;
use Modules\AI\Models\AiMessage;
use Modules\Passaparola\Actions\Widget\BuildEmbedSnippetAction;
use Modules\Passaparola\Actions\Widget\ListConversationMessagesAction;
use Modules\Passaparola\Enums\ConversationStatusEnum;
use Modules\Passaparola\Models\ChatWidget;
use Modules\Passaparola\Models\Conversation;
use Modules\Passaparola\Tests\TestCase;
use PHPUnit\Framework\Assert;

uses(TestCase::class);

/** @param  list<string>  $origins */
function chatWidget(array $origins): ChatWidget
{
    $widget = new ChatWidget;
    $widget->forceFill(['allowed_origins' => $origins, 'public_key' => 'pp_'.str_repeat('a', 40)]);

    return $widget;
}

describe('ChatWidget::allowsOrigin', function (): void {
    test('_accepts_the_same_site_ignoring_case_and_trailing_slash', function (): void {
        $widget = chatWidget(['https://www.Negozio.it/']);

        Assert::assertTrue($widget->allowsOrigin('https://www.negozio.it'));
    });

    test('_rejects_other_sites_schemes_ports_and_missing_origin', function (?string $origin): void {
        Assert::assertFalse(chatWidget(['https://www.negozio.it'])->allowsOrigin($origin));
    })->with([
        'altro sito' => 'https://evil.example',
        'sottodominio' => 'https://shop.negozio.it',
        'http invece di https' => 'http://www.negozio.it',
        'porta diversa' => 'https://www.negozio.it:8443',
        'nessun origin' => null,
        'origin vuoto' => '',
        'schema non web' => 'file://www.negozio.it',
    ]);

    test('_rejects_everything_when_no_site_is_configured', function (): void {
        Assert::assertFalse(chatWidget([])->allowsOrigin('https://www.negozio.it'));
    });

    test('_keeps_explicit_ports', function (): void {
        Assert::assertTrue(chatWidget(['http://localhost:8000'])->allowsOrigin('http://localhost:8000'));
    });
});

describe('chiavi e token', function (): void {
    test('_public_key_and_visitor_token_match_the_route_constraints', function (): void {
        Assert::assertMatchesRegularExpression('/^pp_[A-Za-z0-9]{40}$/', ChatWidget::generatePublicKey());
        Assert::assertMatchesRegularExpression('/^[A-Za-z0-9]{48}$/', Conversation::generateVisitorToken());
        Assert::assertNotSame(ChatWidget::generatePublicKey(), ChatWidget::generatePublicKey());
    });
});

describe('ConversationStatusEnum', function (): void {
    test('_only_closed_conversations_refuse_messages', function (): void {
        Assert::assertTrue(ConversationStatusEnum::OPEN->acceptsMessages());
        Assert::assertTrue(ConversationStatusEnum::HANDED_OFF->acceptsMessages());
        Assert::assertFalse(ConversationStatusEnum::CLOSED->acceptsMessages());
    });
});

describe('ListConversationMessagesAction::publicRole', function (): void {
    test('_maps_thread_roles_to_widget_roles', function (): void {
        $make = static function (AiMessageRoleEnum $role, ?array $payload): AiMessage {
            $message = new AiMessage;
            $message->forceFill(['role' => $role, 'payload' => $payload]);

            return $message;
        };

        Assert::assertSame('visitor', ListConversationMessagesAction::publicRole($make(AiMessageRoleEnum::USER, null)));
        Assert::assertSame('assistant', ListConversationMessagesAction::publicRole($make(AiMessageRoleEnum::ASSISTANT, ['kind' => 'assistant_answer'])));
        Assert::assertSame('operator', ListConversationMessagesAction::publicRole($make(AiMessageRoleEnum::ASSISTANT, ['kind' => ListConversationMessagesAction::OPERATOR_REPLY_KIND])));
    });
});

describe('BuildEmbedSnippetAction', function (): void {
    test('_builds_an_escaped_script_tag_on_the_app_url', function (): void {
        config(['app.url' => 'https://app.passaparola.test/']);

        $snippet = app(BuildEmbedSnippetAction::class)->execute(chatWidget([]), 'en');

        Assert::assertSame(
            '<script src="https://app.passaparola.test/assets/passaparola/widget.js" data-key="pp_'.str_repeat('a', 40).'" data-lang="en" defer></script>',
            $snippet,
        );
    });
});
