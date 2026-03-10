<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeaturePackage;
use App\Models\PaidFeature;
use Illuminate\Http\Request;

class FeaturePackageController extends Controller
{
    public function index(PaidFeature $paidFeature)
    {
        $packages = $paidFeature->packages()->orderBy('duration_days')->get();
        return view('admin.feature-packages.index', compact('paidFeature', 'packages'));
    }

    public function create(PaidFeature $paidFeature)
    {
        return view('admin.feature-packages.create', compact('paidFeature'));
    }

    public function store(Request $request, PaidFeature $paidFeature)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1|max:365',
            'price_cents' => 'required|integer|min:0',
            'currency' => 'required|string|size:3',
            'is_active' => 'sometimes|boolean',
        ]);
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        $validated['paid_feature_id'] = $paidFeature->id;
        // Ensure required program linkage is set if the column exists
        if (\Schema::hasColumn('feature_packages', 'feature_program_id')) {
            $validated['feature_program_id'] = $paidFeature->feature_program_id;
        }
        FeaturePackage::create($validated);
        return redirect()->route('admin.feature-packages.index', $paidFeature)->with('success', 'Package created.');
    }

    public function edit(PaidFeature $paidFeature, FeaturePackage $featurePackage)
    {
        return view('admin.feature-packages.edit', compact('paidFeature', 'featurePackage'));
    }

    public function update(Request $request, PaidFeature $paidFeature, FeaturePackage $featurePackage)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1|max:365',
            'price_cents' => 'required|integer|min:0',
            'currency' => 'required|string|size:3',
            'is_active' => 'sometimes|boolean',
        ]);
        $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
        $featurePackage->update($validated);
        return redirect()->route('admin.feature-packages.index', $paidFeature)->with('success', 'Package updated.');
    }

    public function destroy(PaidFeature $paidFeature, FeaturePackage $featurePackage)
    {
        $featurePackage->delete();
        return redirect()->route('admin.feature-packages.index', $paidFeature)->with('success', 'Package deleted.');
    }
}


