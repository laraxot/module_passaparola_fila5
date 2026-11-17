<?php

declare(strict_types=1);

namespace Modules\Passaparola\Models\Policies;

use Modules\Passaparola\Actions\Access\ResolveAccessibleWorkspaceIdsAction;
use Modules\Xot\Contracts\UserContract;
use Modules\Xot\Models\Policies\XotBasePolicy;

/**
 * Base delle policy Passaparola. super-admin e `passaparola::admin` passano da XotBasePolicy::before;
 * per tutti gli altri conta l'appartenenza all'attività (WorkspaceMember).
 */
abstract class PassaparolaBasePolicy extends XotBasePolicy
{
    protected function isMemberOf(UserContract $user, ?int $workspaceId): bool
    {
        return app(ResolveAccessibleWorkspaceIdsAction::class)->allows($user, $workspaceId);
    }

    protected function isOwnerOf(UserContract $user, ?int $workspaceId): bool
    {
        return app(ResolveAccessibleWorkspaceIdsAction::class)->allows($user, $workspaceId, onlyOwner: true);
    }

    protected function hasAnyWorkspace(UserContract $user, bool $onlyOwner = false): bool
    {
        $ids = app(ResolveAccessibleWorkspaceIdsAction::class)->execute($user, $onlyOwner);

        return $ids === null || $ids !== [];
    }
}
