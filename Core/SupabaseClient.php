<?php
namespace Core;

class SupabaseClient {
    private $apiUrl;
    private $apiKey;
    private $debug;

    public function __construct($apiUrl, $apiKey, $debug = false) {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiKey = $apiKey;
        $this->debug = $debug;
    }

    public function request($endpoint, $method = 'GET', $data = null, $filters = '', $extraHeaders = []) {
        $url = $this->apiUrl . '/' . ltrim($endpoint, '/');
        if (!empty($filters)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . $filters;
        }

        $ch = curl_init();
        $headers = array_merge([
            'apikey: ' . $this->apiKey,
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ], $extraHeaders);

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYPEER => defined('SSL_VERIFY') ? SSL_VERIFY : false,   // Usar constante si está definida
            CURLOPT_SSL_VERIFYHOST => defined('SSL_VERIFY') && SSL_VERIFY ? 2 : 0,
        ];
        
        if ($this->debug) {
            $options[CURLOPT_VERBOSE] = true;
            $options[CURLOPT_STDERR] = fopen('php://temp', 'w+');
        }
        
        curl_setopt_array($ch, $options);
        

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $responseHeaders = substr($response, 0, $headerSize);
        $responseBody = substr($response, $headerSize);

        if ($this->debug) {
            error_log("[$method] $url");
            error_log("Headers enviados: " . print_r($headers, true));
            error_log("Respuesta ($httpCode): $responseBody");
        }

        if ($error) {
            throw new \Exception("Error de conexión: " . $error);
        }

        if (empty($responseBody)) {
            return ['status' => $httpCode, 'headers' => $responseHeaders, 'data' => []];
        }

        $decoded = json_decode($responseBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Error al decodificar JSON: " . json_last_error_msg());
        }

        if ($httpCode >= 400) {
            $errorMsg = $decoded['message'] ?? $decoded['error_description'] ?? $decoded['error'] ?? 'Error desconocido';
            throw new \Exception("Error API ($httpCode): " . $errorMsg, $httpCode);
        }

        return ['status' => $httpCode, 'headers' => $responseHeaders, 'data' => $decoded];
    }
}

?>
