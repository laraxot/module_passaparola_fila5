<?php

declare(strict_types=1);

namespace Modules\Passaparola\Models\Policies;

use Modules\Passaparola\Models\ChatWidget;
use Modules\Xot\Contracts\UserContract;

/** I widget li vede ogni membro dell'attività; li crea e modifica il titolare. */
class ChatWidgetPolicy extends PassaparolaBasePolicy
{
    public function viewAny(UserContract $user): bool
    {
        return $this->hasAnyWorkspace($user);
    }

    public function view(UserContract $user, ChatWidget $chatWidget): bool
    {
        return $this->isMemberOf($user, $chatWidget->workspace_id);
    }

    public function create(UserContract $user): bool
    {
        return $this->hasAnyWorkspace($user, onlyOwner: true);
    }

    public function update(UserContract $user, ChatWidget $chatWidget): bool
    {
        return $this->isOwnerOf($user, $chatWidget->workspace_id);
    }

    public function delete(UserContract $user, ChatWidget $chatWidget): bool
    {
        return $this->isOwnerOf($user, $chatWidget->workspace_id);
    }
}
