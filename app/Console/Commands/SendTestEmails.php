<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email : Recipient email address} {count=5 : Number of test emails to send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test emails to a given address (for debugging mail configuration)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $count = (int) $this->argument('count');

        if ($count < 1 || $count > 20) {
            $this->error('Count must be between 1 and 20.');
            return self::FAILURE;
        }

        $this->info("Sending {$count} test email(s) to {$email}...");

        for ($i = 1; $i <= $count; $i++) {
            try {
                Mail::raw(
                    "This is test email #{$i} of {$count} from My Gig Guide.\n\nSent at: " . now()->toIso8601String() . "\n\nIf you received this, mail is working.",
                    function ($message) use ($email, $i, $count) {
                        $message->to($email)
                            ->subject("[My Gig Guide] Test email {$i}/{$count}");
                    }
                );
                $this->line("  Sent test email {$i}/{$count}");
            } catch (\Throwable $e) {
                $this->error("  Failed to send email {$i}: " . $e->getMessage());
                return self::FAILURE;
            }
        }

        $this->info("Done. {$count} test email(s) sent to {$email}.");
        return self::SUCCESS;
    }
}
