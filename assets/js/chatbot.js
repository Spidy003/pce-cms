document.addEventListener('DOMContentLoaded', () => {
    const chatToggleBtn = document.getElementById('chat-toggle');
    const chatWidget = document.getElementById('chat-widget');
    const chatCloseBtn = document.getElementById('chat-close');
    const chatSendBtn = document.getElementById('chat-send');
    const chatInput = document.getElementById('chat-input');
    const chatBody = document.getElementById('chat-body');

    // === GEMINI API INTEGRATION ===
    // Place your Gemini API Key here.
    // NOTE: For a real production app, restrict your API key to your specific domain to prevent theft!
    const GEMINI_API_KEY = "AIzaSyAyyQACP6nnJPVEKrYuapbnoBFrNqDDKdw"; 

    if (chatToggleBtn) {
        chatToggleBtn.addEventListener('click', () => {
            chatWidget.style.display = 'flex';
        });
    }

    if (chatCloseBtn) {
        chatCloseBtn.addEventListener('click', () => {
            chatWidget.style.display = 'none';
        });
    }

    if (chatSendBtn) {
        chatSendBtn.addEventListener('click', handleSend);
    }

    // Make the function global for the onkeypress event
    window.handleChatKeyPress = function (e) {
        if (e.key === 'Enter') {
            handleSend();
        }
    }

    async function handleSend() {
        if (!chatInput || !chatBody) return;
        const text = chatInput.value.trim();
        if (text === '') return;

        // Add user message
        appendMessage('USER', text, 'user-message');
        chatInput.value = '';

        // Add a waiting/typing indicator
        const loadingDiv = document.createElement('div');
        loadingDiv.className = `chat-message ai-message`;
        loadingDiv.innerHTML = `<strong>GEMINI:</strong> <span style="font-family:'JetBrains Mono'; animation: blink 1s infinite alternate;">...processing...</span>`;
        chatBody.appendChild(loadingDiv);
        chatBody.scrollTop = chatBody.scrollHeight;

        try {
            const aiText = await fetchGeminiResponse(text);
            loadingDiv.innerHTML = `<strong>GEMINI:</strong> ` + formatResponse(aiText);
        } catch (error) {
            console.error("Gemini Details:", error);
            loadingDiv.innerHTML = `<strong>SYSTEM_ERROR:</strong> ${error.message}`;
            loadingDiv.style.color = "red";
        }

        chatBody.scrollTop = chatBody.scrollHeight;
    }

    async function fetchGeminiResponse(userQuery) {
        if (!GEMINI_API_KEY || GEMINI_API_KEY === "YOUR_GEMINI_API_KEY_HERE" || GEMINI_API_KEY === "") {
            throw new Error("Missing API Key. Check line 12 of chatbot.js.");
        }

        // Using gemini-2.0-flash which is generally more stable and handles higher queue capacities
        const url = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${GEMINI_API_KEY}`;

        const payload = {
            contents: [{
                parts: [{
                    text: `You are an AI assistant for a college platform. A student is asking: "${userQuery}". Provide a helpful and smart response like Gemini. You can write applications, give career or course info, etc. Be concise and format with line breaks if it's long.`
                }]
            }]
        };

        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        // If the API threw an HTTP error, it usually provides a descriptive error payload.
        if (!response.ok) {
            const errMsg = data.error && data.error.message ? data.error.message : "Failed to fetch from Gemini API.";
            throw new Error(errMsg);
        }

        if (data.candidates && data.candidates[0].content && data.candidates[0].content.parts) {
            return data.candidates[0].content.parts[0].text;
        } else {
            throw new Error("Received anomalous payload structure from Gemini.");
        }
    }

    // Helper function to render asterisks to bold and \n to <br>
    function formatResponse(text) {
        let formatted = text.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
        formatted = formatted.replace(/\n/g, '<br>');
        return formatted;
    }

    function appendMessage(sender, text, className) {
        if (!chatBody) return;
        const msgDiv = document.createElement('div');
        msgDiv.className = `chat-message ${className}`;
        msgDiv.innerHTML = `<strong>${sender}:</strong> ${text}`;
        chatBody.appendChild(msgDiv);
        chatBody.scrollTop = chatBody.scrollHeight;
    }
});
