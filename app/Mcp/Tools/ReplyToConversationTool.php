<?php

declare(strict_types=1);

namespace Modules\Passaparola\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;
use Modules\Passaparola\Actions\Operator\ReplyAsOperatorAction;
use Modules\Passaparola\Mcp\Concerns\ResolvesPassaparolaUser;

#[Name('reply_to_conversation')]
#[Description("Invia al visitatore una risposta a nome dell'operatore: la vede nella chat del sito. Mostra sempre il testo all'utente e chiedi conferma prima di inviarlo. Non promettere rimborsi o eccezioni che l'utente non ha deciso.")]
#[IsOpenWorld(true)]
class ReplyToConversationTool extends Tool
{
    use ResolvesPassaparolaUser;

    public function handle(Request $request, ReplyAsOperatorAction $reply): Response
    {
        $validated = $request->validate([
            'conversation_id' => ['required', 'integer'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $user = $this->user($request);
        $conversation = $this->conversation($user, (int) $validated['conversation_id'], 'update');

        try {
            $reply->execute($conversation, (string) $user->getKey(), $validated['message']);
        } catch (\DomainException) {
            return Response::error('La conversazione '.$conversation->id.' è chiusa: non si può più rispondere.');
        }

        return Response::text('Risposta inviata nella conversazione '.$conversation->id.'.');
    }

    /** @return array<string, \Illuminate\JsonSchema\Types\Type> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'conversation_id' => $schema->integer()->description('Id della conversazione.')->required(),
            'message' => $schema->string()->description('Testo che vedrà il visitatore.')->required(),
        ];
    }
}
