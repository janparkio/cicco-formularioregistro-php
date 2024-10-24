<?php
// Configure session settings
ini_set('session.cookie_secure', 'On');
ini_set('session.cookie_httponly', 'On');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '.conacyt.gov.py');

// Start session with cookie lifetime
session_start(['cookie_lifetime' => 3600]);

// Set response headers
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Validate CAPTCHA token first
if (!isset($_POST['captcha_token']) || !isset($_SESSION['captcha_token'])) {
    $response = [
        'success' => false,
        'message' => 'Error de verificación de seguridad: Token no encontrado',
        'debug' => [
            'token_received' => isset($_POST['captcha_token']),
            'session_token' => isset($_SESSION['captcha_token']),
            'session_data' => $_SESSION
        ]
    ];
    echo json_encode($response);
    exit;
}

if ($_POST['captcha_token'] !== $_SESSION['captcha_token']) {
    $response = [
        'success' => false,
        'message' => 'Error de verificación de seguridad: Token inválido',
        'debug' => [
            'received_token' => $_POST['captcha_token'],
            'session_token' => $_SESSION['captcha_token']
        ]
    ];
    echo json_encode($response);
    exit;
}

// Log session details
error_log("Form Processing Session: " . json_encode([
    'session_id' => session_id(),
    'cookies' => $_COOKIE,
    'session_status' => session_status(),
    'headers' => getallheaders()
]));

// Constants
const REQUIRED_FIELDS = [
    'nombres' => 'Nombres',
    'apellidos' => 'Apellidos', 
    'nacionalidad' => 'Nacionalidad',
    'dni' => 'Número de Documento',
    'genero' => 'Género',
    'phone' => 'Teléfono',
    'email' => 'Correo Institucional',
    'departamento' => 'Departamento',
    'ciudad' => 'Ciudad',
    'organizacion' => 'Institución',
    'organizacion_facultad' => 'Facultad',
    'organizacion_facultad_carrera' => 'Unidad/Carrera',
    'rol' => 'Rol Institucional'
];

const RESEARCH_AREAS = [
    'et_pb_contact_area_investigacion_0_23_0' => 'Ciencias Naturales',
    'et_pb_contact_area_investigacion_0_23_1' => 'Ingeniería y Tecnología',
    'et_pb_contact_area_investigacion_0_23_2' => 'Ciencias Médicas y de la Salud',
    'et_pb_contact_area_investigacion_0_23_3' => 'Ciencias Agrícolas y Veterinarias',
    'et_pb_contact_area_investigacion_0_23_4' => 'Ciencias Sociales',
    'et_pb_contact_area_investigacion_0_23_5' => 'Humanidades y Artes'
];

function validateFormData($data) {
    $errors = [];
    
    // Validate required fields
    foreach (REQUIRED_FIELDS as $field => $label) {
        if (empty($data[$field])) {
            $errors[] = [
                'field' => $field,
                'message' => "El campo {$label} es requerido"
            ];
        }
    }
    
    // Validate DNI format
    if (!empty($data['dni']) && !preg_match('/^\d{6,15}$/', $data['dni'])) {
        $errors[] = [
            'field' => 'dni',
            'message' => 'El número de documento debe tener entre 6 y 15 dígitos'
        ];
    }
    
    // Validate email format
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = [
            'field' => 'email',
            'message' => 'El formato del correo electrónico no es válido'
        ];
    }
    
    // Validate research areas
    $hasResearchArea = false;
    foreach (RESEARCH_AREAS as $key => $value) {
        if (isset($data[$key]) && $data[$key] === 'on') {
            $hasResearchArea = true;
            break;
        }
    }
    
    if (!$hasResearchArea) {
        $errors[] = [
            'field' => 'research_areas',
            'message' => 'Debe seleccionar al menos un área de investigación'
        ];
    }
    
    return $errors;
}

function processSubmission($data) {
    try {
        $response = [
            'success' => false,
            'message' => '',
            'errors' => [],
            'debug' => [
                'timestamp' => date('Y-m-d H:i:s'),
                'request_method' => $_SERVER['REQUEST_METHOD']
            ]
        ];

        // Validate form data
        $errors = validateFormData($data);
        if (!empty($errors)) {
            $response['errors'] = $errors;
            $response['message'] = 'Por favor, corrija los errores señalados';
            return $response;
        }

        // Process valid submission
        $result = executeRegistration($data);
        
        $response['success'] = $result['success'];
        $response['message'] = $result['success'] 
            ? 'Registro exitoso. Sus datos han sido enviados para verificación.'
            : 'Error en el procesamiento del registro.';
        
        if (isset($result['debug'])) {
            $response['debug']['processing'] = $result['debug'];
        }

        return $response;

    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
        return [
            'success' => false,
            'message' => 'Error interno del servidor',
            'debug' => [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]
        ];
    }
}

// Main execution
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    $result = processSubmission($_POST);
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}