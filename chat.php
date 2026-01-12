<?php
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $role = isset($_SESSION['roll']) ? $_SESSION['roll'] : 'User';
    $profile_image = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'assets/img/random.png';
} else {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}

// Fetch messages from the database
require_once 'db.php';  // Include your DB connection
$query = "SELECT * FROM user_bot_chat WHERE user_id = ? ORDER BY timestamp ASC";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Interface</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .chat-container {
            width: 380px;
            background: white;
            border-radius: 15px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: none;
            position: fixed;
            bottom: 80px;
            right: 20px;
            z-index: 10000;
        }
        .top-bar {
            background: #3fbbc0;
            color: white;
            padding: 15px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .chatbox-body {
            padding: 15px;
            height: 400px;
            overflow-y: auto;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
        }
        .message {
            max-width: 75%;
            padding: 12px;
            border-radius: 18px;
            margin-bottom: 10px;
            word-wrap: break-word;
        }
        .bot-message {
            background: #3fbbc0;
            color: white;
            align-self: flex-start;
        }
        .user-message {
            background: #d1d1d1;
            color: black;
            align-self: flex-end;
        }
        .chatbox-footer {
            display: flex;
            align-items: center;
            padding: 15px;
            background: rgb(235, 235, 235);
            border-top: 1px solid #ddd;
        }
        .chatbox-footer input {
            flex-grow: 1;
            border: none;
            padding: 10px;
            border-radius: 20px;
            outline: none;
            background: #b9bcbd;
        }
        .chatbox-footer button {
            background: transparent;
            border: none;
            cursor: pointer;
            margin-left: 10px;
        }
        .chat-icon {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: #0bc5ac;
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            cursor: pointer;
             z-index: 10000;
            transition: all 0.3s ease; 

        }
        .message {
            max-width: 75%;
            padding: 12px;
            border-radius: 18px;
            margin-bottom: 10px;
            word-wrap: break-word;
            position: relative;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        body {
            position: static !important; /* Override if needed */
        }
    </style>
</head>
<body>
    <div class="chat-icon" onclick="toggleChat()">💬</div>
    <div class="chat-container" id="chatContainer">
        <div class="top-bar">
            <div class="profile">
                <span>Chatbot</span>
            </div>
        </div>
        <div class="chatbox-body" id="chatbox-body">
            <!-- Messages will be injected here dynamically -->
        </div>
        <div class="chatbox-footer">
            <input type="text" id="chatInput" placeholder="Type a message..." onkeypress="sendMessage(event)">
            <button onclick="sendMessage({type: 'click'})">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <script>
        const user_id = "<?php echo $_SESSION['user_id']; ?>";

        // Function to toggle chat visibility
        function toggleChat() {
            let chatBox = document.getElementById("chatContainer");
            chatBox.style.display = chatBox.style.display === "none" ? "block" : "none";
        }

        // Fetch and display messages from PHP
        const messages = <?php echo json_encode($messages); ?>; // Inject PHP array into JS
        function loadMessages() {
    const chatBox = document.getElementById("chatbox-body");

    // Loop through the messages and display them
    messages.forEach((message) => {
        // First display the user's message
        if (message.user_message) {
            const userMessageDiv = document.createElement("div");
            userMessageDiv.classList.add("message", "user-message");
            userMessageDiv.innerText = message.user_message;  // Show user message
            chatBox.appendChild(userMessageDiv);
        }

        // Then display the bot's response
        if (message.bot_message) {
            const botMessageDiv = document.createElement("div");
            botMessageDiv.classList.add("message", "bot-message");
            botMessageDiv.innerText = message.bot_message;  // Show bot message
            chatBox.appendChild(botMessageDiv);
        }
    });

    // Scroll to the bottom of the chatbox to show the latest message
    chatBox.scrollTop = chatBox.scrollHeight;
}
        // Function to handle sending messages
        async function sendMessage(event) {
            if (event.type === "keypress" && event.key !== "Enter") return;

            let input = document.getElementById("chatInput");
            let message = input.value.trim();
            if (!message) return;

            displayMessage(message, "user");
            input.value = "";

            try {
                const botResponse = await getBotResponse(message, user_id);
                displayMessage(botResponse, "bot");
            } catch (error) {
                displayMessage("Sorry, there was an issue with the server.", "bot");
            }
        }

        // Fetch response from the bot
        async function getBotResponse(message, user_id) {
            const response = await fetch("http://localhost:5000/chat", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ message: message, user_id: user_id })
            });

            if (!response.ok) {
                throw new Error("Server error");
            }

            const data = await response.json();
            return data.response || "Sorry, I couldn't understand that.";
        }

        // Display messages in chatbox
        function displayMessage(text, sender) {
            const chatBox = document.getElementById("chatbox-body");
            const messageDiv = document.createElement("div");
            messageDiv.classList.add("message", sender === "bot" ? "bot-message" : "user-message");
            messageDiv.innerText = text;
            chatBox.appendChild(messageDiv);
            chatBox.scrollTop = chatBox.scrollHeight;
        }
         function toggleChat() {
            const chatBox = document.getElementById("chatContainer");
            if (chatBox.style.display === "none" || !chatBox.style.display) {
                chatBox.style.display = "block";
                chatBox.style.animation = "fadeIn 0.3s ease";
                document.getElementById("chatInput").focus();
            } else {
                chatBox.style.animation = "fadeOut 0.3s ease";
                setTimeout(() => {
                    chatBox.style.display = "none";
                }, 250);
            }
        }


        // Load existing messages when page loads
        loadMessages();
    </script>
</body>
</html>
