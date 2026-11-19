<?php

declare(strict_types=1);

namespace Modules\Passaparola\Actions\Operator;

use Illuminate\Database\Eloquent\Collection;
use Modules\Passaparola\Actions\Access\ResolveAccessibleWorkspaceIdsAction;
use Modules\Passaparola\Enums\ConversationStatusEnum;
use Modules\Passaparola\Models\Conversation;
use Modules\Xot\Contracts\UserContract;
use Spatie\QueueableAction\QueueableAction;

/**
 * Conversazioni passate a una persona nelle attività dell'utente, dalla più vecchia in attesa.
 * La stessa coda del pannello (ConversationResource), per i canali senza pannello.
 */
final class ListWaitingConversationsAction
{
    use QueueableAction;

    public function __construct(private readonly ResolveAccessibleWorkspaceIdsAction $accessible) {}

    /** @return Collection<int, Conversation> */
    public function execute(UserContract $user, ?int $workspaceId = null, int $limit = 20): Collection
    {
        $ids = $this->accessible->execute($user);

        return Conversation::query()
            ->with('workspace')
            ->where('status', ConversationStatusEnum::HANDED_OFF->value)
            ->when($ids !== null, static fn ($query) => $query->whereIn('workspace_id', $ids ?? []))
            ->when($workspaceId !== null, static fn ($query) => $query->where('workspace_id', $workspaceId))
            ->orderBy('last_message_at')
            ->limit(max(1, min($limit, 100)))
            ->get();
    }
}
