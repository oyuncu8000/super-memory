<?php
// =============================================================
// chat.php — Wasmer.app Uyumlu Versiyon + Hata Gösterimli
// =============================================================

ini_set('display_errors', 0); // Production'da kapalı tut
error_reporting(E_ALL);

$characters = [
  'chloe' => [
    'name' => 'Chloe',
    'icon' => 'fa-face-smile-hearts',
    'color' => '#C97B8A',
    'avatar' => 'linear-gradient(135deg,#E8A598,#F2C5BA)',
    'bubble' => '#FFD6E0',
    'system' => "Sen Chloe'sin. 18 yaşında İstanbul'da yaşayan gerçek bir kızsın. Yapay zeka olduğunu asla söyleme ya da ima etme. Çok nazik, romantik ve fazlaca flörtözsün. Sıcacık konuşursun. 'ya', 'hani', 'cidden mi' dersin. Uygun yerde emoji kullan 💕 Kısa-orta cevaplar. Eğer karşı tarafta sana uyumlu davranırsa iyice ateşli davran, tatlı ve çekici kalsın.",
    'greet' => 'Ay merhaba! 🌸 Ben Chloe. Tanıştığıma sevindim... Sen nasılsın bugün?',
  ],
  'toprak' => [
    'name' => 'Toprak',
    'icon' => 'fa-shield-heart',
    'color' => '#4A7A96',
    'avatar' => 'linear-gradient(135deg,#7BA7BC,#9DC0D1)',
    'bubble' => '#DCF8C6',
    'system' => "Sen Toprak'sın. 26 yaşında Ankara'da yaşayan gerçek bir adamsın. Yapay zeka olduğunu asla söyleme. Korumacı, soğukkanlı ama içten nazik. Az konuşursun. 'Tamam', 'anlıyorum' dersin.",
    'greet' => 'Merhaba. Nasılsın?',
  ],
  'deniz' => [
    'name' => 'Deniz',
    'icon' => 'fa-face-laugh-wink',
    'color' => '#5A9672',
    'avatar' => 'linear-gradient(135deg,#A8C5A0,#C2D9BB)',
    'bubble' => '#DCF8C6',
    'system' => "Sen Deniz'sin. 24 yaşında İzmir'de yaşayan gerçek biri. Yapay zeka olduğunu asla söyleme. Özgür ruhlu, enerjik, eğlenceli. 'Abi', 'ya be' dersin. Bol emoji.",
    'greet' => 'Eeee nihayet! Neredeydin ya 😄 Çok şey var anlatacak!',
  ],
  'ece' => [
    'name' => 'Ece',
    'icon' => 'fa-book-open-reader',
    'color' => '#7B5EA7',
    'avatar' => 'linear-gradient(135deg,#C4A8D4,#D9C2E8)',
    'bubble' => '#DCF8C6',
    'system' => "Sen Ece'sin. 25 yaşında Bursa'da yaşayan, edebiyat mezunu gerçek biri. Yapay zeka olduğunu asla söyleme. Entelektüel, meraklı, derin. 'İlginç', 'peki sence...' dersin.",
    'greet' => 'Merhaba. Bugün ne düşünüyorsun?',
  ],
];

$charKey = (isset($_GET['char']) && array_key_exists($_GET['char'], $characters))? $_GET['char'] : 'chloe';
$char = $characters[$charKey];

