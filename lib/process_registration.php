<?php
require_once(__DIR__ . '/validation-utils.php');
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error: [$errno] $errstr in $errfile on line $errline");
    
    // Ensure headers haven't been sent
    if (!headers_sent()) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json');
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'debug' => [
            'error' => $errstr,
            'file' => basename($errfile),
            'line' => $errline,
            'type' => 'php_error'
        ]
    ]);
    exit;
});

set_exception_handler(function($e) {
    error_log("Unhandled Exception: " . $e->getMessage());
    
    // Ensure headers haven't been sent
    if (!headers_sent()) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json');
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'debug' => [
            'error' => $e->getMessage(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
            'type' => 'exception'
        ]
    ]);
    exit;
});

error_log('=== Starting Registration Process ===');
error_log('Request Method: ' . $_SERVER['REQUEST_METHOD']);
error_log('Content Type: ' . ($_SERVER['CONTENT_TYPE'] ?? 'Not Set'));
error_log('Session ID: ' . session_id());
error_log('POST Data: ' . print_r($_POST, true));

// Start with session configuration
ini_set('session.cookie_path', '/solicitud_registro_usuario/');
ini_set('session.cookie_domain', '');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');

// Start session before any output
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

// Log raw POST data for debugging
error_log('Raw POST data received: ' . print_r($_POST, true));

// Debug session information
error_log("Session ID: " . session_id());
error_log("Session Status: " . session_status());
error_log("Session Data: " . print_r($_SESSION, true));
error_log("POST Data: " . print_r($_POST, true));

// Try to include WordPress if available
$wp_loaded = false;
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php')) {
    try {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
        $wp_loaded = true;
    } catch (Exception $e) {
        error_log("WordPress integration not available: " . $e->getMessage());
    }
}

function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function sendJsonResponse($success, $message, $errors = [], $debug = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'errors' => $errors,
        'debug' => $debug
    ]);
    exit;
}

// Debug information for file loading
error_log("Current directory: " . __DIR__);
error_log("Attempting to load REGION_CIUDAD.json");

// Load validation data with better error handling and success logging
$jsonString = false;
$successful_path = null;

// First attempt
$path1 = __DIR__ . '/../data/REGION_CIUDAD.json';
$jsonString = @file_get_contents($path1);
if ($jsonString !== false) {
    $successful_path = $path1;
    error_log("Successfully loaded REGION_CIUDAD.json from: " . $path1);
} else {
    error_log("Failed to load REGION_CIUDAD.json from: " . $path1);
    
    // Second attempt
    $path2 = '/solicitud_registro_usuario/data/REGION_CIUDAD.json';
    $jsonString = @file_get_contents($path2);
    if ($jsonString !== false) {
        $successful_path = $path2;
        error_log("Successfully loaded REGION_CIUDAD.json from: " . $path2);
    } else {
        error_log("Failed to load from alternate path: " . $path2);
        sendJsonResponse(false, 'Error de configuración del sistema', [], [
            'error' => 'Could not load required data file',
            'attempted_paths' => [$path1, $path2],
            'current_dir' => __DIR__,
            'document_root' => $_SERVER['DOCUMENT_ROOT']
        ]);
    }
}

// Once we know which path works, you can update the code to use just that path
error_log("RECOMMENDATION: Update the code to use this working path: " . $successful_path);

$data = json_decode($jsonString, true);
if ($data === null) {
    error_log("JSON decode error: " . json_last_error_msg());
    sendJsonResponse(false, 'Error de configuración del sistema', [], [
        'error' => 'Invalid JSON data',
        'json_error' => json_last_error_msg()
    ]);
}

// Debug the loaded data
error_log("Loaded department list: " . print_r($data['pais'], true));

$pais = $data['pais'];
$lista_departamento = array();
$lista_ciudad = array();
foreach ($pais as $item) {
    array_push($lista_departamento, $item['nombre_region']);
    foreach ($item['ciudades'] as $tmp) {
        if (strlen($tmp['ciudad']) > 0) {
            array_push($lista_ciudad, $tmp['ciudad']);
        }
    }
}

