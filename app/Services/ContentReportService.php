<?php

namespace App\Services;

use App\Mail\ContentReportMail;
use App\Models\Artist;
use App\Models\ContentReport;
use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class ContentReportService
{
    public const DAILY_LIMIT = 5;

    /**
     * @return array{type: string, model: Model}
     */
    public function resolveReportable(string $type, int $id): array
    {
        $type = strtolower(trim($type));

        $model = match ($type) {
            'events' => Event::query()->find($id),
            'artists' => Artist::query()->find($id),
            'venues' => Venue::query()->find($id),
            default => null,
        };

        if (! $model) {
            throw new InvalidArgumentException('Not found.');
        }

        return ['type' => $type, 'model' => $model];
    }

    public function store(User $user, string $type, int $id, string $category, ?string $message): ContentReport
    {
        if (! array_key_exists($category, ContentReport::CATEGORIES)) {
            throw ValidationException::withMessages([
                'category' => 'Invalid report category.',
            ]);
        }

        ['model' => $model] = $this->resolveReportable($type, $id);

        $todayCount = ContentReport::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        if ($todayCount >= self::DAILY_LIMIT) {
            throw ValidationException::withMessages([
                'message' => 'Daily report limit reached. Try again tomorrow.',
            ]);
        }

        $message = $message !== null ? trim($message) : null;
        if ($message === '') {
            $message = null;
        }

        $existing = ContentReport::query()
            ->where('user_id', $user->id)
            ->where('reportable_type', $model::class)
            ->where('reportable_id', $model->getKey())
            ->where('category', $category)
            ->where('status', ContentReport::STATUS_NEW)
            ->where('updated_at', '>=', now()->subDays(7))
            ->first();

        if ($existing) {
            $existing->update(['message' => $message]);

            return $existing->fresh(['user', 'reportable']);
        }

        $report = ContentReport::query()->create([
            'user_id' => $user->id,
            'reportable_type' => $model::class,
            'reportable_id' => $model->getKey(),
            'category' => $category,
            'message' => $message,
            'status' => ContentReport::STATUS_NEW,
        ])->load(['user', 'reportable']);

        try {
            Mail::to('dave@mygigguide.co.za')
                ->send(new ContentReportMail($report));
        } catch (\Throwable $e) {
            \Log::error('Failed to send content report email', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $report;
    }

    public function resolve(ContentReport $report, User $admin, ?string $adminNotes = null): ContentReport
    {
        $report->update([
            'status' => ContentReport::STATUS_RESOLVED,
            'admin_notes' => $adminNotes !== null && trim($adminNotes) !== '' ? trim($adminNotes) : $report->admin_notes,
            'resolved_at' => now(),
            'resolved_by_user_id' => $admin->id,
        ]);

        return $report->fresh(['user', 'reportable', 'resolvedBy']);
    }

    public function dismiss(ContentReport $report, User $admin, ?string $adminNotes = null): ContentReport
    {
        $report->update([
            'status' => ContentReport::STATUS_DISMISSED,
            'admin_notes' => $adminNotes !== null && trim($adminNotes) !== '' ? trim($adminNotes) : $report->admin_notes,
            'resolved_at' => now(),
            'resolved_by_user_id' => $admin->id,
        ]);

        return $report->fresh(['user', 'reportable', 'resolvedBy']);
    }
}
