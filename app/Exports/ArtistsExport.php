<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ArtistsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected \Illuminate\Support\Collection $artists
    ) {}

    public function collection()
    {
        return $this->artists;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Stage Name',
            'Real Name',
            'Genre',
            'Bio',
            'Phone Number',
            'Contact Email',
            'Instagram',
            'Facebook',
            'Twitter',
            'User ID',
            'User Email',
            'User Name',
            'Created At',
            'Updated At',
        ];
    }

    /**
     * @param \App\Models\Artist $artist
     */
    public function map($artist): array
    {
        return [
            $artist->id,
            $artist->stage_name,
            $artist->real_name ?? '',
            $artist->genre ?? '',
            $artist->bio ?? '',
            $artist->phone_number ?? '',
            $artist->contact_email ?? '',
            $artist->instagram ?? '',
            $artist->facebook ?? '',
            $artist->twitter ?? '',
            $artist->user_id ?? '',
            $artist->user?->email ?? '',
            $artist->user?->name ?? '',
            $artist->created_at?->format('Y-m-d H:i:s') ?? '',
            $artist->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
