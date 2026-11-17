<?php

declare(strict_types=1);

namespace Modules\Passaparola\Actions\Widget;

use Modules\Passaparola\Models\ChatWidget;
use Spatie\QueueableAction\QueueableAction;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Trova il widget di una chiamata pubblica e controlla che il sito chiamante sia ammesso.
 *
 * 404 se il widget non esiste o e' spento (o e' spenta la sua attivita'),
 * 403 se l'header Origin non e' tra i siti ammessi del widget.
 */
final class ResolveChatWidgetAction
{
    use QueueableAction;

    public function execute(string $publicKey, ?string $origin): ChatWidget
    {
        $widget = ChatWidget::query()
            ->with('workspace')
            ->where('public_key', $publicKey)
            ->where('is_active', true)
            ->first();

        if (! $widget instanceof ChatWidget || $widget->workspace?->is_active !== true) {
            throw new NotFoundHttpException;
        }

        if (! $widget->allowsOrigin($origin)) {
            throw new AccessDeniedHttpException;
        }

        return $widget;
    }
}
