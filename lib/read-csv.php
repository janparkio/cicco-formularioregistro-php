<?php
// Generate the path to today's CSV file
$date = date('Y-m-d');  // Get current date in 'YYYY-MM-DD' format
$file_path = "/var/www/CSV/Usuarios_por_Validar_V2_" . $date . ".csv";  // Construct the file path

// Check if the file is readable and then output it
if (is_readable($file_path)) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="Usuarios_por_Validar_V2_' . $date . '.csv"');
    readfile($file_path);
} else {
    echo "CSV file for today (" . $date . ") is not accessible or does not exist.";
}
?>