// Debug the processed lists
error_log("Processed departments: " . print_r($lista_departamento, true));
error_log("Processed cities: " . print_r($lista_ciudad, true));

// Debug incoming form data for problematic fields
error_log("Received department: " . ($_POST['et_pb_contact_departamento_0'] ?? 'not set'));
error_log("Received city: " . ($_POST['et_pb_contact_ciudad_0'] ?? 'not set'));
error_log("Received role: " . ($_POST['et_pb_contact_rol_0'] ?? 'not set'));

if (isset($_POST['et_pb_contact_fecha_nacimiento_0'])) {
    if (!validateDate($_POST['et_pb_contact_fecha_nacimiento_0'])) {
        $MSJ_ERROR .= "Fecha Nacimiento" . $separador;
        $errors[] = "La fecha de nacimiento no es válida.";
    }
}

// Validation lists for institutional roles
// The array keys match the form values, while the values are the display names
$lista_cargo_institucion = array(
    "investigador" => "Investigador",
    "investigador-pronii" => "Investigador PRONII", 
    "docente" => "Docente universitario",
    "estudiante" => "Estudiante universitario",
    "administrativo" => "Personal administrativo",
    "tecnico" => "Personal técnico",
    "consultor" => "Consultor/Asesor"
);
$lista_sexo = array("Masculino", "Femenino");
$lista_nacionalidad = array("afgano","alemán","árabe","argentino","australiano","belga","boliviano","brasileño","camboyano","canadiense","chileno","chino","colombiano","coreano","costarricense","cubano","danés","ecuatoriano","egipcio","salvadoreño","escocés","español","estadounidense","estonio","etiope","filipino","finlandés","francés","galés","griego","guatemalteco","haitiano","holandés","hondureño","indonés","inglés","iraquí","iraní","irlandés","israelí","italiano","japonés","jordano","laosiano","letón","letonés","malayo","marroquí","mexicano","nicaragüense","noruego","neozelandés","panameño","paraguayo","peruano","polaco","portugués","puertorriqueño","dominicano","rumano","ruso","sueco","suizo","tailandés","taiwanes","turco","ucraniano","uruguayo","venezolano","vietnamita","afgana","alemana","árabe","argentina","australiana","belga","boliviana","brasileña","camboyana","canadiense","chilena","china","colombiana","coreana","costarricense","cubana","danesa","ecuatoriana","egipcia","salvadoreña","escocesa","española","estadounidense","estonia","etiope","filipina","finlandesa","francesa","galesa","griega","guatemalteca","haitiana","holandesa","hondureña","indonesa","inglesa","iraquí","iraní","irlandesa","israelí","italiana","japonesa","jordana","laosiana","letona","letonesa","malaya","marroquí","mexicana","nicaragüense","noruega","neozelandesa","panameña","paraguaya","peruana","polaca","portuguesa","puertorriqueño","dominicana","rumana","rusa","sueca","suiza","tailandesa","taiwanesa","turca","ucraniana","uruguaya","venezolana","vietnamita");

// Enhanced CAPTCHA validation
if (!isset($_POST['captcha']) || !isset($_SESSION['captcha'])) {
    $debug_info = [
        'post_captcha' => $_POST['captcha'] ?? null,
        'session_captcha' => $_SESSION['captcha'] ?? null,
        'session_id' => session_id(),
        'session_status' => session_status(),
        'cookies' => $_COOKIE
    ];
    
    error_log("CAPTCHA validation failed: " . print_r($debug_info, true));
    
    header('HTTP/1.1 400 Bad Request');
    echo json_encode([
        'success' => false,
        'message' => 'Error de verificación CAPTCHA',
        'debug' => $debug_info
    ]);
    exit;
}

