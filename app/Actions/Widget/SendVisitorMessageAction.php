<?php

declare(strict_types=1);

namespace Modules\Passaparola\Actions\Widget;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Modules\AI\Actions\AppendAiMessageAction;
use Modules\AI\Actions\Assistant\AnswerQuestionAction;
use Modules\AI\Enums\AiMessageRoleEnum;
use Modules\Passaparola\Enums\ConversationStatusEnum;
use Modules\Passaparola\Models\ChatWidget;
use Modules\Passaparola\Models\Conversation;
use Spatie\QueueableAction\QueueableAction;

/**
 * Domanda di un visitatore dal widget: risponde l'assistente del modulo AI con la base di
 * conoscenza dell'attivita'; se serve una persona la conversazione passa a `handed_off`.
 * Da quel momento le domande successive non vanno piu' all'assistente: restano nel thread
 * per l'operatore e il visitatore riceve il messaggio di attesa.
 *
 * Rotta: POST /api/passaparola/widgets/{publicKey}/messages
 */
final class SendVisitorMessageAction
{
    use QueueableAction;

    public const int MAX_LENGTH = 1000;

    public function __construct(
        private readonly ResolveChatWidgetAction $resolveWidget,
        private readonly FindOrStartConversationAction $findOrStart,
        private readonly AnswerQuestionAction $answer,
        private readonly AppendAiMessageAction $appendMessage,
    ) {}

    public function __invoke(Request $request, string $publicKey): JsonResponse
    {
        /** @var array{message: string, conversation_token?: string|null} $data */
        $data = $request->validate([
            'message' => ['required', 'string', 'max:'.self::MAX_LENGTH],
            'conversation_token' => ['nullable', 'string', 'max:64'],
        ]);

        $widget = $this->resolveWidget->execute($publicKey, $request->headers->get('Origin'));

        return response()->json($this->execute($widget, $data['conversation_token'] ?? null, $data['message']));
    }

    /** @return array{conversation_token: string, answer: string, handed_off: bool, status: string} */
    public function execute(ChatWidget $widget, ?string $visitorToken, string $message): array
    {
        $conversation = $this->findOrStart->execute($widget, $visitorToken);
        $workspace = $widget->workspace;
        $thread = $conversation->thread;

        if ($workspace === null || $thread === null) {
            throw new \LogicException('Conversation '.$conversation->id.' without workspace or thread');
        }

        if ($conversation->status === ConversationStatusEnum::HANDED_OFF) {
            $this->appendMessage->execute($thread, AiMessageRoleEnum::USER, trim($message));
            $reply = \trans_string('passaparola::widget.waiting_for_operator');
        } else {
            $answer = $this->answer->execute(
                thread: $thread,
                collection: $workspace->knowledgeCollection(),
                question: trim($message),
                userId: null,
                handoffKeywords: $workspace->handoff_keywords,
            );
            $reply = $answer->answer;
            if ($answer->needsHuman) {
                $conversation->status = ConversationStatusEnum::HANDED_OFF;
            }
        }

        $conversation->forceFill(['last_message_at' => Carbon::now()])->save();

        return [
            'conversation_token' => $conversation->visitor_token,
            'answer' => $reply,
            'handed_off' => $conversation->status === ConversationStatusEnum::HANDED_OFF,
            'status' => $conversation->status->value,
        ];
    }
}
