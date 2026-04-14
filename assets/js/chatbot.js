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
    const GEMINI_API_KEY = "AIzaSyDjDUDarjqpI5yqXt_lN5f0qV4XUIfR4I4"; 

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

        const contextData = `
        PCE College FAQ Database:
        1. Bonafide Certificate: Log in to the PCE Student Portal, go to "Student Services", fill request form. Collect physical copy at admin office.
        2. Railway Concession: On portal, select "Railway Concession", enter source/destination, submit.
        3. Wrong Personal Details: Submit "Profile Update Request" via CMS. For name/DOB, visit Student Section with SSC/HSC docs.
        4. Online Fee Payment: Go to "Fee Payment" module, select year, pay via UPI/Net Banking/Card.
        5. Attendance Update: Marked by faculty via AMS. If wrong, approach teacher within 3 days.
        6. Minimum Attendance: 75% min required. Less leads to terms not granted.
        7. Internal Marks: Check "Marks/Result" section after cycle.
        8. Hall Ticket: Go to "Exam" tab. Fees must be clear & attendance verified.
        9. B.Tech Admission: CAP rounds by DTE/CET. Register on CET portal, list PCE preferred.
        10. Computer Eng Cutoff: Gen category MHT-CET 93-96%, JEE Main ~73,000 rank.
        11. IT Cutoff: MHT-CET 91-93%.
        12. Direct Second Year (DSE): Yes, diploma holders apply via CAP based on diploma %.
        13. Placements: TCS, Infosys, Capgemini, Accenture, Reliance, Wipro, L&T, Jio.
        14. Packages: Avg 5 LPA, highest 15-18 LPA.
        15. Student Associations: MESA, CSI, IEEE organize workshops, hackathons, visits.
        16. Join Association: Register at campus desks during yearly drives.
        17. ASK Portal: Mentor-Mentee system for certificates, co-curricular tracking.
        18. Reset CMS Password: Click "Forgot Password", link sent to @student.mes.ac.in.
        19. Get @student Email: Apply via Google Services link on PCE website or Admin office.
        20. Tech Issues: Visit System Admin 3rd floor or email support@mes.ac.in.
        `;

        const payload = {
            contents: [{
                parts: [{
                    text: `You are the official PCE Study Buddy AI Chatbot. Answer the student's query based ONLY on the following FAQ database.
                    
                    FAQ Database:
                    ${contextData}
                    
                    Student Query: "${userQuery}"
                    
                    Instructions:
                    1. Identify the keywords.
                    2. Answer the query concisely strictly using the provided FAQ info.
                    3. If the answer is not in the database, politely say you don't have that information and advise them to contact support@mes.ac.in.
                    4. Keep it brief and professional. Do not say "According to the database".`
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
