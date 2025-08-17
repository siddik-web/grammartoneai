
const settings = {
    apiProvider: 'google',
    apiKey: 'YOUR_API_KEY',
    ollamaApiUrl: 'api/ollama-proxy.php', // Using PHP proxy to avoid CORS issues
};

function saveSettings() {
    localStorage.setItem('settings', JSON.stringify(settings));
}

function loadSettings() {
    const savedSettings = localStorage.getItem('settings');
    if (savedSettings) {
        Object.assign(settings, JSON.parse(savedSettings));
    }
}

loadSettings();