// Case-insensitive CAPTCHA comparison with trimming
if (strtoupper(trim($_POST['captcha'])) !== strtoupper(trim($_SESSION['captcha']))) {
    $debug_info = [
        'received' => $_POST['captcha'],
        'expected' => $_SESSION['captcha'],
        'session_id' => session_id()
    ];
    
    error_log("CAPTCHA mismatch: " . print_r($debug_info, true));
    
    header('HTTP/1.1 400 Bad Request');
    echo json_encode([
        'success' => false,
        'message' => 'Código CAPTCHA incorrecto',
        'debug' => $debug_info
    ]);
    exit;
}

// Store CAPTCHA validation success in session
$_SESSION['captcha_validated'] = true;

// Clear used CAPTCHA but maintain the session
$captcha_value = $_SESSION['captcha'];
unset($_SESSION['captcha']);

// Initialize error collection
$errors = [];
$MSJ_ERROR = "";
$separador = "|";

// Validate required fields
$required_fields = [
    'et_pb_contact_nombres_0' => 'Nombres',
    'et_pb_contact_apellidos_0' => 'Apellidos',
    'et_pb_contact_dni_0' => 'Número de Documento',
    'et_pb_contact_nacionalidad_0' => 'Nacionalidad',
    'et_pb_contact_genero_0' => 'Género',
    'et_pb_contact_phone_0' => 'Teléfono',
    'et_pb_contact_email_0' => 'Correo Institucional',
    'et_pb_contact_departamento_0' => 'Departamento',
    'et_pb_contact_ciudad_0' => 'Ciudad',
    'organizacion' => 'Institución',
    'organizacion_facultad' => 'Facultad',
    'organizacion_facultad_carrera' => 'Unidad/Carrera',
    'et_pb_contact_rol_0' => 'Rol Institucional'
];

// Validate each field with specific rules
foreach ($required_fields as $field => $label) {
    $value = trim($_POST[$field] ?? '');
    
    switch ($field) {
        case 'et_pb_contact_dni_0':
            if (!preg_match('/^\d{6,15}$/', $value)) {
                $MSJ_ERROR .= "DNI" . $separador;
                $errors[] = "El número de documento debe tener entre 6 y 15 dígitos.";
            }
            break;
            
        case 'et_pb_contact_nacionalidad_0':
            if (!in_array($value, $lista_nacionalidad)) {
                $MSJ_ERROR .= "Nacionalidad" . $separador;
                $errors[] = "Nacionalidad no válida.";
            }
            break;
            
        case 'et_pb_contact_genero_0':
            if (!in_array($value, $lista_sexo)) {
                $MSJ_ERROR .= "Sexo" . $separador;
                $errors[] = "Género no válido.";
            }
            break;
            
        case 'et_pb_contact_phone_0':
            $phone = preg_replace('/[^0-9]/', '', $value);
            if (strlen($phone) < 6 || strlen($phone) > 12) {
                $MSJ_ERROR .= "Teléfono" . $separador;
                $errors[] = "El número de teléfono debe tener entre 6 y 12 dígitos.";
            }
            break;
            
        case 'et_pb_contact_email_0':
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $MSJ_ERROR .= "Email" . $separador;
                $errors[] = "El formato del correo electrónico no es válido.";
            }
            break;
            
        case 'et_pb_contact_departamento_0':
            if (!in_array($value, $lista_departamento)) {
                error_log("Invalid department: '$value'. Valid departments: " . implode(", ", $lista_departamento));
                $MSJ_ERROR .= "Departamento" . $separador;
                $errors[] = "Departamento no válido. Valor recibido: '$value'";
            }
            break;
            
        case 'et_pb_contact_ciudad_0':
            if (!in_array($value, $lista_ciudad)) {
                error_log("Invalid city: '$value'. Valid cities: " . implode(", ", $lista_ciudad));
                $MSJ_ERROR .= "Ciudad" . $separador;
                $errors[] = "Ciudad no válida. Valor recibido: '$value'";
            }
            break;
            
        case 'organizacion':
            if (empty($value)) {
                $MSJ_ERROR .= "Institución" . $separador;
                $errors[] = "Institución es requerida.";
            }
            break;
            
        case 'organizacion_facultad':
            if (empty($value)) {
                $MSJ_ERROR .= "Facultad" . $separador;
                $errors[] = "La facultad es requerida.";
            }
            break;

        case 'et_pb_contact_rol_0':
            // Convert role value to lowercase and trim whitespace for consistent comparison
            $value = strtolower(trim($value));
            
            // Check if value exists as either a key or value in the roles array
            if (!array_key_exists($value, $lista_cargo_institucion) && !in_array($value, $lista_cargo_institucion)) {
                // Log detailed error info including both role keys and full names
                error_log("Invalid role: '$value'. Valid roles: " . implode(", ", array_keys($lista_cargo_institucion)));
                error_log("Valid role full names: " . implode(", ", array_values($lista_cargo_institucion)));
                
                // Add error messages
                $MSJ_ERROR .= "Cargo Institución" . $separador;
                $errors[] = "Rol institucional no válido. Valor recibido: '$value'";
            }
            break;
            
        default:
            // Check if any required field is empty
            if (empty($value)) {
                $MSJ_ERROR .= "$label" . $separador;
                $errors[] = "El campo $label es requerido.";
            }
    }
}