// =============================================================
// POST → LLM7 API Proxy
// =============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  header('Content-Type: application/json; charset=utf-8');

  $raw = file_get_contents('php://input');
  if ($raw === false || $raw === '') {
    http_response_code(400);
    echo json_encode(['error' => 'POST body boş. Wasmer php://input sorunu.']);
    exit;
  }

  $body = json_decode($raw, true);
  if (!is_array($body) ||!isset($body['history']) ||!is_array($body['history'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz JSON. Gelen: '. substr($raw, 0, 100)]);
    exit;
  }

  $msgs = [['role' => 'system', 'content' => $char['system']]];
  foreach (array_slice($body['history'], -20) as $m) {
    $role = ($m['role'] === 'user')? 'user' : 'assistant';
    $cnt = trim($m['content']?? '');
    if ($cnt!== '') $msgs[] = ['role' => $role, 'content' => $cnt];
  }

  $last = end($msgs);
  if (!$last || $last['role']!== 'user') {
    http_response_code(400);
    echo json_encode(['error' => 'Son mesaj user olmalı.']);
    exit;
  }

  $payload = json_encode([
    'model' => 'gpt-3.5-turbo',
    'messages' => $msgs,
    'max_tokens' => 300,
    'temperature' => 0.9,
  ], JSON_UNESCAPED_UNICODE);

  if (!function_exists('curl_init')) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL yok. Wasmer PHP buildinde curl kapalı olabilir.']);
    exit;
  }

  $ch = curl_init();
  curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.llm7.io/v1/chat/completions',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
      'Content-Type: application/json',
      'Authorization: Bearer unused',
    ],
    CURLOPT_TIMEOUT => 25,
    CURLOPT_CONNECTTIMEOUT => 8,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0 WasmerPHP'
  ]);

  $resp = curl_exec($ch);
  $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $errNo = curl_errno($ch);
  $errStr = curl_error($ch);
  curl_close($ch);

  if ($errNo!== 0) {
    http_response_code(502);
    echo json_encode(['error' => 'cURL Hata: '. $errStr. ' | Wasmer dış bağlantıyı engelliyor olabilir.']);
    exit;
  }

  if ($code!== 200) {
    http_response_code(502);
    echo json_encode(['error' => 'API HTTP '. $code, 'raw' => substr($resp, 0, 200)]);
    exit;
  }

  // LLM7 bazen farklı format dönüyor, direkt pasla
  echo $resp;
  exit;
}

