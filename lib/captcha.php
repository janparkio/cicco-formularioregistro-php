<?php
// Configure session before anything else
ini_set('session.cookie_path', '/solicitud_registro_usuario/');
ini_set('session.cookie_domain', '');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');

// Start session
session_start();

// Set headers
header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

function generateCaptcha($length = 6) {
    $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'; // Removed confusing characters
    $captcha = '';
    for ($i = 0; $i < $length; $i++) {
        $captcha .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $captcha;
}

try {
    // Generate CAPTCHA
    $captcha = generateCaptcha();
    
    // Store in session
    $_SESSION['captcha'] = $captcha;
    $_SESSION['captcha_time'] = time();
    
    // Also store in WordPress transient if available
    if (function_exists('set_transient')) {
        set_transient('user_captcha_' . $_SERVER['REMOTE_ADDR'], $captcha, 5 * MINUTE_IN_SECONDS);
    }
    
    // Log for debugging
    error_log("Generated CAPTCHA: " . $captcha);
    error_log("Session ID: " . session_id());
    error_log("Session Data: " . json_encode($_SESSION));
    
    // Create image
    $image = imagecreatetruecolor(120, 30);
    $bg = imagecolorallocate($image, 255, 255, 255);
    $text = imagecolorallocate($image, 0, 0, 0);
    
    // Fill background
    imagefill($image, 0, 0, $bg);
    
    // Add noise
    for ($i = 0; $i < 5; $i++) {
        $line_color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
        imageline($image, rand(0, 120), rand(0, 30), rand(0, 120), rand(0, 30), $line_color);
    }
    
    // Add text
    imagestring($image, 5, 5, 5, $captcha, $text);
    
    // Output
    imagepng($image);
    imagedestroy($image);
    
} catch (Exception $e) {
    error_log("CAPTCHA generation error: " . $e->getMessage());
    // Create error image
    $image = imagecreatetruecolor(120, 30);
    $bg = imagecolorallocate($image, 255, 0, 0);
    imagefill($image, 0, 0, $bg);
    imagepng($image);
    imagedestroy($image);
}