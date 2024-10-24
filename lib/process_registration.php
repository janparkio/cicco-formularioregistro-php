<?php
session_start();

header('Content-Type: application/json');

function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

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

// Load JSON data for validation
$instituciones = json_decode(file_get_contents('../data/INSTITUCIONES_2023.json'), true)['datos'];
$facultades = json_decode(file_get_contents('../data/FACULTADES_2023.json'), true)['datos'];
$carreras = json_decode(file_get_contents('../data/CARRERAS_2023.json'), true)['datos'];

$lista_instituciones = array_column($instituciones, 'denominacion');
$lista_facultades = array_column($facultades, 'denominacion');
$lista_carreras = array_column($carreras, 'denominacion');

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

$birth_date = $_POST['birth_year'] . '-' . $_POST['birth_month'] . '-' . $_POST['birth_day'];
if (!validateDate($birth_date)) {
    $MSJ_ERROR .= "Fecha Nacimiento$separador";
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
    'ciencias-naturales',
    'ingenieria-tecnologia',
    'ciencias-medicas-salud',
    'ciencias-agricolas-veterinarias',
    'ciencias-sociales',
    'humanidades-artes'
];

$selected_areas = array_filter($research_areas, function($area) use ($_POST) {
    return isset($_POST[$area]) && $_POST[$area] === 'on';
});

if (empty($selected_areas)) {
    $MSJ_ERROR .= "Área de Investigación$separador";
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
        "ciencias_naturales" => in_array('ciencias-naturales', $selected_areas) ? 'on' : 'off',
        "ingenieria_tecnologia" => in_array('ingenieria-tecnologia', $selected_areas) ? 'on' : 'off',
        "ciencias_medicas_salud" => in_array('ciencias-medicas-salud', $selected_areas) ? 'on' : 'off',
        "ciencias_agricolas_veterinarias" => in_array('ciencias-agricolas-veterinarias', $selected_areas) ? 'on' : 'off',
        "ciencias_sociales" => in_array('ciencias-sociales', $selected_areas) ? 'on' : 'off',
        "humanidades_artes" => in_array('humanidades-artes', $selected_areas) ? 'on' : 'off',
    );

    $json = json_encode($arreglo);
    $parametros = '"' . base64_encode($json) . '"';

    // Execute external script
    exec('/var/www/PY/rutina_ingreso_2023.sh ' . $accion . ' ' . $parametros, $output, $return);

    if ($return == '0') {
        $response['success'] = true;
        $response['message'] = 'Registro exitoso. Sus datos han sido enviados para verificación.';
    } else {
        $response['message'] = 'Error en el procesamiento del formulario.';
    }
} else {
    $response['message'] = 'Se encontraron errores en el formulario. Por favor, corríjalos e intente nuevamente.';
    $response['errors'] = explode($separador, trim($MSJ_ERROR, $separador));
}

echo json_encode($response);