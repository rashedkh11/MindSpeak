import os
import numpy as np
import torch
import torch.nn as nn
import torch.nn.functional as F
import pickle
from datetime import datetime
import mysql.connector
from mysql.connector import Error
from flask import Flask, request, jsonify, session
from flask_cors import CORS
import ollama
from Material import *
os.environ["TORCH_TEXT_DISABLE_CPP_EXTENSIONS"] = "1"
from transformers import BertTokenizer
device = torch.device("cuda" if torch.cuda.is_available() else "cpu")
# Setup for Flask app
app = Flask(__name__)
CORS(app)  # Enable CORS for frontend access
app.secret_key = 'your_secret_key'  # Set your secret key for session management

# Database connection function
def get_db_connection():
    try:
        connection = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",  # Replace with your database password
            database="mindspeak1"
        )
        if connection.is_connected():
            return connection
    except Error as e:
        print("Database Error:", e)
        return None
def preprocess_text(text):
    # Initial cleaning
    text = remove_html_tags(text)
    text = remove_urls1(text)
    text = remove_urls2(text)
    text = remove_emojis(text)
    text = remove_mentions(text)
    
    # Standardization
    text = LowerCase(text)
    text = expand_contractions(text)
    text = remove_non_ascii(text)
    text = Remove_text_within_sqbrackets(text)
    
    # Remove special characters and punctuation
    text = Remove_special_characters(text)
    text = remove_punctuation(text)
    
    # Handling spaces and repeated characters
    text = Replace_multiple_spaces_with_a_single_space(text)
    text = normalize_repeated_chars(text)
    text = remove_numbers(text)
    
   
    
    return text
def tokenize_text(text, v, max_seq_len):
    # Pass the text as a string directly
    tokens = v.text_to_tensor(text, max_seq_len)
    tokens = tokens.unsqueeze(0)
    return tokens.to(DEVICE)
# Step 4: Function to predict class probabilities and the predicted class
def predict_text(text, model, v, max_seq_len):
    processed_text = preprocess_text(text)
    tokens = tokenize_text(processed_text, v, max_seq_len)
    # Ensure no gradients are calculated during prediction
    with torch.no_grad():
        output,_ = model(tokens)  # Raw logits from the model
        probabilities = F.softmax(output, dim=1).cpu().numpy()[0]  # Convert to probabilities
        predicted_class = probabilities.argmax()  # Get class index with highest probability
    return probabilities, predicted_class


#((((((((((((((((((((((((--------------------------------------- PosNegNeu_model ---------------------------------------))))))))))))))))))))))))))))
MAX_SEQ_LEN = 200 #400, 500
EMBED_DIM = 128 #256, 256
HIDDEN_DIM = 32 #128, 128
NUM_LAYERS = 2 # 4, 3
DROPOUT = 0.1 #0.2, 0.5
v = Vocabulary([])
PosNegNeu_model = LSTMClassifierBi(v,len(v.vocab),EMBED_DIM, HIDDEN_DIM, 3, NUM_LAYERS, dropout=DROPOUT).to("cuda" if torch.cuda.is_available() else "cpu")
PosNegNeu_model.to(DEVICE)
PosNegNeu_model.load_state_dict(torch.load("Models&Tokenizers\PosNegNeu_model.pth", map_location=DEVICE))
PosNegNeu_model.eval()

with open("Models&Tokenizers\PosNegNeu_model_tokenizer.pkl", "rb") as f:
    tokenizer = pickle.load(f)



#((((((((((((((((((((((((--------------------------------------- Suicide_Detection ---------------------------------------))))))))))))))))))))))))))))
EMBED_DIM = 256
HIDDEN_DIM = 64
NUM_LAYERS = 2 
DROPOUT = 0.1 
MAX_SEQ_LEN = 150

v = Vocabulary([])
Suicide_model = LSTMClassifierBi(v,len(v.vocab),EMBED_DIM, HIDDEN_DIM, 2, NUM_LAYERS, dropout=DROPOUT).to("cuda" if torch.cuda.is_available() else "cpu")
Suicide_model.to(DEVICE)
Suicide_model.load_state_dict(torch.load("Models&Tokenizers\Suicide_model.pth", map_location=DEVICE))
Suicide_model.eval()
with open("Models&Tokenizers\Suicide_model_tokenizer.pkl", "rb") as f:
    tokenizer = pickle.load(f)

