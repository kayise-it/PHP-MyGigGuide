<?php

namespace App\Imports;

use App\Models\Artist;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ArtistsImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public int $created = 0;
    public int $updated = 0;

    public int $skipped = 0;

    /** @var array<int, string> */
    public array $errors = [];

    /** @var array<int, string> */
    public array $warnings = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // 1-based, +1 for header

            $rowData = [
                'id' => $this->getCell($row, 'id'),
                'stage_name' => trim((string) $this->getCell($row, 'stage_name', '')),
                'real_name' => trim((string) $this->getCell($row, 'real_name', '')),
                'genre' => trim((string) $this->getCell($row, 'genre', '')),
                'bio' => trim((string) $this->getCell($row, 'bio', '')),
                'phone_number' => trim((string) $this->getCell($row, 'phone_number', '')),
                'contact_email' => trim((string) $this->getCell($row, 'contact_email', '')),
                'instagram' => trim((string) $this->getCell($row, 'instagram', '')),
                'facebook' => trim((string) $this->getCell($row, 'facebook', '')),
                'twitter' => trim((string) $this->getCell($row, 'twitter', '')),
                'tiktok' => trim((string) $this->getCell($row, 'tiktok', '')),
                'user_id' => $this->getCell($row, 'user_id'),
                'profile_picture' => trim((string) $this->getCell($row, 'profile_picture', '')),
                'gallery_json' => trim((string) $this->getCell($row, 'gallery_json', '')),
            ];

            // Normalize user_id
            if ($rowData['user_id'] !== null && $rowData['user_id'] !== '') {
                $rowData['user_id'] = is_numeric($rowData['user_id'])
                    ? (int) $rowData['user_id']
                    : null;
            } else {
                $rowData['user_id'] = null;
            }

            // Skip empty rows
            if (empty($rowData['stage_name']) && empty($rowData['real_name']) && empty($rowData['genre'])) {
                continue;
            }

            if (empty($rowData['stage_name'])) {
                $this->errors[] = "Row {$rowNum}: Stage name is required";
                $this->skipped++;
                continue;
            }

            if (empty($rowData['genre'])) {
                $this->warnings[] = "Row {$rowNum}: Genre missing, defaulted to 'Unknown'";
                $rowData['genre'] = 'Unknown';
            }

            $artist = null;
            if (! empty($rowData['id']) && is_numeric($rowData['id'])) {
                $artist = Artist::find((int) $rowData['id']);
            }
            if (! $artist) {
                $artist = Artist::whereRaw('LOWER(stage_name) = ?', [strtolower($rowData['stage_name'])])->first();
            }

            if (! empty($rowData['user_id'])) {
                $user = User::find($rowData['user_id']);
                if (! $user) {
                    // Live exports may reference user IDs that do not exist locally.
                    // Import artist as unclaimed instead of skipping the whole row.
                    $this->warnings[] = "Row {$rowNum}: User ID {$rowData['user_id']} not found, imported as unclaimed artist";
                    $rowData['user_id'] = null;
                }
            }

            if (empty($rowData['contact_email']) && empty($rowData['user_id'])) {
                $base = Str::slug($rowData['stage_name']);
                $rowData['contact_email'] = $base . '+' . time() . rand(1000, 9999) . '@example.local';
            }

            $profilePicturePath = $this->resolveProfilePictureForImport($rowData['profile_picture'], $rowNum);
            $gallery = $this->parseGalleryJsonForImport($rowData['gallery_json'], $rowNum);

            $updateData = array_filter([
                'stage_name' => $rowData['stage_name'],
                'real_name' => $rowData['real_name'] ?: null,
                'genre' => $rowData['genre'],
                'bio' => $rowData['bio'] ?: null,
                'phone_number' => $rowData['phone_number'] ?: null,
                'contact_email' => $rowData['contact_email'] ?: null,
                'instagram' => $rowData['instagram'] ?: null,
                'facebook' => $rowData['facebook'] ?: null,
                'twitter' => $rowData['twitter'] ?: null,
                'tiktok' => $rowData['tiktok'] ?: null,
                'user_id' => $rowData['user_id'],
            ], fn ($v) => $v !== '');

            if ($profilePicturePath !== null && $profilePicturePath !== '') {
                $updateData['profile_picture'] = $profilePicturePath;
            }
            if ($gallery !== null && $gallery !== []) {
                $updateData['gallery'] = $gallery;
            }

            try {
                if ($artist) {
                    $artist->update($updateData);
                    $this->updated++;
                } else {
                    Artist::create($updateData);
                    $this->created++;
                }
                $this->imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Row {$rowNum}: " . $e->getMessage();
                $this->skipped++;
            }
        }
    }

    /**
     * Get cell value by heading (supports slugified keys from WithHeadingRow).
     * Laravel Excel uses str_slug() so "Stage Name" becomes "stage-name".
     */
    protected function getCell(array|Collection $row, string $key, mixed $default = null): mixed
    {
        if ($row instanceof Collection) {
            $row = $row->toArray();
        }

        $variants = [
            $key,
            str_replace('_', '-', $key),   // stage_name -> stage-name
            str_replace('-', '_', $key),   // stage-name -> stage_name
            Str::slug($key, '_'),
            Str::slug($key, '-'),
        ];

        // Heading "Gallery JSON" slugifies to gallery-json / gallery_json depending on Excel package
        if ($key === 'gallery_json') {
            $variants = array_merge($variants, ['gallery-json', 'gallery_json', 'galleryjson']);
        }
        if ($key === 'profile_picture') {
            $variants = array_merge($variants, ['profile-picture', 'profile picture']);
        }

        foreach ($variants as $variant) {
            if (isset($row[$variant])) {
                $val = $row[$variant];
                return $val === '' || $val === null ? $default : $val;
            }
        }

        return $default;
    }

    /**
     * Accepts: empty, https URL (download into storage), or relative public-disk path (e.g. artists/profile_pictures/…).
     * Also accepts /storage/… URLs from the site — strips to disk path. Paths are stored even if the file is not
     * present yet (e.g. after rsync from production).
     */
    protected function resolveProfilePictureForImport(string $raw, int $rowNum): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        if (str_contains($raw, '..')) {
            $this->warnings[] = "Row {$rowNum}: Ignored profile picture path containing '..'";

            return null;
        }

        if (preg_match('#^https?://#i', $raw)) {
            try {
                $response = Http::timeout(60)->withOptions(['allow_redirects' => true])->get($raw);
                if (! $response->successful()) {
                    $this->warnings[] = "Row {$rowNum}: Profile picture URL HTTP {$response->status()}";

                    return null;
                }
                $body = $response->body();
                if (strlen($body) < 64) {
                    $this->warnings[] = "Row {$rowNum}: Profile picture download too small / empty";

                    return null;
                }
                $ext = 'jpg';
                $ct = strtolower((string) $response->header('Content-Type'));
                if (str_contains($ct, 'png')) {
                    $ext = 'png';
                } elseif (str_contains($ct, 'webp')) {
                    $ext = 'webp';
                } elseif (str_contains($ct, 'gif')) {
                    $ext = 'gif';
                }
                $name = 'artists/profile_pictures/import_' . Str::random(12) . '.' . $ext;
                Storage::disk('public')->put($name, $body);

                return $name;
            } catch (\Throwable $e) {
                $this->warnings[] = "Row {$rowNum}: Profile picture download failed — {$e->getMessage()}";

                return null;
            }
        }

        if (str_starts_with($raw, '/storage/')) {
            $raw = ltrim(substr($raw, strlen('/storage/')), '/');
        }
        $raw = ltrim($raw, '/');
        if (str_starts_with($raw, 'storage/')) {
            $raw = substr($raw, strlen('storage/'));
        }

        return $raw !== '' ? $raw : null;
    }

    /**
     * @return array<int, string>|null
     */
    protected function parseGalleryJsonForImport(string $raw, int $rowNum): ?array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            $this->warnings[] = "Row {$rowNum}: Gallery JSON is not a valid JSON array — skipped";

            return null;
        }
        $out = [];
        foreach ($decoded as $item) {
            if (! is_string($item) || $item === '' || str_contains($item, '..')) {
                continue;
            }
            $p = trim($item);
            if (str_starts_with($p, '/storage/')) {
                $p = ltrim(substr($p, strlen('/storage/')), '/');
            }
            $p = ltrim($p, '/');
            if (str_starts_with($p, 'storage/')) {
                $p = substr($p, strlen('storage/'));
            }
            if ($p !== '') {
                $out[] = $p;
            }
        }

        return $out === [] ? null : $out;
    }
}
