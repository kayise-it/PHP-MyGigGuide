<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

/**
 * Verifies Firebase ID tokens via Identity Toolkit (no Admin SDK required).
 *
 * Set FIREBASE_WEB_API_KEY in .env (Web API Key from Firebase Console).
 */
class FirebaseIdTokenService
{
    /**
     * @return array{uid: string, email: ?string}
     */
    public function verify(string $idToken): array
    {
        $idToken = trim($idToken);
        if ($idToken === '') {
            throw new InvalidArgumentException('Firebase ID token is required.');
        }

        $apiKey = config('services.firebase.web_api_key');
        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('Firebase is not configured on the server (FIREBASE_WEB_API_KEY).');
        }

        try {
            $response = Http::acceptJson()
                ->timeout(15)
                ->post(
                    'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key='.urlencode($apiKey),
                    ['idToken' => $idToken],
                )
                ->throw();
        } catch (RequestException $e) {
            $message = $e->response?->json('error.message') ?? 'Invalid Firebase ID token.';
            throw new InvalidArgumentException($message);
        }

        $users = $response->json('users');
        if (! is_array($users) || $users === []) {
            throw new InvalidArgumentException('Invalid Firebase ID token.');
        }

        $record = $users[0];
        $uid = (string) ($record['localId'] ?? '');
        if ($uid === '') {
            throw new InvalidArgumentException('Firebase user id missing from token.');
        }

        $email = isset($record['email']) ? strtolower(trim((string) $record['email'])) : null;
        if ($email === '') {
            $email = null;
        }

        return ['uid' => $uid, 'email' => $email];
    }
}
