<?php
// Prevent any output before headers
ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
// Allow requests from the main domain
header('Access-Control-Allow-Origin: https://cicco.conacyt.gov.py');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Accept, X-Requested-With');

try {
    // Generate a random token
    $token = bin2hex(random_bytes(32));
    
    // Store the token in the session
    $_SESSION['captcha_token'] = $token;
    
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