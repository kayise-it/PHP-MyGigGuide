<?php

namespace App\Http\Resources\Api\V1\Concerns;

use Illuminate\Http\Request;

trait SerializesRatingSummary
{
    /**
     * @return array{average: float|null, count: int, user: array{rating: int, review: string|null}|null}
     */
    protected function ratingSummary(Request $request): array
    {
        $model = $this->resource;

        $count = (int) ($model->ratings_count ?? $model->ratings()->count());
        $averageRaw = $model->ratings_avg_rating ?? ($count > 0 ? $model->ratings()->avg('rating') : null);
        $average = $averageRaw !== null ? round((float) $averageRaw, 1) : null;

        $userBlock = null;
        $user = $request->user('sanctum');
        if ($user) {
            $mine = $model->ratings()->where('user_id', $user->id)->first();
            if ($mine) {
                $userBlock = [
                    'rating' => (int) $mine->rating,
                    'review' => $mine->review,
                ];
            }
        }

        return [
            'average' => $average,
            'count' => $count,
            'user' => $userBlock,
        ];
    }
}
