<?php

namespace App\Http\Controllers;

use App\Support\SiteBrand;
use Illuminate\View\View;

class Fm919EngagementController extends Controller
{
    public function poll(): View
    {
        abort_unless(SiteBrand::current()->isFm919, 404);

        return view('fm919.poll', [
            'siteBrand' => SiteBrand::current(),
        ]);
    }

    public function request(): View
    {
        abort_unless(SiteBrand::current()->isFm919, 404);

        return view('fm919.request', [
            'siteBrand' => SiteBrand::current(),
            'whatsAppE164' => (string) config('fm919.whatsapp_e164'),
        ]);
    }
}