// Validate research areas
$research_areas = [
    'et_pb_contact_area_investigacion_0_23_0' => ['Ciencias Naturales', 'Ciencias Naturales'],
    'et_pb_contact_area_investigacion_0_23_1' => ['Ingeniería y Tecnología', 'Ingenieria y Tecnologia'],
    'et_pb_contact_area_investigacion_0_23_2' => ['Ciencias Médicas y de la Salud', 'Ciencias Medicas y de la Salud'],
    'et_pb_contact_area_investigacion_0_23_3' => ['Ciencias Agrícolas y Veterinarias', 'Ciencias Agricolas y Veterinarias'],
    'et_pb_contact_area_investigacion_0_23_4' => ['Ciencias Sociales', 'Ciencias Sociales'],
    'et_pb_contact_area_investigacion_0_23_5' => ['Humanidades y Artes', 'Humanidades y Artes']
];

$area_selected = false;
foreach ($research_areas as $post_key => $values) {
    error_log("Checking research area: $post_key");
    if (isset($_POST[$post_key]) && (
        $_POST[$post_key] === $values[0] || // Check accented version
        $_POST[$post_key] === $values[1]    // Check non-accented version
    )) {
        error_log("Found selected area: " . $_POST[$post_key]);
        $area_selected = true;
        break;
    }
}

if (!$area_selected) {
    error_log('No research areas selected');
    error_log('POST data: ' . print_r($_POST, true));
    $MSJ_ERROR .= "Dominio científico de su interés" . $separador;
    $errors[] = "Debe seleccionar al menos un área de investigación.";
}

// If there are errors, return them
if (!empty($errors)) {
    error_log('Validation errors found: ' . print_r($errors, true));
    sendJsonResponse(false, 'Por favor, corrija los errores señalados', $errors, ['MSJ_ERROR' => $MSJ_ERROR]);
}

// Prepare data for LDAP creation
error_log('Preparing LDAP data...');
$arreglo = [
    "accion" => "validar_usuarios",
    "metodo" => $_SERVER['REQUEST_METHOD'],
    "fecha_registro" => date('Y-m-d H:i:s'),
    "nombres" => $_POST['et_pb_contact_nombres_0'],
    "apellidos" => $_POST['et_pb_contact_apellidos_0'],
    "uid" => 'cona' . $_POST['et_pb_contact_dni_0'],
    "nacionalidad" => $_POST['et_pb_contact_nacionalidad_0'],
    "sexo" => $_POST['et_pb_contact_genero_0'],
    "nacimiento" => $_POST['et_pb_contact_fecha_nacimiento_0'],
    "telefono" => preg_replace('/[^0-9]/', '', $_POST['et_pb_contact_phone_0']),
    "email" => $_POST['et_pb_contact_email_0'],
    "instituciones" => $_POST['organizacion'],
    "facultad" => $_POST['organizacion_facultad'],
    "carrera" => $_POST['organizacion_facultad_carrera'],
    "cargo_institucion" => $_POST['et_pb_contact_rol_0'],
    "categoria_pronii" => $_POST['et_pb_contact_categoria_pronii_0'] ?? '',
    "contact_orcid" => $_POST['et_pb_contact_orcid_0'] ?? '',
    "contact_scopus" => $_POST['et_pb_contact_scopus_0'] ?? '',
    "contact_wos" => $_POST['et_pb_contact_wos_0'] ?? '',
    "departamento" => $_POST['et_pb_contact_departamento_0'],
    "ciudad" => $_POST['et_pb_contact_ciudad_0']
];

