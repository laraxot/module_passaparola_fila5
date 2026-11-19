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
use Modules\Passaparola\Actions\Widget\ListConversationMessagesAction;
use Modules\Passaparola\Mcp\Concerns\ResolvesPassaparolaUser;

#[Name('show_conversation')]
#[Description('Mostra tutti i messaggi di una conversazione (visitatore, assistente AI, operatore) e il suo stato.')]
#[IsReadOnly]
#[IsOpenWorld(false)]
class ShowConversationTool extends Tool
{
    use ResolvesPassaparolaUser;

    public function handle(Request $request, ListConversationMessagesAction $messages): ResponseFactory
    {
        $validated = $request->validate(['conversation_id' => ['required', 'integer']]);
        $conversation = $this->conversation($this->user($request), (int) $validated['conversation_id']);

        if ($conversation->chatWidget === null) {
            return Response::structured(['status' => $conversation->status->value, 'messages' => []]);
        }

        return Response::structured($messages->execute($conversation->chatWidget, $conversation->visitor_token));
    }

    /** @return array<string, \Illuminate\JsonSchema\Types\Type> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'conversation_id' => $schema->integer()->description('Id della conversazione (da list_waiting_conversations).')->required(),
        ];
    }
}
