/**
 * ============================================================
 * Keyboard Master — 10-Finger Typing Engine
 * Corefive / SantriKoding Interactive Typing Tutor
 * ============================================================
 */

// --- 1. FINGER & KEYBOARD MAPPING DICTIONARY ---
const KEY_MAP = {
  // Row 1 (Numbers)
  '`': { id: 'Backquote', finger: 'Kelingking Kiri', hand: 'Kiri', class: 'finger-pinky-left' },
  '1': { id: 'Digit1', finger: 'Kelingking Kiri', hand: 'Kiri', class: 'finger-pinky-left' },
  '2': { id: 'Digit2', finger: 'Jari Manis Kiri', hand: 'Kiri', class: 'finger-ring-left' },
  '3': { id: 'Digit3', finger: 'Jari Tengah Kiri', hand: 'Kiri', class: 'finger-middle-left' },
  '4': { id: 'Digit4', finger: 'Telunjuk Kiri', hand: 'Kiri', class: 'finger-index-left' },
  '5': { id: 'Digit5', finger: 'Telunjuk Kiri', hand: 'Kiri', class: 'finger-index-left' },
  '6': { id: 'Digit6', finger: 'Telunjuk Kanan', hand: 'Kanan', class: 'finger-index-right' },
  '7': { id: 'Digit7', finger: 'Telunjuk Kanan', hand: 'Kanan', class: 'finger-index-right' },
  '8': { id: 'Digit8', finger: 'Jari Tengah Kanan', hand: 'Kanan', class: 'finger-middle-right' },
  '9': { id: 'Digit9', finger: 'Jari Manis Kanan', hand: 'Kanan', class: 'finger-ring-right' },
  '0': { id: 'Digit0', finger: 'Kelingking Kanan', hand: 'Kanan', class: 'finger-pinky-right' },
  '-': { id: 'Minus', finger: 'Kelingking Kanan', hand: 'Kanan', class: 'finger-pinky-right' },
  '=': { id: 'Equal', finger: 'Kelingking Kanan', hand: 'Kanan', class: 'finger-pinky-right' },

  // Row 2 (Top Row)
  'q': { id: 'KeyQ', finger: 'Kelingking Kiri', hand: 'Kiri', class: 'finger-pinky-left' },
  'w': { id: 'KeyW', finger: 'Jari Manis Kiri', hand: 'Kiri', class: 'finger-ring-left' },
  'e': { id: 'KeyE', finger: 'Jari Tengah Kiri', hand: 'Kiri', class: 'finger-middle-left' },
  'r': { id: 'KeyR', finger: 'Telunjuk Kiri', hand: 'Kiri', class: 'finger-index-left' },
  't': { id: 'KeyT', finger: 'Telunjuk Kiri', hand: 'Kiri', class: 'finger-index-left' },
  'y': { id: 'KeyY', finger: 'Telunjuk Kanan', hand: 'Kanan', class: 'finger-index-right' },
  'u': { id: 'KeyU', finger: 'Telunjuk Kanan', hand: 'Kanan', class: 'finger-index-right' },
  'i': { id: 'KeyI', finger: 'Jari Tengah Kanan', hand: 'Kanan', class: 'finger-middle-right' },
  'o': { id: 'KeyO', finger: 'Jari Manis Kanan', hand: 'Kanan', class: 'finger-ring-right' },
  'p': { id: 'KeyP', finger: 'Kelingking Kanan', hand: 'Kanan', class: 'finger-pinky-right' },
  '[': { id: 'BracketLeft', finger: 'Kelingking Kanan', hand: 'Kanan', class: 'finger-pinky-right' },
  ']': { id: 'BracketRight', finger: 'Kelingking Kanan', hand: 'Kanan', class: 'finger-pinky-right' },

  // Row 3 (Home Row)
  'a': { id: 'KeyA', finger: 'Kelingking Kiri', hand: 'Kiri', class: 'finger-pinky-left' },
  's': { id: 'KeyS', finger: 'Jari Manis Kiri', hand: 'Kiri', class: 'finger-ring-left' },
  'd': { id: 'KeyD', finger: 'Jari Tengah Kiri', hand: 'Kiri', class: 'finger-middle-left' },
  'f': { id: 'KeyF', finger: 'Telunjuk Kiri', hand: 'Kiri', class: 'finger-index-left' },
  'g': { id: 'KeyG', finger: 'Telunjuk Kiri', hand: 'Kiri', class: 'finger-index-left' },
  'h': { id: 'KeyH', finger: 'Telunjuk Kanan', hand: 'Kanan', class: 'finger-index-right' },
  'j': { id: 'KeyJ', finger: 'Telunjuk Kanan', hand: 'Kanan', class: 'finger-index-right' },
  'k': { id: 'KeyK', finger: 'Jari Tengah Kanan', hand: 'Kanan', class: 'finger-middle-right' },
  'l': { id: 'KeyL', finger: 'Jari Manis Kanan', hand: 'Kanan', class: 'finger-ring-right' },
  ';': { id: 'Semicolon', finger: 'Kelingking Kanan', hand: 'Kanan', class: 'finger-pinky-right' },
  "'": { id: 'Quote', finger: 'Kelingking Kanan', hand: 'Kanan', class: 'finger-pinky-right' },

  // Row 4 (Bottom Row)
  'z': { id: 'KeyZ', finger: 'Kelingking Kiri', hand: 'Kiri', class: 'finger-pinky-left' },
  'x': { id: 'KeyX', finger: 'Jari Manis Kiri', hand: 'Kiri', class: 'finger-ring-left' },
  'c': { id: 'KeyC', finger: 'Jari Tengah Kiri', hand: 'Kiri', class: 'finger-middle-left' },
  'v': { id: 'KeyV', finger: 'Telunjuk Kiri', hand: 'Kiri', class: 'finger-index-left' },
  'b': { id: 'KeyB', finger: 'Telunjuk Kiri', hand: 'Kiri', class: 'finger-index-left' },
  'n': { id: 'KeyN', finger: 'Telunjuk Kanan', hand: 'Kanan', class: 'finger-index-right' },
  'm': { id: 'KeyM', finger: 'Telunjuk Kanan', hand: 'Kanan', class: 'finger-index-right' },
  ',': { id: 'Comma', finger: 'Jari Tengah Kanan', hand: 'Kanan', class: 'finger-middle-right' },
  '.': { id: 'Period', finger: 'Jari Manis Kanan', hand: 'Kanan', class: 'finger-ring-right' },
  '/': { id: 'Slash', finger: 'Kelingking Kanan', hand: 'Kanan', class: 'finger-pinky-right' },

  // Space
  ' ': { id: 'Space', finger: 'Ibu Jari (Jempol)', hand: 'Kiri / Kanan', class: 'finger-thumb' }
};

