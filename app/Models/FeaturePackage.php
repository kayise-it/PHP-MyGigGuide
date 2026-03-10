<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeaturePackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'paid_feature_id',
        'feature_program_id',
        'name',
        'duration_days',
        'price_cents',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function feature(): BelongsTo
    {
        return $this->belongsTo(PaidFeature::class, 'paid_feature_id');
    }
}


