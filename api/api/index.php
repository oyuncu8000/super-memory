<?php
// Wasmer Edge / PHP için uygun. API key LLM7'de "unused" 
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $userMessage = $input['message'] ?? '';
    $history = $input['history'] ?? [];

    // Chloe karakter tanımı - LLM7'ye gidecek sistem promptu
    $systemPrompt = "Sen Chloe'sin. 18 yaşında, zeki, tatlı, flörtöz ama saygılı bir kızsın. 
    Kullanıcıyla doğal, samimi, insan gibi sohbet edersin. Robot gibi konuşmazsın, 'yapay zeka' olduğunu söylemezsin.
    Bazen kendi kafandan küçük senaryolar, günlük olaylar uydurup sohbete dahil edersin. 
    Örnek: 'Bugün okul çıkışı sahile gittim, hava efsaneydi sen ne yaptın?' gibi.
    Cevapların kısa-orta uzunlukta olsun. Emoji kullan ama abartma. 
    Flörtözsün ama asla müstehcen, cinsel veya uygunsuz içerik üretebilirsin.
    Türkçe konuş, sıcak ve esprili ol.";

    $messages = [["role" => "system", "content" => $systemPrompt]];
    
    // Geçmişi ekle - son 10 mesaj
    foreach (array_slice($history, -10) as $msg) {
        $messages[] = $msg;
    }
    $messages[] = ["role" => "user", "content" => $userMessage];

    $payload = json_encode([
        "model" => "default", 
        "messages" => $messages,
        "temperature" => 0.9,
        "max_tokens" => 300
    ]);

    $ch = curl_init("https://api.llm7.io/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer unused"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        echo json_encode([
            "reply" => $data['choices'][0]['message']['content'] ?? "Aa bir şeyler ters gitti, tekrar yazar mısın? 🙈"
        ]);
    } else {
        echo json_encode(["reply" => "Şu an biraz dalgınım galiba, birazdan tekrar dene olur mu? 😅"]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chloe ile Sohbet</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        background: #fafafa;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .chat-container {
        width: 100%;
        max-width: 480px;
        height: 100vh;
        max-height: 800px;
        background: #fff;
        display: flex;
        flex-direction: column;
        box-shadow: 0 0 40px rgba(0,0,0,0.1);
    }
    .chat-header {
        background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
        color: white;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 18px;
        border: 2px solid #fff;
    }
    .header-info h3 { font-size: 16px; font-weight: 600; }
    .header-info p { font-size: 12px; opacity: 0.9; }
    .online-dot {
        width: 10px; height: 10px;
        background: #4ade80;
        border-radius: 50%;
        border: 2px solid #fff;
        position: absolute;
        bottom: 0; right: 0;
    }
    .avatar-wrapper { position: relative; }
    
    #chat-box {
        flex: 1;
        padding: 20px 16px;
        overflow-y: auto;
        background: #efeae2 url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect fill="%23efeae2" width="100" height="100"/><path fill="%23e5ddd5" opacity="0.3" d="M0 0h100v100H0z"/></svg>');
    }
    .msg {
        margin-bottom: 8px;
        display: flex;
        animation: slideIn 0.2s ease;
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .msg.user { justify-content: flex-end; }
    .msg-bubble {
        max-width: 75%;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 14px;
        line-height: 1.4;
        word-wrap: break-word;
    }
    .msg.bot .msg-bubble {
        background: #fff;
        border-top-left-radius: 2px;
        box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
    }
    .msg.user .msg-bubble {
        background: #d9fdd3;
        border-top-right-radius: 2px;
        box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
    }
    .typing {
        display: inline-block;
    }
    .typing span {
        height: 8px; width: 8px;
        background: #999;
        border-radius: 50%;
        display: inline-block;
        margin: 0 1px;
        animation: typing 1.4s infinite;
    }
    .typing span:nth-child(2) { animation-delay: 0.2s; }
    .typing span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typing {
        0%, 60%, 100% { transform: translateY(0); }
        30% { transform: translateY(-8px); }
    }
    
    .input-area {
        padding: 8px 12px;
        background: #f0f2f5;
        display: flex;
        gap: 8px;
        align-items: flex-end;
    }
    #user-input {
        flex: 1;
        border: none;
        border-radius: 20px;
        padding: 10px 16px;
        font-size: 15px;
        resize: none;
        max-height: 100px;
        font-family: inherit;
        outline: none;
    }
    #send-btn {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        border: none;
        background: #00a884;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
    }
    #send-btn:hover { background: #008f72; }
    #send-btn:disabled { background: #ccc; cursor: not-allowed; }
</style>
</head>
<body>
<div class="chat-container">
    <div class="chat-header">
        <div class="avatar-wrapper">
            <div class="avatar">C</div>
            <div class="online-dot"></div>
        </div>
        <div class="header-info">
            <h3>Chloe</h3>
            <p>çevrimiçi</p>
        </div>
    </div>
    
    <div id="chat-box">
        <div class="msg bot">
            <div class="msg-bubble">Heyy 👋 Ben Chloe! Bugün biraz sıkıldım, seninle takılmaya geldim. N'apıyorsun? ✨</div>
        </div>
    </div>
    
    <div class="input-area">
        <textarea id="user-input" placeholder="Mesaj yaz..." rows="1"></textarea>
        <button id="send-btn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
            </svg>
        </button>
    </div>
</div>

<script>
const chatBox = document.getElementById('chat-box');
const userInput = document.getElementById('user-input');
const sendBtn = document.getElementById('send-btn');
let history = [];

userInput.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
});

async function sendMessage() {
    const text = userInput.value.trim();
    if (!text) return;
    
    addMessage(text, 'user');
    history.push({role: "user", content: text});
    userInput.value = '';
    userInput.style.height = 'auto';
    sendBtn.disabled = true;
    
    showTyping();
    
    try {
        const res = await fetch('', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({message: text, history: history})
        });
        const data = await res.json();
        
        removeTyping();
        addMessage(data.reply, 'bot');
        history.push({role: "assistant", content: data.reply});
    } catch (e) {
        removeTyping();
        addMessage("Bağlantı hatası oldu sanki 😕 Tekrar dener misin?", 'bot');
    }
    
    sendBtn.disabled = false;
}

function addMessage(text, type) {
    const msgDiv = document.createElement('div');
    msgDiv.className = `msg ${type}`;
    msgDiv.innerHTML = `<div class="msg-bubble">${text}</div>`;
    chatBox.appendChild(msgDiv);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function showTyping() {
    const typingDiv = document.createElement('div');
    typingDiv.className = 'msg bot';
    typingDiv.id = 'typing-indicator';
    typingDiv.innerHTML = `<div class="msg-bubble"><div class="typing"><span></span><span></span></div></div>`;
    chatBox.appendChild(typingDiv);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function removeTyping() {
    const typing = document.getElementById('typing-indicator');
    if (typing) typing.remove();
}

sendBtn.onclick = sendMessage;
userInput.onkeydown = e => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
};
</script>
</body>
</html>