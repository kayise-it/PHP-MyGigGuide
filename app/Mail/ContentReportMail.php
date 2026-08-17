<?php

namespace App\Mail;

use App\Models\ContentReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContentReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContentReport $report,
    ) {}

    public function build(): self
    {
        $this->report->loadMissing(['user', 'reportable']);

        $subject = sprintf(
            '[Content report] %s — %s',
            $this->report->reportableTypeLabel(),
            $this->report->categoryLabel(),
        );

        return $this->subject($subject)
            ->replyTo(
                $this->report->user?->email ?? 'noreply@mygigguide.co.za',
                $this->report->user?->name ?? 'App user',
            )
            ->view('emails.content-report')
            ->with([
                'report' => $this->report,
                'adminUrl' => route('admin.content-reports.show', $this->report),
            ]);
    }
}
