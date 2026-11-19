<?php

declare(strict_types=1);

namespace Modules\Passaparola\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Passport\Passport;
use Modules\Passaparola\Actions\Report\SendWeeklyReportsAction;
use Modules\Xot\Providers\XotBaseServiceProvider;

class PassaparolaServiceProvider extends XotBaseServiceProvider
{
    public string $name = 'Passaparola';

    public function boot(): void
    {
        parent::boot();

        // Consenso OAuth per i client MCP (Claude, Cursor...): la vista inclusa in laravel/mcp.
        // Passport e' del modulo User; qui solo la vista, se il progetto non ne ha gia' impostata una.
        if (class_exists(Passport::class) && view()->exists('mcp::authorize')) {
            Passport::authorizationView('mcp::authorize');
        }

        // Report settimanale ai titolari: lunedi' alle 7:40 (prima dell'apertura, fuori dall'ora piena).
        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
            $schedule->call(static fn (): int => app(SendWeeklyReportsAction::class)->execute())
                ->name('passaparola-weekly-reports')
                ->weeklyOn(1, '7:40')
                ->withoutOverlapping();
        });
    }
}