error_log('Processing research areas...');
// Modified foreach to use simple key mapping
foreach ($research_areas as $post_key => $value) {
    // Extract the numeric part from the key (0 to 5) and map to the correct field name
    $id = substr($post_key, -1);
    switch ($id) {
        case '0': $key = 'ciencias_naturales'; break;
        case '1': $key = 'ingenieria_tecnologia'; break;
        case '2': $key = 'ciencias_medicas_salud'; break;
        case '3': $key = 'ciencias_agricolas_veterinarias'; break;
        case '4': $key = 'ciencias_sociales'; break;
        case '5': $key = 'humanidades_artes'; break;
        default: continue;
    }
    $arreglo[$key] = isset($_POST[$post_key]) ? $_POST[$post_key] : '';
}

// Load registration logger
$logger_path = dirname(__DIR__) . '/lib/RegistrationLogger.php';
error_log("Attempting to load logger from: $logger_path");
if (file_exists($logger_path)) {
    require_once $logger_path;
    $logger = new RegistrationLogger();
    error_log("Logger loaded successfully");
} else {
    error_log("RegistrationLogger.php not found at: " . $logger_path);
    $logger = null;
}
// Execute the Python script with the prepared data
$scriptData = $arreglo; // Use the already prepared data array
$json_data = json_encode($scriptData, JSON_UNESCAPED_UNICODE);
$encoded_data = base64_encode($json_data);

error_log('Preparing script execution...');
error_log('Parameters: ' . $encoded_data);
error_log('Environment: ' . print_r($_ENV, true));