#((((((((((((((((((((((((--------------------------------------- Facebook Emotions ---------------------------------------))))))))))))))))))))))))))))
EMBED_DIM = 128 #256, 256
HIDDEN_DIM = 64 #128, 128
NUM_LAYERS = 2 # 4, 3
DROPOUT = 0.1 #0.2, 0.5
MAX_SEQ_LEN = 100

v = Vocabulary([])
facebookEmo_model = LSTMClassifierBi(v,len(v.vocab),EMBED_DIM, HIDDEN_DIM, 6, NUM_LAYERS, dropout=DROPOUT).to("cuda" if torch.cuda.is_available() else "cpu")
facebookEmo_model.to(DEVICE)
facebookEmo_model.load_state_dict(torch.load(r"Models&Tokenizers\facebookEmo_model.pth", map_location=DEVICE))
facebookEmo_model.eval()

with open(r"Models&Tokenizers\facebookEmo_model_tokenizer.pkl", "rb") as f:
    tokenizer = pickle.load(f)


#((((((((((((((((((((((((--------------------------------------- Litigious Model ---------------------------------------))))))))))))))))))))))))))))
EMBED_DIM = 128 #256, 256
HIDDEN_DIM = 32 #128, 128
NUM_LAYERS = 2 # 4, 3
DROPOUT = 0.1 #0.2, 0.5
MAX_SEQ_LEN = 200

v= Vocabulary([])
litigious_model = LSTMClassifierBi(v,len(v.vocab),EMBED_DIM, HIDDEN_DIM, 4, NUM_LAYERS, dropout=DROPOUT).to("cuda" if torch.cuda.is_available() else "cpu")
litigious_model.to(DEVICE)
litigious_model.load_state_dict(torch.load("Models&Tokenizers\litigious_model.pth", map_location=DEVICE))
litigious_model.eval()

with open("Models&Tokenizers\litigious_model_tokenizer.pkl", "rb") as f:
    tokenizer = pickle.load(f)

#((((((((((((((((((((((((--------------------------------------- Depression Model ---------------------------------------))))))))))))))))))))))))))))
EMBED_DIM = 128 #256, 256
HIDDEN_DIM = 32 #128, 128
NUM_LAYERS = 1 # 4, 3
DROPOUT = 0.2 #0.2, 0.5
MAX_SEQ_LEN = 100
v= Vocabulary([])
depression_model = LSTMClassifierBi(v,len(v.vocab),EMBED_DIM, HIDDEN_DIM, 2, NUM_LAYERS, dropout=DROPOUT).to("cuda" if torch.cuda.is_available() else "cpu")
depression_model.to(DEVICE)
depression_model.load_state_dict(torch.load("Models&Tokenizers\depression_model.pth", map_location=DEVICE))
depression_model.eval()

with open("Models&Tokenizers\depression_model_tokenizer.pkl", "rb") as f:
    tokenizer = pickle.load(f)

#((((((((((((((((((((((((--------------------------------------- Main Model ---------------------------------------))))))))))))))))))))))))))))
EMBED_DIM = 256 #256, 256
HIDDEN_DIM = 256 #128, 128
NUM_LAYERS = 3 # 4, 3
DROPOUT = 0.3 #0.2, 0.5
MAX_SEQ_LEN = 500

v= Vocabulary([])
main_model = LSTMClassifierBi(v,len(v.vocab),EMBED_DIM, HIDDEN_DIM, 5, NUM_LAYERS, dropout=DROPOUT).to("cuda" if torch.cuda.is_available() else "cpu")
main_model.to(DEVICE)
main_model.load_state_dict(torch.load("Models&Tokenizers\main_model.pth", map_location=DEVICE))
main_model.eval()

with open("Models&Tokenizers\main_model_tokenizer.pkl", "rb") as f:
    tokenizer = pickle.load(f)
