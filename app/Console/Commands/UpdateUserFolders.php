<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateUserFolders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-folders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing users to have folder settings and create their folders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating user folders...');

        $users = User::whereNull('settings')
            ->orWhere('settings', '{}')
            ->orWhere('settings', '[]')
            ->orWhere('settings', '')
            ->get();

        $this->info("Found {$users->count()} users without folder settings.");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            try {
                $user->getOrCreateFolderSettings();
                $this->line("\n✓ Updated user: {$user->username} ({$user->name})");
            } catch (\Exception $e) {
                $this->error("\n✗ Failed to update user {$user->username}: ".$e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('User folder update completed!');
    }
}
