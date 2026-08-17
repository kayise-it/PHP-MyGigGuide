<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\User;
use App\Services\ArtistRepertoireService;
use App\Services\Spotify\SpotifyOAuthService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpotifyCallbackController extends Controller
{
    public function __invoke(
        Request $request,
        SpotifyOAuthService $oauth,
        ArtistRepertoireService $repertoire,
    ): View {
        $error = trim((string) $request->query('error', ''));
        if ($error !== '') {
            return view('spotify.callback', [
                'success' => false,
                'message' => 'Spotify connection was cancelled.',
            ]);
        }

        $state = trim((string) $request->query('state', ''));
        $code = trim((string) $request->query('code', ''));
        if ($state === '' || $code === '') {
            return view('spotify.callback', [
                'success' => false,
                'message' => 'Spotify connection failed — missing authorization data.',
            ]);
        }

        $context = $oauth->oauthContextFromState($state);
        if ($context === null) {
            return view('spotify.callback', [
                'success' => false,
                'message' => 'Spotify connection expired. Open the app and try Connect Spotify again.',
            ]);
        }

        $user = User::query()->find($context['user_id']);
        $artist = Artist::query()->find($context['artist_id']);
        if ($user === null || $artist === null) {
            return view('spotify.callback', [
                'success' => false,
                'message' => 'Artist profile not found for this connection.',
            ]);
        }

        try {
            $repertoire->assertCanEdit($user, $artist);
            $oauth->connectArtistFromCallback($artist, $code);
        } catch (\Throwable) {
            return view('spotify.callback', [
                'success' => false,
                'message' => 'Spotify connection failed. Check server credentials and try again.',
            ]);
        }

        return view('spotify.callback', [
            'success' => true,
            'message' => 'Spotify connected for '.$artist->stage_name.'. Return to the My Gig Guide app and import your playlist.',
        ]);
    }
}
