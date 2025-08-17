const textInput = document.getElementById("text-input");
const analyzeButton = document.getElementById("analyze-button");
const resultDiv = document.getElementById("result");
const suggestionsContainer = document.getElementById("suggestions-container");
const toneAnalysisContainer = document.getElementById(
  "tone-analysis-container"
);
const wordCountEl = document.getElementById("word-count");
const sentenceCountEl = document.getElementById("sentence-count");
const readabilityEl = document.getElementById("readability");
const gradeLevelEl = document.getElementById("grade-level");
const toneButtons = document.querySelectorAll(".tone-btn");
const settingsLink = document.getElementById("settings-link");
const settingsModal = document.getElementById("settings-modal");
const closeButton = document.querySelector(".close-button");
const apiProviderSelect = document.getElementById("api-provider");
const apiKeyInput = document.getElementById("api-key");
const ollamaApiUrlInput = document.getElementById("ollama-api-url");
const saveSettingsButton = document.getElementById("save-settings-button");
const toneImprovementsEl = document.getElementById("tone-improvements");
const apiKeyContainer = document.getElementById("api-key-container");
const ollamaSettingsContainer = document.getElementById("ollama-settings-container");

let activeTone = "neutral";

// Settings Modal
settingsLink.addEventListener(
  "click",
  () => (settingsModal.style.display = "block")
);
closeButton.addEventListener(
  "click",
  () => (settingsModal.style.display = "none")
);
window.addEventListener("click", (event) => {
  if (event.target == settingsModal) {
    settingsModal.style.display = "none";
  }
});

// Handle API provider change
apiProviderSelect.addEventListener("change", () => {
  updateSettingsVisibility();
});

// Function to update visibility of settings based on provider
function updateSettingsVisibility() {
  const provider = apiProviderSelect.value;
  
  if (provider === "google") {
    apiKeyContainer.classList.remove("hidden");
    ollamaSettingsContainer.classList.add("hidden");
  } else if (provider === "ollama") {
    apiKeyContainer.classList.add("hidden");
    ollamaSettingsContainer.classList.remove("hidden");
  }
}

// Load Settings
apiProviderSelect.value = settings.apiProvider;
apiKeyInput.value = settings.apiKey;
ollamaApiUrlInput.value = settings.ollamaApiUrl;
updateSettingsVisibility(); // Set initial visibility

// Save Settings
saveSettingsButton.addEventListener("click", () => {
  settings.apiProvider = apiProviderSelect.value;
  settings.apiKey = apiKeyInput.value;
  settings.ollamaApiUrl = ollamaApiUrlInput.value;
  saveSettings();
  alert("Settings saved!");
  settingsModal.style.display = "none";
});

// Tone Selection
toneButtons.forEach((button) => {
  button.addEventListener("click", () => {
    toneButtons.forEach((btn) => btn.classList.remove("active"));
    button.classList.add("active");
    activeTone = button.dataset.tone;
  });
});

// Analyze Text
analyzeButton.addEventListener("click", () => {
  const text = textInput.value;
  if (!text) {
    resultDiv.innerHTML =
      '<p class="error">Please enter some text to analyze.</p>';
    return;
  }

  resultDiv.innerHTML =
    '<div class="loading"><div class="spinner"></div><p>Analyzing...</p></div>';
  suggestionsContainer.style.display = "none";
  toneAnalysisContainer.style.display = "none";

  if (settings.apiProvider === "google") {
    analyzeWithGoogle(text, activeTone);
  } else if (settings.apiProvider === "ollama") {
    analyzeWithOllama(text, activeTone);
  }
});

function getPrompt(text, tone) {
  return `Return a JSON object with five keys: "corrected_text", "tone_suggestions", "grammar_suggestions", "readability_score" (Flesch-Kincaid score), and "grade_level" (Flesch-Kincaid grade level).
            The "corrected_text" key should contain the corrected version of the following text, adjusted for a ${tone} tone.
            The "tone_suggestions" key should contain an array of objects, where each object has "original" and "improved" keys, showing how the tone was adjusted.
            The "grammar_suggestions" key should contain an array of objects, where each object has "original" and "improved" keys, showing the grammar corrections made.
            The "readability_score" should be a numeric Flesch-Kincaid readability score (0-100, where higher is more readable).
            The "grade_level" should be a numeric Flesch-Kincaid grade level (0-20, where 0 is kindergarten and 15 is college level).
            Text: ${text}`;
}

