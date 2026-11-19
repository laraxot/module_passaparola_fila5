<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Passaparola\Actions\Agent\RunOperatorAssistantAction;

// Gruppo `web` gia' applicato da XotBaseRouteServiceProvider: sessione e CSRF.
Route::post('passaparola/operator-assistant/run', RunOperatorAssistantAction::class)
    ->middleware(['auth', 'throttle:30,1'])
    ->name('passaparola.operator-assistant.run');
