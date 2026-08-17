<?php

namespace App\Services;

use App\Helpers\NameNormalizer;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Detect likely duplicate gigs before crowd-source create (same night, same place).
 */
class EventDuplicateService
{
    /** Minutes — two start times within this window on the same day count as the same slot. */
    private const TIME_WINDOW_MINUTES = 30;

    /**
     * @param  array{name: string, date: string, time: string, venue_id: int|string, ticket_url?: string|null}  $payload
     */
    public function findDuplicate(array $payload): ?Event
    {
        if (! empty($payload['ticket_url'])) {
            $byUrl = $this->findByTicketUrl((string) $payload['ticket_url']);
            if ($byUrl !== null) {
                return $byUrl;
            }
        }

        $venueId = (int) $payload['venue_id'];
        $day = Carbon::parse($payload['date'])->toDateString();
        $inputTime = $this->normalizeTimeString($payload['time']);

        if ($inputTime === null) {
            return null;
        }

        $candidates = Event::query()
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->where('venue_id', $venueId)
            ->whereDate('date', $day)
            ->orderBy('id')
            ->get();

        foreach ($candidates as $event) {
            if (! $this->timesCloseEnough($inputTime, $event)) {
                continue;
            }
            if ($this->namesLikelySame($payload['name'], $event->name)) {
                return $event;
            }
        }

        return null;
    }

    private function findByTicketUrl(string $url): ?Event
    {
        $needle = $this->normalizeTicketUrl($url);
        if ($needle === '') {
            return null;
        }

        return Event::query()
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->whereNotNull('ticket_url')
            ->where('ticket_url', '!=', '')
            ->orderBy('id')
            ->get()
            ->first(fn (Event $event) => $this->normalizeTicketUrl((string) $event->ticket_url) === $needle);
    }

    private function normalizeTicketUrl(string $url): string
    {
        $url = trim(strtolower($url));
        if ($url === '') {
            return '';
        }

        return rtrim($url, '/');
    }

    private function normalizeTimeString(string $time): ?int
    {
        $time = trim($time);
        if ($time === '') {
            return null;
        }

        if (preg_match('/^\d{1,2}:\d{2}$/', $time)) {
            [$h, $m] = array_map('intval', explode(':', $time));

            return $h * 60 + $m;
        }

        try {
            return Carbon::parse($time)->hour * 60 + Carbon::parse($time)->minute;
        } catch (\Throwable) {
            return null;
        }
    }

    private function eventStartMinutes(Event $event): ?int
    {
        $time = $event->time;
        if ($time instanceof \DateTimeInterface) {
            return ((int) $time->format('H')) * 60 + (int) $time->format('i');
        }
        if (is_string($time) && $time !== '') {
            return $this->normalizeTimeString($time);
        }

        return null;
    }

    private function timesCloseEnough(int $inputMinutes, Event $event): bool
    {
        $existing = $this->eventStartMinutes($event);
        if ($existing === null) {
            return true;
        }

        return abs($inputMinutes - $existing) <= self::TIME_WINDOW_MINUTES;
    }

    private function namesLikelySame(string $a, string $b): bool
    {
        if (NameNormalizer::areDuplicates($a, $b)) {
            return true;
        }

        $na = NameNormalizer::normalize($a);
        $nb = NameNormalizer::normalize($b);
        if ($na === '' || $nb === '') {
            return false;
        }

        if (Str::contains($na, $nb) || Str::contains($nb, $na)) {
            $shorter = min(strlen($na), strlen($nb));
            if ($shorter >= 8) {
                return true;
            }
        }

        similar_text($na, $nb, $percent);

        return $percent >= 85.0;
    }
}
