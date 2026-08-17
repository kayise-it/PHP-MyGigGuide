<?php

namespace Tests\Unit;

use App\Helpers\NameNormalizer;
use App\Services\VenueDirectorySearch;
use PHPUnit\Framework\TestCase;

class VenueDirectorySearchTest extends TestCase
{
    public function test_normalize_for_search_strips_hyphens_from_query(): void
    {
        $this->assertSame('fubar', VenueDirectorySearch::normalizeForSearch('Fubar'));
        $this->assertSame('fu bar', VenueDirectorySearch::normalizeForSearch('Fu-Bar'));
    }

    public function test_name_normalizer_treats_hyphen_variants_as_same_compact_form(): void
    {
        $this->assertSame(
            NameNormalizer::normalize('Fu-Bar'),
            NameNormalizer::normalize('Fubar'),
        );
    }
}
