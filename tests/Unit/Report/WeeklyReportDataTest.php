<?php

declare(strict_types=1);

namespace Modules\Passaparola\Tests\Unit\Report;

use Illuminate\Support\Carbon;
use Modules\AI\Datas\AssistantReportData;
use Modules\Passaparola\Datas\WeeklyReportData;
use Modules\Passaparola\Tests\TestCase;
use PHPUnit\Framework\Assert;

uses(TestCase::class);

test('_maps_source_ids_to_question_texts_keeping_order_and_limit', function (): void {
    $report = new AssistantReportData(
        questions: 10,
        answered: 7,
        handoffs: 3,
        topSourceIds: [5 => 4, 9 => 2, 77 => 1],
        handoffReasons: ['no_knowledge' => 2, 'sensitive_topic:rimbors' => 1],
        unansweredQuestions: ['Avete parcheggio?'],
    );

    $data = WeeklyReportData::fromAssistantReport(
        'Ceramiche Rossi',
        Carbon::parse('2026-11-09'),
        Carbon::parse('2026-11-16'),
        $report,
        [5 => 'Quanto costa la spedizione?', 9 => 'Come faccio un reso?'],
        limit: 5,
    );

    Assert::assertSame([
        ['question' => 'Quanto costa la spedizione?', 'count' => 4],
        ['question' => 'Come faccio un reso?', 'count' => 2],
    ], $data->topQuestions, 'la voce 77 (cancellata) non compare');
    Assert::assertSame(['Avete parcheggio?'], $data->unansweredQuestions);
    Assert::assertSame(70, $data->answeredPercent());
});

test('_answered_percent_is_zero_without_questions', function (): void {
    $data = WeeklyReportData::fromAssistantReport('X', Carbon::now(), Carbon::now(), new AssistantReportData(0, 0, 0, [], [], []), []);

    Assert::assertSame(0, $data->answeredPercent());
    Assert::assertSame([], $data->topQuestions);
});
