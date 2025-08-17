
const settings = {
    apiProvider: 'google',
    apiKey: 'YOUR_API_KEY',
    ollamaApiUrl: 'http://localhost:11434/api/generate',
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
