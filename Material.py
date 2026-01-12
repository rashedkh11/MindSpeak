import torch
import torch.nn as nn
from torch.utils.data import Dataset, DataLoader
from torchinfo import summary
import numpy as np
import os
import matplotlib.pyplot as plt
import seaborn as sns
from sklearn.model_selection import StratifiedKFold
from sklearn.metrics import confusion_matrix
os.environ["TORCH_TEXT_DISABLE_CPP_EXTENSIONS"] = "1"
from sklearn.metrics import classification_report
from transformers import BertTokenizer
import torch.optim as optim
import re
from nltk.corpus import stopwords
from nltk.stem import WordNetLemmatizer
from nltk.stem import PorterStemmer
from datasets import load_dataset
import torch.nn.functional as F
import contractions
from torchinfo import summary
import torch.nn.functional as F
import pickle
from transformers import BertTokenizer
import re
import pickle
import torch
import pandas as pd
import numpy as np
import os
import re
import contractions
import string
import nltk
from nltk.corpus import stopwords
from nltk.stem import WordNetLemmatizer
from nltk.stem import PorterStemmer
from datasets import load_dataset
import torch.nn.functional as F
stop_words = set(stopwords.words('english'))
lemmatizer = WordNetLemmatizer()
ps = PorterStemmer()
SEED = 42
np.random.seed(SEED)
torch.manual_seed(SEED)
if torch.cuda.is_available():
    torch.cuda.manual_seed(SEED)
DEVICE = torch.device("cuda" if torch.cuda.is_available() else "cpu")
def remove_html_tags(text):
    return re.sub(r'<.*?>', '', text)
def remove_urls1(text) :
    return re.sub(r'http\S+', '', text)
def remove_urls2(text) :
    return re.sub(r'www.\S+', '', text) 
def remove_emojis(text):
        emoji_pattern = re.compile("["
                                    u"\U0001F600-\U0001F64F"  # emoticons
                                    u"\U0001F300-\U0001F5FF"  # symbols & pictographs
                                    u"\U0001F680-\U0001F6FF"  # transport & map symbols
                                    u"\U0001F1E0-\U0001F1FF"  # flags (iOS)
                                    u"\U00002500-\U00002BEF"  # chinese char
                                    u"\U00002702-\U000027B0"
                                    u"\U00002702-\U000027B0"
                                    u"\U000024C2-\U0001F251"
                                    u"\U0001f926-\U0001f937"
                                    u"\U00010000-\U0010ffff"
                                    u"\u2640-\u2642"
                                    u"\u2600-\u2B55"
                                    u"\u200d"
                                    u"\u23cf"
                                    u"\u23e9"
                                    u"\u231a"
                                    u"\ufe0f"  # dingbats
                                    u"\u3030"
                                    "]+", flags=re.UNICODE)
        return emoji_pattern.sub(r'', text)
def remove_mentions(text):
    return re.sub(r'@[A-Za-z0-9_]+', '', text)
def LowerCase(text):
    return text.lower()
def expand_contractions(text):
    return contractions.fix(text)
def remove_non_ascii(text):
    return text.encode('ascii', 'ignore').decode('utf-8', 'ignore')
def Remove_text_within_sqbrackets (text) :
    return re.sub(r'\[.*?\]', ' ', text)
def Remove_special_characters (text) :
    return re.sub(r'[()!?]', ' ', text)
def remove_punctuation(text):
    return text.translate(str.maketrans('', '', string.punctuation))
def Replace_multiple_spaces_with_a_single_space (text):
    return re.sub(r'\s+', ' ', text).strip()
def normalize_repeated_chars(text):
    return re.sub(r'(.)\1{2,}', r'\1\1', text)
def remove_numbers(text):
    return re.sub(r'\d+', '', text)
def remove_stopwords(text):
    return ' '.join([word for word in text.split() if word.lower() not in stop_words])
def lemmatize_text(text):
    return ' '.join([lemmatizer.lemmatize(word) for word in text.split()])
def stem_text(text):
    return ' '.join([ps.stem(word) for word in text.split()])