#((((((((((((((((((((((((--------------------------------------- Main Model 2---------------------------------------))))))))))))))))))))))))))))
EMBED_DIM = 128 #256, 256
HIDDEN_DIM = 64 #128, 128
NUM_LAYERS = 3 # 4, 3
DROPOUT = 0.2 #0.2, 0.5
MAX_SEQ_LEN = 450

v= Vocabulary([])
main2_model = LSTMClassifierBi(v,len(v.vocab),EMBED_DIM, HIDDEN_DIM, 7, NUM_LAYERS, dropout=DROPOUT).to("cuda" if torch.cuda.is_available() else "cpu")
main2_model.to(DEVICE)
main2_model.load_state_dict(torch.load("Models&Tokenizers\main2_model.pth", map_location=DEVICE))
main2_model.eval()

with open("Models&Tokenizers\main2_model_tokenizer.pkl", "rb") as f:
    tokenizer = pickle.load(f)

#((((((((((((((((((((((((---------------------------------------))))))))))))))))))))))))))))



#-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
#-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
#-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
#-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
#-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
#-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
#-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
#-----------------------------------------------------------------------------------------------------------------------------------------------------------------------
#-----------------------------------------------------------------------------------------------------------------------------------------------------------------------

@app.route("/chat", methods=["POST"])
def chat():
    try:
        data = request.get_json()
        if not data or "message" not in data or "user_id" not in data:
            return jsonify({"error": "Invalid request format"}), 400

        user_id = data["user_id"]
        user_input = data["message"].strip()

        print("-------------------------------------------------------------------------")
        print(user_id)
        print("-------------------------------------------------------------------------")

        if not user_input:
            return jsonify({"error": "Empty message received"}), 400

        # Verify if user_id exists in the users table
        conn = get_db_connection()
        if conn is None:
            return jsonify({"error": "Database connection failed"}), 500

        cursor = conn.cursor()
        cursor.execute("SELECT id FROM users WHERE id = %s", (user_id,))
        user_exists = cursor.fetchone()

        if not user_exists:
            return jsonify({"error": "User not found in the database"}), 400

        # Get the bot's response using Ollama API
        response = ollama.chat(model="llama3.2", messages=[{"role": "user", "content": user_input}])

        bot_message = response.get("message", {}).get("content", "No response")
        v =Vocabulary([])
        # Get prediction probabilities for the user's input message
        probabilities, predicted_class = predict_text(user_input, main_model,v, 1000)

        # Convert numpy.float32 to regular float for JSON compatibility
        predicted_condition = ['ADHD', 'Aspergers', 'Depression', 'OCD', 'PTSD'][predicted_class]

        # Save chat and prediction to DB
        cursor.execute(
            """
            INSERT INTO model1 (user_id, message, predicted_condition, probability, 
                                ADHD_prob, Aspergers_prob, Depression_prob, OCD_prob, PTSD_prob, timestamp) 
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """,
            (
                user_id, 
                user_input, 
                predicted_condition, 
                float(probabilities[predicted_class]),  # Store the most probable class' probability
                float(probabilities[0]),
                float(probabilities[1]),
                float(probabilities[2]),
                float(probabilities[3]),
                float(probabilities[4]),
                datetime.now()
            )
        )
        probabilities, predicted_class = predict_text(user_input, PosNegNeu_model,v, 1000)
        predicted_condition = ['negative', 'Neutral', 'positive'][predicted_class]

        cursor.execute(
            """
            INSERT INTO PosNegNeu_model (user_id, message, predicted_label, probability, 
                                Negative_prob, Neutral_prob, Positive_prob, timestamp) 
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
            """,
            (
                user_id, 
                user_input, 
                predicted_condition, 
                float(probabilities[predicted_class]),  # Store the most probable class' probability
                float(probabilities[0]),
                float(probabilities[1]),
                float(probabilities[2]),
                datetime.now()
            )
        )
        probabilities, predicted_class = predict_text(user_input, Suicide_model,v, 1000)
        predicted_condition = ['non Suicide', 'Suicide'][predicted_class]

        cursor.execute(
            """
            INSERT INTO suicide_model (user_id, message, predicted_label, probability, 
                               NonSuicide_prob , Suicide_prob, timestamp) 
            VALUES (%s, %s, %s, %s, %s, %s,%s)
            """,
            (
                user_id, 
                user_input, 
                predicted_condition, 
                float(probabilities[predicted_class]),  # Store the most probable class' probability
                float(probabilities[0]),
                float(probabilities[1]),
                
                datetime.now()
            )
        )
        probabilities, predicted_class = predict_text(user_input, Suicide_model,v, 1000)
        predicted_condition = ['Non-Depression','Depression'][predicted_class]

        cursor.execute(
            """
            INSERT INTO depression_model (user_id, message, predicted_label, probability, 
                               non_depression_prob , depression_prob, timestamp) 
            VALUES (%s, %s, %s, %s, %s, %s,%s)
            """,
            (
                user_id, 
                user_input, 
                predicted_condition, 
                float(probabilities[predicted_class]),  # Store the most probable class' probability
                float(probabilities[0]),
                float(probabilities[1]),
                
                datetime.now()
            )
        )
        probabilities, predicted_class = predict_text(user_input, facebookEmo_model,v, 1000)
        predicted_condition = ['sadness', 'joy','love','anger','fear','surprise'][predicted_class]

        cursor.execute(
            """
            INSERT INTO facebookEmo_model (user_id, message, predicted_label, probability, 
                               sadness_prob , joy_prob,love_prob,anger_prob,fear_prob,surprise_prob, timestamp) 
            VALUES (%s,%s, %s, %s, %s, %s, %s,%s,%s, %s,%s)
            """,
            (
                user_id, 
                user_input, 
                predicted_condition, 
                float(probabilities[predicted_class]),  # Store the most probable class' probability
                float(probabilities[0]),
                float(probabilities[1]),
                float(probabilities[2]),
                float(probabilities[3]),
                float(probabilities[4]),
                float(probabilities[5]),
                datetime.now()
            )
        )
        probabilities, predicted_class = predict_text(user_input, main2_model,v, 1000)
        predicted_condition = ['anxiety', 'bipolar','depression', 'normal','personality disorder','stress','suicidal'][predicted_class]

        cursor.execute(
            """
            INSERT INTO model2 (user_id, message, predicted_label, probability, 
                               anxiety_prob , bipolar_prob,depression_prob,normal_prob,personality_disorder_prob,stress_prob,suicidal_prob,timestamp) 
            VALUES (%s,%s, %s, %s, %s, %s, %s,%s,%s, %s,%s,%s)
            """,
            (
                user_id, 
                user_input, 
                predicted_condition, 
                float(probabilities[predicted_class]),  # Store the most probable class' probability
                float(probabilities[0]),
                float(probabilities[1]),
                float(probabilities[2]),
                float(probabilities[3]),
                float(probabilities[4]),
                float(probabilities[5]),
                float(probabilities[6]),

                datetime.now()
            )
        )
        conn.commit()
        cursor.close()
        conn.close()

        return jsonify({"response": bot_message})

    except Exception as e:
        print(f"Error: {str(e)}")  # Log error to console
        return jsonify({"error": f"An error occurred: {str(e)}"}), 500

# Route for chat history (Optional, if you want to fetch all previous messages)
@app.route("/chat_history", methods=["GET"])
def chat_history():
    try:
        user_id = session.get("user_id")  # Get the user_id from the session

        if not user_id:
            return jsonify({"error": "User is not logged in"}), 401  # User is not logged in

        conn = get_db_connection()
        if conn is None:
            return jsonify({"error": "Database connection failed"}), 500

        cursor = conn.cursor()
        cursor.execute(
            "SELECT user_message, bot_message, timestamp FROM user_bot_chat WHERE user_id = %s ORDER BY timestamp ASC",
            (user_id,)
        )
        chats = cursor.fetchall()
        cursor.close()
        conn.close()

        # Format chat history as a list of dictionaries
        chat_history = [
            {"user_message": chat[0], "bot_message": chat[1], "timestamp": chat[2].strftime("%Y-%m-%d %H:%M:%S")}
            for chat in chats
        ]

        return jsonify({"chat_history": chat_history})

    except Exception as e:
        return jsonify({"error": str(e)}), 1000

# Run Flask app
if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)
