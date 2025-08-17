<?php
/**
 * Test script for Ollama API Proxy
 * This script demonstrates how to use the OllamaProxy class
 */

require_once 'OllamaProxy.php';

// Example usage
try {
    // Create an instance of the OllamaProxy
    $proxy = new OllamaProxy();
    
    // For testing purposes, we can simulate a request
    // In a real scenario, this would come from an actual HTTP request
    
    // Example data that would normally come from $_POST
    $testData = [
        'model' => 'gemma3:1b',
        'prompt' => 'Why is the sky blue?',
        'stream' => false,
        'format' => 'json'
    ];
    
    // Validate input
    $proxy->validateInput($testData);
    
    // Prepare data
    $preparedData = $proxy->prepareData($testData);
    
    echo "Prepared data for Ollama API:\n";
    echo json_encode($preparedData, JSON_PRETTY_PRINT);
    
    echo "\n\nThe proxy is ready to handle requests at ollama-proxy.php\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}