try {
    error_log('Executing Python script...');
    
    // Validate script exists
    $script_path = '/var/www/PY/rutina_ingreso_2023.sh';
    if (!file_exists($script_path)) {
        throw new Exception("Script file not found: $script_path");
    }
    
    // Validate parameters
    if (empty($encoded_data)) {
        throw new Exception("Invalid parameters for script execution");
    }

    // Log script data preparation
    $scriptLog = $logger->logScriptData($scriptData, $encoded_data);
    
    // Transform role value right before script execution
    switch($scriptData['cargo_institucion']) {
        case 'investigador-pronii':
            $scriptData['cargo_institucion'] = 'investigador pronii';
            break;
        case 'docente':
            $scriptData['cargo_institucion'] = 'docente universitario';
            break;
        case 'estudiante':
            $scriptData['cargo_institucion'] = 'estudiante universitario';
            break;
        case 'administrativo':
            $scriptData['cargo_institucion'] = 'personal administrativo';
            break;
        case 'tecnico':
            $scriptData['cargo_institucion'] = 'personal tecnico';
            break;
        case 'consultor':
            $scriptData['cargo_institucion'] = 'consultor_asesor';
            break;
    }
    // Sanitize organization name right before script execution
    error_log("Starting organization name sanitization...");
    if (isset($scriptData['organizacion'])) {
        $originalName = $scriptData['organizacion'];
        error_log("Original organization name: " . $originalName);
        
        // Clean the institution name
        $cleanName = stripslashes($originalName);              // Remove backslashes
        error_log("After stripslashes: " . $cleanName);
        
        $cleanName = str_replace(['"', "'", "\\"], '', $cleanName); // Remove quotes and backslashes
        error_log("After removing quotes and backslashes: " . $cleanName);
        
        $cleanName = preg_replace('/\s+/', ' ', $cleanName);  // Normalize spaces
        error_log("After normalizing spaces: " . $cleanName);
        
        // Allow letters (including Spanish), numbers, and basic punctuation
        $cleanName = preg_replace('/[^a-zA-ZáéíóúüñÁÉÍÓÚÜÑ0-9\s\.,_-]/', '', $cleanName);
        error_log("After character filtering: " . $cleanName);
        
        $cleanName = trim($cleanName);                        // Remove leading/trailing spaces
        error_log("After final trim: " . $cleanName);
        
        $scriptData['organizacion'] = $cleanName;
        error_log("Institution name sanitized: '$originalName' -> '$cleanName'");
    } else {
        error_log("Warning: organizacion field not found in scriptData");
    }

    // Sanitize unit/career name right before script execution  
    error_log("Starting unit/career name sanitization...");
    if (isset($scriptData['organizacion_facultad_carrera'])) {
        $originalName = $scriptData['organizacion_facultad_carrera'];
        error_log("Original unit/career name: " . $originalName);
        
        // Clean the unit/career name
        $cleanName = stripslashes($originalName);              // Remove backslashes
        error_log("After stripslashes: " . $cleanName);
        
        $cleanName = str_replace(['"', "'"], '', $cleanName); // Remove quotes
        error_log("After removing quotes: " . $cleanName);
        
        $cleanName = preg_replace('/\s+/', ' ', $cleanName);  // Normalize spaces
        error_log("After normalizing spaces: " . $cleanName);
        
        // Allow letters (including Spanish), numbers, and basic punctuation
        $cleanName = preg_replace('/[^a-zA-ZáéíóúüñÁÉÍÓÚÜÑ0-9\s\.,_-]/', '', $cleanName);
        error_log("After character filtering: " . $cleanName);
        
        $cleanName = trim($cleanName);                        // Remove leading/trailing spaces
        error_log("After final trim: " . $cleanName);
        
        $scriptData['organizacion_facultad_carrera'] = $cleanName;
        error_log("Unit/career name sanitized: '$originalName' -> '$cleanName'");
    } else {
        error_log("Warning: organizacion_facultad_carrera field not found in scriptData");
    }
    
    error_log('Final role value for script: ' . $scriptData['cargo_institucion']);
    
    // Re-encode the data with transformed role
    $json_data = json_encode($scriptData, JSON_UNESCAPED_UNICODE);
    $encoded_data = base64_encode($json_data);
    
    // Execute with full error capture
    $cmd = sprintf('%s validar_usuarios %s 2>&1', $script_path, escapeshellarg($encoded_data));
    $output = [];
    $return = 0;
    exec($cmd, $output, $return);
    
    error_log("Script execution complete. Return code: $return");
    error_log("Script output: " . print_r($output, true));

    // Log script execution response
    $scriptLog = array_merge($scriptLog, $logger->logScriptResponse($output, $return));
    
    if ($return === 0 && !empty($output[0])) {
        // Success handling
        $response = [
            'success' => true,
            'message' => 'Registro exitoso',
            'redirect' => $output[0],
            'script_output' => $output
        ];
        
        $logEntry = $logger->logAttempt($_POST, true, $response, $scriptLog);
        
        sendJsonResponse(true, 'Registro exitoso', [], [
            'redirect' => $output[0],
            'source' => 'python_script',
            'log_id' => $logEntry['timestamp']
        ]);
    } else {
        throw new Exception(
            "Script execution failed with code $return: " . implode("\n", $output)
        );
    }
} catch (Exception $e) {
    error_log("Script execution error: " . $e->getMessage());
    
    $response = [
        'success' => false,
        'message' => 'Error en el procesamiento del registro',
        'error' => $e->getMessage(),
        'type' => 'script_execution_error'
    ];

    if ($logger) {
        $logger->logAttempt($_POST, false, $response, $scriptLog ?? []);
    }
    
    sendJsonResponse(false, 'Error en el procesamiento del registro', [
        'Error interno del servidor'
    ], [
        'error_type' => 'script_execution',
        'error_message' => $e->getMessage()
    ]);
}