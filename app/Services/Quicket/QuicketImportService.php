<?php

namespace App\Services\Quicket;

use App\Helpers\NameNormalizer;
use App\Models\Artist;
use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use App\Services\EventDuplicateService;
use App\Services\EventPosterCardService;
use App\Services\GooglePlacesService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class QuicketImportService
{
    private const VENUE_PROXIMITY_METERS = 400;

    public function __construct(
        private readonly QuicketApiClient $client,
        private readonly EventDuplicateService $duplicateService,
        private readonly QuicketPosterService $posterService,
        private readonly EventPosterCardService $posterCardService,
        private readonly GooglePlacesService $googlePlaces,
    ) {}

    /**
     * Pull one page and optionally create events.
     *
     * @param  array{page?: int, page_size?: int, apply?: bool, provinces?: list<string>|null, categories?: list<int>|string|null}  $options
     * @return array{
     *     page: int,
     *     pages: int,
     *     records: int,
     *     fetched: int,
     *     kept: int,
     *     skipped_province: int,
     *     skipped_past: int,
     *     would_create: int,
     *     created: int,
     *     duplicates: int,
     *     venues_matched: int,
     *     venues_created: int,
     *     rows: list<array<string, mixed>>
     * }
     */
    public function pullPage(array $options = []): array
    {
        $apply = (bool) ($options['apply'] ?? false);
        $page = max(1, (int) ($options['page'] ?? 1));
        $pageSize = (int) ($options['page_size'] ?? config('quicket.page_size', 25));

        $quicketCategoryIds = $this->normalizeQuicketCategoryIds(
            $options['categories'] ?? config('quicket.categories', [1])
        );
        $defaultQuicketCategoryId = $quicketCategoryIds[0] ?? 1;

        $payload = $this->client->listEvents([
            'page' => $page,
            'page_size' => $pageSize,
            'categories' => $quicketCategoryIds,
        ]);

        $provinces = $options['provinces'] ?? config('quicket.province_allowlist', []);
        if (! is_array($provinces)) {
            $provinces = [];
        }
        $provinces = array_values(array_filter(array_map(
            fn ($p) => Str::lower(trim((string) $p)),
            $provinces
        )));

        $owner = null;
        if ($apply) {
            $owner = $this->resolveOwnerUser();
        }

        $stats = [
            'page' => $page,
            'pages' => $payload['pages'],
            'records' => $payload['records'],
            'fetched' => count($payload['results']),
            'kept' => 0,
            'skipped_province' => 0,
            'skipped_past' => 0,
            'would_create' => 0,
            'created' => 0,
            'duplicates' => 0,
            'venues_matched' => 0,
            'venues_created' => 0,
            'rows' => [],
        ];

        foreach ($payload['results'] as $raw) {
            $mapped = $this->mapEvent($raw);
            if ($mapped === null) {
                $stats['skipped_past']++;
                continue;
            }

            if ($provinces !== [] && ! $this->provinceAllowed($mapped['province'], $provinces)) {
                $stats['skipped_province']++;
                $stats['rows'][] = [
                    'action' => 'skip_province',
                    'quicket_id' => $mapped['quicket_id'],
                    'name' => $mapped['name'],
                    'province' => $mapped['province'],
                    'quicket_category_id' => $mapped['quicket_category_id'] ?? $defaultQuicketCategoryId,
                    'ticket_url' => $mapped['ticket_url'],
                ];
                continue;
            }

            $stats['kept']++;
            $quicketCatId = (int) ($mapped['quicket_category_id'] ?? $defaultQuicketCategoryId);

            $duplicate = null;
            if ($mapped['ticket_url'] !== '') {
                $duplicate = $this->duplicateService->findDuplicate([
                    'name' => $mapped['name'],
                    'date' => $mapped['date'],
                    'time' => $mapped['time'],
                    'venue_id' => 0,
                    'ticket_url' => $mapped['ticket_url'],
                ]);
            }

            if ($duplicate !== null) {
                $stats['duplicates']++;
                $stats['rows'][] = [
                    'action' => 'duplicate',
                    'quicket_id' => $mapped['quicket_id'],
                    'name' => $mapped['name'],
                    'date' => $mapped['date'],
                    'time' => $mapped['time'],
                    'venue' => $mapped['venue_name'],
                    'quicket_category_id' => $quicketCatId,
                    'ticket_url' => $mapped['ticket_url'],
                    'existing_event_id' => $duplicate->id,
                ];
                continue;
            }

            if (! $apply) {
                $stats['would_create']++;
                $stats['rows'][] = [
                    'action' => 'would_create',
                    'quicket_id' => $mapped['quicket_id'],
                    'name' => $mapped['name'],
                    'date' => $mapped['date'],
                    'time' => $mapped['time'],
                    'price' => $mapped['price'],
                    'venue' => $mapped['venue_name'],
                    'city' => $mapped['city'],
                    'province' => $mapped['province'],
                    'quicket_category_id' => $quicketCatId,
                    'mgg_slugs' => implode(',', $this->mggSlugsForQuicketCategory($quicketCatId, $mapped['name'], (string) ($mapped['description'] ?? ''))),
                    'ticket_url' => $mapped['ticket_url'],
                ];
                continue;
            }

            $venueResult = $this->resolveVenue($mapped);
            if ($venueResult['created']) {
                $stats['venues_created']++;
            } else {
                $stats['venues_matched']++;
            }

            $duplicateAfterVenue = $this->duplicateService->findDuplicate([
                'name' => $mapped['name'],
                'date' => $mapped['date'],
                'time' => $mapped['time'],
                'venue_id' => $venueResult['venue']->id,
                'ticket_url' => $mapped['ticket_url'],
            ]);
            if ($duplicateAfterVenue !== null) {
                $stats['duplicates']++;
                $stats['rows'][] = [
                    'action' => 'duplicate',
                    'quicket_id' => $mapped['quicket_id'],
                    'name' => $mapped['name'],
                    'existing_event_id' => $duplicateAfterVenue->id,
                    'ticket_url' => $mapped['ticket_url'],
                ];
                continue;
            }

            $posterPath = null;
            $posterCard = null;
            if (! empty($mapped['image_url']) || $mapped['ticket_url'] !== '') {
                $posterPath = $this->posterService->storeFromUrl(
                    (string) ($mapped['image_url'] ?? ''),
                    'imports/quicket/'.($mapped['quicket_id'] ?? 'x').'/poster',
                    $mapped['ticket_url'] !== '' ? $mapped['ticket_url'] : null
                );
                // Portrait sources may get an honest 2:3 card; landscape banners stay poster-only.
                if ($posterPath !== null) {
                    $posterCard = $this->portraitCardForPoster($posterPath);
                }
            }

            $event = Event::create([
                'name' => $mapped['name'],
                'description' => $mapped['description'],
                'date' => $mapped['date'],
                'time' => $mapped['time'],
                'price' => $mapped['price'],
                'ticket_url' => $mapped['ticket_url'],
                'poster' => $posterPath,
                'poster_card' => $posterCard,
                'status' => 'upcoming',
                'venue_id' => $venueResult['venue']->id,
                'owner_id' => $owner->id,
                'owner_type' => 'user',
            ]);

            $this->attachCategoriesForQuicketCategory($event, $quicketCatId);
            $this->attachArtistsFromPerformerNames($event, $mapped['performer_names'] ?? []);

            $stats['created']++;
            $stats['rows'][] = [
                'action' => 'created',
                'quicket_id' => $mapped['quicket_id'],
                'event_id' => $event->id,
                'name' => $mapped['name'],
                'date' => $mapped['date'],
                'time' => $mapped['time'],
                'venue' => $mapped['venue_name'],
                'venue_id' => $venueResult['venue']->id,
                'venue_match' => $venueResult['match_method'],
                'poster' => $posterPath ? 'yes' : 'no',
                'ticket_url' => $mapped['ticket_url'],
                'quicket_category_id' => $quicketCatId,
            ];
        }

        return $stats;
    }

    /**
     * Attach MGG categories for a Quicket API category to existing Quicket-linked events.
     *
     * @param  array{apply?: bool, limit?: int, quicket_category_id?: int}  $options
     * @return array<string, mixed>
     */
    public function backfillCategories(array $options = []): array
    {
        $apply = (bool) ($options['apply'] ?? false);
        $limit = max(1, (int) ($options['limit'] ?? 500));
        $quicketCategoryId = max(1, (int) ($options['quicket_category_id'] ?? 1));

        $slugs = $this->mggSlugsForQuicketCategory($quicketCategoryId);
        $categoryIds = $this->resolveCategoryIdsBySlugs($slugs);

        $events = Event::query()
            ->whereNotNull('ticket_url')
            ->where('ticket_url', 'like', '%quicket.co.za%')
            ->with('categories:id,slug')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $stats = [
            'candidates' => $events->count(),
            'already_tagged' => 0,
            'would_update' => 0,
            'updated' => 0,
            'missing_categories' => [],
            'quicket_category_id' => $quicketCategoryId,
            'rows' => [],
        ];

        if ($categoryIds === []) {
            $stats['missing_categories'] = $slugs;

            return $stats;
        }

        $foundSlugs = Category::query()
            ->whereIn('id', $categoryIds)
            ->pluck('slug')
            ->all();
        $stats['missing_categories'] = array_values(array_diff($slugs, $foundSlugs));

        foreach ($events as $event) {
            $have = $event->categories->pluck('id')->all();
            $need = array_values(array_diff($categoryIds, $have));

            if ($need === []) {
                $stats['already_tagged']++;
                $stats['rows'][] = [
                    'action' => 'already_tagged',
                    'event_id' => $event->id,
                    'name' => $event->name,
                ];
                continue;
            }

            if (! $apply) {
                $stats['would_update']++;
                $stats['rows'][] = [
                    'action' => 'would_update',
                    'event_id' => $event->id,
                    'name' => $event->name,
                    'adding' => implode(',', array_values(array_diff($slugs, $event->categories->pluck('slug')->all()))),
                ];
                continue;
            }

            $event->categories()->syncWithoutDetaching($need);
            $stats['updated']++;
            $stats['rows'][] = [
                'action' => 'updated',
                'event_id' => $event->id,
                'name' => $event->name,
            ];
        }

        return $stats;
    }

    /**
     * MGG slugs for a Quicket API category id (Music default).
     * Runs refineCategories() over the result so that broad Quicket buckets
     * (Arts & Culture, Other) pick up more specific tags from the event text.
     *
     * @return list<string>
     */
    public function mggSlugsForQuicketCategory(int $quicketCategoryId, ?string $eventTitle = null, ?string $description = null): array
    {
        $map = config('quicket.category_slug_map', []);
        if (is_array($map) && isset($map[$quicketCategoryId]) && is_array($map[$quicketCategoryId])) {
            $slugs = $this->normalizeSlugList($map[$quicketCategoryId]);
        } elseif (is_array($map) && isset($map[(string) $quicketCategoryId]) && is_array($map[(string) $quicketCategoryId])) {
            // String keys from PHP arrays sometimes
            $slugs = $this->normalizeSlugList($map[(string) $quicketCategoryId]);
        } else {
            $context = $eventTitle !== null ? " for event '{$eventTitle}'" : '';
            Log::warning("Quicket: unmapped category ID {$quicketCategoryId}{$context} — falling back to default slugs");
            $slugs = $this->configuredCategorySlugs();
        }

        if ($eventTitle !== null || $description !== null) {
            $slugs = $this->refineCategories($slugs, $eventTitle ?? '', $description ?? '');
        }

        return $slugs;
    }

    /**
     * Scan event title and description for keywords and add more specific MGG
     * category slugs when the base Quicket category is too broad to be reliable.
     * Only maps to slugs that exist in the MGG categories table (as seeded).
     * The `quicket` source tag is always preserved.
     *
     * @param  list<string>  $slugs  Base slugs already determined by category ID
     * @return list<string>
     */
    protected function refineCategories(array $slugs, string $title, string $description = ''): array
    {
        $text = mb_strtolower($title.' '.$description);

        // slug => keywords that trigger it (only slugs that exist in CategorySeeder)
        $keywordMap = [
            'comedy'      => ['comedy', 'stand-up', 'standup', 'comedian', 'comic', 'humour', 'humor'],
            'open-mic'    => ['open mic', 'open-mic', 'open_mic'],
            'theatre'     => ['theatre', 'theater', 'play', 'musical', 'opera', 'ballet', 'cabaret', 'pantomime', 'improv', 'drama'],
            'festival'    => ['festival'],
            'live-music'  => ['concert', 'live music', 'live band', 'gig'],
            'dj-club'     => ['dj set', 'club night', 'nightclub'],
            'family-friendly' => ['kids', 'children', 'family show', 'family event', 'youth'],
        ];

        foreach ($keywordMap as $slug => $keywords) {
            if (in_array($slug, $slugs, true)) {
                continue; // already tagged
            }
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) {
                    $slugs[] = $slug;
                    break;
                }
            }
        }

        // Always keep 'quicket' source tag
        if (! in_array('quicket', $slugs, true)) {
            $slugs[] = 'quicket';
        }

        return array_values(array_unique($slugs));
    }

    /**
     * @return list<string>
     */
    public function configuredCategorySlugs(): array
    {
        $slugs = config('quicket.mgg_category_slugs', ['live-music', 'quicket']);
        if (! is_array($slugs)) {
            return ['live-music', 'quicket'];
        }

        return $this->normalizeSlugList($slugs);
    }

    /**
     * @return list<int>
     */
    public function resolveConfiguredCategoryIds(): array
    {
        return $this->resolveCategoryIdsBySlugs($this->configuredCategorySlugs());
    }

    /**
     * @param  list<string>  $slugs
     * @return list<int>
     */
    public function resolveCategoryIdsBySlugs(array $slugs): array
    {
        if ($slugs === []) {
            return [];
        }

        return Category::query()
            ->whereIn('slug', $slugs)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function attachCategoriesForQuicketCategory(Event $event, int $quicketCategoryId): void
    {
        $ids = $this->resolveCategoryIdsBySlugs(
            $this->mggSlugsForQuicketCategory($quicketCategoryId, $event->name, (string) ($event->description ?? ''))
        );
        if ($ids === []) {
            return;
        }

        $event->categories()->syncWithoutDetaching($ids);
    }

    /**
     * @param  mixed  $raw
     * @return list<int>
     */
    public function normalizeQuicketCategoryIds(mixed $raw): array
    {
        if (is_string($raw)) {
            $raw = explode(',', $raw);
        }
        if (! is_array($raw)) {
            return [1];
        }

        $ids = [];
        foreach ($raw as $item) {
            $id = (int) $item;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return $ids !== [] ? array_values(array_unique($ids)) : [1];
    }

    /**
     * @param  list<mixed>  $slugs
     * @return list<string>
     */
    private function normalizeSlugList(array $slugs): array
    {
        return array_values(array_filter(array_map(
            fn ($s) => Str::lower(trim((string) $s)),
            $slugs
        )));
    }

    /**
     * Attach posters to existing Quicket-linked events that have none
     * (or all Quicket events when force=true).
     *
     * @param  array{apply?: bool, limit?: int, force?: bool}  $options
     * @return array<string, mixed>
     */
    public function backfillPosters(array $options = []): array
    {
        $apply = (bool) ($options['apply'] ?? false);
        $force = (bool) ($options['force'] ?? false);
        $limit = max(1, (int) ($options['limit'] ?? 50));

        $query = Event::query()
            ->whereNotNull('ticket_url')
            ->where('ticket_url', 'like', '%quicket.co.za%')
            ->orderByDesc('id');

        if (! $force) {
            $query->where(function ($q) {
                $q->whereNull('poster')->orWhere('poster', '');
            });
        }

        $events = $query->limit($limit)->get();

        $stats = [
            'candidates' => $events->count(),
            'matched' => 0,
            'already_have' => 0,
            'would_update' => 0,
            'updated' => 0,
            'failed' => 0,
            'no_image' => 0,
            'not_found' => 0,
            'rows' => [],
        ];

        foreach ($events as $event) {
            $quicketId = $this->quicketIdFromTicketUrl((string) $event->ticket_url);
            if ($quicketId === null) {
                $stats['not_found']++;
                $stats['rows'][] = [
                    'action' => 'bad_url',
                    'event_id' => $event->id,
                    'name' => $event->name,
                ];
                continue;
            }

            $stats['matched']++;

            if (! $force && ! empty($event->poster)) {
                $stats['already_have']++;
                continue;
            }

            try {
                $raw = $this->client->getEvent($quicketId);
            } catch (\Throwable $e) {
                $stats['failed']++;
                $stats['rows'][] = [
                    'action' => 'api_error',
                    'event_id' => $event->id,
                    'name' => $event->name,
                    'error' => mb_substr($e->getMessage(), 0, 80),
                ];
                continue;
            }

            $imageUrl = trim((string) ($raw['imageUrl'] ?? ''));
            $ticketUrl = (string) $event->ticket_url;

            if (! $apply) {
                $resolved = $this->posterService->resolveBestImageUrl($imageUrl !== '' ? $imageUrl : null, $ticketUrl);
                if ($resolved === null || $resolved === '') {
                    $stats['no_image']++;
                    $stats['rows'][] = [
                        'action' => 'no_image',
                        'event_id' => $event->id,
                        'name' => $event->name,
                    ];
                    continue;
                }
                $stats['would_update']++;
                $stats['rows'][] = [
                    'action' => 'would_update',
                    'event_id' => $event->id,
                    'name' => $event->name,
                    'quicket_id' => $quicketId,
                    'image_url' => $resolved,
                ];
                continue;
            }

            $posterPath = $this->posterService->storeFromUrl(
                $imageUrl,
                'imports/quicket/'.$quicketId.'/poster',
                $ticketUrl
            );
            if ($posterPath === null) {
                $stats['failed']++;
                $stats['rows'][] = [
                    'action' => 'failed',
                    'event_id' => $event->id,
                    'name' => $event->name,
                ];
                continue;
            }

            $posterCard = $this->portraitCardForPoster($posterPath);

            $event->update([
                'poster' => $posterPath,
                'poster_card' => $posterCard,
            ]);

            $stats['updated']++;
            $stats['rows'][] = [
                'action' => 'updated',
                'event_id' => $event->id,
                'name' => $event->name,
                'poster' => $posterPath,
                'poster_card' => $posterCard ? 'yes' : 'skipped_landscape',
            ];
        }

        return $stats;
    }

    /**
     * Regenerate poster_card (2:3) from existing local poster files — no Quicket download.
     * Skips landscape banners (no invented crops).
     *
     * @param  array{apply?: bool, limit?: int, force?: bool}  $options
     * @return array<string, mixed>
     */
    public function backfillPosterCards(array $options = []): array
    {
        $apply = (bool) ($options['apply'] ?? false);
        $force = (bool) ($options['force'] ?? false);
        $limit = max(1, (int) ($options['limit'] ?? 200));

        $query = Event::query()
            ->whereNotNull('ticket_url')
            ->where('ticket_url', 'like', '%quicket.co.za%')
            ->whereNotNull('poster')
            ->where('poster', '!=', '')
            ->orderByDesc('id');

        if (! $force) {
            $query->where(function ($q) {
                $q->whereNull('poster_card')->orWhere('poster_card', '');
            });
        }

        $events = $query->limit($limit)->get();
        $disk = Storage::disk('public');

        $stats = [
            'candidates' => $events->count(),
            'would_update' => 0,
            'updated' => 0,
            'skipped_landscape' => 0,
            'missing_file' => 0,
            'failed' => 0,
            'rows' => [],
        ];

        foreach ($events as $event) {
            $posterPath = (string) $event->poster;
            if (! $disk->exists($posterPath)) {
                $stats['missing_file']++;
                $stats['rows'][] = [
                    'action' => 'missing_file',
                    'event_id' => $event->id,
                    'name' => $event->name,
                    'poster' => $posterPath,
                ];
                continue;
            }

            if ($this->posterService->isLandscapePoster($posterPath)) {
                $stats['skipped_landscape']++;
                $stats['rows'][] = [
                    'action' => 'skipped_landscape',
                    'event_id' => $event->id,
                    'name' => $event->name,
                    'poster' => $posterPath,
                ];
                continue;
            }

            if (! $apply) {
                $stats['would_update']++;
                $stats['rows'][] = [
                    'action' => 'would_update',
                    'event_id' => $event->id,
                    'name' => $event->name,
                    'poster' => $posterPath,
                ];
                continue;
            }

            if (! empty($event->poster_card)) {
                $this->posterCardService->deletePortraitCard($event->poster_card);
            }

            $posterCard = $this->posterCardService->generatePortraitCard($posterPath);
            if ($posterCard === null) {
                $stats['failed']++;
                $stats['rows'][] = [
                    'action' => 'failed',
                    'event_id' => $event->id,
                    'name' => $event->name,
                    'poster' => $posterPath,
                ];
                continue;
            }

            $event->update(['poster_card' => $posterCard]);
            $stats['updated']++;
            $stats['rows'][] = [
                'action' => 'updated',
                'event_id' => $event->id,
                'name' => $event->name,
                'poster_card' => $posterCard,
            ];
        }

        return $stats;
    }

    /**
     * Clear poster_card on Quicket-linked events (restore full poster + letterbox in lists).
     * Does not touch non-Quicket user-upload cards.
     *
     * @param  array{apply?: bool, limit?: int, landscape_only?: bool}  $options
     * @return array<string, mixed>
     */
    public function clearQuicketPosterCards(array $options = []): array
    {
        $apply = (bool) ($options['apply'] ?? false);
        $landscapeOnly = (bool) ($options['landscape_only'] ?? false);
        $limit = max(1, (int) ($options['limit'] ?? 500));

        $events = Event::query()
            ->whereNotNull('ticket_url')
            ->where('ticket_url', 'like', '%quicket.co.za%')
            ->whereNotNull('poster_card')
            ->where('poster_card', '!=', '')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $disk = Storage::disk('public');

        $stats = [
            'candidates' => $events->count(),
            'would_clear' => 0,
            'cleared' => 0,
            'skipped_portrait' => 0,
            'missing_poster' => 0,
            'rows' => [],
        ];

        foreach ($events as $event) {
            $posterPath = (string) ($event->poster ?? '');
            $cardPath = (string) $event->poster_card;

            if ($landscapeOnly) {
                if ($posterPath === '' || ! $disk->exists($posterPath)) {
                    // No readable source — still clear forced Quicket cards when landscape-only
                    // cannot be verified? Prefer clear to undo bad force-backfill.
                    $stats['missing_poster']++;
                    // Fall through and clear — Quicket force-backfill damaged these.
                } elseif (! $this->posterService->isLandscapePoster($posterPath)) {
                    $stats['skipped_portrait']++;
                    $stats['rows'][] = [
                        'action' => 'skipped_portrait',
                        'event_id' => $event->id,
                        'name' => $event->name,
                        'poster_card' => $cardPath,
                    ];
                    continue;
                }
            }

            if (! $apply) {
                $stats['would_clear']++;
                $stats['rows'][] = [
                    'action' => 'would_clear',
                    'event_id' => $event->id,
                    'name' => $event->name,
                    'poster_card' => $cardPath,
                ];
                continue;
            }

            $this->posterCardService->deletePortraitCard($cardPath);
            $event->update(['poster_card' => null]);

            $stats['cleared']++;
            $stats['rows'][] = [
                'action' => 'cleared',
                'event_id' => $event->id,
                'name' => $event->name,
                'poster_card' => $cardPath,
            ];
        }

        return $stats;
    }

    /**
     * Honest 2:3 card for portrait posters only — never invent crops from landscape banners.
     */
    public function portraitCardForPoster(?string $posterPath): ?string
    {
        return $this->posterCardService->portraitCardForPoster($posterPath);
    }

    public function quicketIdFromTicketUrl(string $ticketUrl): ?string
    {
        if (preg_match('#quicket\.co\.za/events/(\d+)#i', $ticketUrl, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array{
     *     quicket_id: int|string|null,
     *     quicket_category_id: int|null,
     *     name: string,
     *     description: string|null,
     *     date: string,
     *     time: string,
     *     price: float|int,
     *     ticket_url: string,
     *     image_url: string|null,
     *     venue_name: string,
     *     venue_address: string,
     *     city: string,
     *     province: string,
     *     latitude: float|null,
     *     longitude: float|null,
     *     performer_names: list<string>
     * }|null
     */
    public function mapEvent(array $raw): ?array
    {
        $name = trim(html_entity_decode(strip_tags((string) ($raw['name'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($name === '') {
            return null;
        }

        $startRaw = (string) ($raw['startDate'] ?? '');
        if ($startRaw === '') {
            return null;
        }

        try {
            $start = Carbon::parse($startRaw, 'Africa/Johannesburg');
        } catch (\Throwable) {
            return null;
        }

        if ($start->lt(now('Africa/Johannesburg')->startOfDay())) {
            return null;
        }

        $venue = is_array($raw['venue'] ?? null) ? $raw['venue'] : [];
        $locality = is_array($raw['locality'] ?? null) ? $raw['locality'] : [];

        $ticketUrl = trim((string) ($raw['url'] ?? ''));
        if ($ticketUrl !== '' && ! Str::startsWith($ticketUrl, ['http://', 'https://'])) {
            $ticketUrl = 'https://'.ltrim($ticketUrl, '/');
        }

        $imageUrl = trim((string) ($raw['imageUrl'] ?? ''));
        if ($imageUrl !== '' && Str::startsWith($imageUrl, '//')) {
            $imageUrl = 'https:'.$imageUrl;
        }

        $description = trim(html_entity_decode(strip_tags((string) ($raw['description'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if (mb_strlen($description) > 5000) {
            $description = mb_substr($description, 0, 5000).'…';
        }

        $addressParts = array_filter([
            trim((string) ($venue['addressLine1'] ?? '')),
            trim((string) ($venue['addressLine2'] ?? '')),
        ]);

        return [
            'quicket_id' => $raw['id'] ?? null,
            'quicket_category_id' => $this->extractQuicketCategoryId($raw),
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'date' => $start->toDateString(),
            'time' => $start->format('H:i'),
            'price' => $this->lowestTicketPrice($raw),
            'ticket_url' => $ticketUrl,
            'image_url' => $imageUrl !== '' ? $imageUrl : null,
            'venue_name' => trim((string) ($venue['name'] ?? '')) ?: 'TBA',
            'venue_address' => implode(', ', $addressParts),
            'city' => trim((string) ($locality['levelThree'] ?? '')),
            'province' => trim((string) ($locality['levelTwo'] ?? '')),
            'latitude' => isset($venue['latitude']) ? (float) $venue['latitude'] : null,
            'longitude' => isset($venue['longitude']) ? (float) $venue['longitude'] : null,
            'performer_names' => $this->extractPerformerNames($raw),
        ];
    }

    /**
     * Best-effort Quicket category id from a raw event payload.
     * When absent, pullPage falls back to the requested categories filter.
     *
     * @param  array<string, mixed>  $raw
     */
    public function extractQuicketCategoryId(array $raw): ?int
    {
        foreach (['categoryId', 'CategoryId', 'category_id'] as $key) {
            if (isset($raw[$key]) && is_numeric($raw[$key]) && (int) $raw[$key] > 0) {
                return (int) $raw[$key];
            }
        }

        $category = $raw['category'] ?? null;
        if (is_array($category)) {
            foreach (['id', 'Id', 'categoryId'] as $key) {
                if (isset($category[$key]) && is_numeric($category[$key]) && (int) $category[$key] > 0) {
                    return (int) $category[$key];
                }
            }
        } elseif (is_numeric($category) && (int) $category > 0) {
            return (int) $category;
        }

        $categories = $raw['categories'] ?? null;
        if (is_array($categories) && $categories !== []) {
            $first = $categories[0];
            if (is_numeric($first) && (int) $first > 0) {
                return (int) $first;
            }
            if (is_array($first)) {
                foreach (['id', 'Id', 'categoryId'] as $key) {
                    if (isset($first[$key]) && is_numeric($first[$key]) && (int) $first[$key] > 0) {
                        return (int) $first[$key];
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    private function lowestTicketPrice(array $raw): float|int
    {
        $tickets = $raw['tickets'] ?? [];
        if (! is_array($tickets) || $tickets === []) {
            return 0;
        }

        $prices = [];
        foreach ($tickets as $ticket) {
            if (! is_array($ticket)) {
                continue;
            }
            if (! empty($ticket['soldOut']) || ! empty($ticket['donation'])) {
                continue;
            }
            if (! isset($ticket['price']) || ! is_numeric($ticket['price'])) {
                continue;
            }
            $prices[] = (float) $ticket['price'];
        }

        if ($prices === []) {
            return 0;
        }

        $min = min($prices);

        return $min == (int) $min ? (int) $min : round($min, 2);
    }

    /**
     * @param  list<string>  $allowlistLower
     */
    private function provinceAllowed(string $province, array $allowlistLower): bool
    {
        $p = Str::lower(trim($province));
        if ($p === '') {
            return false;
        }

        foreach ($allowlistLower as $allowed) {
            if ($allowed !== '' && ($p === $allowed || Str::contains($p, $allowed) || Str::contains($allowed, $p))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $mapped
     * @return array{venue: Venue, created: bool, match_method: string}
     */
    private function resolveVenue(array $mapped): array
    {
        $name = (string) $mapped['venue_name'];
        $lat = $mapped['latitude'];
        $lng = $mapped['longitude'];
        $address = (string) ($mapped['venue_address'] ?? '');

        if (is_float($lat) && is_float($lng)) {
            $byPlaceId = $this->findByGooglePlaceId($name, $lat, $lng);
            if ($byPlaceId !== null) {
                $this->ensureVenuePhoto($byPlaceId, $name, $lat, $lng);

                return ['venue' => $byPlaceId->fresh(), 'created' => false, 'match_method' => 'google_place_id'];
            }

            $nearby = $this->findNearbyByName($name, $lat, $lng);
            if ($nearby !== null) {
                $this->ensureVenuePhoto($nearby, $name, $lat, $lng);

                return ['venue' => $nearby->fresh(), 'created' => false, 'match_method' => 'proximity'];
            }
        }

        $byNameAddress = $this->findByNameAndAddress($name, $address);
        if ($byNameAddress !== null) {
            $this->ensureVenuePhoto(
                $byNameAddress,
                $name,
                is_float($lat) ? $lat : null,
                is_float($lng) ? $lng : null
            );

            return ['venue' => $byNameAddress->fresh(), 'created' => false, 'match_method' => 'name_address'];
        }

        $byNameCity = $this->findByNameAndCity($name, (string) $mapped['city']);
        if ($byNameCity !== null) {
            $this->ensureVenuePhoto(
                $byNameCity,
                $name,
                is_float($lat) ? $lat : null,
                is_float($lng) ? $lng : null
            );

            return ['venue' => $byNameCity->fresh(), 'created' => false, 'match_method' => 'name_city'];
        }

        $venue = Venue::create([
            'name' => $name,
            'address' => $mapped['venue_address'] !== '' ? $mapped['venue_address'] : null,
            'city' => $mapped['city'] !== '' ? $mapped['city'] : null,
            'latitude' => $lat,
            'longitude' => $lng,
            'contact_email' => 'quicket-import+'.Str::lower(Str::random(8)).'@example.local',
            'user_id' => null,
            'owner_id' => null,
            'owner_type' => null,
        ]);

        $this->ensureVenuePhoto(
            $venue,
            $name,
            is_float($lat) ? $lat : null,
            is_float($lng) ? $lng : null
        );

        return ['venue' => $venue->fresh(), 'created' => true, 'match_method' => 'created'];
    }

    private function ensureVenuePhoto(Venue $venue, string $name, ?float $lat, ?float $lng): void
    {
        if (! empty($venue->main_picture)) {
            return;
        }

        try {
            $found = $this->googlePlaces->findPlaceNear($name, $lat, $lng);
        } catch (\Throwable) {
            return;
        }

        if ($found === null) {
            return;
        }

        $updates = [];
        $placeId = trim((string) ($found['place_id'] ?? ''));
        if ($placeId !== '' && empty($venue->google_place_id)) {
            $taken = Venue::query()
                ->where('google_place_id', $placeId)
                ->where('id', '!=', $venue->id)
                ->exists();
            if (! $taken) {
                $updates['google_place_id'] = $placeId;
            }
        }

        $photoRef = $found['photo_reference'];
        if ($photoRef === null && $placeId !== '') {
            try {
                $photoRef = $this->googlePlaces->firstPhotoReferenceForPlace($placeId);
            } catch (\Throwable) {
                $photoRef = null;
            }
        }

        if ($photoRef !== null) {
            try {
                $path = $this->googlePlaces->downloadPhoto($photoRef, 'imports/quicket/venues');
                if ($path !== null) {
                    $updates['main_picture'] = $path;
                }
            } catch (\Throwable) {
                // Photo is optional — never abort the import for it.
            }
        }

        if ($updates === []) {
            return;
        }

        try {
            $venue->update($updates);
        } catch (\Throwable $e) {
            // Unique google_place_id races, etc. — keep going without photo/place link.
            try {
                if (isset($updates['main_picture'])) {
                    $venue->update(['main_picture' => $updates['main_picture']]);
                }
            } catch (\Throwable) {
                // ignore
            }
        }
    }

    private function findNearbyByName(string $name, float $lat, float $lng): ?Venue
    {
        $candidates = Venue::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [$lat - 0.05, $lat + 0.05])
            ->whereBetween('longitude', [$lng - 0.05, $lng + 0.05])
            ->limit(80)
            ->get();

        foreach ($candidates as $venue) {
            if (! $this->namesLikelySame($name, (string) $venue->name)) {
                continue;
            }
            $distance = $this->haversineMeters($lat, $lng, (float) $venue->latitude, (float) $venue->longitude);
            if ($distance <= self::VENUE_PROXIMITY_METERS) {
                return $venue;
            }
        }

        return null;
    }

    private function findByGooglePlaceId(string $name, float $lat, float $lng): ?Venue
    {
        try {
            $found = $this->googlePlaces->findPlaceNear($name, $lat, $lng);
        } catch (\Throwable) {
            return null;
        }

        if ($found === null) {
            return null;
        }

        $placeId = trim((string) ($found['place_id'] ?? ''));
        if ($placeId === '') {
            return null;
        }

        return Venue::query()->where('google_place_id', $placeId)->first();
    }

    private function findByNameAndAddress(string $name, string $address): ?Venue
    {
        $address = trim($address);
        if ($address === '') {
            return null;
        }

        $normalizedAddress = Str::lower($address);

        foreach (Venue::query()->orderBy('id')->whereNotNull('address')->limit(500)->get() as $venue) {
            if (! $this->namesLikelySame($name, (string) $venue->name)) {
                continue;
            }

            if (Str::lower(trim((string) $venue->address)) === $normalizedAddress) {
                return $venue;
            }
        }

        return null;
    }

    private function findByNameAndCity(string $name, string $city): ?Venue
    {
        if ($city !== '') {
            foreach (Venue::query()
                ->orderBy('id')
                ->where('city', 'like', '%'.$city.'%')
                ->limit(100)
                ->get() as $venue) {
                if ($this->namesLikelySame($name, (string) $venue->name)) {
                    return $venue;
                }
            }
        }

        // Legacy CSV rows often have no city — still match on normalized name.
        foreach (Venue::query()
            ->orderBy('id')
            ->where(function ($query) {
                $query->whereNull('city')->orWhere('city', '');
            })
            ->limit(200)
            ->get() as $venue) {
            if ($this->namesLikelySame($name, (string) $venue->name)) {
                return $venue;
            }
        }

        return null;
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

        similar_text($na, $nb, $percent);

        return $percent >= 88.0;
    }

    private function haversineMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371000.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return 2 * $earth * asin(min(1, sqrt($a)));
    }

    private function resolveOwnerUser(): User
    {
        $id = config('quicket.owner_user_id');
        if (! is_int($id) || $id < 1) {
            throw new RuntimeException(
                'Set QUICKET_OWNER_USER_ID in .env to a real users.id before using --apply.'
            );
        }

        $user = User::query()->find($id);
        if ($user === null) {
            throw new RuntimeException("QUICKET_OWNER_USER_ID={$id} does not exist.");
        }

        return $user;
    }

    /**
     * Extract performer/artist names from a raw Quicket API payload.
     * The Quicket API does not officially document a performers field, but we
     * probe common field names so this auto-activates if they ever appear.
     *
     * @param  array<string, mixed>  $raw
     * @return list<string>
     */
    public function extractPerformerNames(array $raw): array
    {
        $names = [];

        foreach (['performers', 'artists', 'lineup', 'headliners'] as $field) {
            $value = $raw[$field] ?? null;
            if (! is_array($value) || $value === []) {
                continue;
            }

            foreach ($value as $item) {
                if (is_string($item) && trim($item) !== '') {
                    $names[] = trim($item);
                } elseif (is_array($item)) {
                    foreach (['name', 'stageName', 'stage_name', 'title', 'artistName'] as $nameKey) {
                        if (isset($item[$nameKey]) && is_string($item[$nameKey]) && trim($item[$nameKey]) !== '') {
                            $names[] = trim($item[$nameKey]);
                            break;
                        }
                    }
                }
            }

            if ($names !== []) {
                break;
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * Match each performer name to an existing Artist row (fuzzy) or create a new one.
     * Results are attached to the given event.
     *
     * @param  list<string>  $performerNames
     */
    private function attachArtistsFromPerformerNames(Event $event, array $performerNames): void
    {
        if ($performerNames === []) {
            return;
        }

        $artistIds = [];

        foreach ($performerNames as $rawName) {
            $artist = $this->resolveArtistByName($rawName);
            if ($artist !== null) {
                $artistIds[] = $artist->id;
            }
        }

        if ($artistIds !== []) {
            $event->artists()->syncWithoutDetaching($artistIds);
        }
    }

    /**
     * Find an existing Artist by fuzzy name match, or create a new one.
     * Match priority: exact (case-insensitive) → normalized → substring → Levenshtein ≤ 2.
     */
    private function resolveArtistByName(string $name): ?Artist
    {
        $normalized = NameNormalizer::normalize($name);
        if ($normalized === '') {
            return null;
        }

        $candidates = Artist::query()
            ->whereNotNull('stage_name')
            ->where('stage_name', '!=', '')
            ->get(['id', 'stage_name']);

        $bestMatch = null;
        foreach ($candidates as $candidate) {
            $candidateName = (string) $candidate->stage_name;

            // Case-insensitive exact match
            if (mb_strtolower($name) === mb_strtolower($candidateName)) {
                $bestMatch = $candidate;
                break;
            }

            // Normalized match (strips punctuation/accents)
            $candidateNorm = NameNormalizer::normalize($candidateName);
            if ($normalized === $candidateNorm) {
                $bestMatch = $candidate;
                break;
            }

            // Substring containment (one name fully contained in the other)
            if ($candidateNorm !== '' && (
                str_contains($normalized, $candidateNorm) ||
                str_contains($candidateNorm, $normalized)
            )) {
                $bestMatch = $candidate;
                // Don't break — a later exact match is preferred
                continue;
            }

            // Levenshtein ≤ 2 for short names (≤ 10 chars normalized, avoids false positives on long names)
            if ($bestMatch === null && mb_strlen($normalized) <= 10 && mb_strlen($candidateNorm) <= 10) {
                if (levenshtein($normalized, $candidateNorm) <= 2) {
                    $bestMatch = $candidate;
                }
            }
        }

        if ($bestMatch !== null) {
            Log::info("Quicket artist matched: '{$name}' → Artist ID {$bestMatch->id} '{$bestMatch->stage_name}'");

            return $bestMatch;
        }

        // No match — create a new unclaimed artist stub
        $artist = Artist::create([
            'stage_name' => $name,
        ]);

        Log::info("Quicket artist created: '{$name}' → Artist ID {$artist->id}");

        return $artist;
    }
}
