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

    /**
     * Log script data before execution
     */
    public function logScriptData($scriptData, $encodedData) {
        // Instead of creating a separate log entry, store the data to be merged later
        return [
            'script_preparation' => [
                'timestamp' => date('Y-m-d H:i:s'),
                'data_to_encode' => $scriptData,
                'encoded_data' => $encodedData,
                'decoded_verification' => json_decode(base64_decode($encodedData), true)
            ]
        ];
    }

    /**
     * Log script execution response
     */
    public function logScriptResponse($output, $returnCode) {
        // Instead of creating a separate log entry, store the response to be merged later
        return [
            'script_execution' => [
                'timestamp' => date('Y-m-d H:i:s'),
                'output' => $output,
                'return_code' => $returnCode
            ]
        ];
    }

    /**
     * Enhanced logAttempt to include script data
     */
    public function logAttempt($data, $success, $response, $scriptLog = null) {
        // Log raw data for debugging
        error_log('Debug - Raw data received by logger: ' . print_r($data, true));
        
        $timestamp = date('Y-m-d H:i:s');
        
        // Build log entry with complete data
        $logEntry = [
            'timestamp' => $timestamp,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'success' => $success,
            'request_data' => [
                // Personal information
                'fecha_ingreso' => $data['et_pb_contact_fecha_ingreso_0'] ?? null,
                'nombres' => $data['et_pb_contact_nombres_0'] ?? null,
                'apellidos' => $data['et_pb_contact_apellidos_0'] ?? null,
                'dni' => $data['et_pb_contact_dni_0'] ?? null,
                'nacionalidad' => $data['et_pb_contact_nacionalidad_0'] ?? null,
                'sexo' => $data['et_pb_contact_genero_0'] ?? null,
                'nacimiento' => $data['et_pb_contact_fecha_nacimiento_0'] ?? null,
                'telefono' => $data['et_pb_contact_phone_0'] ?? null,
                'email' => $data['et_pb_contact_email_0'] ?? null,
                
                // Institutional information
                'instituciones' => $data['organizacion'] ?? null,
                'facultad' => $data['organizacion_facultad'] ?? null,
                'carrera' => $data['organizacion_facultad_carrera'] ?? null,
                'cargo_institucion' => $data['et_pb_contact_rol_0'] ?? null,
                'categoria_pronii' => $data['et_pb_contact_pronii_categoria_0'] ?? null,
                
                // Research identifiers
                'contact_orcid' => $data['et_pb_contact_orcid_0'] ?? null,
                'contact_scopus' => $data['et_pb_contact_scopus_0'] ?? null,
                'contact_wos' => $data['et_pb_contact_wos_0'] ?? null,
                
                // Location data
                'departamento' => $data['et_pb_contact_departamento_0'] ?? null,
                'ciudad' => $data['et_pb_contact_ciudad_0'] ?? null,
                
                // Research areas
                'ciencias_naturales' => $data['et_pb_contact_area_investigacion_0_23_0'] ?? null,
                'ingenieria_tecnologia' => $data['et_pb_contact_area_investigacion_0_23_1'] ?? null,
                'ciencias_medicas_salud' => $data['et_pb_contact_area_investigacion_0_23_2'] ?? null,
                'ciencias_agricolas_veterinarias' => $data['et_pb_contact_area_investigacion_0_23_3'] ?? null,
                'ciencias_sociales' => $data['et_pb_contact_area_investigacion_0_23_4'] ?? null,
                'humanidades_artes' => $data['et_pb_contact_area_investigacion_0_23_5'] ?? null
            ],
            'response' => $response,
            'captcha_used' => isset($_SESSION['captcha_validated']),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'raw_data' => $data, // Store complete raw data for debugging
            'python_script' => [
                'success' => $success,
                'output' => $response['script_output'] ?? null,
                'error_code' => $response['error_code'] ?? null,
                'preparation' => $scriptLog['script_preparation'] ?? null,
                'execution' => $scriptLog['script_execution'] ?? null
            ]
        ];

        // Create a more detailed text log entry
        $textLog = sprintf(
            "[%s] %s | Success: %s | Error: %s | User: %s %s (%s) | DNI: %s | Institution: %s | Role: %s | Areas: %s\n",
            $timestamp,
            $_SERVER['REMOTE_ADDR'],
            $success ? 'YES' : 'NO',
            $success ? 'NONE' : ($response['error'] ?? $response['message'] ?? 'Unknown error'),
            $data['et_pb_contact_nombres_0'] ?? 'N/A',
            $data['et_pb_contact_apellidos_0'] ?? 'N/A',
            $data['et_pb_contact_email_0'] ?? 'N/A',
            $data['et_pb_contact_dni_0'] ?? 'N/A',
            $data['organizacion'] ?? 'N/A',
            $data['et_pb_contact_rol_0'] ?? 'N/A',
            implode(', ', array_filter([
                $data['et_pb_contact_area_investigacion_0_23_0'] ?? null,
                $data['et_pb_contact_area_investigacion_0_23_1'] ?? null,
                $data['et_pb_contact_area_investigacion_0_23_2'] ?? null,
                $data['et_pb_contact_area_investigacion_0_23_3'] ?? null,
                $data['et_pb_contact_area_investigacion_0_23_4'] ?? null,
                $data['et_pb_contact_area_investigacion_0_23_5'] ?? null
            ]))
        );

        // Ensure log directory exists
        $logsDir = dirname($this->logFile);
        if (!file_exists($logsDir)) {
            mkdir($logsDir, 0755, true);
        }

        // Append to text log
        file_put_contents($this->logFile, $textLog, FILE_APPEND);

        // Update JSON log with better error handling
        try {
            $jsonLog = [];
            if (file_exists($this->jsonLogFile)) {
                $jsonContent = file_get_contents($this->jsonLogFile);
                if (!empty($jsonContent)) {
                    $jsonLog = json_decode($jsonContent, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        error_log('JSON decode error: ' . json_last_error_msg());
                        $jsonLog = [];
                    }
                }
            }
            
            // Keep last 1000 entries but ensure we don't lose successful registrations
            if (count($jsonLog) >= 1000) {
                // Keep all successful entries from the last 1000
                $successful = array_filter($jsonLog, function($entry) {
                    return $entry['success'] === true;
                });
                $jsonLog = array_merge($successful, array_slice($jsonLog, -500));
            }
            
            $jsonLog[] = $logEntry;
            
            file_put_contents(
                $this->jsonLogFile, 
                json_encode($jsonLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                LOCK_EX
            );
        } catch (Exception $e) {
            error_log('Error writing to JSON log: ' . $e->getMessage());
        }
        
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