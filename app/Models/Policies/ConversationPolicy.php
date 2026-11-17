<?php

declare(strict_types=1);

namespace Modules\Passaparola\Models\Policies;

use Modules\Passaparola\Models\Conversation;
use Modules\Xot\Contracts\UserContract;

/** La coda la lavora ogni membro dell'attività (titolare o operatore). Le conversazioni nascono solo dal widget. */
class ConversationPolicy extends PassaparolaBasePolicy
{
    public function viewAny(UserContract $user): bool
    {
        return $this->hasAnyWorkspace($user);
    }

    public function view(UserContract $user, Conversation $conversation): bool
    {
        return $this->isMemberOf($user, $conversation->workspace_id);
    }

    /** Rispondere e chiudere. */
    public function update(UserContract $user, Conversation $conversation): bool
    {
        return $this->isMemberOf($user, $conversation->workspace_id);
    }

    public function create(UserContract $user): bool
    {
        return false;
    }

    public function delete(UserContract $user, Conversation $conversation): bool
    {
        return false;
    }
}
