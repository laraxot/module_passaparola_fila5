<?php

declare(strict_types=1);

namespace Modules\Passaparola\Actions\Report;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\AI\Actions\Assistant\BuildAssistantReportAction;
use Modules\AI\Models\AiKnowledgeEntry;
use Modules\Passaparola\Datas\WeeklyReportData;
use Modules\Passaparola\Mail\WeeklyReportMail;
use Modules\Passaparola\Models\Workspace;
use Spatie\QueueableAction\QueueableAction;
use Throwable;

/**
 * Invia il report settimanale a ogni attività attiva con un'email per il report.
 * Pianificato dal PassaparolaServiceProvider (lunedi' mattina). Un errore su un'attività
 * non ferma le altre.
 */
final class SendWeeklyReportsAction
{
    use QueueableAction;

    public function __construct(private readonly BuildAssistantReportAction $buildReport) {}

    /** @return int numero di report inviati */
    public function execute(?CarbonInterface $now = null): int
    {
        $to = ($now ?? Carbon::now())->copy();
        $from = $to->copy()->subWeek();
        $sent = 0;

        Workspace::query()
            ->where('is_active', true)
            ->whereNotNull('report_email')
            ->each(function (Workspace $workspace) use ($from, $to, &$sent): void {
                try {
                    Mail::to((string) $workspace->report_email)->send(new WeeklyReportMail($this->build($workspace, $from, $to)));
                    $sent++;
                } catch (Throwable $throwable) {
                    Log::error('Passaparola weekly report failed', ['workspace_id' => $workspace->id, 'error' => $throwable->getMessage()]);
                }
            });

        return $sent;
    }

    public function build(Workspace $workspace, CarbonInterface $from, CarbonInterface $to): WeeklyReportData
    {
        $report = $this->buildReport->execute($workspace->knowledgeCollection(), $from, $to);

        /** @var array<int, string> $questionsById */
        $questionsById = AiKnowledgeEntry::query()
            ->whereIn('id', array_keys($report->topSourceIds))
            ->pluck('question', 'id')
            ->all();

        return WeeklyReportData::fromAssistantReport($workspace->name, $from, $to, $report, $questionsById);
    }
}
