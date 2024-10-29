<?php
class RegistrationLogger {
    private $logFile;
    private $jsonLogFile;
    
    public function __construct() {
        // Check if running locally
        if (in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1'])) {
            $this->logFile = '/logs/registrations.log';
            $this->jsonLogFile = '/logs/attempts.json';
        } else {
            $this->logFile = __DIR__ . '/../logs/registrations.log';
            $this->jsonLogFile = __DIR__ . '/../logs/attempts.json';
        }
        
        // Create logs directory if it doesn't exist
        $logsDir = dirname($this->logFile);
        if (!file_exists($logsDir)) {
            mkdir($logsDir, 0755, true);
        }
    }
    
    /**
     * Get the path to the JSON log file
     * @return string Path to the JSON log file
     */
    public function getLogFile() {
        return $this->jsonLogFile;
    }
    public function logAttempt($data, $success, $response) {
        // Log raw data for debugging
        error_log('Debug - Raw data received by logger: ' . print_r($data, true));
        
        // Get current timestamp for log entry
        $timestamp = date('Y-m-d H:i:s');
        
        // Build log entry array with user registration data and metadata
        $logEntry = [
            'timestamp' => $timestamp,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'success' => $success,
            'request_data' => [
                // Personal information - supporting both new and legacy form field names
                'fecha_ingreso' => $data['fecha-ingreso'] ?? $data['et_pb_contact_fecha_ingreso_0'] ?? null,
                'nombres' => $data['first-name'] ?? $data['et_pb_contact_nombres_0'] ?? null,
                'apellidos' => $data['last-name'] ?? $data['et_pb_contact_apellidos_0'] ?? null,
                'dni' => $data['id-number'] ?? $data['et_pb_contact_dni_0'] ?? null,
                'nacionalidad' => $data['nationality'] ?? $data['et_pb_contact_nacionalidad_0'] ?? null,
                'sexo' => $data['gender'] ?? $data['et_pb_contact_genero_0'] ?? null,
                'nacimiento' => $data['birth-date'] ?? $data['et_pb_contact_fecha_nacimiento_0'] ?? null,
                'telefono' => $data['mobile-phone'] ?? $data['et_pb_contact_phone_0'] ?? null,
                'email' => $data['institutional-email'] ?? $data['et_pb_contact_email_0'] ?? null,
                
                // Institutional information
                'instituciones' => $data['institution-name'] ?? $data['organizacion'] ?? null,
                'facultad' => $data['campus-faculty'] ?? $data['organizacion_facultad'] ?? null,
                'carrera' => $data['specific-unit-career'] ?? $data['organizacion_facultad_carrera'] ?? null,
                'cargo_institucion' => $data['institutional-role'] ?? $data['et_pb_contact_rol_0'] ?? null,
                'categoria_pronii' => $data['pronii-category'] ?? $data['et_pb_contact_pronii_categoria_0'] ?? null,
                
                // Research identifiers
                'contact_orcid' => $data['orcid-id'] ?? $data['et_pb_contact_orcid_0'] ?? null,
                'contact_scopus' => $data['scopus-id'] ?? $data['et_pb_contact_scopus_0'] ?? null,
                'contact_wos' => $data['wos-id'] ?? $data['et_pb_contact_wos_0'] ?? null,
                
                // Location data
                'departamento' => $data['department'] ?? $data['et_pb_contact_departamento_0'] ?? null,
                'ciudad' => $data['city'] ?? $data['et_pb_contact_ciudad_0'] ?? null,
                
                // Research areas
                'ciencias_naturales' => $data['research-area-natural'] ?? $data['et_pb_contact_area_investigacion_0_23_0'] ?? null,
                'ingenieria_y_tecnologia' => $data['research-area-engineering'] ?? $data['et_pb_contact_area_investigacion_0_23_1'] ?? null,
                'ciencias_medicas_y_de_la_salud' => $data['research-area-medical'] ?? $data['et_pb_contact_area_investigacion_0_23_2'] ?? null,
                'ciencias_agricolas' => $data['research-area-agricultural'] ?? $data['et_pb_contact_area_investigacion_0_23_3'] ?? null,
                'ciencias_sociales' => $data['research-area-social'] ?? $data['et_pb_contact_area_investigacion_0_23_4'] ?? null,
                'humanidades' => $data['research-area-humanities'] ?? $data['et_pb_contact_area_investigacion_0_23_5'] ?? null
            ],
            'response' => $response,
            'captcha_used' => isset($_SESSION['captcha_validated']), // Track if CAPTCHA validation was used
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];

        // Log the processed entry for debugging
        error_log('Debug - Processed Log Entry: ' . print_r($logEntry, true));

        // Format and append a text log entry with key registration details
        // Format: [timestamp] ip | success | user info | institution | role
        $textLog = sprintf(
            "[%s] %s | Success: %s | User: %s %s (%s) | Institution: %s | Role: %s\n",
            $timestamp,
            $_SERVER['REMOTE_ADDR'],
            $success ? 'YES' : 'NO', 
            $data['first-name'] ?? $data['et_pb_contact_nombres_0'] ?? 'N/A',
            $data['last-name'] ?? $data['et_pb_contact_apellidos_0'] ?? 'N/A',
            $data['institutional-email'] ?? $data['et_pb_contact_email_0'] ?? 'N/A',
            $data['institution-name'] ?? $data['organizacion'] ?? 'N/A',
            $data['institutional-role'] ?? $data['et_pb_contact_rol_0'] ?? 'N/A'
        );

        // Append the log entry to the text log file
        file_put_contents($this->logFile, $textLog, FILE_APPEND);

        // Load existing JSON log entries
        $jsonLog = [];
        if (file_exists($this->jsonLogFile)) {
            $jsonContent = file_get_contents($this->jsonLogFile);
            if (!empty($jsonContent)) {
                $jsonLog = json_decode($jsonContent, true) ?? [];
            }
        }
        
        // Keep only last 1000 entries to prevent file from growing too large
        $jsonLog = array_slice($jsonLog, -999, 999);
        $jsonLog[] = $logEntry;
        
        // Save updated JSON log
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