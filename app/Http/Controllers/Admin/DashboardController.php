<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $statsEnabled = config('features.dashboard_stats', true);
        $stats = [];

        if ($statsEnabled) {
            $stats = [
                'total_users' => User::count(),
                'total_events' => Event::count(),
                'total_venues' => Venue::count(),
                'total_artists' => Artist::count(),
                'total_organisers' => Organiser::count(),
                'active_events' => Event::where('status', 'active')->count(),
                'pending_events' => Event::where('status', 'pending')->count(),
                'cancelled_events' => Event::where('status', 'cancelled')->count(),
            ];
        }

        // Get recent events
        $recent_events = Event::with(['venue', 'owner'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent users
        $recent_users = User::with('roles')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $events_by_status = collect();
        $users_by_role = collect();
        $monthly_events = collect();

        if ($statsEnabled) {
            // Get events by status
            $events_by_status = Event::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status');

            // Get users by role
            $users_by_role = DB::table('role_user')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->select('roles.name', DB::raw('count(*) as count'))
                ->groupBy('roles.name')
                ->get()
                ->pluck('count', 'name');

            // Get monthly event counts for the last 6 months
            $monthly_events = Event::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('count(*) as count')
            )
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }

        return view('admin.dashboard', compact(
            'stats',
            'recent_events',
            'recent_users',
            'events_by_status',
            'users_by_role',
            'monthly_events',
            'statsEnabled'
        ));
    }
}
