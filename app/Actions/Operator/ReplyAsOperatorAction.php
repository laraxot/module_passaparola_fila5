<?php

declare(strict_types=1);

namespace Modules\Passaparola\Actions\Operator;

use Illuminate\Support\Carbon;
use Modules\AI\Actions\AppendAiMessageAction;
use Modules\AI\Actions\ConfirmAiActionProposalAction;
use Modules\AI\Adapters\AssistantHandoffHandler;
use Modules\AI\Enums\AiActionProposalStatusEnum;
use Modules\AI\Enums\AiMessageRoleEnum;
use Modules\AI\Models\AiActionProposal;
use Modules\AI\Models\AiMessage;
use Modules\Passaparola\Actions\Widget\ListConversationMessagesAction;
use Modules\Passaparola\Enums\ConversationStatusEnum;
use Modules\Passaparola\Models\Conversation;
use Spatie\QueueableAction\QueueableAction;

/**
 * Risposta di un operatore a una conversazione passata a una persona.
 *
 * Il messaggio entra nel thread AI con payload `kind = operator_reply` (il widget lo mostra come
 * "operatore" al prossimo aggiornamento). Le richieste `assistant_handoff` ancora in attesa nel thread
 * vengono confermate: risultano prese in carico da chi ha risposto.
 */
final class ReplyAsOperatorAction
{
    use QueueableAction;

    public function __construct(
        private readonly AppendAiMessageAction $appendMessage,
        private readonly ConfirmAiActionProposalAction $confirmProposal,
    ) {}

    public function execute(Conversation $conversation, int|string $operatorUserId, string $body): AiMessage
    {
        $body = trim($body);
        if ($body === '') {
            throw new \InvalidArgumentException('Operator reply cannot be empty');
        }

        if ($conversation->status === ConversationStatusEnum::CLOSED) {
            throw new \DomainException('Conversation '.$conversation->id.' is closed');
        }

        $thread = $conversation->thread;
        if ($thread === null) {
            throw new \LogicException('Conversation '.$conversation->id.' without thread');
        }

        $message = $this->appendMessage->execute($thread, AiMessageRoleEnum::ASSISTANT, $body, $operatorUserId, [
            'kind' => ListConversationMessagesAction::OPERATOR_REPLY_KIND,
            'workspace_id' => $conversation->workspace_id,
        ]);

        AiActionProposal::query()
            ->where('ai_thread_id', $thread->id)
            ->where('type', AssistantHandoffHandler::TYPE)
            ->where('status', AiActionProposalStatusEnum::PENDING->value)
            ->get()
            ->each(fn (AiActionProposal $proposal): AiActionProposal => $this->confirmProposal->execute($proposal, $operatorUserId));

        $conversation->forceFill([
            'status' => ConversationStatusEnum::HANDED_OFF,
            'last_message_at' => Carbon::now(),
        ])->save();

        return $message;
    }
}
