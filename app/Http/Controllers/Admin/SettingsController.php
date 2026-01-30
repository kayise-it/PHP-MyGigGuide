<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $settings = [
            'facebook_login_enabled' => SiteSetting::get('facebook_login_enabled', false),
            'paid_features_enabled' => SiteSetting::get('paid_features_enabled', false),
            'boost_profile_enabled' => SiteSetting::get('boost_profile_enabled', true),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update the settings.
     */
    public function update(Request $request)
    {
        // Toggle Facebook login setting
        $facebookLoginEnabled = $request->has('facebook_login_enabled') ? '1' : '0';
        
        SiteSetting::set(
            'facebook_login_enabled',
            $facebookLoginEnabled,
            'boolean',
            'Enable or disable Facebook login/registration feature'
        );

        // Toggle Paid features setting
        $paidFeaturesEnabled = $request->has('paid_features_enabled') ? '1' : '0';
        
        SiteSetting::set(
            'paid_features_enabled',
            $paidFeaturesEnabled,
            'boolean',
            'Enable or disable paid features system'
        );

        // Toggle Boost Profile setting
        $boostProfileEnabled = $request->has('boost_profile_enabled') ? '1' : '0';
        
        SiteSetting::set(
            'boost_profile_enabled',
            $boostProfileEnabled,
            'boolean',
            'Enable or disable Boost Profile feature visibility in frontend'
        );

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    /**
     * Toggle a setting via AJAX.
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'required|boolean',
        ]);

        $key = $request->input('key');
        $value = $request->input('value') ? '1' : '0';

        // Only allow toggling known settings
        $allowedKeys = ['facebook_login_enabled', 'paid_features_enabled', 'boost_profile_enabled'];
        
        if (!in_array($key, $allowedKeys)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid setting key.',
            ], 400);
        }

        SiteSetting::set($key, $value, 'boolean');

        $statusMessage = $value === '1' ? 'enabled' : 'disabled';
        $featureName = str_replace('_', ' ', str_replace('_enabled', '', $key));

        return response()->json([
            'success' => true,
            'message' => ucfirst($featureName) . ' ' . $statusMessage . ' successfully.',
            'value' => $value === '1',
        ]);
    }
}

