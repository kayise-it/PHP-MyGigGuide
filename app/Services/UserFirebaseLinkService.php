<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserFirebaseLinkService
{
    public function __construct(
        private readonly FirebaseIdTokenService $firebaseIdToken,
        private readonly ApiUserRegistrationService $registration,
    ) {}

    /**
     * @return array{uid: string, email: ?string, name: ?string}
     */
    public function claimsFromToken(string $idToken): array
    {
        return $this->firebaseIdToken->verify($idToken);
    }

    public function linkAuthenticatedUser(User $user, string $idToken): User
    {
        $claims = $this->claimsFromToken($idToken);
        $this->guardLink($user, $claims['uid'], $claims['email']);
        $user->update(['firebase_uid' => $claims['uid']]);

        return $user->fresh();
    }

    public function resolveUserForFirebaseLogin(string $idToken): User
    {
        $claims = $this->claimsFromToken($idToken);

        $byUid = User::where('firebase_uid', $claims['uid'])->first();
        if ($byUid) {
            return $byUid;
        }

        if ($claims['email']) {
            $byEmail = User::whereRaw('LOWER(email) = ?', [$claims['email']])->first();
            if ($byEmail) {
                if ($byEmail->firebase_uid !== null && $byEmail->firebase_uid !== $claims['uid']) {
                    throw ValidationException::withMessages([
                        'id_token' => ['This email is linked to a different Firebase account.'],
                    ]);
                }
                if ($byEmail->firebase_uid === null) {
                    $byEmail->update(['firebase_uid' => $claims['uid']]);

                    return $byEmail->fresh();
                }
            }
        }

        if ($claims['email']) {
            $name = $claims['name'] ?? Str::before($claims['email'], '@');
            $result = $this->registration->register(
                name: $name,
                email: $claims['email'],
                password: Str::random(40),
                username: null,
            );
            $user = $result['user'];
            $user->update(['firebase_uid' => $claims['uid']]);

            return $user->fresh();
        }

        throw new ModelNotFoundException('No website account is linked to this Firebase sign-in.');
    }

    private function guardLink(User $user, string $uid, ?string $firebaseEmail): void
    {
        $existing = User::where('firebase_uid', $uid)->where('id', '!=', $user->id)->exists();
        if ($existing) {
            throw ValidationException::withMessages([
                'id_token' => ['This Firebase account is already linked to another website user.'],
            ]);
        }

        if ($user->firebase_uid !== null && $user->firebase_uid !== $uid) {
            throw ValidationException::withMessages([
                'id_token' => ['Your website account is already linked to a different Firebase account.'],
            ]);
        }

        $laravelEmail = strtolower(trim((string) $user->email));
        if ($firebaseEmail && $laravelEmail !== '' && $firebaseEmail !== $laravelEmail) {
            throw ValidationException::withMessages([
                'id_token' => ['Firebase email does not match your website account email. Use the same email on both, or contact support.'],
            ]);
        }
    }
}
