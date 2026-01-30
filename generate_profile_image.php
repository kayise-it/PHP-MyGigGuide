<?php
/**
 * Generate a 1024x1024 profile image placeholder
 * Usage: php generate_profile_image.php
 */

// Check if GD extension is available
if (!extension_loaded('gd')) {
    die("Error: GD extension is not loaded. Please install php-gd.\n");
}

// Image dimensions
$width = 1024;
$height = 1024;

// Create image
$image = imagecreatetruecolor($width, $height);

// Define colors (purple/blue gradient to match your app theme)
$colors = [
    'purple' => imagecolorallocate($image, 139, 92, 246), // Purple-600
    'blue' => imagecolorallocate($image, 37, 99, 235),   // Blue-600
    'white' => imagecolorallocate($image, 255, 255, 255),
    'light_purple' => imagecolorallocate($image, 196, 181, 253), // Purple-300
];

// Create gradient background
for ($y = 0; $y < $height; $y++) {
    $ratio = $y / $height;
    $r = (int)(139 + (37 - 139) * $ratio);
    $g = (int)(92 + (99 - 92) * $ratio);
    $b = (int)(246 + (235 - 246) * $ratio);
    $color = imagecolorallocate($image, $r, $g, $b);
    imageline($image, 0, $y, $width, $y, $color);
}

// Add a circular shape in the center (profile icon style)
$centerX = $width / 2;
$centerY = $height / 2;
$radius = 350;

// Draw a subtle circle outline
imagefilledellipse($image, $centerX, $centerY, $radius * 2, $radius * 2, $colors['light_purple']);

// Add a user icon (simplified - a circle with a person silhouette)
// Outer circle
$iconRadius = 200;
imagefilledellipse($image, $centerX, $centerY - 30, $iconRadius * 2, $iconRadius * 2, $colors['white']);

// Person head (smaller circle)
$headRadius = 60;
imagefilledellipse($image, $centerX, $centerY - 80, $headRadius * 2, $headRadius * 2, $colors['purple']);

// Person body (rounded rectangle/trapezoid shape)
$bodyPoints = [
    $centerX - 80, $centerY - 20,  // Top left
    $centerX + 80, $centerY - 20,  // Top right
    $centerX + 100, $centerY + 60, // Bottom right
    $centerX - 100, $centerY + 60, // Bottom left
];
imagefilledpolygon($image, $bodyPoints, 4, $colors['purple']);

// Output image
$outputPath = __DIR__ . '/public/logos/1Profile.jpeg';
imagejpeg($image, $outputPath, 90); // 90% quality
imagedestroy($image);

echo "Profile image generated successfully!\n";
echo "Saved to: {$outputPath}\n";
echo "Dimensions: {$width}x{$height}px\n";




