<?php

// Add these helper functions after the initial setup
function normalizeString($str) {
    // Convert to lowercase and trim
    $str = mb_strtolower(trim($str));
    
    // Replace special characters
    $unwanted_array = array(
        'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u',
        'Á'=>'A', 'É'=>'E', 'Í'=>'I', 'Ó'=>'O', 'Ú'=>'U',
        'ñ'=>'n', 'Ñ'=>'N'
    );
    $str = strtr($str, $unwanted_array);
    
    // Log the normalization
    error_log("Normalizing string: '$str'");
    
    return $str;
}
// Log validation messages
function logValidation($message, $data = null) {
    $log = "Validation: $message";
    if ($data !== null) {
        $log .= " - " . print_r($data, true);
    }
    error_log($log);
}
// Validate institution data
function validateInstitutionData($inst_name, $inst_data) {
    // First check if it's an array
    if (!is_array($inst_data)) {
        logValidation("Invalid institution data: not an array", $inst_data);
        return false;
    }

    // Check if there are any faculties
    if (empty($inst_data)) {
        logValidation("Institution has no faculties", [
            'institution' => $inst_name
        ]);
        return false;
    }

    // Log successful validation
    logValidation("Institution validated successfully", [
        'nombre' => $inst_name,
        'facultades_count' => count($inst_data)
    ]);

    return true;
}
// Helper function to find institution
function findInstitution($institutions_data, $search_name) {
    if (!is_array($institutions_data)) {
        logValidation("Invalid institutions data: not an array");
        return null;
    }

    $normalized_search = normalizeString($search_name);
    
    // In this case, the institution names are the keys
    foreach ($institutions_data as $inst_name => $inst_data) {
        if (normalizeString($inst_name) === $normalized_search) {
            if (validateInstitutionData($inst_name, $inst_data)) {
                return [
                    'nombre' => $inst_name,
                    'facultades' => array_keys($inst_data)
                ];
            }
        }
    }

    logValidation("Institution not found", [
        'search_name' => $search_name,
        'normalized_search' => $normalized_search,
        'available_institutions' => array_keys($institutions_data)
    ]);
    
    return null;
}