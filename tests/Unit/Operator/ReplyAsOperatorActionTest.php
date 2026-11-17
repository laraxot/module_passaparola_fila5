<?php

declare(strict_types=1);

namespace Modules\Passaparola\Tests\Unit\Operator;

use Modules\Passaparola\Actions\Operator\ReplyAsOperatorAction;
use Modules\Passaparola\Enums\ConversationStatusEnum;
use Modules\Passaparola\Models\Conversation;
use Modules\Passaparola\Tests\TestCase;

uses(TestCase::class);

function conversationWithStatus(ConversationStatusEnum $status): Conversation
{
    $conversation = new Conversation;
    $conversation->forceFill(['id' => 1, 'status' => $status]);

    return $conversation;
}

test('_refuses_an_empty_reply', function (): void {
    app(ReplyAsOperatorAction::class)->execute(conversationWithStatus(ConversationStatusEnum::HANDED_OFF), 1, "   \n");
})->throws(\InvalidArgumentException::class);

test('_refuses_to_reply_to_a_closed_conversation', function (): void {
    app(ReplyAsOperatorAction::class)->execute(conversationWithStatus(ConversationStatusEnum::CLOSED), 1, 'Ciao');
})->throws(\DomainException::class);
