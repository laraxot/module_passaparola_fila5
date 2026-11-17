<?php

declare(strict_types=1);

namespace Modules\Passaparola\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Passaparola\Actions\Report\SendWeeklyReportsAction;
use Modules\Xot\Providers\XotBaseServiceProvider;

class PassaparolaServiceProvider extends XotBaseServiceProvider
{
    public string $name = 'Passaparola';

    public function boot(): void
    {
        parent::boot();

        // Report settimanale ai titolari: lunedi' alle 7:40 (prima dell'apertura, fuori dall'ora piena).
        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
            $schedule->call(static fn (): int => app(SendWeeklyReportsAction::class)->execute())
                ->name('passaparola-weekly-reports')
                ->weeklyOn(1, '7:40')
                ->withoutOverlapping();
        });
    }
}
