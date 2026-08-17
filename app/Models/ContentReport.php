<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentReport extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_DISMISSED = 'dismissed';

    /** @var array<string, string> */
    public const CATEGORIES = [
        'wrong_date_time' => 'Wrong date / time',
        'wrong_venue' => 'Wrong venue or location',
        'wrong_lineup' => 'Wrong artists / lineup',
        'cancelled_or_past' => 'Cancelled or already happened',
        'duplicate' => 'Duplicate listing',
        'spam_or_fake' => 'Spam or fake',
        'wrong_links' => 'Wrong links / contact info',
        'other' => 'Other',
    ];

    protected $fillable = [
        'user_id',
        'reportable_type',
        'reportable_id',
        'category',
        'message',
        'status',
        'admin_notes',
        'resolved_at',
        'resolved_by_user_id',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function reportableTypeLabel(): string
    {
        return match ($this->reportable_type) {
            Event::class => 'Event',
            Artist::class => 'Artist',
            Venue::class => 'Venue',
            default => class_basename((string) $this->reportable_type),
        };
    }

    public function reportableTitle(): string
    {
        $target = $this->reportable;

        if (! $target) {
            return $this->reportableTypeLabel().' #'.$this->reportable_id;
        }

        return match (true) {
            $target instanceof Event => (string) ($target->name ?: 'Event #'.$target->id),
            $target instanceof Artist => (string) ($target->stage_name ?: 'Artist #'.$target->id),
            $target instanceof Venue => (string) ($target->name ?: 'Venue #'.$target->id),
            default => $this->reportableTypeLabel().' #'.$target->getKey(),
        };
    }
}
