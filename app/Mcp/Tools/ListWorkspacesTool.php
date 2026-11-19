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
use Modules\Passaparola\Actions\Access\ResolveAccessibleWorkspaceIdsAction;
use Modules\Passaparola\Enums\ConversationStatusEnum;
use Modules\Passaparola\Mcp\Concerns\ResolvesPassaparolaUser;
use Modules\Passaparola\Models\Workspace;

#[Name('list_workspaces')]
#[Description("Elenca le attività su cui l'utente lavora, con il codice (slug) da usare negli altri tool e il numero di conversazioni in attesa di una persona.")]
#[IsReadOnly]
#[IsOpenWorld(false)]
class ListWorkspacesTool extends Tool
{
    use ResolvesPassaparolaUser;

    public function handle(Request $request, ResolveAccessibleWorkspaceIdsAction $accessible): ResponseFactory
    {
        $user = $this->user($request);
        $ids = $accessible->execute($user);
        $owned = $accessible->execute($user, onlyOwner: true);

        $workspaces = Workspace::query()
            ->where('is_active', true)
            ->when($ids !== null, static fn ($query) => $query->whereKey($ids ?? []))
            ->withCount(['conversations as waiting' => static fn ($query) => $query->where('status', ConversationStatusEnum::HANDED_OFF->value)])
            ->orderBy('name')
            ->get()
            ->map(static fn (Workspace $workspace): array => [
                'slug' => $workspace->slug,
                'name' => $workspace->name,
                'role' => $owned === null || in_array($workspace->id, $owned, true) ? 'owner' : 'operator',
                'waiting_conversations' => (int) $workspace->getAttribute('waiting'),
            ])
            ->all();

        return Response::structured(['workspaces' => $workspaces]);
    }

    /** @return array<string, \Illuminate\JsonSchema\Types\Type> */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'workspaces' => $schema->array()->items($schema->object([
                'slug' => $schema->string()->required(),
                'name' => $schema->string()->required(),
                'role' => $schema->string()->enum(['owner', 'operator'])->required(),
                'waiting_conversations' => $schema->integer()->required(),
            ]))->required(),
        ];
    }
}
