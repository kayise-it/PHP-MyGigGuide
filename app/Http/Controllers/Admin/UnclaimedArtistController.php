<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UnclaimedArtistController extends Controller
{
    public function index(Request $request)
    {
        $query = Artist::query()->whereNull('user_id');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('stage_name', 'like', "%{$search}%")
                  ->orWhere('genre', 'like', "%{$search}%");
            });
        }

        $unclaimed = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.unclaimed-artists.index', compact('unclaimed'));
    }

    public function edit(Artist $artist)
    {
        // Ensure this is an unclaimed artist
        if ($artist->user_id !== null) {
            abort(404);
        }

        return view('admin.unclaimed-artists.edit', compact('artist'));
    }

    public function update(Request $request, Artist $artist)
    {
// Ensure this is an unclaimed artist
        if ($artist->user_id !== null) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'stage_name' => 'required|string|max:255',
            'real_name' => 'nullable|string|max:255',
            'genre' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'contact_email' => 'required|email',
            'website' => 'nullable|url',
            'instagram' => 'nullable|url',
            'facebook' => 'nullable|url',
            'twitter' => 'nullable|url',
            'tiktok' => 'nullable|url',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        // Superuser editing unclaimed artist: do not use their own email as artist contact
        $validator->after(function ($validator) use ($request) {
            if ($request->filled('contact_email') && auth()->check() && auth()->user()->hasRole('superuser')) {
                $adminEmail = auth()->user()->email;
                if (strtolower(trim($request->contact_email)) === strtolower($adminEmail)) {
                    $validator->errors()->add('contact_email', 'Do not use your own email as the unclaimed artist\'s contact. Enter the artist\'s contact email.');
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->except(['profile_picture']);
        
        // Ensure user_id stays null for unclaimed artists
        $data['user_id'] = null;

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($artist->profile_picture && Storage::disk('public')->exists($artist->profile_picture)) {
                Storage::disk('public')->delete($artist->profile_picture);
            }

            // Store new profile picture
            $data['profile_picture'] = $request->file('profile_picture')->store('artists/profile_pictures', 'public');
        }

        $artist->update($data);

        return redirect()->route('admin.unclaimed-artists.index')
            ->with('success', 'Unclaimed artist updated successfully.');
    }
}

