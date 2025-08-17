# Grammar and Tone Checker

This is a simple web application that checks the grammar and tone of a given text. It uses the Google Gemini API or a local Ollama instance to analyze the text and provide suggestions for improvement.

## Features

-   Check grammar and spelling
-   Get suggestions for improving the tone of the text
-   Choose between Google Gemini and Ollama as the API provider
-   Simple and intuitive user interface
-   Readability scoring and grade level assessment using Flesch-Kincaid formulas

## How to Use

1.  Open the `index.html` file in your web browser.
2.  Enter the text you want to analyze in the text area.
3.  Click the "Analyze Text" button.
4.  The corrected text and tone suggestions will be displayed below the text area.

## Settings

You can configure the API provider and API key in the settings section.

-   **API Provider:** Choose between "Google" and "Ollama".
-   **API Key:** If you are using the Google Gemini API, you need to provide your API key.
-   **Ollama API URL:** If you are using Ollama, you need to provide the URL of your Ollama API endpoint.

## Ollama CORS Configuration

If you are using Ollama as your API provider, you might encounter Cross-Origin Resource Sharing (CORS) issues. This project includes a PHP OOP proxy API that can be used to avoid these issues.

### Option 1: Use the PHP OOP Proxy (Recommended)

The PHP OOP proxy included in this project (`api/ollama-proxy.php`) forwards requests to the Ollama API and handles CORS automatically. The proxy is implemented using Object-Oriented Programming principles for better maintainability and extensibility. To use it:

1. Make sure PHP is installed and configured on your web server
2. Ensure the API files are accessible through your web server:
   - `api/ollama-proxy.php` - Main endpoint
   - `api/OllamaProxy.php` - Main class implementation
   - `api/Config.php` - Configuration settings
   - `api/TextStatistics.php` - Readability and grade level calculations
3. In the application settings, set the Ollama API URL to point to the PHP proxy (e.g., `http://localhost/GrammarToneAI/api/ollama-proxy.php`)

The PHP proxy also automatically calculates Flesch-Kincaid readability scores and grade levels for the analyzed text if they're not provided by the Ollama model.

### Option 2: Configure Ollama CORS

Alternatively, you can configure your Ollama server to allow requests from your web application's origin by setting the `OLLAMA_ORIGINS` environment variable before starting the Ollama server:

```bash
OLLAMA_ORIGINS="http://localhost:8000,https://your-app-domain.com" ollama serve
```

Replace `http://localhost:8000` and `https://your-app-domain.com` with the actual origins from which your web application is served.

## API Documentation

For detailed information about the PHP OOP API implementation, see [API README](api/README.md).

## Contributing

Contributions are welcome! Please feel free to open an issue or submit a pull request.