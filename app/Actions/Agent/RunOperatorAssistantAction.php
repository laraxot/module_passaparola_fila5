<?php

declare(strict_types=1);

namespace Modules\Passaparola\Actions\Agent;

use Illuminate\Http\Request;
use Modules\AI\Actions\Agent\StreamAgentRunAction;
use Modules\Passaparola\Actions\Access\ResolveAccessibleWorkspaceIdsAction;
use Modules\Passaparola\Neuron\Agents\OperatorAssistant;
use Modules\Xot\Contracts\UserContract;
use Spatie\QueueableAction\QueueableAction;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Endpoint AG-UI dell'assistente operatore (POST, sessione web + CSRF).
 *
 * Solo chi lavora su almeno un'attività (o gli amministratori) puo' far girare l'agente: ogni turno
 * costa chiamate al modello. Il thread e' `operator-{idUtente}-{uuid}`, creato dalla pagina.
 */
final class RunOperatorAssistantAction
{
    use QueueableAction;

    public function __invoke(Request $request): StreamedResponse
    {
        $user = $request->user();
        if (! $user instanceof UserContract) {
            throw new AccessDeniedHttpException('Accesso negato.');
        }

        return $this->execute($request, $user);
    }

    public function execute(Request $request, UserContract $user): StreamedResponse
    {
        if (! self::canUse($user)) {
            throw new AccessDeniedHttpException('Non lavori su nessuna attività.');
        }

        return app(StreamAgentRunAction::class)->execute($request, app(OperatorAssistant::class), self::threadPrefix($user));
    }

    public static function canUse(UserContract $user): bool
    {
        $ids = app(ResolveAccessibleWorkspaceIdsAction::class)->execute($user);

        return $ids === null || $ids !== [];
    }

    public static function threadPrefix(UserContract $user): string
    {
        return 'operator-'.$user->getKey().'-';
    }
}
