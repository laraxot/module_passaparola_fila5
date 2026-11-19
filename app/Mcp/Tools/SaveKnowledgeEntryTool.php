<?php

declare(strict_types=1);

namespace Modules\Passaparola\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;
use Modules\AI\Actions\Assistant\SaveKnowledgeEntryAction;
use Modules\Passaparola\Actions\Access\FindWorkspaceForUserAction;
use Modules\Passaparola\Mcp\Concerns\ResolvesPassaparolaUser;

#[Name('save_knowledge_entry')]
#[Description("Aggiunge o corregge una risposta ufficiale nella base di conoscenza di un'attività (solo il titolare). Con `id` aggiorna quella voce; senza, una voce con la stessa domanda viene aggiornata invece di duplicata. Scrivi solo informazioni confermate dal titolare.")]
#[IsIdempotent]
#[IsOpenWorld(false)]
class SaveKnowledgeEntryTool extends Tool
{
    use ResolvesPassaparolaUser;

    public function handle(Request $request, FindWorkspaceForUserAction $find, SaveKnowledgeEntryAction $save): Response
    {
        $validated = $request->validate([
            'workspace' => ['required', 'string', 'max:100'],
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string', 'max:5000'],
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:50'],
            'id' => ['nullable', 'integer'],
        ]);

        $workspace = $find->execute($this->user($request), $validated['workspace'], onlyOwner: true);

        /** @var list<string>|null $tags */
        $tags = $validated['tags'] ?? null;
        $entry = $save->execute(
            $workspace->knowledgeCollection(),
            $validated['question'],
            $validated['answer'],
            $tags,
            isset($validated['id']) ? (int) $validated['id'] : null,
        );

        return Response::text('Voce '.$entry->id.' salvata nella base di conoscenza di '.$workspace->name.'.');
    }

    /** @return array<string, \Illuminate\JsonSchema\Types\Type> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'workspace' => $schema->string()->description("Codice dell'attività.")->required(),
            'question' => $schema->string()->description('Domanda tipo dei clienti.')->required(),
            'answer' => $schema->string()->description('Risposta ufficiale, confermata dal titolare.')->required(),
            'tags' => $schema->array()->items($schema->string())->description('Parole chiave facoltative.'),
            'id' => $schema->integer()->description('Id della voce da correggere (da search_knowledge).'),
        ];
    }
}
