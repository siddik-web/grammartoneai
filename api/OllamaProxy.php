<?php
require_once 'Config.php';
require_once 'TextStatistics.php';

class OllamaProxy {
    private $ollamaUrl;
    private $defaultModel;
    
    public function __construct($ollamaUrl = null, $defaultModel = null) {
        $this->ollamaUrl = $ollamaUrl ?? Config::OLLAMA_API_URL;
        $this->defaultModel = $defaultModel ?? Config::DEFAULT_MODEL;
    }
    
    /**
     * Handle CORS headers
     */
    public function handleCors() {
        // For development, we allow all origins
        // In production, you might want to restrict this
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        header('Content-Type: application/json');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
    
    /**
     * Get and validate input data
     */
    public function getInputData() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            throw new Exception('Invalid JSON input');
        }
        
        return $input;
    }
    
    /**
     * Validate required fields
     */
    public function validateInput($input) {
        if (!isset($input['prompt'])) {
            throw new Exception('Prompt is required');
        }
        
        return true;
    }
    
    /**
     * Prepare data for Ollama API
     */
    public function prepareData($input) {
        $data = [
            'model' => $input['model'] ?? $this->defaultModel,
            'prompt' => $input['prompt'],
            'stream' => $input['stream'] ?? false,
            'format' => $input['format'] ?? 'json'
        ];
        
        // Only add options if it's provided and is a non-empty array or object
        if (isset($input['options']) && !empty($input['options']) && 
            (is_array($input['options']) || is_object($input['options']))) {
            $data['options'] = $input['options'];
        }
        
        return $data;
    }
    
    /**
     * Make request to Ollama API
     */
    public function makeRequest($data) {
        $ch = curl_init($this->ollamaUrl);
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data))
            ],
            CURLOPT_TIMEOUT => Config::REQUEST_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => Config::CONNECT_TIMEOUT
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception('cURL error: ' . $error);
        }
        
        return [
            'response' => $response,
            'httpCode' => $httpCode
        ];
    }
    
    /**
     * Process Ollama response and add readability metrics if missing
     */
    public function processOllamaResponse($responseText, $originalPrompt) {
        // Try to extract the text that was analyzed from the prompt
        $originalText = $this->extractTextFromPrompt($originalPrompt);
        
        // Decode the response
        $responseData = json_decode($responseText, true);
        
        // If we couldn't decode it, return as is
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $responseText;
        }
        
        // Add readability metrics if they're missing
        if (!isset($responseData['readability_score']) || !isset($responseData['grade_level'])) {
            // Calculate readability metrics for the corrected text if available
            $textToAnalyze = $responseData['corrected_text'] ?? $originalText;
            
            if ($textToAnalyze) {
                $responseData['readability_score'] = TextStatistics::calculateReadabilityScore($textToAnalyze);
                $responseData['grade_level'] = TextStatistics::calculateGradeLevel($textToAnalyze);
            }
        }
        
        // Return the updated response as JSON
        return json_encode($responseData);
    }
    
    /**
     * Extract original text from prompt
     * This is a simple extraction that looks for "Text: " in the prompt
     */
    private function extractTextFromPrompt($prompt) {
        // Look for "Text: " followed by the actual text
        $pattern = '/Text:\s*(.+)$/s';
        if (preg_match($pattern, $prompt, $matches)) {
            return trim($matches[1]);
        }
        
        return '';
    }
    
    /**
     * Process and return the response
     */
    public function processResponse($response, $originalPrompt = '') {
        http_response_code($response['httpCode']);
        
        // If this is a successful response from Ollama, process it to add readability metrics
        if ($response['httpCode'] == 200) {
            $responseData = json_decode($response['response'], true);
            
            if (isset($responseData['response']) && json_last_error() === JSON_ERROR_NONE) {
                // Process the Ollama response to add readability metrics
                $processedResponse = $responseData;
                $processedResponse['response'] = $this->processOllamaResponse($responseData['response'], $originalPrompt);
                
                echo json_encode($processedResponse);
                return;
            }
        }
        
        // For all other cases, return the response as is
        echo $response['response'];
    }
    
    /**
     * Log requests for debugging
     */
    public function logRequest($data, $response) {
        if (!Config::ENABLE_LOGGING) {
            return;
        }
        
        $logEntry = date('Y-m-d H:i:s') . " - Request: " . json_encode($data) . 
                   " - Response Code: " . $response['httpCode'] . "\n";
        
        file_put_contents(Config::LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Handle errors
     */
    public function handleError($exception) {
        // Log error if enabled
        if (Config::ENABLE_LOGGING) {
            $errorEntry = date('Y-m-d H:i:s') . " - Error: " . $exception->getMessage() . "\n";
            file_put_contents(Config::LOG_FILE, $errorEntry, FILE_APPEND | LOCK_EX);
        }
        
        http_response_code(400);
        echo json_encode(['error' => $exception->getMessage()]);
    }
    
    /**
     * Main method to handle the request
     */
    public function handleRequest() {
        try {
            $this->handleCors();
            $input = $this->getInputData();
            $this->validateInput($input);
            $data = $this->prepareData($input);
            $response = $this->makeRequest($data);
            
            // Log the request if enabled
            $this->logRequest($data, $response);
            
            $this->processResponse($response, $data['prompt'] ?? '');
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }
}