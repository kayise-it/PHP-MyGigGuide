<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FeatureProgramController extends Controller
{
    public function index()
    {
        $programs = FeatureProgram::orderBy('name')->withCount('features')->paginate(20);
        return view('admin.feature-programs.index', compact('programs'));
    }

    public function create()
    {
        return view('admin.feature-programs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'applies_to' => 'required|in:artist,venue,event,organiser',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);

        FeatureProgram::create($validated);
        return redirect()->route('admin.feature-programs.index')->with('success', 'Program created.');
    }

    public function edit(FeatureProgram $featureProgram)
    {
        return view('admin.feature-programs.edit', ['program' => $featureProgram]);
    }

    public function update(Request $request, FeatureProgram $featureProgram)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'applies_to' => 'required|in:artist,venue,event,organiser',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        $featureProgram->update($validated);
        return redirect()->route('admin.feature-programs.index')->with('success', 'Program updated.');
    }

    public function destroy(FeatureProgram $featureProgram)
    {
        $featureProgram->delete();
        return redirect()->route('admin.feature-programs.index')->with('success', 'Program deleted.');
    }
}









