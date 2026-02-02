<?php

namespace App\Console\Commands;

use App\Services\MailCredentialsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MailCredentialsSetup extends Command
{
    protected $signature = 'mail:credentials-setup
                            {--email= : SMTP email (default: from default mail account)}
                            {--password= : SMTP password (prompted if not set)}';

    protected $description = 'Create encrypted mail credentials file so SMTP works without password in .env';

    public function handle(): int
    {
        $email = $this->option('email');
        $password = $this->option('password');

        if (! $email) {
            $accountId = (int) config('mail.default_account_id', 2);
            try {
                $row = DB::connection('mailserver')
                    ->table('virtual_users')
                    ->where('id', $accountId)
                    ->first();
                $email = $row->email ?? null;
            } catch (\Throwable) {
                $email = null;
            }
            if (! $email) {
                $this->error('Could not resolve default mail account email. Use --email=...');
                return self::FAILURE;
            }
            $this->line("Using default mail account: {$email}");
        }

        if (! $password) {
            $password = $this->secret('SMTP password');
            if ($password === null || $password === '') {
                $this->error('Password is required.');
                return self::FAILURE;
            }
        }

        if (! MailCredentialsService::store($email, $password)) {
            $this->error('Failed to write credentials file.');
            return self::FAILURE;
        }

        $this->info('Mail credentials saved to storage/app/mail_credentials.json (encrypted).');
        $this->info('You can remove MAIL_PASSWORD from .env if it was set there.');
        return self::SUCCESS;
    }
}
