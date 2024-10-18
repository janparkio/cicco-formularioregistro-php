<?php
session_start();

header("Content-Type: application/json");

function validateDate($date, $format = "Y-m-d")
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

$response = ["success" => false, "message" => ""];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate CAPTCHA
    if (
        !isset($_POST["captcha_challenge"]) ||
        $_POST["captcha_challenge"] !== $_SESSION["captcha_text"]
    ) {
        $response["message"] = "Error de CAPTCHA";
        echo json_encode($response);
        exit();
    }

    // Validate other fields
    $required_fields = [
        "et_pb_contact_nombres_0",
        "et_pb_contact_apellidos_0",
        "et_pb_contact_nacionalidad_0",
        "et_pb_contact_dni_0",
        "et_pb_contact_genero_0",
        "et_pb_contact_fecha_nacimiento_0",
        "et_pb_contact_phone_0",
        "et_pb_contact_email_0",
        "et_pb_contact_departamento_0",
        "et_pb_contact_ciudad_0",
        "organizacion",
        "organizacion_facultad",
        "et_pb_contact_rol_0",
    ];

    $errors = [];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = $field;
        }
    }

    if (!empty($errors)) {
        $response["message"] =
            "Faltan campos requeridos: " . implode(", ", $errors);
        echo json_encode($response);
        exit();
    }

    // Validate DNI
    if (!preg_match('/^\d{5,15}$/', $_POST["et_pb_contact_dni_0"])) {
        $response["message"] = "DNI inválido";
        echo json_encode($response);
        exit();
    }

    // Validate date
    if (!validateDate($_POST["et_pb_contact_fecha_nacimiento_0"])) {
        $response["message"] = "Fecha de nacimiento inválida";
        echo json_encode($response);
        exit();
    }

    // Validate email
    if (!filter_var($_POST["et_pb_contact_email_0"], FILTER_VALIDATE_EMAIL)) {
        $response["message"] = "Email inválido";
        echo json_encode($response);
        exit();
    }

    // If all validations pass, process the form
    // Here you would typically save the data to a database
    // For this example, we'll just return a success message

    $response["success"] = true;
    $response["message"] = "Formulario procesado exitosamente";
    $response["redirect"] = "https://cicco.conacyt.gov.py/ingreso-exitoso/";

    echo json_encode($response);
    exit();
} else {
    $response["message"] = "Método de solicitud no válido";
    echo json_encode($response);
    exit();
}
