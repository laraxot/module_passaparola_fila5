<?php

declare(strict_types=1);

namespace Modules\Passaparola\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;
use Modules\Passaparola\Actions\Operator\CloseConversationAction;
use Modules\Passaparola\Mcp\Concerns\ResolvesPassaparolaUser;

#[Name('close_conversation')]
#[Description('Chiude una conversazione gestita. Se il visitatore riscrive si apre una conversazione nuova.')]
#[IsDestructive]
#[IsIdempotent]
#[IsOpenWorld(false)]
class CloseConversationTool extends Tool
{
    use ResolvesPassaparolaUser;

    public function handle(Request $request, CloseConversationAction $close): Response
    {
        $validated = $request->validate(['conversation_id' => ['required', 'integer']]);
        $conversation = $this->conversation($this->user($request), (int) $validated['conversation_id'], 'update');

        $close->execute($conversation);

        return Response::text('Conversazione '.$conversation->id.' chiusa.');
    }

    /** @return array<string, \Illuminate\JsonSchema\Types\Type> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'conversation_id' => $schema->integer()->description('Id della conversazione.')->required(),
        ];
    }
}
