<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ArtistsImportTemplateExport implements FromArray, WithHeadings
{
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

    public function array(): array
    {
        return [
            [
                '',  // ID (leave blank for new artists)
                '',  // Stage Name (required)
                '',  // Real Name
                '',  // Genre (required)
                '',  // Bio
                '',  // Phone Number
                '',  // Contact Email (auto-generated for unclaimed if blank)
                '',  // Instagram
                '',  // Facebook
                '',  // Twitter
                '',  // User ID (optional existing artist user)
                '',  // User Email (ignored on import)
                '',  // User Name (ignored on import)
                '',  // Created At (ignored on import)
                '',  // Updated At (ignored on import)
            ],
        ];
    }
}
