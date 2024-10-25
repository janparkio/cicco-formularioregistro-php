<?php
class RegistrationLogger {
    private $logFile;
    private $jsonLogFile;
    
    public function __construct() {
        $this->logFile = __DIR__ . '/../logs/registrations.log';
        $this->jsonLogFile = __DIR__ . '/../logs/attempts.json';
        
        // Create logs directory if it doesn't exist
        $logsDir = dirname($this->logFile);
        if (!file_exists($logsDir)) {
            mkdir($logsDir, 0755, true);
        }
    }
    
    public function logAttempt($data, $success, $response) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = [
            'timestamp' => $timestamp,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'success' => $success,
            'request_data' => [
                'nombres' => $data['nombres'] ?? null,
                'apellidos' => $data['apellidos'] ?? null,
                'email' => $data['email'] ?? null,
                'dni' => $data['uid'] ?? null,
                'instituciones' => $data['instituciones'] ?? null,
                'cargo_institucion' => $data['cargo_institucion'] ?? null,
                'nacionalidad' => $data['nacionalidad'] ?? null,
                'sexo' => $data['sexo'] ?? null,
                'nacimiento' => $data['nacimiento'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'facultad' => $data['facultad'] ?? null,
                'carrera' => $data['carrera'] ?? null,
                'categoria_pronii' => $data['categoria_pronii'] ?? null,
                'contact_orcid' => $data['contact_orcid'] ?? null,
                'contact_scopus' => $data['contact_scopus'] ?? null,
                'contact_wos' => $data['contact_wos'] ?? null,
                'departamento' => $data['departamento'] ?? null,
                'ciudad' => $data['ciudad'] ?? null,
                'ciencias_naturales' => $data['ciencias_naturales'] ?? null,
                'ingenieria_y_tecnologia' => $data['ingenieria_y_tecnologia'] ?? null,
                'ciencias_medicas_y_de_la_salud' => $data['ciencias_medicas_y_de_la_salud'] ?? null,
                'ciencias_agricolas' => $data['ciencias_agricolas'] ?? null,
                'ciencias_sociales' => $data['ciencias_sociales'] ?? null,
                'humanidades' => $data['humanidades'] ?? null,
            ],
            'response' => $response,
            'captcha_used' => isset($_SESSION['captcha_validated']), // Don't log actual CAPTCHA value for security
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];

        // Text log
        $textLog = sprintf(
            "[%s] %s | Success: %s | User: %s %s (%s) | Institution: %s | Role: %s\n",
            $timestamp,
            $_SERVER['REMOTE_ADDR'],
            $success ? 'YES' : 'NO',
            $data['nombres'] ?? 'N/A',
            $data['apellidos'] ?? 'N/A',
            $data['email'] ?? 'N/A',
            $data['instituciones'] ?? 'N/A',
            $data['cargo_institucion'] ?? 'N/A'
        );
        file_put_contents($this->logFile, $textLog, FILE_APPEND);

        // JSON log
        $jsonLog = [];
        if (file_exists($this->jsonLogFile)) {
            $jsonContent = file_get_contents($this->jsonLogFile);
            if (!empty($jsonContent)) {
                $jsonLog = json_decode($jsonContent, true) ?? [];
            }
        }
        
        // Keep only last 1000 entries
        $jsonLog = array_slice($jsonLog, -999, 999);
        $jsonLog[] = $logEntry;
        
        file_put_contents($this->jsonLogFile, json_encode($jsonLog, JSON_PRETTY_PRINT));
        
        return $logEntry;
    }

    public function getStats() {
        if (!file_exists($this->jsonLogFile)) {
            return [
                'total_attempts' => 0,
                'successful_attempts' => 0,
                'failed_attempts' => 0,
                'last_24h_attempts' => 0,
                'last_successful' => null
            ];
        }

        $logs = json_decode(file_get_contents($this->jsonLogFile), true) ?? [];
        $now = time();
        $last24h = 0;
        $successful = 0;
        $lastSuccessful = null;

        foreach ($logs as $log) {
            if ($log['success']) {
                $successful++;
                $lastSuccessful = $log;
            }
            
            $logTime = strtotime($log['timestamp']);
            if ($now - $logTime <= 86400) { // 24 hours
                $last24h++;
            }
        }

        return [
            'total_attempts' => count($logs),
            'successful_attempts' => $successful,
            'failed_attempts' => count($logs) - $successful,
            'last_24h_attempts' => $last24h,
            'last_successful' => $lastSuccessful
        ];
    }
}