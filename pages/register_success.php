<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Exitoso - CICCO Conacyt</title>
    <link rel="icon" type="image/png" href="../favicon-48x48.png" sizes="48x48" />
    <link rel="icon" type="image/svg+xml" href="../favicon.svg" />
    <link rel="shortcut icon" href="../favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="CICCO" />
    <link rel="manifest" href="../site.webmanifest" />
    <link href="../output.css" rel="stylesheet">
    <meta name="description" content="Formulario de registro de usuario para CICCO - Consejo Nacional de Ciencia y Tecnología de Paraguay">
    <meta name="keywords" content="CICCO, Conacyt, registro, usuario, ciencia, tecnología, Paraguay">
    <meta name="author" content="CICCO - Conacyt">
    <meta property="og:title" content="Formulario de Registro - CICCO Conacyt">
    <meta property="og:description" content="Registro de usuario para acceder a los servicios de CICCO - Conacyt">
    <meta property="og:image" content="../img/cicco-registro-usuario-og.png">
    <meta property="og:url" content="https://cicco.conacyt.gov.py/solicitud_registro_usuario/register_success">
    <meta name="twitter:card" content="summary_large_image">
</head>
<body>
    <div class="min-h-screen bg-gray-100 py-6 flex flex-col justify-center sm:py-12">
        <div class="relative py-3 sm:max-w-xl sm:mx-auto">
            <div class="relative px-4 py-10 bg-white shadow-lg sm:rounded-3xl sm:p-20">
                <div class="max-w-md mx-auto">
                    <div class="divide-y divide-gray-200">
                        <div class="py-8 text-base leading-6 space-y-4 text-gray-700 sm:text-lg sm:leading-7">
                            <div class="text-center">
                                <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <h1 class="text-2xl font-bold text-green-600 mb-4">¡Registro Exitoso!</h1>
                                <?php if (isset($_SESSION['form_data'])): ?>
                                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                    <p class="font-medium text-gray-800">
                                        Gracias <?php echo htmlspecialchars($_SESSION['form_data']['et_pb_contact_nombres_0']); ?>!
                                    </p>
                                    <p class="text-gray-600">
                                        Enviaremos tus credenciales a:<br>
                                        <span class="font-medium"><?php echo htmlspecialchars($_SESSION['form_data']['et_pb_contact_email_0']); ?></span>
                                    </p>
                                </div>
                                <?php endif; ?>
                                <p class="mt-4 text-lg text-gray-600">
                                    Su solicitud de registro ha sido recibida correctamente.
                                </p>
                                <div class="mt-6 space-y-4">
                                    <p class="text-gray-700">
                                        Recibirá sus credenciales de acceso en el correo electrónico registrado dentro de las próximas 72 horas hábiles.
                                    </p>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <p class="text-gray-700 font-medium mb-2">Información adicional:</p>
                                        <ul class="text-gray-600 space-y-2">
                                            <li>Revise su carpeta de correo no deseado (SPAM)</li>
                                            <li>Si no recibe sus credenciales después de 72 horas hábiles, contáctenos vía WhatsApp: +595 991 838 829</li>
                                        </ul>
                                    </div>
                                    <p class="text-sm text-gray-500 italic">
                                        *Para garantizar la compatibilidad con diferentes clientes de correo electrónico, se han omitido los signos diacríticos.
                                    </p>
                                </div>
                                <div class="mt-8 space-y-4">
                                    <a href="/" 
                                       class="inline-block bg-primary-600 text-white px-6 py-3 rounded-md hover:bg-primary-700 transition-colors">
                                        Volver al Inicio
                                    </a>
                                    <div>
                                        <a href="/solicitud_registro_usuario" 
                                           class="inline-block text-gray-600 hover:text-gray-800 underline text-sm mt-2">
                                            ¿Datos incorrectos? Registrarse nuevamente
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            <?php if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true'): ?>
            console.log('Form Data:', <?php 
                $debugData = $_SESSION['form_data'] ?? [];
                echo json_encode($debugData, JSON_PRETTY_PRINT);
            ?>);
            <?php endif; ?>
        </script>
    </body>
</html>