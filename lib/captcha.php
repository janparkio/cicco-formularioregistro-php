<?php
session_start();
header('Content-Type: image/png');

$captcha = generateCaptcha();
$_SESSION['captcha'] = $captcha;

// Log for debugging
error_log("Generated CAPTCHA: " . $captcha);
error_log("Session ID: " . session_id());
error_log("Session Data: " . json_encode($_SESSION));

$image = imagecreatetruecolor(120, 30);
$bg = imagecolorallocate($image, 255, 255, 255);
$textcolor = imagecolorallocate($image, 0, 0, 0);
imagefill($image, 0, 0, $bg);
imagestring($image, 5, 5, 5, $captcha, $textcolor);

imagepng($image);
imagedestroy($image);

function generateCaptcha($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUV';
    $captcha = '';
    for ($i = 0; $i < $length; $i++) {
        $captcha .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $captcha;
}