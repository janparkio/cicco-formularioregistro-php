<?php
require_once '../lib/RegistrationLogger.php';

// Basic authentication
$valid_username = 'cicco'; // cambiar luego
$valid_password = 'CICCOeslaclave#2024'; // cambiar luego

if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== $valid_username || 
    $_SERVER['PHP_AUTH_PW'] !== $valid_password) {
    header('WWW-Authenticate: Basic realm="Registration Stats"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Authentication required';
    exit;
}

$logger = new RegistrationLogger();
$stats = $logger->getStats();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas de Registro - CICCO Conacyt</title>
    
    <!-- Favicon and app icons -->
    <link rel="icon" type="image/png" href="../favicon-48x48.png" sizes="48x48" />
    <link rel="icon" type="image/svg+xml" href="../favicon.svg" />
    <link rel="shortcut icon" href="../favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="CICCO" />
    <link rel="manifest" href="../site.webmanifest" />
    <link href="../output.css" rel="stylesheet">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Estadísticas de registro de usuarios para CICCO - Consejo Nacional de Ciencia y Tecnología de Paraguay">
    <meta name="keywords" content="CICCO, Conacyt, estadísticas, registro, usuario, ciencia, tecnología, Paraguay">
    <meta name="author" content="CICCO - Conacyt">
    
    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:title" content="Estadísticas de Registro - CICCO Conacyt">
    <meta property="og:description" content="Estadísticas de registro de usuarios para CICCO - Conacyt">
    <meta property="og:image" content="../img/cicco-registro-usuario-og.png">
    <meta property="og:url" content="https://cicco.conacyt.gov.py/solicitud_registro_usuario/registration_stats.php">
    <meta name="twitter:card" content="summary_large_image">

    <!-- Modal Styles -->
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        
        .modal-content {
            position: relative;
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 800px;
            max-height: 70vh;
            overflow-y: auto;
        }
        
        .close-button {
            position: absolute;
            right: 20px;
            top: 20px;
        }
        
        .json-content {
            white-space: pre-wrap;
            font-family: monospace;
            background-color: #f3f4f6;
            padding: 1rem;
            border-radius: 4px;
            margin-top: 1rem;
        }
    </style>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Estadísticas de Registro</h1>
        
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Summary Stats Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Resumen</h2>
                <div class="space-y-4">
                    <p>Intentos Totales: <span class="font-bold"><?php echo $stats['total_attempts']; ?></span></p>
                    <p>Exitosos: <span class="font-bold text-green-600"><?php echo $stats['successful_attempts']; ?></span></p>
                    <p>Fallidos: <span class="font-bold text-red-600"><?php echo $stats['failed_attempts']; ?></span></p>
                    <p>Últimas 24h: <span class="font-bold"><?php echo $stats['last_24h_attempts']; ?></span></p>
                </div>
            </div>

            <!-- Last Successful Registration Card -->
            <?php if ($stats['last_successful']): ?>
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Último Registro Exitoso</h2>
                <div class="space-y-2">
                    <?php if (isset($stats['last_successful']) && is_array($stats['last_successful'])): ?>
                        <p>Fecha: <?php echo $stats['last_successful']['timestamp']; ?></p>
                        <p>Nombre: <?php echo htmlspecialchars($stats['last_successful']['request_data']['nombres'] . ' ' . $stats['last_successful']['request_data']['apellidos']); ?></p>
                        <p>Correo: <?php echo htmlspecialchars($stats['last_successful']['request_data']['email']); ?></p>
                    <?php else: ?>
                        <p>No hay registros exitosos recientes.</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Recent Attempts Table -->
        <div class="mt-8 bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institución</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    $logs = json_decode(file_get_contents($logger->getLogFile()), true) ?? [];
                    $recent_logs = array_slice($logs, -10); // Get last 10 entries
                    foreach ($recent_logs as $index => $log): 
                    ?>
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="showJsonDetails(<?php echo htmlspecialchars(json_encode($log), ENT_QUOTES, 'UTF-8'); ?>)">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $log['timestamp']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $log['success'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $log['success'] ? 'Exitoso' : 'Fallido'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo htmlspecialchars($log['request_data']['nombres'] . ' ' . $log['request_data']['apellidos']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($log['request_data']['instituciones']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Details Modal -->
    <div id="jsonModal" class="modal">
        <div class="modal-content">
            <button class="close-button bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">
                Cerrar
            </button>
            <h2 class="text-xl font-semibold mb-4">Detalles del Intento</h2>
            <div id="jsonContent" class="json-content"></div>
        </div>
    </div>

    <!-- Modal Control Scripts -->
    <script>
    const modal = document.getElementById('jsonModal');
    const jsonContent = document.getElementById('jsonContent');
    const closeButton = document.querySelector('.close-button');

    // Display modal with formatted JSON data
    function showJsonDetails(data) {
        jsonContent.textContent = JSON.stringify(data, null, 2);
        modal.style.display = 'block';
    }

    // Close modal handlers
    closeButton.onclick = function() {
        modal.style.display = 'none';
    }

    // Close on outside click
    window.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }

    // Close on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.style.display === 'block') {
            modal.style.display = 'none';
        }
    });
    </script>
</body>
</html>