class Vocabulary:
    def __init__(self, train_text):
        self.train_text = train_text
        self.tokenizer = BertTokenizer.from_pretrained("bert-base-uncased")
        self.vocab = self.tokenizer.vocab  
        self.pad_token = self.tokenizer.pad_token_id
        self.unk_token = self.tokenizer.unk_token_id
        
    def text_to_tensor(self, text, max_seq_len):
        encoding = self.tokenizer.encode_plus(
            text,
            max_length=max_seq_len,
            padding="max_length",
            truncation=True,
            return_tensors="pt"
        )
        tensor = encoding["input_ids"].squeeze(0)
        return tensor

class TextDataset(Dataset):
    def __init__(self, texts, labels):
        self.texts = texts
        self.labels = labels

    def __len__(self):
        return len(self.texts)

    def __getitem__(self, idx):
        return self.texts[idx], self.labels[idx]

class Attention(nn.Module):
    def __init__(self, hidden_dim):
        super(Attention, self).__init__()
        self.attn = nn.Linear(hidden_dim, 1)
        self.softmax = nn.Softmax(dim=1)
    def forward(self, lstm_output):
        attention_scores = self.attn(lstm_output)
        attention_weights = self.softmax(attention_scores)
        context_vector = torch.sum(attention_weights * lstm_output, dim=1)
        return context_vector, attention_weights

class LSTMClassifierBi(nn.Module):
    def __init__(self, vocab, vocab_size, embed_dim, hidden_dim, output_dim, num_layers, dropout):
        super(LSTMClassifierBi, self).__init__()
        self.droprate=dropout
        self.embedding = nn.Embedding(vocab_size, embed_dim, padding_idx=vocab.pad_token)
        self.lstm = nn.LSTM(embed_dim, hidden_dim, num_layers=num_layers, batch_first=True, dropout=dropout, bidirectional=True)
        self.attention = Attention(hidden_dim * 2)  
        self.fc = nn.Linear(hidden_dim * 2, output_dim)
        if dropout > 0.0:
            self.dropout = nn.Dropout(dropout)
    def forward(self, x):
        x = self.embedding(x)
        lstm_out, _ = self.lstm(x)
        context_vector, attention_weights = self.attention(lstm_out)
        x = context_vector 
        if self.droprate > 0.0:
            x = self.dropout(context_vector)
        x = self.fc(x)
        return x, attention_weights
    def summary(self, input_size, dtype=torch.long):
        example_input = torch.zeros(input_size, dtype=dtype, device=DEVICE)
        model_summary = summary(self.to(DEVICE), input_data=example_input, device=str(DEVICE))
        return model_summary

