<?php

declare(strict_types=1);

namespace Modules\Passaparola\Datas;

use Carbon\CarbonInterface;
use Modules\AI\Datas\AssistantReportData;
use Spatie\LaravelData\Data;

/**
 * Report settimanale di un'attività, pronto per l'email: numeri dell'assistente, domande
 * frequenti con il loro testo (non solo gli id delle voci) e domande senza risposta.
 */
class WeeklyReportData extends Data
{
    /**
     * @param  list<array{question: string, count: int}>  $topQuestions
     * @param  list<string>  $unansweredQuestions
     */
    public function __construct(
        public string $workspaceName,
        public CarbonInterface $from,
        public CarbonInterface $to,
        public int $questions,
        public int $answered,
        public int $handoffs,
        public array $topQuestions,
        public array $unansweredQuestions,
    ) {}

    /** @param  array<int, string>  $questionsById  testo delle voci della base di conoscenza */
    public static function fromAssistantReport(
        string $workspaceName,
        CarbonInterface $from,
        CarbonInterface $to,
        AssistantReportData $report,
        array $questionsById,
        int $limit = 5,
    ): self {
        $top = [];
        foreach ($report->topSourceIds as $id => $count) {
            if (isset($questionsById[$id])) {
                $top[] = ['question' => $questionsById[$id], 'count' => $count];
            }
            if (count($top) === $limit) {
                break;
            }
        }

        return new self(
            workspaceName: $workspaceName,
            from: $from,
            to: $to,
            questions: $report->questions,
            answered: $report->answered,
            handoffs: $report->handoffs,
            topQuestions: $top,
            unansweredQuestions: array_slice($report->unansweredQuestions, 0, 10),
        );
    }

    /** Percentuale di domande risolte dall'assistente (0 se nessuna domanda). */
    public function answeredPercent(): int
    {
        return $this->questions === 0 ? 0 : (int) round($this->answered * 100 / $this->questions);
    }
}
