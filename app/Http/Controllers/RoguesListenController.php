<?php

namespace App\Http\Controllers;

use App\Support\SiteBrand;
use Illuminate\Http\Response;
use Illuminate\View\View;

class RoguesListenController extends Controller
{
    public function show(): View|Response
    {
        $siteBrand = SiteBrand::current();

        if ($siteBrand->isFm919) {
            return view('fm919.listen-live', [
                'siteBrand' => $siteBrand,
                'streamUrl' => (string) config('fm919.stream_url'),
                'publicSiteUrl' => (string) config('fm919.public_site_url'),
                'embed' => request()->boolean('embed'),
            ]);
        }

        if (! $siteBrand->isRogues) {
            abort(404);
        }

        return view('rogues.listen-live', [
            'siteBrand' => $siteBrand,
            'streamUrl' => (string) config('rogues.stream_url'),
            'publicSiteUrl' => (string) config('rogues.public_site_url'),
            'embed' => request()->boolean('embed'),
        ]);
    }
}
