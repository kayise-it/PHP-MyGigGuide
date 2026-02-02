<?php

namespace App\Imports;

use App\Models\Artist;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ArtistsImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;

    public int $skipped = 0;

    /** @var array<int, string> */
    public array $errors = [];

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
                'user_id' => $this->getCell($row, 'user_id'),
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
                $this->errors[] = "Row {$rowNum}: Genre is required";
                $this->skipped++;
                continue;
            }

            $artist = null;
            if (! empty($rowData['id']) && is_numeric($rowData['id'])) {
                $artist = Artist::find((int) $rowData['id']);
            }
            if (! $artist) {
                $artist = Artist::where('stage_name', $rowData['stage_name'])->first();
            }

            if (! empty($rowData['user_id'])) {
                $user = User::find($rowData['user_id']);
                if (! $user) {
                    $this->errors[] = "Row {$rowNum}: User ID {$rowData['user_id']} not found";
                    $this->skipped++;
                    continue;
                }
            }

            if (empty($rowData['contact_email']) && empty($rowData['user_id'])) {
                $base = Str::slug($rowData['stage_name']);
                $rowData['contact_email'] = $base . '+' . time() . rand(1000, 9999) . '@example.local';
            }

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
                'user_id' => $rowData['user_id'],
            ], fn ($v) => $v !== '');

            try {
                if ($artist) {
                    $artist->update($updateData);
                } else {
                    Artist::create($updateData);
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
    protected function getCell(array $row, string $key, mixed $default = null): mixed
    {
        $variants = [
            $key,
            str_replace('_', '-', $key),   // stage_name -> stage-name
            str_replace('-', '_', $key),   // stage-name -> stage_name
            Str::slug($key, '_'),
            Str::slug($key, '-'),
        ];

        foreach ($variants as $variant) {
            if (isset($row[$variant])) {
                $val = $row[$variant];
                return $val === '' || $val === null ? $default : $val;
            }
        }

        return $default;
    }
}
