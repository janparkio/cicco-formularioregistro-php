<?php
// Include WordPress core
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// Set headers
header('Content-Type: image/png');

// Generate CAPTCHA
function generateCaptcha($length = 6) {
    $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    $captcha = '';
    for ($i = 0; $i < $length; $i++) {
        $captcha .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $captcha;
}

try {
    // Generate CAPTCHA
    $captcha = generateCaptcha();
    
    // Store in WordPress transient (like a session)
    set_transient('user_captcha_' . $_SERVER['REMOTE_ADDR'], $captcha, 5 * MINUTE_IN_SECONDS);
    
    // Create image
    $width = 120;
    $height = 30;
    $image = imagecreatetruecolor($width, $height);
    
    // Colors
    $bg = imagecolorallocate($image, 255, 255, 255);
    $text = imagecolorallocate($image, 0, 0, 0);
    
    // Fill background
    imagefill($image, 0, 0, $bg);
    
    // Add text
    imagestring($image, 5, 5, 5, $captcha, $text);
    
    // Output
    imagepng($image);
    imagedestroy($image);
    
    error_log("CAPTCHA generated and stored in transient: " . $captcha);
    
} catch (Exception $e) {
    error_log("CAPTCHA generation error: " . $e->getMessage());
    // Create error image
    $image = imagecreatetruecolor(120, 30);
    $bg = imagecolorallocate($image, 255, 0, 0);
    imagefill($image, 0, 0, $bg);
    imagepng($image);
    imagedestroy($image);
}