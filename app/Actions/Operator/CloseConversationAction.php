<?php

declare(strict_types=1);

namespace Modules\Passaparola\Actions\Operator;

use Modules\Passaparola\Enums\ConversationStatusEnum;
use Modules\Passaparola\Models\Conversation;
use Spatie\QueueableAction\QueueableAction;

/**
 * Chiude una conversazione: il visitatore che riscrive ne apre una nuova (FindOrStartConversationAction).
 */
final class CloseConversationAction
{
    use QueueableAction;

    public function execute(Conversation $conversation): Conversation
    {
        $conversation->forceFill(['status' => ConversationStatusEnum::CLOSED])->save();

        return $conversation;
    }
}
