<?php

declare(strict_types=1);

namespace Modules\Passaparola\Actions\Widget;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\AI\Enums\AiMessageRoleEnum;
use Modules\AI\Models\AiMessage;
use Modules\Passaparola\Models\ChatWidget;
use Modules\Passaparola\Models\Conversation;
use Spatie\QueueableAction\QueueableAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Storico di una conversazione per il widget (anche le risposte dell'operatore arrivate dopo).
 * Solo messaggi del visitatore, dell'assistente e dell'operatore: mai strumenti o prompt di sistema.
 *
 * Rotta: GET /api/passaparola/widgets/{publicKey}/conversations/{token}/messages
 */
final class ListConversationMessagesAction
{
    use QueueableAction;

    /** Tipo di payload dei messaggi scritti da un operatore (vedi le Action della coda operatore). */
    public const string OPERATOR_REPLY_KIND = 'operator_reply';

    public function __construct(private readonly ResolveChatWidgetAction $resolveWidget) {}

    public function __invoke(Request $request, string $publicKey, string $token): JsonResponse
    {
        $widget = $this->resolveWidget->execute($publicKey, $request->headers->get('Origin'));

        return response()->json($this->execute($widget, $token));
    }

    /** @return array{status: string, messages: list<array{role: string, text: string, at: string|null}>} */
    public function execute(ChatWidget $widget, string $token): array
    {
        $conversation = Conversation::query()
            ->where('chat_widget_id', $widget->id)
            ->where('visitor_token', $token)
            ->first();

        if (! $conversation instanceof Conversation) {
            throw new NotFoundHttpException;
        }

        $messages = AiMessage::query()
            ->where('ai_thread_id', $conversation->ai_thread_id)
            ->whereIn('role', [AiMessageRoleEnum::USER->value, AiMessageRoleEnum::ASSISTANT->value])
            ->orderBy('id')
            ->get()
            ->map(static fn (AiMessage $message): array => [
                'role' => self::publicRole($message),
                'text' => (string) $message->content,
                'at' => $message->created_at?->toIso8601String(),
            ])
            ->values()
            ->all();

        return ['status' => $conversation->status->value, 'messages' => $messages];
    }

    /** visitor | assistant | operator */
    public static function publicRole(AiMessage $message): string
    {
        if ($message->role === AiMessageRoleEnum::USER) {
            return 'visitor';
        }

        return ($message->payload['kind'] ?? null) === self::OPERATOR_REPLY_KIND ? 'operator' : 'assistant';
    }
}
