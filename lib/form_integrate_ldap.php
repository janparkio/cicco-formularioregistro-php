<?php
session_start();

function validateDate($date, $format = "Y-m-d")
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function loadJSONData($file)
{
    $jsonString = file_get_contents($file);
    return json_decode($jsonString, true);
}

function validateForm($postData)
{
    $errors = [];
    $separador = "|";

    // Load necessary data
    $geographicData = loadJSONData("../data/REGION_CIUDAD.json");
    $institutionsData = loadJSONData("../data/INSTITUCIONES_2023_NEW.json");
    $nationalityData = loadJSONData("../data/nacionalidades.json");

    // Validate each field
    if (empty($postData["et_pb_contact_nombres_0"])) {
        $errors[] = "Nombres" . $separador;
    }

    if (empty($postData["et_pb_contact_apellidos_0"])) {
        $errors[] = "Apellidos" . $separador;
    }

    $dni = $postData["et_pb_contact_dni_0"];
    if (!(strlen($dni) >= 5 && strlen($dni) <= 15 && is_numeric($dni))) {
        $errors[] = "DNI" . $separador;
    }

    $nacionalidad = $postData["et_pb_contact_nacionalidad_0"];
    if (
        !in_array(
            $nacionalidad,
            array_column($nationalityData["paises"], "datos")[0]
        )
    ) {
        $errors[] = "Nacionalidad" . $separador;
    }

    $sexo = $postData["et_pb_contact_genero_0"];
    if (!in_array($sexo, ["Masculino", "Femenino"])) {
        $errors[] = "Sexo" . $separador;
    }

    if (!validateDate($postData["et_pb_contact_fecha_nacimiento_0"])) {
        $errors[] = "Fecha Nacimiento" . $separador;
    }

    $telefono = $postData["et_pb_contact_phone_0"];
    if (
        !(
            strlen($telefono) >= 6 &&
            strlen($telefono) <= 12 &&
            is_numeric($telefono)
        )
    ) {
        $errors[] = "Teléfono" . $separador;
    }

    if (
        !filter_var($postData["et_pb_contact_email_0"], FILTER_VALIDATE_EMAIL)
    ) {
        $errors[] = "Email" . $separador;
    }

    $departamento = $postData["et_pb_contact_departamento_0"];
    if (
        !in_array(
            $departamento,
            array_column($geographicData["pais"], "nombre_region")
        )
    ) {
        $errors[] = "Departamento" . $separador;
    }

    $ciudad = $postData["et_pb_contact_ciudad_0"];
    $ciudades = [];
    foreach ($geographicData["pais"] as $region) {
        $ciudades = array_merge(
            $ciudades,
            array_column($region["ciudades"], "ciudad")
        );
    }
    if (!in_array($ciudad, $ciudades)) {
        $errors[] = "Ciudad" . $separador;
    }

    // Validate research areas
    $researchAreas = [
        "et_pb_contact_area_investigacion_0_23_0",
        "et_pb_contact_area_investigacion_0_23_1",
        "et_pb_contact_area_investigacion_0_23_2",
        "et_pb_contact_area_investigacion_0_23_3",
        "et_pb_contact_area_investigacion_0_23_4",
        "et_pb_contact_area_investigacion_0_23_5",
    ];
    $hasResearchArea = false;
    foreach ($researchAreas as $area) {
        if (!empty($postData[$area])) {
            $hasResearchArea = true;
            break;
        }
    }
    if (!$hasResearchArea) {
        $errors[] = "Dominio científico de su interés" . $separador;
    }

    // Validate institution
    $institucion = $postData["organizacion"];
    if (!isset($institutionsData[$institucion])) {
        $errors[] = "Institución" . $separador;
    }

    // Validate faculty
    $facultad = $postData["organizacion_facultad"];
    if (!isset($institutionsData[$institucion][$facultad])) {
        $errors[] = "Facultad" . $separador;
    }

    // Validate role
    $roles = [
        "Investigador",
        "Investigador PRONII",
        "Docente universitario",
        "Estudiante universitario",
        "Personal administrativo",
        "Personal técnico",
        "Consultor/Asesor",
    ];
    if (!in_array($postData["et_pb_contact_rol_0"], $roles)) {
        $errors[] = "Cargo Institución" . $separador;
    }

    return $errors;
}

function prepareFormData($postData)
{
    return [
        "accion" => "validar_usuarios",
        "metodo" => $_SERVER["REQUEST_METHOD"],
        "fecha_registro" => $postData["et_pb_contact_fecha_ingreso_0"],
        "nombres" => $postData["et_pb_contact_nombres_0"],
        "apellidos" => $postData["et_pb_contact_apellidos_0"],
        "uid" => "cona" . $postData["et_pb_contact_dni_0"],
        "nacionalidad" => $postData["et_pb_contact_nacionalidad_0"],
        "sexo" => $postData["et_pb_contact_genero_0"],
        "nacimiento" => $postData["et_pb_contact_fecha_nacimiento_0"],
        "telefono" => $postData["et_pb_contact_phone_0"],
        "email" => $postData["et_pb_contact_email_0"],
        "instituciones" => $postData["organizacion"],
        "facultad" => $postData["organizacion_facultad"],
        "carrera" => $postData["organizacion_facultad_carrera"],
        "cargo_institucion" => $postData["et_pb_contact_rol_0"],
        "categoria_pronii" => $postData["et_pb_contact_pronii_categoria_0"],
        "contact_orcid" => $postData["et_pb_contact_orcid_0"],
        "contact_scopus" => $postData["et_pb_contact_scopus_0"],
        "contact_wos" => $postData["et_pb_contact_wos_0"],
        "departamento" => $postData["et_pb_contact_departamento_0"],
        "ciudad" => $postData["et_pb_contact_ciudad_0"],
        "ciencias_naturales" =>
            $postData["et_pb_contact_area_investigacion_0_23_0"],
        "ingenieria_tecnologia" =>
            $postData["et_pb_contact_area_investigacion_0_23_1"],
        "ciencias_medicas_salud" =>
            $postData["et_pb_contact_area_investigacion_0_23_2"],
        "ciencias_agricolas_veterinarias" =>
            $postData["et_pb_contact_area_investigacion_0_23_3"],
        "ciencias_sociales" =>
            $postData["et_pb_contact_area_investigacion_0_23_4"],
        "humanidades_artes" =>
            $postData["et_pb_contact_area_investigacion_0_23_5"],
    ];
}

// Main execution
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $errors = validateForm($_POST);

    if (empty($errors)) {
        $formData = prepareFormData($_POST);
        $json = json_encode($formData);
        $parametros = '"' . base64_encode($json) . '"';

        // Execute external script
        exec(
            "/var/www/PY/rutina_ingreso_2023.sh validar_usuarios " .
                $parametros,
            $output,
            $return
        );

        if ($return == "0") {
            header("Location: " . $output[0], true, 301);
            exit();
        } else {
            header(
                "Location: https://cicco.conacyt.gov.py/register/register_error_parametros.html?ERROR=Procesamiento",
                true,
                301
            );
            exit();
        }
    } else {
        $errorString = implode("", $errors);
        header(
            "Location: https://cicco.conacyt.gov.py/register/register_error_parametros.html?ERROR=" .
                urlencode($errorString),
            true,
            301
        );
        exit();
    }
}
?>
