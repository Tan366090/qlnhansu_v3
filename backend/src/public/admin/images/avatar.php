<?php
// Set the content-type
header('Content-Type: image/png');

// Create a blank image
$image = imagecreatetruecolor(200, 200);

// Allocate colors
$bg = imagecolorallocate($image, 240, 240, 240);
$textColor = imagecolorallocate($image, 100, 100, 100);

// Fill background
imagefill($image, 0, 0, $bg);

// Draw a circle
$centerX = 100;
$centerY = 100;
$radius = 80;
imagefilledellipse($image, $centerX, $centerY, $radius * 2, $radius * 2, $textColor);

// Add text
$text = "VNPT";
$font = 5; // Use built-in font
$textWidth = imagefontwidth($font) * strlen($text);
$textHeight = imagefontheight($font);
$textX = ($centerX * 2 - $textWidth) / 2;
$textY = ($centerY * 2 - $textHeight) / 2;
imagestring($image, $font, $textX, $textY, $text, $bg);

// Output the image
imagepng($image);
imagedestroy($image);
?> 