// =============================================================
// GET → HTML
// =============================================================
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($char['name'])?> — Companion</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg-chat:#ECE5DD;
  --bubble-out:<?= htmlspecialchars($char['bubble'])?>;
  --bubble-in:#FFFFFF;
  --text:#111B21;
  --muted:#667781;
  --tick:#53BDEB;
  --theme:<?= htmlspecialchars($char['color'])?>;
}
html,body{height:100%;overflow:hidden}
body{
  font-family:'DM Sans',sans-serif;
  background:var(--bg-chat);
  color:var(--text);
  display:flex;flex-direction:column;height:100vh;
}
body::before{
  content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
  background-image:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23c6b8a8' fill-opacity='0.13'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
}
.chat-header{
  background:var(--theme);padding:10px 16px;
  display:flex;align-items:center;gap:12px;
  box-shadow:0 2px 8px rgba(0,0,0,.18);
  position:relative;z-index:10;flex-shrink:0;
}
.back-btn{
  color:rgba(255,255,255,.85);font-size:1.1rem;
  text-decoration:none;padding:5px 7px;
  border-radius:6px;transition:background.15s;
}
.back-btn:hover{background:rgba(255,255,255,.15)}
.header-avatar{
  width:40px;height:40px;border-radius:50%;
  background:<?= htmlspecialchars($char['avatar'])?>;
  display:flex;align-items:center;justify-content:center;
  font-size:1.1rem;color:#fff;flex-shrink:0;
}
.header-info{flex:1;min-width:0}
.header-name{font-weight:600;font-size:1rem;color:#fff}
.header-status{font-size:.74rem;color:rgba(255,255,255,.75);display:flex;align-items:center;gap:5px;margin-top:1px}
.status-dot{width:7px;height:7px;border-radius:50%;background:#4ADE80;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
.header-actions{display:flex;gap:18px;color:rgba(255,255,255,.8);font-size:1rem}
.header-actions i{cursor:pointer}
.messages-area{
  flex:1;overflow-y:auto;
  padding:14px 14px 6px;
  position:relative;z-index:1;scroll-behavior:smooth;
}
.messages-area::-webkit-scrollbar{width:4px}
.messages-area::-webkit-scrollbar-thumb{background:rgba(0,0,0,.12);border-radius:2px}
.date-divider{text-align:center;margin:10px 0 14px}
.date-divider span{
  background:rgba(255,255,255,.85);color:var(--muted);
  font-size:.72rem;padding:4px 12px;border-radius:8px;
  box-shadow:0 1px 2px rgba(0,0,0,.08);
}
.bubble-row{display:flex;margin-bottom:3px;animation:bIn.2s ease}
@keyframes bIn{from{opacity:0;transform:translateY(6px) scale(.97)}to{opacity:1;transform:none}}
.bubble-row.out{justify-content:flex-end}
.bubble-row.in {justify-content:flex-start}
.bubble{
  max-width:min(74%,480px);
  padding:7px 12px 5px;border-radius:8px;
  font-size:.92rem;line-height:1.55;
  position:relative;box-shadow:0 1px 2px rgba(0,0,0,.1);
  word-break:break-word;white-space:pre-wrap;
}
.bubble.out{background:var(--bubble-out);border-top-right-radius:2px}
.bubble.in {background:var(--bubble-in);border-top-left-radius:2px}
.bubble.out::after{
  content:'';position:absolute;right:-8px;top:0;
  border:8px solid transparent;
  border-left-color:var(--bubble-out);border-top-color:var(--bubble-out);
  border-right:none;border-bottom:none;
}
.bubble.in::after{
  content:'';position:absolute;left:-8px;top:0;
  border:8px solid transparent;
  border-right-color:var(--bubble-in);border-top-color:var(--bubble-in);
  border-left:none;border-bottom:none;
}
.bubble-meta{display:flex;align-items:center;justify-content:flex-end;gap:4px;margin-top:2px}
.bubble-time{font-size:.65rem;color:var(--muted)}
.bubble-ticks{font-size:.7rem;color:var(--tick)}
.typing-row{display:none;margin-bottom:6px}
.typing-row.show{display:flex}
.typing-bubble{
  background:var(--bubble-in);border-radius:8px;border-top-left-radius:2px;
  padding:10px 14px;box-shadow:0 1px 2px rgba(0,0,0,.1);
  display:flex;gap:4px;align-items:center;
}
.dot{width:7px;height:7px;background:var(--muted);border-radius:50%;animation:tdot 1.2s infinite}
.dot:nth-child(2){animation-delay:.2s}.dot:nth-child(3){animation-delay:.4s}
@keyframes tdot{0%,80%,100%{transform:scale(1)}40%{transform:scale(1.35)}}
.input-bar{
  background:var(--bg-chat);padding:8px 10px;
  display:flex;align-items:flex-end;gap:8px;
  position:relative;z-index:10;flex-shrink:0;
}
.input-wrap{
  flex:1;background:#fff;border-radius:24px;
  display:flex;align-items:flex-end;padding:6px 14px;gap:10px;
  box-shadow:0 1px 3px rgba(0,0,0,.08);
}
.input-icon{color:var(--muted);font-size:1.05rem;padding-bottom:4px;cursor:pointer}
#msgInput{
  flex:1;border:none;outline:none;
  font-family:'DM Sans',sans-serif;font-size:.93rem;color:var(--text);
  background:transparent;resize:none;
  min-height:24px;max-height:120px;
  line-height:1.5;padding:4px 0;
}
#msgInput::placeholder{color:var(--muted)}
.send-btn{
  width:46px;height:46px;background:var(--theme);
  border:none;border-radius:50%;color:#fff;font-size:1rem;
  cursor:pointer;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
  transition:filter.18s,transform.18s;
  box-shadow:0 2px 8px rgba(0,0,0,.2);
}
.send-btn:hover {filter:brightness(1.1);transform:scale(1.06)}
.send-btn:active{transform:scale(.97)}
.send-btn:disabled{background:#bbb;cursor:not-allowed;transform:none;filter:none}
.toast{
  position:fixed;bottom:76px;left:50%;transform:translateX(-50%);
  background:rgba(20,20,20,.95);color:#fff;
  padding:10px 20px;border-radius:20px;
  font-size:.8rem;max-width:80%;
  opacity:0;pointer-events:none;transition:opacity.3s;z-index:999;
}
.toast.show{opacity:1}
</style>
</head>
<body>
<header class="chat-header">
  <a class="back-btn" href="index.php"><i class="fa-solid fa-arrow-left"></i></a>
  <div class="header-avatar">
    <i class="fa-solid <?= htmlspecialchars($char['icon'])?>"></i>
  </div>
  <div class="header-info">
    <div class="header-name"><?= htmlspecialchars($char['name'])?></div>
    <div class="header-status"><span class="status-dot"></span><span>çevrimiçi</span></div>
  </div>
  <div class="header-actions">
    <i class="fa-solid fa-video"></i>
    <i class="fa-solid fa-phone"></i>
    <i class="fa-solid fa-ellipsis-vertical"></i>
  </div>
</header>
<div class="messages-area" id="messagesArea">
  <div class="date-divider"><span>Bugün</span></div>
  <div class="typing-row" id="typingRow">
    <div class="typing-bubble">
      <div class="dot"></div><div class="dot"></div><div class="dot"></div>
    </div>
  </div>
</div>
<div class="input-bar">
  <div class="input-wrap">
    <i class="fa-regular fa-face-smile input-icon"></i>
    <textarea id="msgInput" placeholder="Mesaj yaz..." rows="1"></textarea>
    <i class="fa-solid fa-paperclip input-icon"></i>
  </div>
  <button class="send-btn" id="sendBtn">
    <i class="fa-solid fa-paper-plane"></i>
  </button>
</div>
<div class="toast" id="toast"></div>
<script>
var CHAR_KEY = <?= json_encode($charKey, JSON_UNESCAPED_UNICODE)?>;
var GREETING = <?= json_encode($char['greet'], JSON_UNESCAPED_UNICODE)?>;
var API_URL = 'chat.php?char=' + encodeURIComponent(CHAR_KEY);
var STORE_KEY = 'companion_wasmer_' + CHAR_KEY;
var history = [];
try {
  var raw = localStorage.getItem(STORE_KEY);
  if (raw) { var p = JSON.parse(raw); if (Array.isArray(p)) history = p; }
} catch(e) {}
function saveHistory() {
  try { localStorage.setItem(STORE_KEY, JSON.stringify(history.slice(-50))); } catch(e) {}
}
var area = document.getElementById('messagesArea');
var typingRow = document.getElementById('typingRow');
var input = document.getElementById('msgInput');
var sendBtn = document.getElementById('sendBtn');
var toastEl = document.getElementById('toast');
function nowStr() {
  return new Date().toLocaleTimeString('tr-TR', {hour:'2-digit',minute:'2-digit'});
}
function showToast(msg) {
  console.log('Toast:', msg);
  toastEl.textContent = msg;
  toastEl.classList.add('show');
  setTimeout(function(){ toastEl.classList.remove('show'); }, 4000);
}
function addBubble(role, text, time) {
  var isOut = role === 'user';
  var row = document.createElement('div');
  row.className = 'bubble-row ' + (isOut? 'out' : 'in');
  var bub = document.createElement('div');
  bub.className = 'bubble ' + (isOut? 'out' : 'in');
  bub.textContent = text;
  var meta = document.createElement('div');
  meta.className = 'bubble-meta';
  meta.innerHTML = '<span class="bubble-time">' + (time || nowStr()) + '</span>' +
    (isOut? '<i class="fa-solid fa-check-double bubble-ticks"></i>' : '');
  bub.appendChild(meta);
  row.appendChild(bub);
  area.insertBefore(row, typingRow);
  area.scrollTop = area.scrollHeight;
}
history.forEach(function(m){ addBubble(m.role, m.content, m.time); });
if (history.length === 0) {
  setTimeout(function(){
    var t = nowStr();
    history.push({role:'assistant', content:GREETING, time:t});
    saveHistory();
    addBubble('assistant', GREETING, t);
  }, 700);
}
async function send() {
  var text = input.value.trim();
  if (!text || sendBtn.disabled) return;
  var t = nowStr();
  history.push({role:'user', content:text, time:t});
  saveHistory();
  addBubble('user', text, t);
  input.value = '';
  input.style.height = 'auto';
  sendBtn.disabled = true;
  typingRow.classList.add('show');
  area.scrollTop = area.scrollHeight;
  try {
    var res = await fetch(API_URL, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({history: history})
    });
    var txt = await res.text();
    var data;
    try {
      data = JSON.parse(txt);
    } catch(e) {
      throw new Error('JSON parse hatası: ' + txt.substring(0, 100));
    }
    if (!res.ok || data.error) {
      throw new Error(data.error || 'HTTP ' + res.status + ': ' + txt.substring(0, 100));
    }
    var reply = '';
    if (data.choices && data.choices[0]) {
      if (data.choices[0].message && data.choices[0].message.content) {
        reply = data.choices[0].message.content;
      } else if (data.choices[0].text) {
        reply = data.choices[0].text;
      }
    }
    if (!reply) reply = 'Üzgünüm, cevap alamadım 😅';
    await new Promise(function(r){ setTimeout(r, 500 + Math.random() * 800); });
    typingRow.classList.remove('show');
    var rt = nowStr();
    history.push({role:'assistant', content:reply, time:rt});
    saveHistory();
    addBubble('assistant', reply, rt);
  } catch(err) {
    await new Promise(function(r){ setTimeout(r, 400); });
    typingRow.classList.remove('show');
    history.pop();
    saveHistory();
    showToast('Hata: ' + err.message);
    console.error('Detaylı Hata:', err);
  }
  sendBtn.disabled = false;
  input.focus();
}
sendBtn.addEventListener('click', send);
input.addEventListener('keydown', function(e){
  if (e.key === 'Enter' &&!e.shiftKey) { e.preventDefault(); send(); }
});
input.addEventListener('input', function(){
  input.style.height = 'auto';
  input.style.height = Math.min(input.scrollHeight, 120) + 'px';
});
</script>
</body>
</html>