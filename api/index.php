<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Companion — Kimi ile Konuşmak İstersin?</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --bg: #F7F4EF;
  --surface: #FFFFFF;
  --border: #E8E2D9;
  --text: #1A1714;
  --muted: #8A8078;
  --accent1: #E8A598;
  --accent2: #7BA7BC;
  --accent3: #A8C5A0;
  --accent4: #C4A8D4;
  --shadow-lg: 0 16px 48px rgba(0,0,0,0.12);
}

body {
  font-family: 'DM Sans', sans-serif;
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

header {
  padding: 24px 40px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--border);
  background: var(--surface);
}
.logo {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text);
  display: flex;
  align-items: center;
  gap: 10px;
}
.logo i { color: var(--accent1); }
.header-badge {
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 6px 14px;
  font-size: 0.78rem;
  color: var(--muted);
  display: flex;
  align-items: center;
  gap: 6px;
}
.badge-dot { width: 7px; height: 7px; border-radius: 50%; background: #4CAF82; }

.hero {
  text-align: center;
  padding: 64px 20px 40px;
  animation: fadeUp 0.6s ease both;
}
.hero-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 6px 16px;
  font-size: 0.8rem;
  color: var(--muted);
  margin-bottom: 22px;
}
.hero h1 {
  font-family: 'Playfair Display', serif;
  font-size: clamp(1.9rem, 4.5vw, 3rem);
  font-weight: 700;
  line-height: 1.25;
  max-width: 540px;
  margin: 0 auto 14px;
}
.hero p {
  color: var(--muted);
  font-size: 1rem;
  font-weight: 300;
  max-width: 380px;
  margin: 0 auto;
  line-height: 1.7;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  max-width: 1060px;
  margin: 0 auto;
  padding: 0 24px 80px;
  animation: fadeUp 0.8s ease both;
}

.card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 30px 22px 26px;
  cursor: pointer;
  transition: transform 0.25s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.25s, border-color 0.25s;
  text-decoration: none;
  display: block;
  position: relative;
  overflow: hidden;
}
.card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 4px;
  background: var(--accent);
  transform: scaleX(0);
  transition: transform 0.25s;
  transform-origin: left;
}
.card:hover { transform: translateY(-6px); box-shadow: var(--shadow-lg); border-color: transparent; }
.card:hover::before { transform: scaleX(1); }

.card[data-char="chloe"]  { --accent: var(--accent1); }
.card[data-char="toprak"] { --accent: var(--accent2); }
.card[data-char="deniz"]  { --accent: var(--accent3); }
.card[data-char="ece"]    { --accent: var(--accent4); }

