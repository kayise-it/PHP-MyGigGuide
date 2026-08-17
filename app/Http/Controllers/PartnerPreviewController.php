<?php

namespace App\Http\Controllers;

use App\Services\StationPortalAccess;
use App\Support\SiteBrand;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Hostname-aware preview pages shared across partner tenants (Rogues, 919 FM).
 */
class PartnerPreviewController extends Controller
{
    public function pitch(): View
    {
        $siteBrand = SiteBrand::current();

        if ($siteBrand->isFm919) {
            return view('fm919.pitch-hub', compact('siteBrand'));
        }

        return view('rogues.pitch-hub', compact('siteBrand'));
    }

    public function station(): View|RedirectResponse
    {
        $siteBrand = SiteBrand::current();

        if ($siteBrand->isFm919) {
            if (! auth()->check()) {
                return redirect()->guest(route('login'));
            }

            $user = auth()->user();
            if (! StationPortalAccess::userCanAccess($user, 'fm919')) {
                abort(403, 'This area is for 919 FM station staff. Ask Kee Consulting to add your login email.');
            }

            return view('fm919.station-portal-mock', [
                'siteBrand' => $siteBrand,
                'stationUser' => $user,
            ]);
        }

        return view('rogues.station-portal-mock', compact('siteBrand'));
    }
}
