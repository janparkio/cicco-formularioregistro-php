<?php
// Force session cookie parameters for better security and compatibility
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/solicitud_registro_usuario/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Start or resume session
session_start();

// Ensure session is working
if (session_status() !== PHP_SESSION_ACTIVE) {
    error_log("Session not active after session_start()!");
    header('HTTP/1.1 500 Internal Server Error');
    exit('Session initialization failed');
}

header('Content-Type: image/png');

// Generate and store CAPTCHA
$captcha = generateCaptcha();
$_SESSION['captcha'] = $captcha;
$_SESSION['captcha_time'] = time();

// Debug logging
error_log("Generated CAPTCHA: " . $captcha);
error_log("Session ID: " . session_id());
error_log("Session Data: " . json_encode($_SESSION));

// Create image
$image = imagecreatetruecolor(120, 30);
$bg = imagecolorallocate($image, 255, 255, 255);
$textcolor = imagecolorallocate($image, 0, 0, 0);

// Fill background
imagefill($image, 0, 0, $bg);

// Add some noise/lines to make it harder for bots
for ($i = 0; $i < 5; $i++) {
    $line_color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
    imageline($image, rand(0, 120), rand(0, 30), rand(0, 120), rand(0, 30), $line_color);
}

// Add text
imagestring($image, 5, 5, 5, $captcha, $textcolor);

// Output image
imagepng($image);
imagedestroy($image);

function generateCaptcha($length = 6) {
    $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'; // Removed confusing characters
    $captcha = '';
    for ($i = 0; $i < $length; $i++) {
        $captcha .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $captcha;
}