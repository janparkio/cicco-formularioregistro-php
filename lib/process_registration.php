<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

// Set secure cookie parameters
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/solicitud_registro_usuario/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

function sendJsonResponse($success, $message, $errors = [], $debug = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'errors' => $errors,
        'debug' => $debug
    ]);
    exit;
}

// Log debugging information
error_log("Processing registration - Session ID: " . session_id());
error_log("Session data: " . json_encode($_SESSION));
error_log("POST data: " . json_encode($_POST));

// Validate CAPTCHA
if (!isset($_POST['captcha']) || !isset($_SESSION['captcha'])) {
    sendJsonResponse(false, 'Error de verificación de seguridad: CAPTCHA no encontrado', [], [
        'captcha_received' => isset($_POST['captcha']),
        'session_captcha' => isset($_SESSION['captcha']),
        'session_status' => session_status(),
        'session_id' => session_id(),
        'post_captcha' => $_POST['captcha'] ?? null,
        'session_captcha_value' => $_SESSION['captcha'] ?? null
    ]);
}

// Check if CAPTCHA has expired (5 minute limit)
if (isset($_SESSION['captcha_time']) && (time() - $_SESSION['captcha_time']) > 300) {
    unset($_SESSION['captcha']);
    unset($_SESSION['captcha_time']);
    sendJsonResponse(false, 'Error de verificación de seguridad: CAPTCHA expirado');
}

// Case-insensitive CAPTCHA comparison
if (strtoupper($_POST['captcha']) !== strtoupper($_SESSION['captcha'])) {
    sendJsonResponse(false, 'Error de verificación de seguridad: CAPTCHA inválido', [], [
        'received_captcha' => $_POST['captcha'],
        'session_captcha' => $_SESSION['captcha']
    ]);
}

// Clear the CAPTCHA from the session after successful validation
unset($_SESSION['captcha']);
unset($_SESSION['captcha_time']);

// Validate required fields and prepare data for LDAP creation
$errors = [];
$arreglo = [];

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

foreach ($required_fields as $field => $label) {
    if (empty($_POST[$field])) {
        $errors[] = "El campo $label es requerido.";
    } else {
        $arreglo[$field] = $_POST[$field];
    }
}

// Validate email format
if (!filter_var($_POST['et_pb_contact_email_0'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "El formato del correo electrónico no es válido.";
}

// Validate DNI format (assuming it should be 6-15 digits)
if (!preg_match('/^\d{6,15}$/', $_POST['et_pb_contact_dni_0'])) {
    $errors[] = "El número de documento debe tener entre 6 y 15 dígitos.";
}

// Add research areas
$research_areas = [
    'et_pb_contact_area_investigacion_0_23_0' => 'ciencias_naturales',
    'et_pb_contact_area_investigacion_0_23_1' => 'ingenieria_tecnologia',
    'et_pb_contact_area_investigacion_0_23_2' => 'ciencias_medicas_salud',
    'et_pb_contact_area_investigacion_0_23_3' => 'ciencias_agricolas_veterinarias',
    'et_pb_contact_area_investigacion_0_23_4' => 'ciencias_sociales',
    'et_pb_contact_area_investigacion_0_23_5' => 'humanidades_artes'
];

$area_selected = false;
foreach ($research_areas as $post_key => $arreglo_key) {
    $arreglo[$arreglo_key] = isset($_POST[$post_key]) ? $_POST[$post_key] : '';
    if ($arreglo[$arreglo_key] === 'on') {
        $area_selected = true;
    }
}

if (!$area_selected) {
    $errors[] = "Debe seleccionar al menos un área de investigación.";
}

if (!empty($errors)) {
    sendJsonResponse(false, 'Por favor, corrija los errores señalados', $errors);
}

// Prepare data for LDAP creation
$arreglo['accion'] = 'validar_usuarios';
$arreglo['metodo'] = $_SERVER['REQUEST_METHOD'];
$arreglo['fecha_registro'] = date('Y-m-d H:i:s');
$arreglo['uid'] = 'cona' . $_POST['et_pb_contact_dni_0'];
$arreglo['nacimiento'] = date('Y-m-d', strtotime($_POST['et_pb_contact_fecha_nacimiento_0']));
$arreglo['telefono'] = preg_replace('/[^0-9]/', '', $_POST['et_pb_contact_phone_0']);
$arreglo['categoria_pronii'] = $_POST['et_pb_contact_categoria_pronii_0'];
$arreglo['contact_orcid'] = $_POST['et_pb_contact_orcid_0'];
$arreglo['contact_scopus'] = $_POST['et_pb_contact_scopus_0'];
$arreglo['contact_wos'] = $_POST['et_pb_contact_wos_0'];

// If everything is successful
sendJsonResponse(true, 'Registro exitoso', [], ['redirect' => 'https://cicco.conacyt.gov.py/register/register_success.html']);