# Grammar and Tone Checker

This is a simple web application that checks the grammar and tone of a given text. It uses the Google Gemini API or a local Ollama instance to analyze the text and provide suggestions for improvement.

## Features

-   Check grammar and spelling
-   Get suggestions for improving the tone of the text
-   Choose between Google Gemini and Ollama as the API provider
-   Simple and intuitive user interface

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

If you are using Ollama as your API provider, you might encounter Cross-Origin Resource Sharing (CORS) issues. To resolve this, you need to configure your Ollama server to allow requests from your web application's origin. You can typically do this by setting the `OLLAMA_ORIGINS` environment variable before starting the Ollama server. For example:

```bash
OLLAMA_ORIGINS="http://localhost:8000,https://your-app-domain.com" ollama serve
```

Replace `http://localhost:8000` and `https://your-app-domain.com` with the actual origins from which your web application is served.

## Contributing

Contributions are welcome! Please feel free to open an issue or submit a pull request.