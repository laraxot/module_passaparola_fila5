<?php

declare(strict_types=1);

namespace Modules\Passaparola\Mcp\Concerns;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Modules\Passaparola\Models\Conversation;
use Modules\Xot\Contracts\UserContract;

/**
 * Utente del token MCP (OAuth Passport) e controlli con le stesse policy del pannello.
 */
trait ResolvesPassaparolaUser
{
    protected function user(Request $request): UserContract
    {
        $user = $request->user();

        if (! $user instanceof UserContract) {
            throw new AuthorizationException('Accesso negato: serve un utente autenticato.');
        }

        return $user;
    }

    /** Conversazione su cui l'utente puo' fare `$ability` (policy ConversationPolicy). */
    protected function conversation(UserContract $user, int $id, string $ability = 'view'): Conversation
    {
        $conversation = Conversation::query()->with(['workspace', 'chatWidget'])->find($id);

        if (! $conversation instanceof Conversation || Gate::forUser($user)->denies($ability, $conversation)) {
            throw new AuthorizationException('Conversazione non trovata o non accessibile: '.$id);
        }

        return $conversation;
    }
}
