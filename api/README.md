# PHP OOP Ollama API Proxy

This PHP script acts as a proxy for the Ollama API to avoid CORS issues when making requests from the browser. It's implemented using Object-Oriented Programming principles for better maintainability and extensibility.

## How it works

The PHP proxy forwards requests from the browser to the Ollama API running on `http://localhost:11434/api/generate` and returns the response back to the browser.

## File Structure

- `ollama-proxy.php`: Main endpoint that handles requests
- `OllamaProxy.php`: Main class implementing the proxy functionality
- `Config.php`: Configuration file for API settings
- `TextStatistics.php`: Class for calculating readability and grade level metrics
- `test-proxy.php`: Test script to verify the implementation

## Setup

1. Make sure you have PHP installed on your server
2. Place all files in your web server's accessible directory
3. Ensure your web server is configured to serve PHP files

## Usage

The proxy accepts POST requests with JSON data containing:
- `model`: The Ollama model to use (e.g., "gemma3:1b")
- `prompt`: The prompt to send to the model (required)
- `stream`: Whether to stream the response (optional, defaults to false)
- `format`: The response format (optional, defaults to "json")
- `options`: Additional options to pass to Ollama (optional)

Example request:
```json
{
  "model": "gemma3:1b",
  "prompt": "Why is the sky blue?",
  "stream": false,
  "format": "json"
}
```

The proxy will return the same response format as the Ollama API.

## Readability and Grade Level Metrics

The proxy automatically calculates Flesch-Kincaid readability scores and grade levels for the text being analyzed. These metrics are added to the response if they're not already provided by the Ollama model:

- `readability_score`: A score between 0-100 indicating how easy the text is to read (higher is easier)
- `grade_level`: The U.S. school grade level needed to understand the text (0-20 scale)

## Configuration

You can modify settings in `Config.php`:
- OLLAMA_API_URL: The URL of your Ollama API
- DEFAULT_MODEL: The default model to use if none is specified
- REQUEST_TIMEOUT: Request timeout in seconds
- ENABLE_LOGGING: Enable or disable request logging
- LOG_FILE: Path to the log file

## CORS Configuration

The proxy includes CORS headers to allow requests from any origin:
```php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
```

For production environments, you may want to restrict the allowed origins to specific domains.