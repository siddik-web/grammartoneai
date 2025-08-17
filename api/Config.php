<?php
/**
 * Configuration file for Ollama API Proxy
 */

class Config {
    // Ollama API settings
    const OLLAMA_API_URL = 'http://localhost:11434/api/generate';
    const DEFAULT_MODEL = 'gemma3:1b';
    
    // Security settings
    const ALLOWED_ORIGINS = [
        'http://localhost',
        'http://localhost:8000',
        'http://127.0.0.1',
        'http://127.0.0.1:8000'
    ];
    
    // Request settings
    const REQUEST_TIMEOUT = 300; // 5 minutes
    const CONNECT_TIMEOUT = 30;
    
    // Logging settings
    const ENABLE_LOGGING = true; // Enable logging for debugging
    const LOG_FILE = __DIR__ . '/../logs/ollama-proxy.log';
}