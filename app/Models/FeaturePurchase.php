<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FeaturePurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'paid_feature_id',
        'featureable_id',
        'featureable_type',
        'price_cents_at_purchase',
        'currency',
        'status',
        'starts_at',
        'ends_at',
        'payment_meta',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'payment_meta' => 'array',
    ];

    public function feature(): BelongsTo
    {
        return $this->belongsTo(PaidFeature::class, 'paid_feature_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function featureable(): MorphTo
    {
        return $this->morphTo();
    }

    public function activateNow(): void
    {
        $durationDays = max(1, (int) ($this->feature->duration_days ?? 7));
        $now = CarbonImmutable::now();
        $this->starts_at = $now;
        $this->ends_at = $now->addDays($durationDays);
        $this->status = 'active';
        $this->save();
    }
}









