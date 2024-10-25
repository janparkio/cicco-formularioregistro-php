<?php
require_once 'RegistrationLogger.php';

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
    <title>Estadísticas de Registro</title>
    <link href="output.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Estadísticas de Registro</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Stats Cards -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Resumen</h2>
                <div class="space-y-4">
                    <p>Intentos Totales: <span class="font-bold"><?php echo $stats['total_attempts']; ?></span></p>
                    <p>Exitosos: <span class="font-bold text-green-600"><?php echo $stats['successful_attempts']; ?></span></p>
                    <p>Fallidos: <span class="font-bold text-red-600"><?php echo $stats['failed_attempts']; ?></span></p>
                    <p>Últimas 24h: <span class="font-bold"><?php echo $stats['last_24h_attempts']; ?></span></p>
                </div>
            </div>

            <!-- Last Successful Registration -->
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
                    $logs = json_decode(file_get_contents($logger->jsonLogFile), true) ?? [];
                    $recent_logs = array_slice($logs, -10);
                    foreach ($recent_logs as $log): 
                    ?>
                    <tr>
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
</body>
</html>