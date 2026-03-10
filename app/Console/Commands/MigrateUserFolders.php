<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateUserFolders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:migrate-folders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing user folders to role-based structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Migrating user folders to role-based structure...');

        $users = User::whereNotNull('settings')->get();
        $this->info("Found {$users->count()} users with existing folders.");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            try {
                $this->migrateUserFolder($user);
                $this->line("\n✓ Migrated: {$user->username} ({$user->name})");
            } catch (\Exception $e) {
                $this->error("\n✗ Failed to migrate {$user->username}: ".$e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('User folder migration completed!');
    }

    private function migrateUserFolder($user)
    {
        $settings = $user->settings;
        if (! isset($settings['folder_name'])) {
            return;
        }

        $oldFolderName = $settings['folder_name'];
        $oldPath = 'users/'.$oldFolderName;
        $newPath = $user->getFolderPath();

        // Check if old folder exists
        if (Storage::disk('public')->exists($oldPath)) {
            // Create new role-based folder structure
            $user->createUserFolders($oldFolderName);

            // Move files from old location to new location
            $this->moveFolderContents($oldPath, $newPath);

            // Update user settings with new path
            $settings['folder_path'] = $newPath;
            $user->update(['settings' => $settings]);

            // Remove old folder if it's empty
            $this->removeOldFolder($oldPath);
        }
    }

    private function moveFolderContents($oldPath, $newPath)
    {
        $files = Storage::disk('public')->allFiles($oldPath);

        foreach ($files as $file) {
            $relativePath = str_replace($oldPath.'/', '', $file);
            $newFilePath = $newPath.'/'.$relativePath;

            // Ensure directory exists
            $newDir = dirname($newFilePath);
            Storage::disk('public')->makeDirectory($newDir);

            // Move file
            if (Storage::disk('public')->exists($file)) {
                Storage::disk('public')->move($file, $newFilePath);
            }
        }
    }

    private function removeOldFolder($oldPath)
    {
        try {
            // Check if folder is empty
            $files = Storage::disk('public')->allFiles($oldPath);
            if (empty($files)) {
                Storage::disk('public')->deleteDirectory($oldPath);
            }
        } catch (\Exception $e) {
            // Ignore errors when removing old folders
        }
    }
}
