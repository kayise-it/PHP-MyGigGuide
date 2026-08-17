<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\ContentReport;
use App\Models\Event;
use App\Models\Venue;
use App\Services\ContentReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentReportController extends Controller
{
    public function __construct(
        private readonly ContentReportService $reports,
    ) {}

    public function index(Request $request): View
    {
        $query = ContentReport::query()
            ->with(['user', 'reportable'])
            ->latest();

        if ($request->filled('status') && in_array($request->status, [
            ContentReport::STATUS_NEW,
            ContentReport::STATUS_RESOLVED,
            ContentReport::STATUS_DISMISSED,
        ], true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $type = match ($request->type) {
                'events' => Event::class,
                'artists' => Artist::class,
                'venues' => Venue::class,
                default => null,
            };
            if ($type) {
                $query->where('reportable_type', $type);
            }
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('message', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        $reports = $query->paginate(20)->withQueryString();

        $newCount = ContentReport::query()->where('status', ContentReport::STATUS_NEW)->count();

        return view('admin.content-reports.index', compact('reports', 'newCount'));
    }

    public function show(ContentReport $contentReport): View
    {
        $contentReport->load(['user', 'reportable', 'resolvedBy']);

        $editRoute = match ($contentReport->reportable_type) {
            Event::class => route('admin.events.edit', $contentReport->reportable_id),
            Artist::class => route('admin.artists.edit', $contentReport->reportable_id),
            Venue::class => route('admin.venues.edit', $contentReport->reportable_id),
            default => null,
        };

        return view('admin.content-reports.show', [
            'report' => $contentReport,
            'editRoute' => $editRoute,
        ]);
    }

    public function resolve(Request $request, ContentReport $contentReport): RedirectResponse
    {
        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->reports->resolve(
            $contentReport,
            $request->user(),
            $validated['admin_notes'] ?? null,
        );

        return redirect()
            ->route('admin.content-reports.show', $contentReport)
            ->with('success', 'Report marked as resolved.');
    }

    public function dismiss(Request $request, ContentReport $contentReport): RedirectResponse
    {
        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->reports->dismiss(
            $contentReport,
            $request->user(),
            $validated['admin_notes'] ?? null,
        );

        return redirect()
            ->route('admin.content-reports.show', $contentReport)
            ->with('success', 'Report dismissed.');
    }
}
