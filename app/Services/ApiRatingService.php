<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\Event;
use App\Models\Rating;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ApiRatingService
{
    /**
     * @return array{type: string, model: Model}
     */
    public function resolveRateable(string $type, int $id): array
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

    public function store(User $user, string $type, int $id, int $rating, ?string $review): Rating
    {
        ['model' => $model] = $this->resolveRateable($type, $id);

        $existing = Rating::query()
            ->where('user_id', $user->id)
            ->where('rateable_type', $model::class)
            ->where('rateable_id', $model->getKey())
            ->first();

        if ($existing) {
            $existing->update([
                'rating' => $rating,
                'review' => $review,
            ]);

            return $existing->fresh(['user']);
        }

        return Rating::query()->create([
            'user_id' => $user->id,
            'rateable_type' => $model::class,
            'rateable_id' => $model->getKey(),
            'rating' => $rating,
            'review' => $review,
        ])->load('user');
    }

    /**
     * @return Collection<int, Rating>
     */
    public function listReviews(string $type, int $id, int $offset, int $limit): Collection
    {
        ['model' => $model] = $this->resolveRateable($type, $id);

        return $model->ratings()
            ->with('user')
            ->latest()
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    public function reviewCount(string $type, int $id): int
    {
        ['model' => $model] = $this->resolveRateable($type, $id);

        return (int) $model->ratings()->count();
    }
}
