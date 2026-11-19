<?php

declare(strict_types=1);

namespace Modules\Passaparola\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Carbon;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Modules\Passaparola\Actions\Access\FindWorkspaceForUserAction;
use Modules\Passaparola\Actions\Report\SendWeeklyReportsAction;
use Modules\Passaparola\Mcp\Concerns\ResolvesPassaparolaUser;

#[Name('weekly_report')]
#[Description("Numeri dell'assistente di un'attività negli ultimi giorni: domande, risolte, passate a una persona, domande più frequenti e domande senza risposta (da aggiungere alla base di conoscenza).")]
#[IsReadOnly]
#[IsOpenWorld(false)]
class WeeklyReportTool extends Tool
{
    use ResolvesPassaparolaUser;

    public function handle(Request $request, FindWorkspaceForUserAction $find, SendWeeklyReportsAction $reports): ResponseFactory
    {
        $validated = $request->validate([
            'workspace' => ['required', 'string', 'max:100'],
            'days' => ['nullable', 'integer', 'min:1', 'max:90'],
        ]);

        $workspace = $find->execute($this->user($request), $validated['workspace']);
        $to = Carbon::now();
        $report = $reports->build($workspace, $to->copy()->subDays((int) ($validated['days'] ?? 7)), $to);

        return Response::structured([
            'workspace' => $report->workspaceName,
            'from' => $report->from->toDateString(),
            'to' => $report->to->toDateString(),
            'questions' => $report->questions,
            'answered' => $report->answered,
            'answered_percent' => $report->answeredPercent(),
            'handoffs' => $report->handoffs,
            'top_questions' => $report->topQuestions,
            'unanswered_questions' => $report->unansweredQuestions,
        ]);
    }

    /** @return array<string, \Illuminate\JsonSchema\Types\Type> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'workspace' => $schema->string()->description("Codice dell'attività.")->required(),
            'days' => $schema->integer()->description('Giorni da considerare (default 7, massimo 90).'),
        ];
    }
}
