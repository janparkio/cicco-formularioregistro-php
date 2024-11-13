<?php
$file_path = '/var/www/PY/rutina_ingreso_2023.py';

if (is_readable($file_path)) {
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="rutina_ingreso_2023.py"');
    readfile($file_path);
} else {
    echo "File is not accessible.";
}
?>
