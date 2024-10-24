<?php
// Prevent any output before headers
ob_start();

// Update session configuration
ini_set('session.cookie_secure', 'On');
ini_set('session.cookie_httponly', 'On');
ini_set('session.cookie_samesite', 'Lax'); // Changed from Strict to Lax
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '.conacyt.gov.py');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start(['cookie_lifetime' => 3600]); // Set 1-hour lifetime
}

// Log session start details
error_log("CAPTCHA Session Start: " . json_encode([
    'session_id' => session_id(),
    'cookies' => $_COOKIE,
    'session_status' => session_status(),
    'headers' => getallheaders()
]));

// Set JSON headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
// Set CORS headers
header('Access-Control-Allow-Origin: https://cicco.conacyt.gov.py');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Accept, X-Requested-With, Content-Type');
header('Access-Control-Max-Age: 1728000');

try {
    // Generate a random token
    $token = bin2hex(random_bytes(32));
    
    // Store the token in the session
    $_SESSION['captcha_token'] = $token;

    // Force session write
    session_write_close();

    // Enhanced debug logging
    error_log("CAPTCHA Session Debug: " . json_encode([
        'session_id' => session_id(),
        'token' => $token,
        'session_status' => session_status(),
        'cookie_params' => session_get_cookie_params(),
        'headers_sent' => headers_sent(),
        'cookies_sent' => $_COOKIE
    ]));
    
    // Clear any buffered output
    ob_clean();
    
    // Return only the JSON response
    echo json_encode([
        'token' => $token,
        'status' => 'success'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to generate captcha token'
    ]);
}

// End output buffering and exit
ob_end_flush();
exit;