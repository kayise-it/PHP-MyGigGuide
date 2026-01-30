<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organiser;
use App\Models\User;
use App\Rules\UniqueNormalizedName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrganiserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Organiser::with('user');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('organisation_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        $organisers = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.organisers.index', compact('organisers'));
    }

    public function create()
    {
        $users = User::whereHas('roles', function ($q) {
            $q->where('name', 'organiser');
        })->get();

        return view('admin.organisers.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organisation_name' => ['required', 'string', 'max:255', UniqueNormalizedName::forOrganiser()],
            'contact_person' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact_phone' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'website' => 'nullable|url',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Organiser::create($request->all());

        return redirect()->route('admin.organisers.index')
            ->with('success', 'Organiser created successfully.');
    }

    public function show(Organiser $organiser)
    {
        $organiser->load('user');

        return view('admin.organisers.show', compact('organiser'));
    }

    public function edit(Organiser $organiser)
    {
        $users = User::whereHas('roles', function ($q) {
            $q->where('name', 'organiser');
        })->get();

        return view('admin.organisers.edit', compact('organiser', 'users'));
    }

    public function update(Request $request, Organiser $organiser)
    {
        $validator = Validator::make($request->all(), [
            'organisation_name' => ['required', 'string', 'max:255', UniqueNormalizedName::forOrganiser($organiser->id)],
            'contact_person' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact_phone' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'website' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $organiser->update($request->all());

        return redirect()->route('admin.organisers.index')
            ->with('success', 'Organiser updated successfully.');
    }

    public function destroy(Organiser $organiser)
    {
        $organiser->delete();

        return redirect()->route('admin.organisers.index')
            ->with('success', 'Organiser deleted successfully.');
    }

    public function toggleStatus(Organiser $organiser)
    {
        $organiser->update(['is_active' => ! $organiser->is_active]);

        return back()->with('success', 'Organiser status updated successfully.');
    }
}
