<?php

declare(strict_types=1);

namespace Modules\Passaparola\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Passaparola\Datas\WeeklyReportData;

class WeeklyReportMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public WeeklyReportData $report) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: \trans_string('passaparola::weekly_report.subject', ['name' => $this->report->workspaceName]),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'passaparola::mail.weekly-report');
    }
}
