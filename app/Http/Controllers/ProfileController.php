<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\User;
use App\Models\Venue;
use App\Rules\UniqueNormalizedName;
use App\Services\ArtistPageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show the user profile.
     */
    public function show()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Ensure roles are loaded for display on the profile page
        $user->load('roles');

        // Get role-specific profile data
        $profile = null;
        if ($user->hasRole('artist')) {
            $profile = $user->artist;
        } elseif ($user->hasRole('organiser')) {
            $profile = $user->organiser;
        }

        return view('profile.show', compact('user', 'profile'));
    }

    /**
     * Show the profile edit form.
     */
    public function edit()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }
// Get role-specific profile data
        $profile = null;
        if ($user->hasRole('artist')) {
            $profile = $user->artist;
        } elseif ($user->hasRole('organiser')) {
            $profile = $user->organiser;
        }

        return view('profile.edit', compact('user', 'profile'));
    }

    /**
     * Update the user profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255', UniqueNormalizedName::forUser($user->id)],
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Store new profile picture
            $profilePicturePath = $request->file('profile_picture')->store('users/profile_pictures', 'public');
            $user->update(['profile_picture' => $profilePicturePath]);
        }

        // Update basic user info (username and email cannot be changed)
        $user->update([
            'name' => $request->name,
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            if (! $request->current_password || ! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Update role-specific profile
        if ($user->hasRole('artist') && $user->artist) {
            $this->updateArtistProfile($user, $request);
        } elseif ($user->hasRole('organiser') && $user->organiser) {
            $this->updateOrganiserProfile($user, $request);
        }

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
    }

    /**
     * Update artist profile.
     */
    private function updateArtistProfile($user, $request)
    {
        $artist = $user->artist;
        if (! $artist || ! app(ArtistPageService::class)->userCanEditPage($user, $artist)) {
            return;
        }

        $request->validate([
            'stage_name' => ['required', 'string', 'max:255', UniqueNormalizedName::forArtist($artist->id)],
            'real_name' => 'nullable|string|max:255',
            'genre' => 'required|string|max:255',
            'bio' => 'nullable|string|max:5000',
            'phone_number' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email|max:255',
            'instagram' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'tiktok' => 'nullable|url|max:255',
            'artist_profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $data = [
            'stage_name' => $request->stage_name,
            'real_name' => $request->real_name,
            'genre' => $request->genre,
            'bio' => $request->bio,
            'phone_number' => $request->phone_number,
            'contact_email' => $request->contact_email,
            'instagram' => $request->instagram,
            'facebook' => $request->facebook,
            'twitter' => $request->twitter,
            'tiktok' => $request->tiktok,
        ];

        if ($request->hasFile('artist_profile_picture')) {
            if ($artist->profile_picture && Storage::disk('public')->exists($artist->profile_picture)) {
                Storage::disk('public')->delete($artist->profile_picture);
            }

            $data['profile_picture'] = $request->file('artist_profile_picture')
                ->store('artists/profile_pictures', 'public');
        }

        $artist->update($data);
    }

    /**
     * Update organiser profile.
     */
    private function updateOrganiserProfile($user, $request)
    {
        $organiser = $user->organiser;
        if (! $organiser || $organiser->claim_status !== 'approved') {
            return;
        }

        $request->validate([
            'organisation_name' => ['required', 'string', 'max:255', UniqueNormalizedName::forOrganiser($organiser->id)],
            'phone_number' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:5000',
        ]);

        $organiser->update([
            'organisation_name' => $request->organisation_name,
            'phone_number' => $request->phone_number,
            'contact_email' => $request->contact_email,
            'website' => $request->website,
            'description' => $request->description,
        ]);
    }

    /**
     * Delete the user's profile and all associated data.
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Validate password confirmation
        $request->validate([
            'password' => 'required|string',
        ]);

        // Verify password
        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password. Account deletion requires password confirmation.']);
        }

        try {
            // Collect all events (direct user events + artist/organiser events)
            $events = $user->events()->get();
            
            // Get events owned by artist (Artist model uses belongsToMany for performances, not ownership)
            if ($user->artist) {
                $artistEvents = Event::where('owner_type', Artist::class)
                    ->where('owner_id', $user->artist->id)
                    ->get();
                $events = $events->merge($artistEvents);
            }
            
            // Get events owned by organiser
            if ($user->organiser) {
                $events = $events->merge($user->organiser->events()->get());
            }
            
            // Remove duplicates by ID
            $events = $events->unique('id');

            // Delete all events and their images
            foreach ($events as $event) {
                // Delete event poster
                if ($event->poster && Storage::disk('public')->exists($event->poster)) {
                    Storage::disk('public')->delete($event->poster);
                }

                // Delete event gallery images
                if ($event->gallery) {
                    $gallery = is_array($event->gallery) ? $event->gallery : json_decode($event->gallery, true);
                    if (is_array($gallery)) {
                        foreach ($gallery as $image) {
                            if ($image && Storage::disk('public')->exists($image)) {
                                Storage::disk('public')->delete($image);
                            }
                        }
                    }
                }

                $event->delete();
            }

            // Collect all venues (direct user venues + artist/organiser venues)
            $venues = $user->venues()->get();
            
            // Get venues through artist/organiser relationships
            if ($user->artist) {
                $venues = $venues->merge($user->artist->venues()->get());
            }
            if ($user->organiser) {
                $venues = $venues->merge($user->organiser->venues()->get());
            }

            // Delete all venues and their images
            foreach ($venues as $venue) {
                // Delete venue main picture
                if ($venue->main_picture && Storage::disk('public')->exists($venue->main_picture)) {
                    Storage::disk('public')->delete($venue->main_picture);
                }

                // Delete venue gallery images
                if ($venue->venue_gallery) {
                    $gallery = is_array($venue->venue_gallery) ? $venue->venue_gallery : json_decode($venue->venue_gallery, true);
                    if (is_array($gallery)) {
                        foreach ($gallery as $image) {
                            if ($image && Storage::disk('public')->exists($image)) {
                                Storage::disk('public')->delete($image);
                            }
                        }
                    }
                }

                $venue->delete();
            }

            // Delete user's profile picture
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Delete user's folder structure
            try {
                if ($user->settings && isset($user->settings['folder_name'])) {
                    $roleFolder = $user->hasRole('artist') ? 'artists' : ($user->hasRole('organiser') ? 'organisers' : 'users');
                    $folderPath = $roleFolder.'/'.$user->settings['folder_name'];
                    
                    if (Storage::disk('public')->exists($folderPath)) {
                        Storage::disk('public')->deleteDirectory($folderPath);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to delete user folder: '.$e->getMessage());
            }

            // Detach all relationships
            $user->favoriteEvents()->detach();
            $user->favoriteVenues()->detach();
            $user->favoriteArtists()->detach();
            $user->favoriteOrganisers()->detach();
            $user->ratings()->delete();

            // Delete role-specific profiles
            if ($user->artist) {
                // Delete artist profile pictures and gallery
                $artist = $user->artist;
                if ($artist->profile_picture && Storage::disk('public')->exists($artist->profile_picture)) {
                    Storage::disk('public')->delete($artist->profile_picture);
                }
                if ($artist->gallery) {
                    $gallery = is_array($artist->gallery) ? $artist->gallery : json_decode($artist->gallery, true);
                    if (is_array($gallery)) {
                        foreach ($gallery as $image) {
                            if ($image && Storage::disk('public')->exists($image)) {
                                Storage::disk('public')->delete($image);
                            }
                        }
                    }
                }
                $artist->delete();
            }

            if ($user->organiser) {
                // Delete organiser logo
                $organiser = $user->organiser;
                if ($organiser->logo && Storage::disk('public')->exists($organiser->logo)) {
                    Storage::disk('public')->delete($organiser->logo);
                }
                $organiser->delete();
            }

            // Logout the user
            Auth::logout();

            // Delete the user
            $user->delete();

            // Invalidate session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home')->with('success', 'Your account and all associated data have been permanently deleted.');
        } catch (\Exception $e) {
            \Log::error('Error deleting user profile: '.$e->getMessage());
            return back()->withErrors(['error' => 'An error occurred while deleting your account. Please try again or contact support.']);
        }
    }
}
