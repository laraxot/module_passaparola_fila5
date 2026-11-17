<?php

declare(strict_types=1);

namespace Modules\Passaparola\Actions\Access;

use Modules\Passaparola\Enums\WorkspaceMemberRoleEnum;
use Modules\Passaparola\Models\WorkspaceMember;
use Modules\Xot\Contracts\UserContract;
use Spatie\QueueableAction\QueueableAction;

/**
 * Attività su cui un utente può lavorare.
 *
 * null = tutte (super-admin o `passaparola::admin`, gli stessi che XotBasePolicy::before lascia passare).
 * Altrimenti gli id delle attività di cui e' membro; con `$onlyOwner` solo quelle di cui e' titolare.
 */
final class ResolveAccessibleWorkspaceIdsAction
{
    use QueueableAction;

    public const array ADMIN_ROLES = ['super-admin', 'passaparola::admin'];

    /** @return list<int>|null */
    public function execute(UserContract $user, bool $onlyOwner = false): ?array
    {
        if ($user->hasRole(self::ADMIN_ROLES)) {
            return null;
        }

        $query = WorkspaceMember::query()->where('user_id', (string) $user->getKey());
        if ($onlyOwner) {
            $query->where('role', WorkspaceMemberRoleEnum::OWNER->value);
        }

        /** @var list<int> $ids */
        $ids = $query->pluck('workspace_id')->map(static fn (mixed $id): int => (int) $id)->unique()->values()->all();

        return $ids;
    }

    public function allows(UserContract $user, ?int $workspaceId, bool $onlyOwner = false): bool
    {
        $ids = $this->execute($user, $onlyOwner);

        return $ids === null || ($workspaceId !== null && in_array($workspaceId, $ids, true));
    }
}
