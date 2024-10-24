<?php
session_start();
header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];
try {
    $debug = [
        'timestamp' => date('Y-m-d H:i:s'),
        'request_method' => $_SERVER['REQUEST_METHOD'],
        'post_data' => $_POST,
        'files' => [
            'instituciones' => __DIR__ . '/../data/INSTITUCIONES_2023.json',
            'facultades' => __DIR__ . '/../data/FACULTADES_2023.json',
            'carreras' => __DIR__ . '/../data/CARRERAS_2023.json'
        ]
    ];

    $response['debug'] = $debug;

    // Check if files exist and are readable
    foreach ($debug['files'] as $key => $path) {
        if (!file_exists($path)) {
            throw new Exception("File not found: {$key} at {$path}");
        }
        if (!is_readable($path)) {
            throw new Exception("File not readable: {$key} at {$path}");
        }
    }

    $instituciones = json_decode(file_get_contents($debug['files']['instituciones']), true);
    $facultades = json_decode(file_get_contents($debug['files']['facultades']), true);
    $carreras = json_decode(file_get_contents($debug['files']['carreras']), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON decode error: " . json_last_error_msg());
    }

    $lista_instituciones = array_column($instituciones['datos'], 'denominacion');
    $lista_facultades = array_column($facultades['datos'], 'denominacion');
    $lista_carreras = array_column($carreras['datos'], 'denominacion');

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    $response['debug'] = $debug ?? null;
    echo json_encode($response);
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
error_log("Processing registration request");


function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Verify CAPTCHA
if (!isset($_POST['captcha_token']) || $_POST['captcha_token'] !== $_SESSION['captcha_token']) {
    $response['message'] = 'CAPTCHA verification failed';
    echo json_encode($response);
    exit;
}

// Other lists for validation
$lista_nacionalidad = ["Paraguaya", "Extranjera"];
$lista_sexo = ["Masculino", "Femenino"];
$lista_cargo_institucion = ["Investigador", "Investigador PRONII", "Docente universitario", "Estudiante universitario", "Personal administrativo", "Personal técnico", "Consultor/Asesor"];

// Process and validate form data
$required_fields = [
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

$MSJ_ERROR = "";
$separador = "|";

foreach ($required_fields as $field => $label) {
    if (empty($_POST[$field])) {
        $MSJ_ERROR .= "$label$separador";
    }
}

// Validate specific fields
if (!preg_match('/^\d{5,15}$/', $_POST['dni'])) {
    $MSJ_ERROR .= "DNI$separador";
}

if (!in_array($_POST['nacionalidad'], $lista_nacionalidad)) {
    $MSJ_ERROR .= "Nacionalidad$separador";
}

if (!in_array($_POST['genero'], $lista_sexo)) {
    $MSJ_ERROR .= "Sexo$separador";
}

$birth_year = $_POST['birth-year'] ?? '';
$birth_month = $_POST['birth-month'] ?? '';
$birth_day = $_POST['birth-day'] ?? '';
$birth_date = sprintf('%04d-%02d-%02d', $birth_year, $birth_month, $birth_day);

if (!validateDate($birth_date)) {
    $MSJ_ERROR .= "Fecha de Nacimiento$separador";
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $MSJ_ERROR .= "Correo Electrónico$separador";
}

if (!in_array($_POST['organizacion'], $lista_instituciones)) {
    $MSJ_ERROR .= "Institución$separador";
}

if (!in_array($_POST['organizacion_facultad'], $lista_facultades)) {
    $MSJ_ERROR .= "Facultad$separador";
}

if (!in_array($_POST['organizacion_facultad_carrera'], $lista_carreras)) {
    $MSJ_ERROR .= "Carrera$separador";
}

if (!in_array($_POST['rol'], $lista_cargo_institucion)) {
    $MSJ_ERROR .= "Cargo Institución$separador";
}

// Validate research areas
$research_areas = [
    'et_pb_contact_area_investigacion_0_23_0' => 'Ciencias Naturales',
    'et_pb_contact_area_investigacion_0_23_1' => 'Ingeniería y Tecnología',
    'et_pb_contact_area_investigacion_0_23_2' => 'Ciencias Médicas y de la Salud',
    'et_pb_contact_area_investigacion_0_23_3' => 'Ciencias Agrícolas y Veterinarias',
    'et_pb_contact_area_investigacion_0_23_4' => 'Ciencias Sociales',
    'et_pb_contact_area_investigacion_0_23_5' => 'Humanidades y Artes'
];

$selected_areas = array_filter($research_areas, function ($value, $key) use ($_POST) {
    return isset($_POST[$key]) && $_POST[$key] === 'on';
}, ARRAY_FILTER_USE_BOTH);

if (empty($selected_areas)) {
    $MSJ_ERROR .= "Dominio científico de su interés$separador";
}

// If there are no errors, process the data
if (empty($MSJ_ERROR)) {
    // Prepare data for external processing
    $accion = 'validar_usuarios';
    $arreglo = array(
        "accion" => $accion,
        "metodo" => $_SERVER['REQUEST_METHOD'],
        "fecha_registro" => date('Y-m-d H:i:s'),
        "nombres" => $_POST['nombres'],
        "apellidos" => $_POST['apellidos'],
        "uid" => 'cona' . $_POST['dni'],
        "nacionalidad" => $_POST['nacionalidad'],
        "sexo" => $_POST['genero'],
        "nacimiento" => $birth_date,
        "telefono" => $_POST['phone'],
        "email" => $_POST['email'],
        "instituciones" => $_POST['organizacion'],
        "facultad" => $_POST['organizacion_facultad'],
        "carrera" => $_POST['organizacion_facultad_carrera'],
        "cargo_institucion" => $_POST['rol'],
        "departamento" => $_POST['departamento'],
        "ciudad" => $_POST['ciudad'],
        "ciencias_naturales" => isset($_POST['et_pb_contact_area_investigacion_0_23_0']) ? 'on' : 'off',
        "ingenieria_tecnologia" => isset($_POST['et_pb_contact_area_investigacion_0_23_1']) ? 'on' : 'off',
        "ciencias_medicas_salud" => isset($_POST['et_pb_contact_area_investigacion_0_23_2']) ? 'on' : 'off',
        "ciencias_agricolas_veterinarias" => isset($_POST['et_pb_contact_area_investigacion_0_23_3']) ? 'on' : 'off',
        "ciencias_sociales" => isset($_POST['et_pb_contact_area_investigacion_0_23_4']) ? 'on' : 'off',
        "humanidades_artes" => isset($_POST['et_pb_contact_area_investigacion_0_23_5']) ? 'on' : 'off',
    );

    $json = json_encode($arreglo);
    $parametros = '"' . base64_encode($json) . '"';

    error_log("Executing external script with parameters: " . $parametros);
    exec('/var/www/PY/rutina_ingreso_2023.sh ' . $accion . ' ' . $parametros . ' 2>&1', $output, $return);

    error_log("Script output: " . print_r($output, true));
    error_log("Script return code: " . $return);

    if ($return == '0') {
        $response['success'] = true;
        $response['message'] = 'Registro exitoso. Sus datos han sido enviados para verificación.';
    } else {
        $response['success'] = false;
        $response['message'] = 'Error en el procesamiento del formulario.';
        $response['debug'] = $output;
    }
} else {
    $response['message'] = 'Se encontraron errores en el formulario. Por favor, corríjalos e intente nuevamente.';
    $response['errors'] = explode($separador, trim($MSJ_ERROR, $separador));
}

echo json_encode($response);