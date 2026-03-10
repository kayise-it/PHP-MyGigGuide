<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportVenuesFromExcel extends Command
{
    protected $signature = 'venues:import 
                            {--preview : Preview import without saving to database}
                            {--limit= : Limit number of venues to import}
                            {--skip-images : Skip image copying (faster for testing)}';

    protected $description = 'Import venues from Excel file and images from folders';

    private $mappingPath = 'public/venues_to_upload/venue_mapping.json';
    private $venuesPath = 'public/venues_to_upload/Venues';
    private $stats = [
        'total' => 0,
        'imported' => 0,
        'skipped' => 0,
        'errors' => 0,
    ];

    public function handle()
    {
        $this->info('🚀 Starting Venue Import Process...');
        $this->newLine();

        // Check if mapping file exists
        if (!File::exists(base_path($this->mappingPath))) {
            $this->error('❌ Mapping file not found: ' . $this->mappingPath);
            $this->error('Please run the Python mapping script first.');
            return 1;
        }

        // Load mapping data
        $mapping = json_decode(File::get(base_path($this->mappingPath)), true);
        $matchedVenues = $mapping['matched_venues'] ?? [];

        if (empty($matchedVenues)) {
            $this->error('❌ No matched venues found in mapping file.');
            return 1;
        }

        $this->info('📊 Found ' . count($matchedVenues) . ' venues to import');

        // Get or create system user for venues
        $systemUser = $this->getSystemUser();
        if (!$systemUser) {
            $this->error('❌ Could not find or create system user.');
            return 1;
        }

        $this->info('✅ Using user: ' . $systemUser->name . ' (ID: ' . $systemUser->id . ')');
        $this->newLine();

        // Apply limit if specified
        $limit = $this->option('limit');
        if ($limit) {
            $matchedVenues = array_slice($matchedVenues, 0, (int)$limit);
            $this->warn("⚠️  Limited to first {$limit} venues");
        }

        // Preview mode
        if ($this->option('preview')) {
            $this->previewImport($matchedVenues);
            return 0;
        }

        // Confirm before import
        if (!$this->confirm('Do you want to proceed with the import?', true)) {
            $this->warn('Import cancelled.');
            return 0;
        }

        $this->newLine();
        $this->info('📥 Starting import...');
        $this->newLine();

        // Progress bar
        $progressBar = $this->output->createProgressBar(count($matchedVenues));
        $progressBar->start();

        // Import venues
        foreach ($matchedVenues as $venueData) {
            try {
                $this->importVenue($venueData, $systemUser);
                $this->stats['imported']++;
            } catch (\Exception $e) {
                $this->stats['errors']++;
                $this->newLine();
                $this->error('Error importing ' . ($venueData['excel_name'] ?? 'unknown') . ': ' . $e->getMessage());
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Show results
        $this->displayResults();

        return 0;
    }

    private function getSystemUser()
    {
        // Try to find admin user
        $user = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->first();

        if (!$user) {
            // Get first user
            $user = User::first();
        }

        return $user;
    }

    private function previewImport($venues)
    {
        $this->info('🔍 PREVIEW MODE - No data will be saved');
        $this->newLine();

        $previewCount = min(10, count($venues));
        $this->info("Showing first {$previewCount} venues:");
        $this->newLine();

        foreach (array_slice($venues, 0, $previewCount) as $i => $venueData) {
            $data = $venueData['data'] ?? [];
            $folder = $venueData['folder'] ?? 'N/A';
            
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info(($i + 1) . '. ' . ($data['name'] ?? 'Unknown'));
            
            if ($folder !== ($data['name'] ?? '')) {
                $this->comment('   Folder: ' . $folder);
            }
            
            $this->line('   Address: ' . ($data['address'] ?: '❌ MISSING'));
            $this->line('   Phone: ' . ($data['phone'] ?: '❌ MISSING'));
            $this->line('   Email: ' . ($data['email'] ?: '⚠️  WILL GENERATE'));
            $this->line('   Website: ' . ($data['website'] ?: '—'));
            
            $coords = $this->parseCoordinates($data['latlong'] ?? '');
            if ($coords) {
                $this->line('   Coordinates: ✅ ' . $coords['lat'] . ', ' . $coords['lng']);
            } else {
                $this->line('   Coordinates: ❌ MISSING');
            }

            // Check images
            $images = $this->getVenueImages($folder);
            if (count($images) > 0) {
                $this->line('   Images: ✅ ' . count($images) . ' image(s)');
            } else {
                $this->line('   Images: ❌ NO IMAGES');
            }
        }

        $this->newLine();
        $this->info('Total venues ready to import: ' . count($venues));
    }

    private function importVenue($venueData, $systemUser)
    {
        $data = $venueData['data'] ?? [];
        $folder = $venueData['folder'] ?? null;

        if (!$folder || empty($data['name'])) {
            $this->stats['skipped']++;
            return;
        }

        $normalizedName = $this->normalizeName($data['name']);

        if ($normalizedName === '') {
            $this->stats['skipped']++;
            return;
        }

        // Check if venue already exists (case-insensitive, trimmed)
        $existingVenue = Venue::whereRaw('LOWER(name) = ?', [Str::lower($normalizedName)])->first();
        if ($existingVenue) {
            $this->stats['skipped']++;
            return; // Skip if already exists
        }

        // Prepare venue data
        $venueAttributes = [
            'name' => $normalizedName,
            'description' => $data['description'] ?: null,
            'address' => $data['address'] ?: null,
            'phone_number' => $data['phone'] ?: null,
            'website' => $this->cleanUrl($data['website']),
            'user_id' => $systemUser->id,
            'owner_id' => $systemUser->id,
            'owner_type' => 'App\Models\User',
        ];

        // Handle email (required with unique constraint)
        if (!empty($data['email'])) {
            $venueAttributes['contact_email'] = $data['email'];
        } else {
            // Generate unique email
            $slug = Str::slug($normalizedName);
            $venueAttributes['contact_email'] = $slug . '-' . time() . '@mygigguide.local';
        }

        // Parse coordinates
        $coords = $this->parseCoordinates($data['latlong'] ?? '');
        if ($coords) {
            $venueAttributes['latitude'] = $coords['lat'];
            $venueAttributes['longitude'] = $coords['lng'];
        }

        // Handle images
        if (!$this->option('skip-images')) {
            $images = $this->getVenueImages($folder);
            if (count($images) > 0) {
                $copiedImages = $this->copyVenueImages($folder, $images, $normalizedName);
                
                if (count($copiedImages) > 0) {
                    $venueAttributes['main_picture'] = $copiedImages[0];
                    
                    if (count($copiedImages) > 1) {
                        $venueAttributes['venue_gallery'] = json_encode(array_slice($copiedImages, 1));
                    }
                }
            }
        }

        // Create venue
        Venue::create($venueAttributes);
        $this->stats['total']++;
    }

    private function getVenueImages($folder)
    {
        $folderPath = base_path($this->venuesPath . '/' . $folder);
        
        if (!File::isDirectory($folderPath)) {
            return [];
        }

        $images = [];
        $files = File::files($folderPath);
        
        foreach ($files as $file) {
            $ext = strtolower($file->getExtension());
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $images[] = $file->getFilename();
            }
        }

        sort($images); // Ensure consistent ordering
        return $images;
    }

    private function copyVenueImages($folder, $images, $venueName)
    {
        $copiedPaths = [];
        $sourcePath = base_path($this->venuesPath . '/' . $folder);
        
        // Create venue folder in storage
        $venueSlug = Str::slug($venueName);
        $destinationFolder = 'venues/' . $venueSlug;
        
        // Ensure storage directory exists
        if (!Storage::disk('public')->exists($destinationFolder)) {
            Storage::disk('public')->makeDirectory($destinationFolder);
        }

        foreach ($images as $index => $image) {
            $sourceFile = $sourcePath . '/' . $image;
            
            if (!File::exists($sourceFile)) {
                continue;
            }

            // Generate new filename
            $extension = pathinfo($image, PATHINFO_EXTENSION);
            $newFilename = ($index === 0 ? 'main' : 'gallery-' . $index) . '.' . $extension;
            $destinationPath = $destinationFolder . '/' . $newFilename;

            // Copy file
            $content = File::get($sourceFile);
            Storage::disk('public')->put($destinationPath, $content);
            
            $copiedPaths[] = $destinationPath;
        }

        return $copiedPaths;
    }

    private function parseCoordinates($latlong)
    {
        if (empty($latlong)) {
            return null;
        }

        // Format: "latitude, longitude"
        $parts = explode(',', $latlong);
        if (count($parts) !== 2) {
            return null;
        }

        $lat = trim($parts[0]);
        $lng = trim($parts[1]);

        if (!is_numeric($lat) || !is_numeric($lng)) {
            return null;
        }

        return [
            'lat' => (float)$lat,
            'lng' => (float)$lng,
        ];
    }

    private function normalizeName(string $name): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($name));

        return $normalized === null ? trim($name) : $normalized;
    }

    private function cleanUrl($url)
    {
        if (empty($url)) {
            return null;
        }

        $url = trim($url);
        
        // Add https:// if missing
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'https://' . $url;
        }

        return $url;
    }

    private function displayResults()
    {
        $this->info('✅ Import completed!');
        $this->newLine();
        
        $this->table(
            ['Metric', 'Count'],
            [
                ['Processed', $this->stats['total']],
                ['Successfully Imported', $this->stats['imported']],
                ['Skipped (duplicates)', $this->stats['skipped']],
                ['Errors', $this->stats['errors']],
            ]
        );

        if ($this->stats['imported'] > 0) {
            $this->newLine();
            $this->info('🎉 Successfully imported ' . $this->stats['imported'] . ' venues!');
            $this->info('📍 Visit: ' . url('/admin/venues') . ' to view them.');
        }
    }
}