// --- 2. LESSONS & WORD BANKS ---
const LESSONS = {
  homerow: [
    "asdf jkl; asdf jkl; asdf jkl;",
    "sad dad fad lad fall ask alas",
    "flask salad flash falls dallas jask",
    "all dads ask dad all lads fall as dad",
    "a sad lad asks dad for a flask of salad"
  ],
  toprow: [
    "qwer uiop qwer uiop qwer uiop",
    "quiet power report trip quote write",
    "type pour pure equip water wire rout",
    "we wrote our quiet report without worry",
    "your pure power will guide every proper write"
  ],
  bottomrow: [
    "zxcv bnm zxcv bnm zxcv bnm",
    "zinc cab ban max van mix man van",
    "cabin comb back calm zoom buzz zinc",
    "can max zoom back into calm cabin zone",
    "many calm men can make big zinc cabins"
  ],
  words_id: [
    "santri koding membuat aplikasi web dengan php mysql dan javascript secara teratur",
    "belajar mengetik sepuluh jari meningkatkan kecepatan dan produktivitas seorang programmer",
    "ilmu logika algoritma dan nahwu sharaf memiliki benang merah yang sangat kuat",
    "pesantren modern melahirkan generasi teknologi yang cerdas kreatif dan berakhlak mulia",
    "konsistensi latihan setiap hari adalah kunci utama menguasai skill koding masa depan",
    "fokus dan disiplin membagi waktu antara hafalan ngaji dan deadline pembuatan sistem web"
  ],
  coding: [
    "function hitungNilai($angka) { return $angka * 2; }",
    "const response = await fetch('/api/data', { method: 'POST' });",
    "SELECT id, nama, status FROM presensi_halacoding WHERE hadir = 1;",
    "<div class=\"flex items-center justify-between p-4 bg-slate-800\">",
    "git add . && git commit -m \"feat: update system\" && git push origin main;"
  ],
  quotes: [
    "Man jadda wajada. Barang siapa yang bersungguh-sungguh maka dia akan berhasil.",
    "Al ilmu bila amalin kasy-syajari bila tsamarin. Ilmu tanpa amal bagaikan pohon tanpa buah.",
    "Jangan takut mencoba hal baru karena setiap ahli koding di dunia ini dulunya adalah seorang pemula.",
    "Teknologi adalah sarana syiar dan dakwah modern untuk memberikan kebermanfaatan seluas-luasnya."
  ]
};

