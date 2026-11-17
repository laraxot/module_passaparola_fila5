<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Passaparola\Actions\Widget\GetWidgetConfigAction;
use Modules\Passaparola\Actions\Widget\ListConversationMessagesAction;
use Modules\Passaparola\Actions\Widget\SendVisitorMessageAction;

/*
 * API pubblica del widget chat (prefisso /api dal RouteServiceProvider del modulo).
 * Nessun login: il widget si identifica con la public_key e il sito con l'header Origin
 * (ResolveChatWidgetAction). Limite di richieste per IP contro abusi e costi del modello.
 */
Route::prefix('passaparola/widgets/{publicKey}')
    ->where(['publicKey' => 'pp_[A-Za-z0-9]{40}'])
    ->name('passaparola.widget.')
    ->group(static function (): void {
        Route::get('config', GetWidgetConfigAction::class)
            ->middleware('throttle:60,1')
            ->name('config');

        Route::post('messages', SendVisitorMessageAction::class)
            ->middleware('throttle:20,1')
            ->name('messages.store');

        Route::get('conversations/{token}/messages', ListConversationMessagesAction::class)
            ->where('token', '[A-Za-z0-9]{48}')
            ->middleware('throttle:60,1')
            ->name('messages.index');
    });
