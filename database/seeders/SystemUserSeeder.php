<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates a dedicated system user for automated Quicket imports.
 *
 * After running this seeder, copy the reported user ID into .env on the VPS:
 *   QUICKET_OWNER_USER_ID=<id>
 *   EVENT_NOTIFY_EXCLUDE_USER_IDS=<dave_id>,<id>
 *
 * Run on VPS:
 *   php artisan db:seed --class=SystemUserSeeder
 */
class SystemUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'quicket@mygigguide.co.za'],
            [
                'name'              => 'Quicket Import',
                'email_verified_at' => now(),
                'password'          => Hash::make(Str::random(40)),
            ]
        );

        $this->command->info("System user ready: \"Quicket Import\" — ID {$user->id}");
        $this->command->info("Add to VPS .env:");
        $this->command->info("  QUICKET_OWNER_USER_ID={$user->id}");
        $this->command->info("  EVENT_NOTIFY_EXCLUDE_USER_IDS=<dave_user_id>,{$user->id}");
        $this->command->info("Then: php artisan config:cache");
    }
}
