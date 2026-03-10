<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaidFeature;
use Illuminate\Http\Request;

class PaidFeatureController extends Controller
{
    public function index()
    {
        $features = PaidFeature::orderByDesc('created_at')->paginate(20);
        return view('admin.paid-features.index', compact('features'));
    }

    public function create()
    {
        return view('admin.paid-features.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'feature_program_id' => 'nullable|exists:feature_programs,id',
            'name' => 'required|string|max:255',
            'applies_to' => 'required|in:artist,venue,event',
            'description' => 'nullable|string',
            'duration_days' => 'required|integer|min:1|max:365',
            'price_cents' => 'required|integer|min:0',
            'currency' => 'required|string|size:3',
            'is_active' => 'sometimes|boolean',
            'settings' => 'nullable|array',
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);

        PaidFeature::create($validated);
        return redirect()->route('admin.paid-features.index')->with('success', 'Paid feature created.');
    }

    public function edit(PaidFeature $paidFeature)
    {
        return view('admin.paid-features.edit', ['feature' => $paidFeature]);
    }

    public function update(Request $request, PaidFeature $paidFeature)
    {
        $validated = $request->validate([
            'feature_program_id' => 'nullable|exists:feature_programs,id',
            'name' => 'required|string|max:255',
            'applies_to' => 'required|in:artist,venue,event',
            'description' => 'nullable|string',
            'duration_days' => 'required|integer|min:1|max:365',
            'price_cents' => 'required|integer|min:0',
            'currency' => 'required|string|size:3',
            'is_active' => 'sometimes|boolean',
            'settings' => 'nullable|array',
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

        $paidFeature->update($validated);
        return redirect()->route('admin.paid-features.index')->with('success', 'Paid feature updated.');
    }

    public function destroy(PaidFeature $paidFeature)
    {
        $paidFeature->delete();
        return redirect()->route('admin.paid-features.index')->with('success', 'Paid feature deleted.');
    }
}


