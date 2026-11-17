<?php

declare(strict_types=1);

namespace Modules\Passaparola\Models\Policies;

use Modules\Passaparola\Models\WorkspaceMember;
use Modules\Xot\Contracts\UserContract;

/** Chi lavora su un'attività lo decide l'amministrazione Passaparola (before); il titolare lo vede. */
class WorkspaceMemberPolicy extends PassaparolaBasePolicy
{
    public function viewAny(UserContract $user): bool
    {
        return $this->hasAnyWorkspace($user, onlyOwner: true);
    }

    public function view(UserContract $user, WorkspaceMember $workspaceMember): bool
    {
        return $this->isOwnerOf($user, $workspaceMember->workspace_id);
    }

    public function create(UserContract $user): bool
    {
        return false;
    }

    public function update(UserContract $user, WorkspaceMember $workspaceMember): bool
    {
        return false;
    }

    public function delete(UserContract $user, WorkspaceMember $workspaceMember): bool
    {
        return false;
    }
}