function analyzeWithGoogle(text, tone) {
  const apiKey = settings.apiKey;
  const url = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${apiKey}`;

  const data = {
    contents: [
      {
        parts: [
          {
            text: getPrompt(text, tone),
          },
        ],
      },
    ],
  };

  fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((data) => {
      const rawResponse = data.candidates[0].content.parts[0].text;
      const jsonResponse = rawResponse.replace(/```json\n(.*?)\n```/s, "$1");
      const response = JSON.parse(jsonResponse);
      displayResult(
        text,
        response.corrected_text,
        response.tone_suggestions,
        response.grammar_suggestions,
        response.readability_score,
        response.grade_level
      );
    })
    .catch((error) => {
      console.error("Error:", error);
      resultDiv.innerHTML =
        '<p class="error">An error occurred while analyzing the text.</p>';
    });
}

function analyzeWithOllama(text, tone) {
  // Use our PHP proxy instead of calling Ollama directly
  const url = "api/ollama-proxy.php";

  // Prepare data without an empty options array
  const data = {
    model: "gemma3:1b",
    prompt: getPrompt(text, tone),
    stream: false,
    format: "json"
    // Note: We're intentionally not sending an empty options array
    // as it causes issues with Ollama's API
  };

  fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((data) => {
      // If we get an error from our proxy, handle it
      if (data.error) {
        throw new Error(data.error);
      }
      
      // Parse the response from Ollama
      const response = JSON.parse(data.response);
      displayResult(
        text,
        response.corrected_text,
        response.tone_suggestions,
        response.grammar_suggestions,
        response.readability_score,
        response.grade_level
      );
    })
    .catch((error) => {
      console.error("Error:", error);
      resultDiv.innerHTML =
        '<p class="error">An error occurred while analyzing the text: ' + error.message + '</p>';
    });
}

function displayResult(
  originalText,
  correctedText,
  toneSuggestions,
  grammarSuggestions,
  readabilityScore,
  gradeLevel
) {
  resultDiv.innerHTML = correctedText;

  let suggestionsHTML = "";
  let toneImprovementsHTML = "";

  if (grammarSuggestions && grammarSuggestions.length > 0) {
    suggestionsHTML += '<div class="suggestion-item">';
    suggestionsHTML +=
      '<div class="suggestion-icon grammar-icon"><i class="fas fa-exclamation-circle"></i></div>';
    suggestionsHTML += '<div class="suggestion-content">';
    suggestionsHTML += `<div class="suggestion-title">Grammar Correction</div>`;
    suggestionsHTML += `<div class="suggestion-desc">We found ${grammarSuggestions.length} grammatical errors in your text</div>`;
    suggestionsHTML += '<div class="suggestion-text">';
    grammarSuggestions.forEach((suggestion) => {
      suggestionsHTML += `<div class="improvement-item"><span class="original-text">${suggestion.original}</span> <span class="improvement-arrow"></span> <span class="improved-text">${suggestion.improved}</span></div>`;
    });
    suggestionsHTML += "</div></div></div>";
  }

  if (toneSuggestions && toneSuggestions.length > 0) {
    suggestionsHTML += '<div class="suggestion-item">';
    suggestionsHTML +=
      '<div class="suggestion-icon tone-icon"><i class="fas fa-comment-alt"></i></div>';
    suggestionsHTML += '<div class="suggestion-content">';
    suggestionsHTML += `<div class="suggestion-title">Tone Improvement</div>`;
    suggestionsHTML += `<div class="suggestion-desc">Enhanced professionalism while maintaining approachability</div>`;
    suggestionsHTML += '<div class="suggestion-text">';
    toneSuggestions.forEach((suggestion) => {
      suggestionsHTML += `<div class="improvement-item"><span class="original-text">"${suggestion.original}"</span> <span class="improvement-arrow"></span> <span class="improved-text">"${suggestion.improved}"</span></div>`;
      toneImprovementsHTML += `<div class="improvement-item"><i class="fas fa-check-circle" style="color: var(--success);"></i> Replaced "${suggestion.original}" with "${suggestion.improved}"</div>`;
    });
    suggestionsHTML += "</div></div></div>";
  }

  if (suggestionsHTML) {
    suggestionsContainer.innerHTML = suggestionsHTML;
    suggestionsContainer.style.display = "block";
    toneAnalysisContainer.style.display = "block";
    toneImprovementsEl.innerHTML = toneImprovementsHTML;
  } else {
    suggestionsContainer.style.display = "none";
    toneAnalysisContainer.style.display = "none";
  }

  // Update stats
  wordCountEl.textContent = correctedText
    .split(/\s+/)
    .filter((word) => word.length > 0).length;
  sentenceCountEl.textContent = correctedText
    .split(/[.!?]+/)
    .filter((sentence) => sentence.length > 0).length;
  readabilityEl.textContent =
    readabilityScore !== undefined ? readabilityScore.toFixed(1) : "N/A";
  gradeLevelEl.textContent =
    gradeLevel !== undefined ? gradeLevel.toFixed(1) : "N/A";
}

textInput.addEventListener("input", () => {
  const text = textInput.value;
  wordCountEl.textContent = text
    .split(/\s+/)
    .filter((word) => word.length > 0).length;
  sentenceCountEl.textContent = text
    .split(/[.!?]+/)
    .filter((sentence) => sentence.length > 0).length;
});
