{{-- Report settimanale per il titolare. Dati: $report (WeeklyReportData). HTML semplice, adatto ai client email. --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<body style="font-family: Arial, sans-serif; color: #0f172a; max-width: 600px; margin: 0 auto; padding: 16px;">
    <h1 style="font-size: 20px; color: #0f766e;">{{ __('passaparola::weekly_report.title', ['name' => $report->workspaceName]) }}</h1>
    <p style="color: #475569;">{{ $report->from->format('d/m/Y') }} – {{ $report->to->format('d/m/Y') }}</p>

    <table role="presentation" style="width: 100%; border-collapse: collapse; margin: 16px 0;">
        <tr>
            <td style="padding: 8px; background: #f1f5f9;">{{ __('passaparola::weekly_report.questions') }}</td>
            <td style="padding: 8px; background: #f1f5f9; text-align: right;"><strong>{{ $report->questions }}</strong></td>
        </tr>
        <tr>
            <td style="padding: 8px;">{{ __('passaparola::weekly_report.answered') }}</td>
            <td style="padding: 8px; text-align: right;"><strong>{{ $report->answered }}</strong> ({{ $report->answeredPercent() }}%)</td>
        </tr>
        <tr>
            <td style="padding: 8px; background: #f1f5f9;">{{ __('passaparola::weekly_report.handoffs') }}</td>
            <td style="padding: 8px; background: #f1f5f9; text-align: right;"><strong>{{ $report->handoffs }}</strong></td>
        </tr>
    </table>

    @if ($report->topQuestions !== [])
        <h2 style="font-size: 16px;">{{ __('passaparola::weekly_report.top_questions') }}</h2>
        <ol>
            @foreach ($report->topQuestions as $item)
                <li>{{ $item['question'] }} — {{ $item['count'] }}</li>
            @endforeach
        </ol>
        <p style="color: #475569; font-size: 13px;">{{ __('passaparola::weekly_report.top_questions_hint') }}</p>
    @endif

    @if ($report->unansweredQuestions !== [])
        <h2 style="font-size: 16px;">{{ __('passaparola::weekly_report.unanswered') }}</h2>
        <ul>
            @foreach ($report->unansweredQuestions as $question)
                <li>{{ $question }}</li>
            @endforeach
        </ul>
        <p style="color: #475569; font-size: 13px;">{{ __('passaparola::weekly_report.unanswered_hint') }}</p>
    @endif

    @if ($report->questions === 0)
        <p>{{ __('passaparola::weekly_report.no_questions') }}</p>
    @endif
</body>
</html>
