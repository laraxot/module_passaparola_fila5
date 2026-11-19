<?php

declare(strict_types=1);

namespace Modules\Passaparola\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Modules\AI\Enums\AiMessageRoleEnum;
use Modules\AI\Models\AiMessage;
use Modules\Passaparola\Actions\Access\FindWorkspaceForUserAction;
use Modules\Passaparola\Actions\Operator\ListWaitingConversationsAction;
use Modules\Passaparola\Mcp\Concerns\ResolvesPassaparolaUser;
use Modules\Passaparola\Models\Conversation;

#[Name('list_waiting_conversations')]
#[Description("Elenca le conversazioni del widget passate a una persona e ancora da gestire, dalla più vecchia, con l'ultima domanda del visitatore.")]
#[IsReadOnly]
#[IsOpenWorld(false)]
class ListWaitingConversationsTool extends Tool
{
    use ResolvesPassaparolaUser;

    public function handle(Request $request, ListWaitingConversationsAction $list, FindWorkspaceForUserAction $find): ResponseFactory
    {
        $validated = $request->validate([
            'workspace' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $user = $this->user($request);
        $workspaceId = isset($validated['workspace']) ? $find->execute($user, $validated['workspace'])->id : null;

        $conversations = $list->execute($user, $workspaceId, (int) ($validated['limit'] ?? 20))
            ->map(static fn (Conversation $conversation): array => [
                'id' => $conversation->id,
                'workspace' => $conversation->workspace?->slug,
                'last_message_at' => $conversation->last_message_at?->toIso8601String(),
                'last_visitor_message' => AiMessage::query()
                    ->where('ai_thread_id', $conversation->ai_thread_id)
                    ->where('role', AiMessageRoleEnum::USER->value)
                    ->latest('id')
                    ->value('content'),
            ])
            ->all();

        return Response::structured(['conversations' => $conversations]);
    }

    /** @return array<string, \Illuminate\JsonSchema\Types\Type> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'workspace' => $schema->string()->description("Codice dell'attività; senza, tutte le attività dell'utente."),
            'limit' => $schema->integer()->description('Quante conversazioni al massimo (default 20).'),
        ];
    }
}
