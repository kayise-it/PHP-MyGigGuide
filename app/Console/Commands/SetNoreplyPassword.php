<?php

namespace App\Console\Commands;

use App\Models\MailAccount;
use App\Services\MailCredentialsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetNoreplyPassword extends Command
{
    protected $signature = 'mail:set-noreply-password {password : The new password for noreply@mygigguide.co.za}';

    protected $description = 'Set noreply mail account password on both the mail server and the app credentials file';

    public function handle(): int
    {
        $password = $this->argument('password');
        if ($password === null || $password === '') {
            $this->error('Password is required.');
            return self::FAILURE;
        }

        $accountId = (int) config('mail.default_account_id', 2);
        $account = DB::connection('mailserver')
            ->table('virtual_users')
            ->where('id', $accountId)
            ->first();

        if (! $account) {
            $this->error("Mail account id {$accountId} not found.");
            return self::FAILURE;
        }

        $email = $account->email;
        $hashed = MailAccount::hashPassword($password);
        if ($hashed === null || $hashed === '') {
            $this->error('Password hashing failed (is doveadm available?).');
            return self::FAILURE;
        }

        DB::connection('mailserver')
            ->table('virtual_users')
            ->where('id', $accountId)
            ->update(['password' => $hashed]);

        if (! MailCredentialsService::store($email, $password)) {
            $this->error('Failed to update app credentials file.');
            return self::FAILURE;
        }

        $this->info("Password updated for {$email} on mail server and in app credentials.");
        return self::SUCCESS;
    }
}
