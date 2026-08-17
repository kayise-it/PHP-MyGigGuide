<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Portrait card crop for list/coverflow tiles — full poster stays on detail pages.
 */
class EventPosterCardService
{
    private const TARGET_WIDTH = 600;

    private const TARGET_HEIGHT = 900;

    /**
     * Honest 2:3 card for portrait posters only — never invent crops from square/landscape art.
     */
    public function portraitCardForPoster(?string $posterStoragePath): ?string
    {
        if ($posterStoragePath === null || $posterStoragePath === '') {
            return null;
        }

        if (! $this->isPortraitPoster($posterStoragePath)) {
            return null;
        }

        return $this->generatePortraitCard($posterStoragePath);
    }

    /**
     * Whether the stored poster is already portrait (height > width).
     * Square and landscape posters should letterbox in lists — no forced 2:3 crop.
     */
    public function isPortraitPoster(string $storagePath): bool
    {
        $dimensions = $this->imageDimensions($storagePath);
        if ($dimensions === null) {
            return false;
        }

        [$width, $height] = $dimensions;

        return $height > $width;
    }

    /**
     * Whether the stored poster is wide (banner-like) — Quicket-style landscape.
     */
    public function isLandscapePoster(string $storagePath): bool
    {
        $dimensions = $this->imageDimensions($storagePath);
        if ($dimensions === null) {
            return false;
        }

        [$width, $height] = $dimensions;

        return $height > 0 && ($width / $height) >= 1.25;
    }

    /**
     * Center-crop to 2:3 portrait and save alongside the original poster.
     */
    public function generatePortraitCard(string $posterStoragePath): ?string
    {
        $disk = Storage::disk('public');
        if (! $disk->exists($posterStoragePath)) {
            return null;
        }

        $fullPath = $disk->path($posterStoragePath);
        $info = @getimagesize($fullPath);
        if ($info === false) {
            return null;
        }

        [$width, $height, $type] = $info;
        $source = $this->loadImage($fullPath, $type);
        if ($source === null) {
            return null;
        }

        [$cropX, $cropY, $cropW, $cropH] = $this->centerCropBox($width, $height, 2 / 3);

        $destination = imagecreatetruecolor(self::TARGET_WIDTH, self::TARGET_HEIGHT);
        if ($destination === false) {
            imagedestroy($source);

            return null;
        }

        imagecopyresampled(
            $destination,
            $source,
            0,
            0,
            $cropX,
            $cropY,
            self::TARGET_WIDTH,
            self::TARGET_HEIGHT,
            $cropW,
            $cropH
        );
        imagedestroy($source);

        $directory = dirname($posterStoragePath);
        $baseName = pathinfo($posterStoragePath, PATHINFO_FILENAME);
        $cardPath = $directory.'/'.$baseName.'_card.jpg';
        $saved = @imagejpeg($destination, $disk->path($cardPath), 88);
        imagedestroy($destination);

        return $saved ? $cardPath : null;
    }

    public function deletePortraitCard(?string $cardStoragePath): void
    {
        if ($cardStoragePath === null || $cardStoragePath === '') {
            return;
        }

        $disk = Storage::disk('public');
        if ($disk->exists($cardStoragePath)) {
            $disk->delete($cardStoragePath);
        }
    }

    /**
     * @return array{0: int, 1: int}|null Width and height in display orientation (EXIF-aware).
     */
    private function imageDimensions(string $storagePath): ?array
    {
        $disk = Storage::disk('public');
        if (! $disk->exists($storagePath)) {
            return null;
        }

        $fullPath = $disk->path($storagePath);
        $info = @getimagesize($fullPath);
        if ($info === false) {
            return null;
        }

        $width = (int) $info[0];
        $height = (int) $info[1];
        $type = (int) $info[2];

        if ($this->exifOrientationSwapsDimensions($fullPath, $type)) {
            return [$height, $width];
        }

        return [$width, $height];
    }

    private function exifOrientationSwapsDimensions(string $fullPath, int $imageType): bool
    {
        if (! function_exists('exif_read_data') || ! in_array($imageType, [IMAGETYPE_JPEG, IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM], true)) {
            return false;
        }

        $exif = @exif_read_data($fullPath);
        if (! is_array($exif)) {
            return false;
        }

        $orientation = (int) ($exif['Orientation'] ?? 1);

        return in_array($orientation, [5, 6, 7, 8], true);
    }

    /**
     * @return array{0: int, 1: int, 2: int, 3: int}
     */
    private function centerCropBox(int $width, int $height, float $targetRatio): array
    {
        $sourceRatio = $width / $height;

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $height;
            $cropWidth = (int) round($height * $targetRatio);
            $cropX = (int) round(($width - $cropWidth) / 2);
            $cropY = 0;
        } else {
            $cropWidth = $width;
            $cropHeight = (int) round($width / $targetRatio);
            $cropX = 0;
            $cropY = (int) round(($height - $cropHeight) / 2);
        }

        return [$cropX, $cropY, $cropWidth, $cropHeight];
    }

    private function loadImage(string $path, int $type): ?\GdImage
    {
        return match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path) ?: null,
            IMAGETYPE_PNG => @imagecreatefrompng($path) ?: null,
            IMAGETYPE_GIF => @imagecreatefromgif($path) ?: null,
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? (@imagecreatefromwebp($path) ?: null) : null,
            default => null,
        };
    }
}
