<?php
session_start();

// Generate a random string
$random_string = substr(md5(rand()), 0, 6);

// Store the string in the session
$_SESSION["captcha_text"] = $random_string;

// Create an image
$image = imagecreatetruecolor(120, 30);

// Set colors
$bg_color = imagecolorallocate($image, 255, 255, 255);
$text_color = imagecolorallocate($image, 0, 0, 0);

// Fill background
imagefilledrectangle($image, 0, 0, 120, 30, $bg_color);

// Add random lines
for ($i = 0; $i < 5; $i++) {
    $line_color = imagecolorallocate(
        $image,
        rand(0, 255),
        rand(0, 255),
        rand(0, 255)
    );
    imageline(
        $image,
        rand(0, 120),
        rand(0, 30),
        rand(0, 120),
        rand(0, 30),
        $line_color
    );
}

// Add the text
imagestring($image, 5, 20, 5, $random_string, $text_color);

// Output the image
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
