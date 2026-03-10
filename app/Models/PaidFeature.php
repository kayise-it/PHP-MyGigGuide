<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaidFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'applies_to',
        'description',
        'duration_days',
        'price_cents',
        'currency',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(FeaturePurchase::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(FeatureProgram::class, 'feature_program_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(FeaturePackage::class, 'paid_feature_id');
    }
}