class trochTrainer:
    def __init__(self, model, train_loader, val_loader, criterion, optimizer, esp=3, rlp=2, epochs = 50):
        self.model = model
        self.train_loader = train_loader
        self.val_loader = val_loader
        self.criterion = criterion
        self.optimizer = optimizer
        self.epochs = epochs
        
        self.early_stopping_counter = 0
        self.best_val_loss = np.inf
        self.early_stopping_patience = esp
        self.reduce_lr_patience = rlp
        self.reduce_lr_factor = 0.5
        
        self.train_losses = []
        self.val_losses = []
        self.train_accuracies = []
        self.val_accuracies = []
    def train_model(self):
        self.model.to(DEVICE)
        for epoch in range(self.epochs):
            # Training
            self.model.train()
            train_loss, correct, total = 0, 0, 0
            
            for texts,labels in self.train_loader:
                texts, labels = texts.to(DEVICE), labels.to(DEVICE)
                self.optimizer.zero_grad()
                outputs, attention = self.model(texts)
                loss = self.criterion(outputs, labels)
                loss.backward()
                self.optimizer.step()
                
                train_loss += loss.item()
                _, predicted = torch.max(outputs, 1)
                correct += (predicted == labels).sum().item()
                total += labels.size(0)
                torch.cuda.empty_cache()
            train_acc = correct / total *100
            train_loss /= len(self.train_loader)
            self.train_losses.append(train_loss)
            self.train_accuracies.append(train_acc)
            # Validation
            val_loss, val_acc = self.evaluate()
            self.val_losses.append(val_loss)
            self.val_accuracies.append(val_acc)
            print(f"Epoch: [{epoch+1}/{self.epochs}]|Train Loss: {train_loss:.4f}, Train Accuracy: {train_acc:.4f}% | Validation Loss: {val_loss:.4f}, Validation Accuracy: {val_acc:.4f}%")
            # Early stopping and learning rate reduction
            if val_loss < self.best_val_loss:
                self.best_val_loss = val_loss
                self.early_stopping_counter = 0
            else :
                self.early_stopping_counter += 1
            if self.early_stopping_counter >= self.early_stopping_patience:
                print("Early stopping triggered!")
                break
            if self.early_stopping_counter >= self.reduce_lr_patience:
                print("Reducing learning rate...")
                for param_group in self.optimizer.param_groups:
                    param_group['lr'] *= self.reduce_lr_factor
    def evaluate(self):
        self.model.eval()
        val_loss, correct, total = 0, 0, 0
        with torch.no_grad():
            for texts, labels in self.val_loader:
                texts, labels = texts.to(DEVICE), labels.to(DEVICE)
                outputs, attention = self.model(texts)
                loss = self.criterion(outputs, labels)
                val_loss += loss.item()
                _, predicted = torch.max(outputs, 1)
                correct += (predicted == labels).sum().item()
                total += labels.size(0)
        val_acc = correct / total *100
        val_loss /= len(self.val_loader)
        return val_loss, val_acc
    def predict(self, loader):
        self.model.eval()
        predictions = []
        with torch.no_grad():
            for batch, _ in loader:
                batch = batch.to(DEVICE)
                outputs, _ = self.model(batch)
                preds = torch.argmax(outputs, dim=1)
                predictions.append(preds.cpu().numpy())
        return np.concatenate(predictions)
    def classification_report(self, X_test, y_test, label_names):
        y_pred = self.predict(X_test)
        y_true = y_test.cpu().numpy()
        reprot = classification_report(y_true, y_pred, target_names=label_names, zero_division=0, output_dict=True)
        return reprot['accuracy']
    def train_on_all_dataset(self, name, full_loader):
        self.model.to(DEVICE)
        print("Training on the entire dataset...")
        self.full_train_losses = list()
        self.full_train_accuracies = list()
        best_epoch_loss = np.inf
        for epoch in range(self.epochs):
            self.model.train()
            train_loss, correct, total = 0, 0, 0
            
            for texts, labels in full_loader:
                texts, labels = texts.to(DEVICE), labels.to(DEVICE)
                self.optimizer.zero_grad()
                outputs, attention = self.model(texts)
                loss = self.criterion(outputs, labels)
                loss.backward()
                self.optimizer.step()
                
                train_loss += loss.item()
                _, predicted = torch.max(outputs, 1)
                correct += (predicted == labels).sum().item()
                total += labels.size(0)
                torch.cuda.empty_cache()
            
            epoch_loss = train_loss / len(full_loader)
            epoch_acc = correct / total * 100
            self.full_train_losses.append(epoch_loss)
            self.full_train_accuracies.append(epoch_acc)
            
            print(f"Epoch: [{epoch+1}/{self.epochs}] | Loss: {epoch_loss:.4f}, Accuracy: {epoch_acc:.4f}%")
                        # Early stopping and learning rate reduction
            if epoch_loss < best_epoch_loss:
                best_epoch_loss = epoch_loss
                self.early_stopping_counter = 0
            else :
                self.early_stopping_counter += 1
            if self.early_stopping_counter >= self.early_stopping_patience:
                print("Early stopping triggered!")
                break
            if self.early_stopping_counter >= self.reduce_lr_patience:
                print("Reducing learning rate...")
                for param_group in self.optimizer.param_groups:
                    param_group['lr'] *= self.reduce_lr_factor

        return {f'{name}':(self.full_train_losses, self.full_train_accuracies)}
    @staticmethod
    def plot_metrics(train_losses, train_accuracies, val_losses, val_accuracies):
        epochs_range = range(1, len(train_losses) + 1)
        plt.figure(figsize=(12, 5))
        plt.subplot(1, 2, 1)
        plt.plot(epochs_range, train_losses, label='Train Loss')
        plt.plot(epochs_range, val_losses, label='Validation Loss')
        plt.xlabel('Epoch')
        plt.ylabel('Loss')
        plt.title('Train vs. Validation Loss')
        plt.legend()
        plt.subplot(1, 2, 2)
        plt.plot(epochs_range, train_accuracies, label='Train Accuracy')
        plt.plot(epochs_range, val_accuracies, label='Validation Accuracy')
        plt.xlabel('Epoch')
        plt.ylabel('Accuracy (%)')
        plt.title('Train vs. Validation Accuracy')
        plt.legend()
        plt.tight_layout()
        plt.show()