// --- 3. SYNTHETIC SOUND EFFECTS (Web Audio API) ---
class SoundManager {
  constructor() {
    this.enabled = true;
    this.ctx = null;
  }

  init() {
    if (!this.ctx) {
      const AudioContext = window.AudioContext || window.webkitAudioContext;
      if (AudioContext) {
        this.ctx = new AudioContext();
      }
    }
  }

  playKeySound() {
    if (!this.enabled) return;
    this.init();
    if (!this.ctx) return;

    try {
      const osc = this.ctx.createOscillator();
      const gain = this.ctx.createGain();

      osc.type = 'triangle';
      osc.frequency.setValueAtTime(600 + Math.random() * 80, this.ctx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(120, this.ctx.currentTime + 0.05);

      gain.gain.setValueAtTime(0.12, this.ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + 0.05);

      osc.connect(gain);
      gain.connect(this.ctx.destination);

      osc.start();
      osc.stop(this.ctx.currentTime + 0.05);
    } catch (e) {}
  }

  playErrorSound() {
    if (!this.enabled) return;
    this.init();
    if (!this.ctx) return;

    try {
      const osc = this.ctx.createOscillator();
      const gain = this.ctx.createGain();

      osc.type = 'sawtooth';
      osc.frequency.setValueAtTime(160, this.ctx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(80, this.ctx.currentTime + 0.12);

      gain.gain.setValueAtTime(0.2, this.ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + 0.12);

      osc.connect(gain);
      gain.connect(this.ctx.destination);

      osc.start();
      osc.stop(this.ctx.currentTime + 0.12);
    } catch (e) {}
  }

  playSuccessSound() {
    if (!this.enabled) return;
    this.init();
    if (!this.ctx) return;

    try {
      const notes = [440, 554, 659, 880];
      notes.forEach((freq, i) => {
        const osc = this.ctx.createOscillator();
        const gain = this.ctx.createGain();

        osc.type = 'sine';
        osc.frequency.setValueAtTime(freq, this.ctx.currentTime + i * 0.1);

        gain.gain.setValueAtTime(0.15, this.ctx.currentTime + i * 0.1);
        gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + i * 0.1 + 0.25);

        osc.connect(gain);
        gain.connect(this.ctx.destination);

        osc.start(this.ctx.currentTime + i * 0.1);
        osc.stop(this.ctx.currentTime + i * 0.1 + 0.25);
      });
    } catch (e) {}
  }
}

// --- 4. APPLICATION STATE & DOM REFERENCES ---
const soundManager = new SoundManager();

const state = {
  mode: 'words_id',
  targetText: '',
  currentIndex: 0,
  startTime: null,
  timerInterval: null,
  isFinished: false,
  correctCount: 0,
  errorCount: 0,
  totalTyped: 0,
  elapsedSeconds: 0
};

// Elements
const textDisplay = document.getElementById('textDisplay');
const hiddenInput = document.getElementById('hiddenInput');
const typingAreaWrapper = document.getElementById('typingAreaWrapper');

// Live Metrics Elements
const wpmDisplay = document.getElementById('wpmDisplay');
const accuracyDisplay = document.getElementById('accuracyDisplay');
const errorsDisplay = document.getElementById('errorsDisplay');
const timerDisplay = document.getElementById('timerDisplay');

// Finger Guidance Badges
const fingerGuideText = document.getElementById('fingerGuideText');
const fingerGuideHand = document.getElementById('fingerGuideHand');

// Sound Toggle Button
const soundToggleBtn = document.getElementById('soundToggleBtn');

// Modal Elements
const resultModal = document.getElementById('resultModal');
const finalWpm = document.getElementById('finalWpm');
const finalAccuracy = document.getElementById('finalAccuracy');
const finalErrors = document.getElementById('finalErrors');
const finalTime = document.getElementById('finalTime');
const finalTier = document.getElementById('finalTier');
const restartBtn = document.getElementById('restartBtn');
const shareWaBtn = document.getElementById('shareWaBtn');

// --- 5. INITIALIZATION & SETUP ---
function initApp() {
  loadLesson(state.mode);
  setupEventListeners();
  updateSoundIcon();
}

function loadLesson(mode) {
  state.mode = mode;
  const list = LESSONS[mode] || LESSONS.words_id;
  state.targetText = list[Math.floor(Math.random() * list.length)];
  resetTypingState();
  renderText();
  highlightTargetKey();
}

function resetTypingState() {
  clearInterval(state.timerInterval);
  state.currentIndex = 0;
  state.startTime = null;
  state.timerInterval = null;
  state.isFinished = false;
  state.correctCount = 0;
  state.errorCount = 0;
  state.totalTyped = 0;
  state.elapsedSeconds = 0;

  wpmDisplay.textContent = '0';
  accuracyDisplay.textContent = '100%';
  errorsDisplay.textContent = '0';
  timerDisplay.textContent = '00:00';

  hiddenInput.value = '';
  resultModal.classList.add('hidden');
}

// Render characters into textDisplay
function renderText() {
  textDisplay.innerHTML = '';
  const chars = state.targetText.split('');

  chars.forEach((char, index) => {
    const span = document.createElement('span');
    span.className = 'char';
    span.textContent = char;
    if (index === 0) span.classList.add('current');
    textDisplay.appendChild(span);
  });
}

// --- 6. TARGET KEY & FINGER HIGHLIGHTING ---
function highlightTargetKey() {
  // Clear previous target keys
  document.querySelectorAll('.key.target').forEach(el => el.classList.remove('target'));

  if (state.currentIndex >= state.targetText.length) {
    fingerGuideText.textContent = 'Selesai!';
    fingerGuideHand.textContent = 'Hebat 🎉';
    return;
  }

  const char = state.targetText[state.currentIndex];
  const lowerChar = char.toLowerCase();
  const map = KEY_MAP[lowerChar];

  if (map) {
    const keyEl = document.querySelector(`.key[data-key="${map.id}"]`);
    if (keyEl) {
      keyEl.classList.add('target');
    }

    // Update guide text
    fingerGuideText.textContent = map.finger;
    fingerGuideHand.textContent = `Tangan ${map.hand}`;
  } else {
    fingerGuideText.textContent = 'Tekan: ' + char;
    fingerGuideHand.textContent = 'Bebas';
  }
}

// --- 7. INPUT HANDLING & METRICS ---
function handleKeyDown(e) {
  if (state.isFinished) return;

  // Prevent default scroll on Space
  if (e.code === 'Space') {
    e.preventDefault();
  }

  // Handle Backspace
  if (e.key === 'Backspace') {
    e.preventDefault();
    handleBackspace();
    return;
  }

  // Ignore non-printable keys
  if (e.key.length !== 1) return;

  // Start timer on first keystroke
  if (!state.startTime) {
    state.startTime = Date.now();
    state.timerInterval = setInterval(updateTimer, 1000);
  }

  const expectedChar = state.targetText[state.currentIndex];
  const typedChar = e.key;

  state.totalTyped++;

  const charElements = textDisplay.querySelectorAll('.char');
  const currentCharEl = charElements[state.currentIndex];

  const map = KEY_MAP[typedChar.toLowerCase()];
  const physicalKeyEl = map ? document.querySelector(`.key[data-key="${map.id}"]`) : null;

  if (typedChar === expectedChar) {
    // Correct
    soundManager.playKeySound();
    state.correctCount++;
    currentCharEl.className = 'char correct';

    if (physicalKeyEl) {
      animateKeyPressed(physicalKeyEl, 'pressed');
    }
  } else {
    // Incorrect
    soundManager.playErrorSound();
    state.errorCount++;
    currentCharEl.className = 'char incorrect';

    if (physicalKeyEl) {
      animateKeyPressed(physicalKeyEl, 'pressed-wrong');
    }
  }

  // Advance index
  state.currentIndex++;

  if (state.currentIndex < state.targetText.length) {
    charElements[state.currentIndex].classList.add('current');
    highlightTargetKey();
  } else {
    // Finished test
    finishTest();
  }

  updateLiveMetrics();
}

function handleBackspace() {
  if (state.currentIndex === 0) return;

  const charElements = textDisplay.querySelectorAll('.char');
  charElements[state.currentIndex].classList.remove('current');

  state.currentIndex--;
  const prevCharEl = charElements[state.currentIndex];
  prevCharEl.className = 'char current';

  highlightTargetKey();
}

function animateKeyPressed(el, className) {
  el.classList.add(className);
  setTimeout(() => {
    el.classList.remove(className);
  }, 120);
}

// --- 8. METRICS CALCULATIONS ---
function updateTimer() {
  state.elapsedSeconds = Math.floor((Date.now() - state.startTime) / 1000);
  const minutes = String(Math.floor(state.elapsedSeconds / 60)).padStart(2, '0');
  const seconds = String(state.elapsedSeconds % 60).padStart(2, '0');
  timerDisplay.textContent = `${minutes}:${seconds}`;

  updateLiveMetrics();
}

function updateLiveMetrics() {
  // WPM = (Total Correct Characters / 5) / (Elapsed Minutes)
  let wpm = 0;
  if (state.elapsedSeconds > 0) {
    const minutes = state.elapsedSeconds / 60;
    wpm = Math.round((state.correctCount / 5) / minutes);
  }
  wpmDisplay.textContent = String(wpm);

  // Accuracy
  let accuracy = 100;
  if (state.totalTyped > 0) {
    accuracy = Math.round((state.correctCount / state.totalTyped) * 100);
  }
  accuracyDisplay.textContent = `${accuracy}%`;

  // Errors
  errorsDisplay.textContent = String(state.errorCount);
}

// --- 9. FINISH & RESULTS ---
function finishTest() {
  state.isFinished = true;
  clearInterval(state.timerInterval);
  soundManager.playSuccessSound();

  const minutes = Math.max(state.elapsedSeconds / 60, 0.05);
  const wpm = Math.round((state.correctCount / 5) / minutes);
  const accuracy = state.totalTyped > 0 ? Math.round((state.correctCount / state.totalTyped) * 100) : 100;

  let tier = "Pemula (Beginner)";
  if (wpm >= 65) tier = "🔥 Sultan Ngetik 10 Jari (Grandmaster)";
  else if (wpm >= 45) tier = "⚡ Juru Ketik Profesional (Pro Typist)";
  else if (wpm >= 30) tier = "✨ Mengetik Cepat & Lancar (Intermediate)";
  else if (wpm >= 20) tier = "🌱 Tahap Berkembang (Good Progress)";

  finalWpm.textContent = String(wpm);
  finalAccuracy.textContent = `${accuracy}%`;
  finalErrors.textContent = String(state.errorCount);
  finalTime.textContent = timerDisplay.textContent;
  finalTier.textContent = tier;

  // Setup WhatsApp share
  const shareText = `Alhamdulillah! Aku baru saja menyelesaikan latihan ketik 10 jari di Keyboard Master (SantriKoding/Corefive):\n\n🚀 Kecepatan: *${wpm} WPM*\n🎯 Akurasi: *${accuracy}%*\n⏱️ Waktu: *${timerDisplay.textContent}*\n🏆 Level: *${tier}*\n\nYuk coba juga latihan ketik 10 jari di sini: https://portofolio.corefive.my.id/keyboard`;
  shareWaBtn.href = `https://wa.me/?text=${encodeURIComponent(shareText)}`;

  resultModal.classList.remove('hidden');
}

// --- 10. EVENT LISTENERS ---
function setupEventListeners() {
  // Focus hidden input on clicking typing area
  typingAreaWrapper.addEventListener('click', () => {
    hiddenInput.focus();
  });

  // Global keydown capture
  window.addEventListener('keydown', (e) => {
    // If not focused on an input/select
    if (document.activeElement.tagName !== 'SELECT') {
      handleKeyDown(e);
    }
  });

  // Mode Selection
  document.querySelectorAll('.mode-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      document.querySelectorAll('.mode-btn').forEach(b => {
        b.classList.remove('bg-accent', 'text-white');
        b.classList.add('bg-slate-800', 'text-slate-300');
      });
      btn.classList.remove('bg-slate-800', 'text-slate-300');
      btn.classList.add('bg-accent', 'text-white');

      const mode = btn.getAttribute('data-mode');
      loadLesson(mode);
    });
  });

  // Sound Toggle
  soundToggleBtn.addEventListener('click', () => {
    soundManager.enabled = !soundManager.enabled;
    updateSoundIcon();
  });

  // Restart Button
  restartBtn.addEventListener('click', () => {
    loadLesson(state.mode);
  });

  document.getElementById('retryHeaderBtn').addEventListener('click', () => {
    loadLesson(state.mode);
  });
}

function updateSoundIcon() {
  const icon = soundToggleBtn.querySelector('i');
  if (soundManager.enabled) {
    icon.className = 'fas fa-volume-up text-emerald-400';
    soundToggleBtn.title = "Suara Aktif (Klik untuk mute)";
  } else {
    icon.className = 'fas fa-volume-mute text-slate-500';
    soundToggleBtn.title = "Suara Nonaktif (Klik untuk aktifkan)";
  }
}

// Auto start on DOM content loaded
document.addEventListener('DOMContentLoaded', initApp);
