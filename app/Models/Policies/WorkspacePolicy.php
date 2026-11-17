<?php

declare(strict_types=1);

namespace Modules\Passaparola\Models\Policies;

use Modules\Passaparola\Models\Workspace;
use Modules\Xot\Contracts\UserContract;

/** Le attività le crea e le cancella solo l'amministrazione Passaparola (before); il titolare le modifica. */
class WorkspacePolicy extends PassaparolaBasePolicy
{
    public function viewAny(UserContract $user): bool
    {
        return $this->hasAnyWorkspace($user);
    }

    public function view(UserContract $user, Workspace $workspace): bool
    {
        return $this->isMemberOf($user, $workspace->id);
    }

    public function create(UserContract $user): bool
    {
        return false;
    }

    public function update(UserContract $user, Workspace $workspace): bool
    {
        return $this->isOwnerOf($user, $workspace->id);
    }

    public function delete(UserContract $user, Workspace $workspace): bool
    {
        return false;
    }
}
