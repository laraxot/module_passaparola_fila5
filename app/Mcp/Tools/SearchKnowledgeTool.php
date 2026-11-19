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
use Modules\AI\Actions\Assistant\SearchKnowledgeEntriesAction;
use Modules\AI\Models\AiKnowledgeEntry;
use Modules\Passaparola\Actions\Access\FindWorkspaceForUserAction;
use Modules\Passaparola\Mcp\Concerns\ResolvesPassaparolaUser;

#[Name('search_knowledge')]
#[Description("Cerca nella base di conoscenza di un'attività le risposte ufficiali pertinenti a una domanda. Sono le sole informazioni che l'assistente del sito usa per rispondere.")]
#[IsReadOnly]
#[IsOpenWorld(false)]
class SearchKnowledgeTool extends Tool
{
    use ResolvesPassaparolaUser;

    public function handle(Request $request, FindWorkspaceForUserAction $find, SearchKnowledgeEntriesAction $search): ResponseFactory
    {
        $validated = $request->validate([
            'workspace' => ['required', 'string', 'max:100'],
            'query' => ['required', 'string', 'max:500'],
        ]);

        $workspace = $find->execute($this->user($request), $validated['workspace']);
        $entries = $search->execute($workspace->knowledgeCollection(), $validated['query'], 10);

        return Response::structured([
            'entries' => array_map(static fn (AiKnowledgeEntry $entry): array => [
                'id' => $entry->id,
                'question' => $entry->question,
                'answer' => $entry->answer,
            ], $entries),
        ]);
    }

    /** @return array<string, \Illuminate\JsonSchema\Types\Type> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'workspace' => $schema->string()->description("Codice dell'attività (vedi list_workspaces).")->required(),
            'query' => $schema->string()->description('Domanda o parole da cercare.')->required(),
        ];
    }
}
