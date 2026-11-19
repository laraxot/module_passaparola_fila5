<?php

declare(strict_types=1);

namespace Modules\Passaparola\Actions\Access;

use Illuminate\Auth\Access\AuthorizationException;
use Modules\Passaparola\Models\Workspace;
use Modules\Xot\Contracts\UserContract;
use Spatie\QueueableAction\QueueableAction;

/**
 * Trova un'attività per codice (slug) tra quelle dell'utente. Usata dai canali senza pannello
 * (server MCP, assistente dell'operatore): stessa regola di accesso delle liste Filament.
 *
 * Un'attività che esiste ma non e' dell'utente da' lo stesso errore di una che non esiste,
 * cosi' non si scopre quali codici esistono.
 */
final class FindWorkspaceForUserAction
{
    use QueueableAction;

    public function __construct(private readonly ResolveAccessibleWorkspaceIdsAction $accessible) {}

    public function execute(UserContract $user, string $slug, bool $onlyOwner = false): Workspace
    {
        $workspace = Workspace::query()->where('slug', $slug)->first();

        if (! $workspace instanceof Workspace || ! $this->accessible->allows($user, $workspace->id, $onlyOwner)) {
            throw new AuthorizationException('Attività non trovata o non accessibile: '.$slug);
        }

        return $workspace;
    }
}
