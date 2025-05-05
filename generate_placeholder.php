<?php
// Script to generate a placeholder image for profile pictures

// Set the content type to JPEG
header('Content-Type: image/jpeg');

// Create a 150x150 image
$image = imagecreatetruecolor(150, 150);

// Colors
$bg_color = imagecolorallocate($image, 227, 227, 227);  // #e3e3e3 - light gray background
$profile_color = imagecolorallocate($image, 160, 160, 160); // #a0a0a0 - medium gray for profile silhouette
$text_color = imagecolorallocate($image, 74, 74, 74); // #4a4a4a - dark gray for text

// Fill the background
imagefilledrectangle($image, 0, 0, 150, 150, $bg_color);

// Draw a circle for the head (center at 75,55 with radius 25)
imagefilledellipse($image, 75, 55, 50, 50, $profile_color);

// Draw the body shape (simplified)
$body_points = [
    32, 80,   // Top left
    118, 80,  // Top right
    118, 120, // Bottom right
    32, 120   // Bottom left
];
imagefilledpolygon($image, $body_points, 4, $profile_color);

// Add text
$font_size = 3; // Built-in font size
$text = "Profile";
$text_width = imagefontwidth($font_size) * strlen($text);
$text_height = imagefontheight($font_size);
$text_x = (150 - $text_width) / 2;
$text_y = 130;

imagestring($image, $font_size, $text_x, $text_y, $text, $text_color);

// Output the image
imagejpeg($image, 'assets/images/placeholder.jpg', 90);

// Free memory
imagedestroy($image);

echo "Placeholder image created successfully at assets/images/placeholder.jpg";
?> 