class MainWihtKFolds:
    def __init__(self,k ):
        self.skf = StratifiedKFold(n_splits=k, shuffle=True, random_state=SEED)
        self.foldsAcc = dict()
        self.metrics = dict()
        self.summaries = dict()
        self.foldsAccuList = list()
        self.Statistics = list()
        self.modelinfos = list()
        self.confmtx = list()
        self.K=k
        self.trainer = None
    def SplitAndTrain(self, df, xname, yname, max_seq_len, bs, modelParam, trainerparam):
        EMBED_DIM, HIDDEN_DIM, CLASSES, NUM_LAYERS, DROPOUT = modelParam
        EPOCHS, LR, label_names, ESP, RLP = trainerparam
        for idx,(trainIdx,testIdx) in enumerate(self.skf.split(df, df[f'{yname}'])):
            train_df = df.iloc[trainIdx].copy()
            test_df = df.iloc[testIdx].copy()
            train_texts, train_labels = train_df[f'{xname}'], train_df[f'{yname}']
            val_texts, val_labels = test_df[f'{xname}'], test_df[f'{yname}']
            
            v = Vocabulary(train_text = train_texts)
            
            train_sequences = [v.text_to_tensor(text, max_seq_len) for text in train_texts]
            val_sequences = [v.text_to_tensor(text, max_seq_len) for text in val_texts]
            train_sequences = torch.stack(train_sequences)
            val_sequences = torch.stack(val_sequences)
            train_labels = torch.tensor(train_labels.values, dtype=torch.long)
            val_labels = torch.tensor(val_labels.values, dtype=torch.long)
            train_dataset = TextDataset(train_sequences, train_labels)
            val_dataset = TextDataset(val_sequences, val_labels)
            train_loader = DataLoader(train_dataset, batch_size=bs, shuffle=True)
            val_loader = DataLoader(val_dataset, batch_size=bs, shuffle=False)
            
            
            model = LSTMClassifierBi(v, len(v.vocab), EMBED_DIM, HIDDEN_DIM, CLASSES, NUM_LAYERS, DROPOUT).to(DEVICE)
            model_before = model.summary((bs, max_seq_len))
            trainer = trochTrainer(
                model= model,
                train_loader= train_loader,
                val_loader= val_loader,
                criterion= nn.CrossEntropyLoss(),
                optimizer= optim.Adam(model.parameters(), lr= LR),
                esp= ESP,
                rlp= RLP,
                epochs= EPOCHS
            )
            trainer.train_model()
            acc = trainer.classification_report(val_loader, val_labels, label_names)
            print(f"acc of fold [{idx+1}] : {acc}")
            self.foldsAccuList.append(acc)
            self.Statistics.append((trainer.train_losses, trainer.train_accuracies, trainer.val_losses, trainer.val_accuracies))
            model_after = model.summary((bs, max_seq_len))
            self.modelinfos.append((model_before, model_after))
            
            y_pred = trainer.predict(val_loader)
            y_true = val_labels.cpu().numpy()
            cm = confusion_matrix(y_true, y_pred)
            self.confmtx.append(cm)
        
        self.foldsAcc[f'{self.K}-folds'] = self.foldsAccuList
        self.metrics[f'{self.K}-folds'] = self.Statistics
        self.summaries[f'{self.K}-folds'] = self.modelinfos
        self.trainer = trainer
    def get_AccLoss(self, name, fold):
        train_losses, train_accuracies, val_losses, val_accuracies = self.metrics[name][fold]
        
        epochs_range = range(1, len(train_losses) + 1)
        plt.figure(figsize=(12, 5))
        plt.subplot(1, 2, 1)
        plt.plot(epochs_range, train_losses, label='Train Loss')
        plt.plot(epochs_range, val_losses, label='Validation Loss')
        plt.xlabel('Epoch')
        plt.ylabel('Loss')
        plt.title('Train vs. Validation Loss')
        plt.legend()

        plt.subplot(1, 2, 2)
        plt.plot(epochs_range, train_accuracies, label='Train Accuracy')
        plt.plot(epochs_range, val_accuracies, label='Validation Accuracy')
        plt.xlabel('Epoch')
        plt.ylabel('Accuracy (%)')
        plt.title('Train vs. Validation Accuracy')
        plt.legend()

        plt.tight_layout()
        plt.show()
    def get_Summaries(self, fold):
        return self.summaries[fold][0]
    def plot_training_history(self, fold, title_prefix = ''):
        num_rows = len(self.metrics[fold])
        fig, axes = plt.subplots(nrows=num_rows, ncols=2, figsize=(12, 4 * num_rows))
        # Ensure axes are always a 2D list for consistency
        if num_rows == 1:
            axes = [axes]
        for i, (train_losses, train_accuracies, val_losses, val_accuracies) in enumerate(self.metrics[fold]):
            ax_loss = axes[i][0]
            ax_loss.plot(train_losses, label='Train Loss', marker='o')
            ax_loss.plot(val_losses, label='Val Loss', marker='o')
            ax_loss.set_title(f'{title_prefix} | Fold {i+1} - Loss')
            ax_loss.set_xlabel('Epoch')
            ax_loss.set_ylabel('Loss')
            ax_loss.legend()

            ax_acc = axes[i][1]
            ax_acc.plot(train_accuracies, label='Train Accuracy', marker='o')
            ax_acc.plot(val_accuracies, label='Val Accuracy', marker='o')
            ax_acc.set_title(f'{title_prefix} | Fold {i+1} - Accuracy')
            ax_acc.set_xlabel('Epoch')
            ax_acc.set_ylabel('Accuracy')
            ax_acc.legend()

        plt.tight_layout()
        plt.show()
    def plot_full_train(self, name, Statistics):
        full_train_losses, full_train_accuracies = Statistics[name]
        epochs_range = range(1, len(full_train_losses) + 1)
        plt.figure(figsize=(12, 5))
        plt.subplot(1, 2, 1)
        plt.plot(epochs_range, full_train_losses, label='Train Loss')
        plt.xlabel('Epoch')
        plt.ylabel('Loss')
        plt.title('Train Loss')
        plt.legend()
        plt.subplot(1, 2, 2)
        plt.plot(epochs_range, full_train_accuracies, label='Train Acc')
        plt.xlabel('Epoch')
        plt.ylabel('Acc')
        plt.title('Train Accuracy')
        plt.legend()
        plt.tight_layout()
        plt.show()
    def plot_confusion_matrices(self, label_names=None):
        num_matrices = len(self.confmtx)
        # Determine grid dimensions (2 columns by default)
        cols = 2
        rows = (num_matrices + 1) // cols

        plt.figure(figsize=(cols * 6, rows * 5))
        
        for idx, cm in enumerate(self.confmtx):
            plt.subplot(rows, cols, idx + 1)
            sns.heatmap(cm, annot=True, fmt="d", cmap="Blues",
                        xticklabels=label_names, yticklabels=label_names)
            plt.title(f"Confusion Matrix - Fold {idx + 1}")
            plt.xlabel("Predicted Label")
            plt.ylabel("True Label")
        
        plt.tight_layout()
        plt.show()
