<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laratrust\Models\Permission;
use Laratrust\Models\Role;

/**
 * Creates Laravel users from the mobile API (mobile-first signup).
 */
class ApiUserRegistrationService
{
    /**
     * @return array{user: User, username: string}
     */
    public function register(string $name, string $email, string $password, ?string $username = null): array
    {
        $email = strtolower(trim($email));
        $name = trim($name);

        if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            throw ValidationException::withMessages([
                'email' => ['That email is already registered. Try signing in, or use the website to recover your account.'],
            ]);
        }

        $username = $this->resolveUsername($username, $email, $name);

        $user = User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'is_active' => true,
            // Mobile-first MVP: app account is usable immediately; web verify stays separate.
            'email_verified_at' => now(),
        ]);

        $user->addRole('user');
        $this->ensureMemberCanCreateEvents($user);
        $user->getOrCreateFolderSettings();

        return ['user' => $user, 'username' => $username];
    }

    private function resolveUsername(?string $requested, string $email, string $name): string
    {
        $candidate = trim((string) $requested);
        if ($candidate === '') {
            $fromName = preg_replace('/[^a-zA-Z0-9_]/', '', Str::slug($name, '_')) ?: '';
            $local = Str::before($email, '@');
            $candidate = $fromName !== '' ? $fromName : (preg_replace('/[^a-zA-Z0-9_]/', '', $local) ?: 'user');
        }

        $candidate = Str::limit($candidate, 40, '');
        if ($candidate === '') {
            $candidate = 'user';
        }

        $base = $candidate;
        $suffix = 1;
        while (User::query()->whereRaw('LOWER(username) = ?', [strtolower($candidate)])->exists()) {
            $candidate = $base.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    /**
     * App-registered users must match the website: plain `user` can list events.
     * Belt-and-braces if production role permissions were not migrated yet.
     */
    public function ensureMemberCanCreateEvents(User $user): void
    {
        $this->ensureUserRoleCanCreateEvents();

        if ($user->can('create-events')) {
            return;
        }

        $permission = Permission::query()->where('name', 'create-events')->first();
        if ($permission) {
            $user->givePermission($permission);
        }
    }

    /** Fix production installs where the `user` role row never got event permissions. */
    private function ensureUserRoleCanCreateEvents(): void
    {
        $role = Role::query()->where('name', 'user')->first();
        if (! $role || $role->hasPermission('create-events')) {
            return;
        }

        $permission = Permission::query()->where('name', 'create-events')->first();
        if ($permission) {
            $role->givePermission($permission);
        }
    }
}