.avatar {
  width: 68px; height: 68px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.7rem; color: #fff;
  margin-bottom: 18px;
  position: relative;
}
.card[data-char="chloe"]  .avatar { background: linear-gradient(135deg,#E8A598,#F2C5BA); }
.card[data-char="toprak"] .avatar { background: linear-gradient(135deg,#7BA7BC,#9DC0D1); }
.card[data-char="deniz"]  .avatar { background: linear-gradient(135deg,#A8C5A0,#C2D9BB); }
.card[data-char="ece"]    .avatar { background: linear-gradient(135deg,#C4A8D4,#D9C2E8); }

.online-dot {
  width: 12px; height: 12px;
  background: #4CAF82;
  border: 2px solid #fff;
  border-radius: 50%;
  position: absolute; bottom: 2px; right: 2px;
}

.card-name {
  font-family: 'Playfair Display', serif;
  font-size: 1.2rem; font-weight: 600;
  margin-bottom: 3px;
}
.card-handle { font-size: 0.76rem; color: var(--muted); margin-bottom: 12px; }
.card-bio { font-size: 0.86rem; color: #5A5350; line-height: 1.65; margin-bottom: 18px; font-weight: 300; }

.card-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 20px; }
.tag {
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 3px 10px;
  font-size: 0.73rem;
  color: var(--muted);
}

.card-btn {
  width: 100%; padding: 12px;
  border: none; border-radius: 12px;
  font-family: 'DM Sans', sans-serif;
  font-size: 0.88rem; font-weight: 500;
  cursor: pointer; color: #fff;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: filter 0.2s, transform 0.2s;
  background: var(--accent);
}
.card-btn:hover { filter: brightness(1.08); transform: scale(1.02); }

footer {
  margin-top: auto;
  text-align: center;
  padding: 20px;
  font-size: 0.76rem;
  color: var(--muted);
  border-top: 1px solid var(--border);
  background: var(--surface);
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

@media (max-width: 600px) {
  header { padding: 16px 18px; }
  .hero  { padding: 44px 16px 28px; }
  .grid  { padding: 0 14px 60px; }
}
</style>
</head>
<body>

<header>
  <div class="logo"><i class="fa-solid fa-comments"></i> Companion</div>
  <div class="header-badge">
    <span class="badge-dot"></span>
    4 arkadaş çevrimiçi
  </div>
</header>

<section class="hero">
  <div class="hero-tag"><i class="fa-regular fa-sparkles"></i> Gerçek hissettiren sohbet</div>
  <h1>Bugün Kimi ile Konuşmak İstersin?</h1>
  <p>Her biri farklı bir kişiliğe sahip dört arkadaşın seni bekliyor.</p>
</section>

<div class="grid">

  <a class="card" data-char="chloe" href="chat.php?char=chloe">
    <div class="avatar">
      <i class="fa-solid fa-face-smile-hearts"></i>
      <span class="online-dot"></span>
    </div>
    <div class="card-name">Chloe</div>
    <div class="card-handle">@chloe_xo · İstanbul</div>
    <div class="card-bio">Nazik, duygusal ve sohbet etmeyi seven biri. Söylediklerinizi ciddiye alır — ama bu onu daha gerçek yapıyor.</div>
    <div class="card-tags">
      <span class="tag">❤️ Duygusal</span>
      <span class="tag">💬 Sohbetçi</span>
      <span class="tag">🌸 Nazik</span>
    </div>
    <button class="card-btn"><i class="fa-solid fa-message"></i> Chloe ile Konuş</button>
  </a>

  <a class="card" data-char="toprak" href="chat.php?char=toprak">
    <div class="avatar">
      <i class="fa-solid fa-shield-heart"></i>
      <span class="online-dot"></span>
    </div>
    <div class="card-name">Toprak</div>
    <div class="card-handle">@toprak.k · Ankara</div>
    <div class="card-bio">Soğukkanlı, korumacı ve güvenilir. Sizi dinler ama duygularını hemen göstermez. Güvenini kazandığında en iyi dostunuz olur.</div>
    <div class="card-tags">
      <span class="tag">🛡️ Korumacı</span>
      <span class="tag">🏔️ Sakin</span>
      <span class="tag">🤝 Güvenilir</span>
    </div>
    <button class="card-btn"><i class="fa-solid fa-message"></i> Toprak ile Konuş</button>
  </a>

  <a class="card" data-char="deniz" href="chat.php?char=deniz">
    <div class="avatar">
      <i class="fa-solid fa-face-laugh-wink"></i>
      <span class="online-dot"></span>
    </div>
    <div class="card-name">Deniz</div>
    <div class="card-handle">@deniz_free · İzmir</div>
    <div class="card-bio">Özgür ruhlu, eğlenceli ve biraz çılgın. Hayatı hafiften alan ama önemli anlarda yanınızda olan biri.</div>
    <div class="card-tags">
      <span class="tag">☀️ Neşeli</span>
      <span class="tag">🌊 Özgür</span>
      <span class="tag">😄 Eğlenceli</span>
    </div>
    <button class="card-btn"><i class="fa-solid fa-message"></i> Deniz ile Konuş</button>
  </a>

  <a class="card" data-char="ece" href="chat.php?char=ece">
    <div class="avatar">
      <i class="fa-solid fa-book-open-reader"></i>
      <span class="online-dot"></span>
    </div>
    <div class="card-name">Ece</div>
    <div class="card-handle">@ece.writes · Bursa</div>
    <div class="card-bio">Entelektüel, meraklı ve derin. Felsefe, sanat, edebiyat konuşmayı sever. Sizi her zaman düşündürecek bir şeyler söyler.</div>
    <div class="card-tags">
      <span class="tag">📚 Entelektüel</span>
      <span class="tag">🎨 Sanatçı</span>
      <span class="tag">🧠 Derin</span>
    </div>
    <button class="card-btn"><i class="fa-solid fa-message"></i> Ece ile Konuş</button>
  </a>

</div>

<footer>
  <i class="fa-solid fa-lock"></i> Sohbetler yalnızca cihazınızın belleğine kaydedilir — başka kimse okuyamaz.
</footer>

</body>
</html>