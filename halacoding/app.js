/**
 * Halacoding Attendance System
 * Core Application Logic: Lookup, Persistence, Role Switching, Confetti & Audio
 */

// ========================================================
// 1. DEFAULT MASTER DATA PESERTA HALACODING
// ========================================================
const DEFAULT_PESERTA_DB = [
  {
    id: "HC-101",
    nama: "Muhammad Rizky Pratama",
    whatsapp: "0812-3456-7801",
    instansi: "Universitas Indonesia",
    track: "Fullstack Web (React & Node)",
    mentor: "Kak Dimas (Senior Fullstack Lead)",
    batch: "Batch 4 - 2026",
    color: "#4f46e5"
  },
  {
    id: "HC-102",
    nama: "Siti Nur Aisyah",
    whatsapp: "0813-4567-8902",
    instansi: "Institut Teknologi Bandung",
    track: "Python & AI Engineering",
    mentor: "Kak Sarah (AI Specialist)",
    batch: "Batch 4 - 2026",
    color: "#059669"
  },
  {
    id: "HC-103",
    nama: "Budi Santoso",
    whatsapp: "0856-7890-1203",
    instansi: "SMKN 1 Jakarta",
    track: "Mobile App (Flutter)",
    mentor: "Kak Andi (Mobile Architect)",
    batch: "Batch 4 - 2026",
    color: "#d97706"
  },
  {
    id: "HC-104",
    nama: "Putri Anggraini",
    whatsapp: "0878-1234-5604",
    instansi: "Binus University",
    track: "UI/UX & Product Design",
    mentor: "Kak Bella (Head of Product Design)",
    batch: "Batch 4 - 2026",
    color: "#db2777"
  },
  {
    id: "HC-105",
    nama: "Fajar Ramadhan",
    whatsapp: "0819-2345-6705",
    instansi: "Telkom University",
    track: "Cloud & DevOps",
    mentor: "Kak Kevin (Cloud Architect)",
    batch: "Batch 4 - 2026",
    color: "#7c3aed"
  },
  {
    id: "HC-106",
    nama: "Anisa Rahmawati",
    whatsapp: "0857-3456-7806",
    instansi: "Universitas Gadjah Mada",
    track: "Fullstack Web (React & Node)",
    mentor: "Kak Dimas (Senior Fullstack Lead)",
    batch: "Batch 4 - 2026",
    color: "#2563eb"
  },
  {
    id: "HC-107",
    nama: "Dimas Arya Sena",
    whatsapp: "0821-4567-8907",
    instansi: "Institut Teknologi Sepuluh Nopember",
    track: "Python & AI Engineering",
    mentor: "Kak Sarah (AI Specialist)",
    batch: "Batch 4 - 2026",
    color: "#0d9488"
  },
  {
    id: "HC-108",
    nama: "Nabila Salsabila",
    whatsapp: "0896-5678-9008",
    instansi: "Politeknik Negeri Bandung",
    track: "UI/UX & Product Design",
    mentor: "Kak Bella (Head of Product Design)",
    batch: "Batch 4 - 2026",
    color: "#e11d48"
  },
  {
    id: "HC-109",
    nama: "Reza Fauzi",
    whatsapp: "0838-6789-0109",
    instansi: "Universitas Brawijaya",
    track: "Mobile App (Flutter)",
    mentor: "Kak Andi (Mobile Architect)",
    batch: "Batch 4 - 2026",
    color: "#ea580c"
  },
  {
    id: "HC-110",
    nama: "Kevin Christian",
    whatsapp: "0812-7890-1210",
    instansi: "Universitas Diponegoro",
    track: "Cloud & DevOps",
    mentor: "Kak Kevin (Cloud Architect)",
    batch: "Batch 4 - 2026",
    color: "#4338ca"
  }
];

// LocalStorage Keys & API Configuration
const STORAGE_PESERTA_KEY = "halacoding_master_peserta";
const STORAGE_REKAP_KEY = "halacoding_presensi_records";
const PRESENSI_API_URL = "presensi.php";
const IS_SERVER_ENV = window.location.protocol.startsWith("http");

// State
let masterPeserta = [];
let rekapPresensi = [];
let currentMatchedPeserta = null;
let currentRole = "peserta"; // 'peserta' | 'admin'
let isAdminAuthenticated = false;
let pendingImportData = [];
let toastTimeout = null;
let autoSyncRekapTimer = null;

// Multi-Batch & 4 Pertemuan State
let activeBatch = "Batch 1";
let activePertemuan = 1;
let selectedPesertaPertemuan = 1;
let userManuallyChangedPertemuan = false;
let availableBatches = ["Batch 1"];
let pertemuanStats = { 1: 0, 2: 0, 3: 0, 4: 0 };

// Default Admin Credentials
const ADMIN_CREDENTIALS = {
  username: "admin",
  password: "admin123"
};

// ========================================================
// 2. INITIALIZATION (LANGSUNG DARI KODE data-peserta.js)
// ========================================================
document.addEventListener("DOMContentLoaded", () => {
  // Check existing admin session
  if (sessionStorage.getItem("halacoding_admin_session") === "true") {
    isAdminAuthenticated = true;
  }
  initData();
  updateBadgeCounts();
  updateSessionUI();

  // Sinkronisasi otomatis rekap setiap 6 detik saat terhubung ke server hosting
  if (IS_SERVER_ENV) {
    if (autoSyncRekapTimer) clearInterval(autoSyncRekapTimer);
    autoSyncRekapTimer = setInterval(() => {
      syncRekapFromServer(true);
    }, 6000);
  }
});

function initData() {
  // 1. Prioritaskan data peserta yang tersimpan langsung di dalam file kode data-peserta.js
  if (typeof DATA_PESERTA_HALACODING !== "undefined" && Array.isArray(DATA_PESERTA_HALACODING) && DATA_PESERTA_HALACODING.length > 0) {
    masterPeserta = [...DATA_PESERTA_HALACODING];
    localStorage.setItem(STORAGE_PESERTA_KEY, JSON.stringify(masterPeserta));
  } else {
    // Fallback dari penyimpanan lokal browser jika file belum ada
    const storedMaster = localStorage.getItem(STORAGE_PESERTA_KEY);
    if (storedMaster) {
      try {
        masterPeserta = JSON.parse(storedMaster);
        masterPeserta = masterPeserta.map(p => {
          if (!p.whatsapp) p.whatsapp = p.email || "-";
          return p;
        });
      } catch (e) {
        masterPeserta = [...DEFAULT_PESERTA_DB];
      }
    } else {
      masterPeserta = [...DEFAULT_PESERTA_DB];
      localStorage.setItem(STORAGE_PESERTA_KEY, JSON.stringify(masterPeserta));
    }
  }

  // Ambil daftar batch yang ada dari master peserta jika tersedia
  const masterBatches = [...new Set(masterPeserta.map(p => p.batch).filter(Boolean))];
  masterBatches.forEach(b => {
    // Normalisasikan label batch seperti "Batch 1 - 2026" atau "Batch 1"
    let cleanB = b.split("-")[0].trim();
    if (!availableBatches.includes(cleanB)) availableBatches.push(cleanB);
  });

  // 2. Muat riwayat presensi lokal (fast initial render)
  const storedRekap = localStorage.getItem(STORAGE_REKAP_KEY);
  if (storedRekap) {
    try {
      rekapPresensi = JSON.parse(storedRekap);
      rekapPresensi = rekapPresensi.map(r => {
        if (!r.whatsapp) r.whatsapp = r.email || "-";
        return r;
      });
    } catch (e) {
      rekapPresensi = [];
    }
  } else {
    rekapPresensi = [];
  }

  // 3. Jika berjalan di server hosting, segera ambil data rekap presensi terpusat dari server
  if (IS_SERVER_ENV) {
    syncRekapFromServer(true);
  }
}

async function syncRekapFromServer(isSilent = true) {
  if (!IS_SERVER_ENV) return;
  try {
    const res = await fetch(`${PRESENSI_API_URL}?action=get_rekap&_t=${Date.now()}`, {
      cache: "no-store"
    });
    if (!res.ok) return;
    const data = await res.json();
    if (data.status === "success") {
      let dataChanged = false;
      if (Array.isArray(data.rekap)) {
        if (JSON.stringify(rekapPresensi) !== JSON.stringify(data.rekap)) {
          rekapPresensi = data.rekap;
          localStorage.setItem(STORAGE_REKAP_KEY, JSON.stringify(rekapPresensi));
          updateBadgeCounts();
          dataChanged = true;
        }
      }
      if (Array.isArray(data.batches) && data.batches.length > 0) {
        data.batches.forEach(b => {
          if (!availableBatches.includes(b)) availableBatches.push(b);
        });
      }
      if (data.active_batch && data.active_batch !== activeBatch) {
        activeBatch = data.active_batch;
        dataChanged = true;
      }
      if (data.active_pertemuan) {
        activePertemuan = Number(data.active_pertemuan) || 1;
        if (!userManuallyChangedPertemuan) {
          selectedPesertaPertemuan = activePertemuan;
        }
      }
      if (data.pertemuan_stats) {
        pertemuanStats = data.pertemuan_stats;
      }

      updateSessionUI();

      // Jika halaman admin sedang terbuka, render ulang tabel agar langsung sinkron
      if (currentRole === "admin" && isAdminAuthenticated && dataChanged) {
        renderAdminDashboard();
      }

      if (!isSilent) {
        showToastNotification("Data rekap presensi berhasil diperbarui!", "🔄");
      }
    }
  } catch (e) {
    console.warn("Sinkronisasi presensi offline:", e);
    if (!isSilent) {
      alert("Gagal memperbarui rekap dari server hosting. Pastikan file presensi.php sudah terunggah di hosting.");
    }
  }
}

function selectPesertaPertemuan(pNum) {
  selectedPesertaPertemuan = Number(pNum);
  userManuallyChangedPertemuan = true;
  for (let i = 1; i <= 4; i++) {
    const pill = document.getElementById("pillP" + i);
    if (pill) {
      if (i === selectedPesertaPertemuan) pill.classList.add("active");
      else pill.classList.remove("active");
    }
  }
  const btnText = document.getElementById("btnSubmitPresensiText");
  if (btnText) {
    btnText.textContent = `Konfirmasi Presensi Pertemuan ${selectedPesertaPertemuan}`;
  }
  if (currentMatchedPeserta) {
    handleIdLookup();
  }
}

function updateSessionUI() {
  const batchDisplay = document.getElementById("activeBatchDisplay");
  if (batchDisplay) {
    batchDisplay.textContent = `Generasi 1 (${activeBatch})`;
  }

  // Update pills for participant
  for (let i = 1; i <= 4; i++) {
    const pill = document.getElementById("pillP" + i);
    if (pill) {
      if (i === selectedPesertaPertemuan) pill.classList.add("active");
      else pill.classList.remove("active");
    }
  }
  const btnText = document.getElementById("btnSubmitPresensiText");
  if (btnText) {
    btnText.textContent = `Konfirmasi Presensi Pertemuan ${selectedPesertaPertemuan}`;
  }

  // Admin active batch select options
  const adminBatchSelect = document.getElementById("adminActiveBatchSelect");
  if (adminBatchSelect) {
    const prevVal = adminBatchSelect.value;
    adminBatchSelect.innerHTML = availableBatches.map(b => 
      `<option value="${escapeHtml(b)}"${b === activeBatch ? ' selected' : ''}>${escapeHtml(b)}</option>`
    ).join("");
    if (availableBatches.includes(prevVal) && !availableBatches.includes(activeBatch)) {
      adminBatchSelect.value = prevVal;
    }
  }

  // Admin filter batch options
  const filterBatchSelect = document.getElementById("filterBatch");
  if (filterBatchSelect) {
    const currentFilter = filterBatchSelect.value;
    filterBatchSelect.innerHTML = `<option value="all">Semua Batch</option>` +
      availableBatches.map(b => `<option value="${escapeHtml(b)}"${b === activeBatch ? ' selected' : ''}>${escapeHtml(b)}</option>`).join("");
    if (availableBatches.includes(currentFilter) || currentFilter === "all") {
      filterBatchSelect.value = currentFilter;
    } else {
      filterBatchSelect.value = activeBatch;
    }
  }

  // Admin active pertemuan toggle
  const adminToggle = document.getElementById("adminPertemuanToggle");
  if (adminToggle) {
    const btns = adminToggle.querySelectorAll(".admin-p-btn");
    btns.forEach(btn => {
      const p = Number(btn.getAttribute("data-p"));
      if (p === activePertemuan) btn.classList.add("active");
      else btn.classList.remove("active");
    });
  }

  // Admin badge
  const adminBadge = document.getElementById("adminCurrentActiveSessionBadge");
  if (adminBadge) {
    adminBadge.textContent = `${activeBatch} • Pertemuan ${activePertemuan}`;
  }
}

function saveMasterPeserta() {
  localStorage.setItem(STORAGE_PESERTA_KEY, JSON.stringify(masterPeserta));
}

function saveRekapData() {
  localStorage.setItem(STORAGE_REKAP_KEY, JSON.stringify(rekapPresensi));
  updateBadgeCounts();
}

function updateBadgeCounts() {
  const badge = document.getElementById("rekapCountBadge");
  if (badge) {
    badge.textContent = rekapPresensi.length;
  }
}

/**
 * Unduh seluruh data master peserta saat ini sebagai file data-peserta.js
 * Sehingga data peserta langsung masuk ke kode dan tidak memerlukan database sama sekali.
 */
function downloadDataPesertaJS() {
  if (!masterPeserta || masterPeserta.length === 0) {
    alert("Data peserta saat ini masih kosong. Silakan import peserta terlebih dahulu.");
    return;
  }

  const jsContent = `/**
 * DATA MASTER PESERTA PELATIHAN HALACODING
 * Seluruh data peserta tersimpan langsung di dalam file kode ini.
 * Tidak memerlukan database, MySQL, ataupun file backend (api.php).
 * Total Peserta: ${masterPeserta.length}
 * Terakhir diperbarui: ${new Date().toLocaleString("id-ID")}
 */

const DATA_PESERTA_HALACODING = ${JSON.stringify(masterPeserta, null, 2)};
`;

  const blob = new Blob([jsContent], { type: "application/javascript;charset=utf-8" });
  const downloadUrl = URL.createObjectURL(blob);
  const downloadLink = document.createElement("a");
  downloadLink.href = downloadUrl;
  downloadLink.download = "data-peserta.js";
  document.body.appendChild(downloadLink);
  downloadLink.click();
  document.body.removeChild(downloadLink);
  URL.revokeObjectURL(downloadUrl);

  showToastNotification(`File data-peserta.js (${masterPeserta.length} peserta) berhasil diunduh!`, "💾");
}

// ========================================================
// 3. ROLE NAVIGATION (PESERTA <-> ADMIN)
// ========================================================
function openAdminLogin() {
  currentRole = "admin";
  const btnPeserta = document.getElementById("btnRolePeserta");
  const btnAdmin = document.getElementById("btnRoleAdmin");
  const btnAdminMasuk = document.getElementById("btnAdminMasuk");
  const viewPeserta = document.getElementById("viewPeserta");
  const viewAdminLogin = document.getElementById("viewAdminLogin");
  const viewAdmin = document.getElementById("viewAdmin");

  if (btnAdmin) btnAdmin.classList.add("active");
  if (btnPeserta) btnPeserta.classList.remove("active");
  if (btnAdminMasuk) btnAdminMasuk.style.display = "none";

  if (viewPeserta) viewPeserta.style.display = "none";
  if (viewAdmin) viewAdmin.style.display = "none";
  if (viewAdminLogin) {
    viewAdminLogin.style.display = "block";
    const errAlert = document.getElementById("loginErrorAlert");
    if (errAlert) errAlert.style.display = "none";
  }

  // Scroll to top agar form login terlihat jelas
  window.scrollTo({ top: 0, behavior: "smooth" });

  setTimeout(() => {
    const userField = document.getElementById("inputAdminUsername");
    if (userField) {
      userField.focus();
    }
  }, 100);
}

function switchRole(role) {
  currentRole = role;
  const btnPeserta = document.getElementById("btnRolePeserta");
  const btnAdmin = document.getElementById("btnRoleAdmin");
  const btnAdminMasuk = document.getElementById("btnAdminMasuk");
  const viewPeserta = document.getElementById("viewPeserta");
  const viewAdminLogin = document.getElementById("viewAdminLogin");
  const viewAdmin = document.getElementById("viewAdmin");

  if (role === "peserta") {
    if (btnPeserta) btnPeserta.classList.add("active");
    if (btnAdmin) btnAdmin.classList.remove("active");
    if (btnAdminMasuk) btnAdminMasuk.style.display = "inline";
    if (viewPeserta) viewPeserta.style.display = "block";
    if (viewAdminLogin) viewAdminLogin.style.display = "none";
    if (viewAdmin) viewAdmin.style.display = "none";
    window.scrollTo({ top: 0, behavior: "smooth" });
    // Refocus input peserta
    setTimeout(() => {
      const idInput = document.getElementById("inputPesertaId");
      if (idInput) idInput.focus();
    }, 100);
  } else {
    // Selalu arahkan ke halaman login admin
    openAdminLogin();
  }
}

// ========================================================
// 3.5. ADMIN LOGIN & AUTHENTICATION HANDLERS
// ========================================================
function handleAdminLoginSubmit(event) {
  event.preventDefault();

  const userField = document.getElementById("inputAdminUsername");
  const passField = document.getElementById("inputAdminPassword");
  const errorAlert = document.getElementById("loginErrorAlert");
  const errorMsg = document.getElementById("loginErrorMessage");

  const inputUsername = userField.value.trim().toLowerCase();
  const inputPassword = passField.value;

  if (inputUsername === ADMIN_CREDENTIALS.username && inputPassword === ADMIN_CREDENTIALS.password) {
    // Login success
    isAdminAuthenticated = true;
    sessionStorage.setItem("halacoding_admin_session", "true");

    if (errorAlert) errorAlert.style.display = "none";
    passField.value = "";

    // Show admin dashboard
    const viewAdminLogin = document.getElementById("viewAdminLogin");
    const viewAdmin = document.getElementById("viewAdmin");
    if (viewAdminLogin) viewAdminLogin.style.display = "none";
    if (viewAdmin) viewAdmin.style.display = "block";

    renderAdminDashboard();
  } else {
    // Login failed
    if (errorAlert) {
      errorAlert.style.display = "flex";
      errorMsg.textContent = "Username atau password salah! Silakan coba lagi.";
      // Re-trigger shake animation
      errorAlert.classList.remove("alert-login-error");
      void errorAlert.offsetWidth; // trigger reflow
      errorAlert.classList.add("alert-login-error");
    }
    passField.select();
  }
}

function toggleAdminPasswordVisibility() {
  const passInput = document.getElementById("inputAdminPassword");
  const eyeIcon = document.getElementById("eyeIcon");
  if (!passInput || !eyeIcon) return;

  if (passInput.type === "password") {
    passInput.type = "text";
    passInput.style.letterSpacing = "0.02em";
    eyeIcon.innerHTML = `
      <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
      <line x1="1" y1="1" x2="23" y2="23"></line>
    `;
  } else {
    passInput.type = "password";
    passInput.style.letterSpacing = "0.12em";
    eyeIcon.innerHTML = `
      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
      <circle cx="12" cy="12" r="3"></circle>
    `;
  }
}


function handleAdminLogout() {
  if (confirm("Apakah Anda yakin ingin keluar dari sesi Admin?")) {
    isAdminAuthenticated = false;
    sessionStorage.removeItem("halacoding_admin_session");
    
    // Switch to Peserta
    switchRole("peserta");
  }
}

// ========================================================
// 4. PESERTA LOOKUP BY ID
// ========================================================
function fillQuickId(id) {
  const input = document.getElementById("inputPesertaId");
  input.value = id;
  handleIdLookup();
}

function handleIdLookup() {
  const input = document.getElementById("inputPesertaId");
  const query = input.value.trim().toUpperCase();

  const emptyState = document.getElementById("emptyStateLookup");
  const profileCard = document.getElementById("profileCard");
  const detailBox = document.getElementById("pesertaDetailBox");
  const feedbackSection = document.getElementById("feedbackSection");
  const helperText = document.getElementById("idHelperText");

  if (!query) {
    resetFormPeserta();
    return;
  }

  // Find in master database
  const match = masterPeserta.find(p => p.id.toUpperCase() === query);

  if (match) {
    currentMatchedPeserta = match;
    detailBox.className = "peserta-detail-box state-found";
    emptyState.style.display = "none";
    profileCard.style.display = "block";
    feedbackSection.style.display = "block";

    // Populate data
    document.getElementById("profileNama").textContent = match.nama;
    document.getElementById("profileIdBadge").textContent = match.id;
    document.getElementById("profileTrack").textContent = match.track;
    const waEl = document.getElementById("profileWhatsapp") || document.getElementById("profileEmail");
    if (waEl) waEl.textContent = match.whatsapp || match.email || "-";
    document.getElementById("profileInstansi").textContent = match.instansi;
    document.getElementById("profileMentor").textContent = match.mentor;
    document.getElementById("profileBatch").textContent = match.batch;

    // Render avatar with initials
    const avatarEl = document.getElementById("profileAvatar");
    avatarEl.style.background = `linear-gradient(135deg, ${match.color} 0%, #1e1b4b 100%)`;
    avatarEl.style.color = "#ffffff";
    avatarEl.textContent = getInitials(match.nama);

    // Update 4 Pertemuan Tracker Header
    const trackerBatchEl = document.getElementById("trackerBatchName");
    if (trackerBatchEl) {
      trackerBatchEl.textContent = activeBatch;
    }

    // Periksa status kehadiran untuk 4 pertemuan di batch ini
    for (let p = 1; p <= 4; p++) {
      const pRecord = rekapPresensi.find(r => 
        r.idPeserta.toUpperCase() === match.id.toUpperCase() &&
        (r.batch || "Batch 1").toLowerCase() === activeBatch.toLowerCase() &&
        Number(r.pertemuan || 1) === p
      );
      const itemEl = document.getElementById(`trackerP${p}`);
      const statusEl = document.getElementById(`trackerStatusP${p}`);
      if (itemEl && statusEl) {
        if (pRecord) {
          itemEl.className = "tracker-item attended";
          statusEl.textContent = "✓ Hadir";
        } else {
          itemEl.className = "tracker-item pending";
          statusEl.textContent = "Belum Hadir";
        }
      }
    }

    // Check if participant has already attended the SELECTED meeting in active batch
    const currentMeetingRecord = rekapPresensi.find(r => 
      r.idPeserta.toUpperCase() === match.id.toUpperCase() &&
      (r.batch || "Batch 1").toLowerCase() === activeBatch.toLowerCase() &&
      Number(r.pertemuan || 1) === Number(selectedPesertaPertemuan)
    );
    const attendedAlert = document.getElementById("alreadyAttendedAlert");
    const prevPLabel = document.getElementById("prevAttendedPertemuanLabel");
    const prevTime = document.getElementById("prevAttendedTime");
    const statusBadge = document.getElementById("profileStatusBadge");
    const statusText = document.getElementById("profileStatusText");

    if (currentMeetingRecord) {
      if (attendedAlert) attendedAlert.style.display = "flex";
      if (prevPLabel) prevPLabel.textContent = `Pertemuan ${selectedPesertaPertemuan}`;
      if (prevTime) prevTime.textContent = currentMeetingRecord.timestamp;
      if (statusBadge) statusBadge.className = "status-indicator-badge attended";
      if (statusText) statusText.textContent = `Sudah Hadir (P${selectedPesertaPertemuan})`;
      // Prefill previous feedback if any
      const feedbackInput = document.getElementById("inputKritikSaran");
      if (feedbackInput && !feedbackInput.value && currentMeetingRecord.kritikSaran) {
        feedbackInput.value = currentMeetingRecord.kritikSaran;
      }
    } else {
      if (attendedAlert) attendedAlert.style.display = "none";
      if (statusBadge) statusBadge.className = "status-indicator-badge";
      if (statusText) statusText.textContent = `Siap Presensi (P${selectedPesertaPertemuan})`;
    }

    helperText.innerHTML = `<span style="color: var(--success); font-weight: 600;">✓ Data ditemukan: ${match.nama} (Siap untuk Pertemuan ${selectedPesertaPertemuan})</span>`;
  } else {
    currentMatchedPeserta = null;
    detailBox.className = "peserta-detail-box state-empty";
    profileCard.style.display = "none";
    feedbackSection.style.display = "none";
    emptyState.style.display = "flex";
    helperText.innerHTML = `<span style="color: var(--danger);">ID "${query}" tidak ditemukan dalam data peserta. Silakan periksa kembali.</span>`;
  }
}

function resetFormPeserta() {
  document.getElementById("inputPesertaId").value = "";
  document.getElementById("inputKritikSaran").value = "";
  currentMatchedPeserta = null;

  const emptyState = document.getElementById("emptyStateLookup");
  const profileCard = document.getElementById("profileCard");
  const detailBox = document.getElementById("pesertaDetailBox");
  const feedbackSection = document.getElementById("feedbackSection");
  const helperText = document.getElementById("idHelperText");

  detailBox.className = "peserta-detail-box state-empty";
  emptyState.style.display = "flex";
  profileCard.style.display = "none";
  feedbackSection.style.display = "none";
  helperText.textContent = "Ketik ID peserta untuk melihat detail data lengkap secara otomatis.";
  document.getElementById("inputPesertaId").focus();
}

// ========================================================
// 5. PRESENSI SUBMISSION & ANIMATION POPUP
// ========================================================
async function handlePresensiSubmit(event) {
  event.preventDefault();

  if (!currentMatchedPeserta) {
    alert("Silakan masukkan ID Peserta yang valid terlebih dahulu.");
    return;
  }

  const submitBtn = document.getElementById("btnSubmitPresensi");
  const originalBtnContent = submitBtn ? submitBtn.innerHTML : "";
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = `<span>⏳ Menyimpan presensi ke server...</span>`;
  }

  const kritikSaran = document.getElementById("inputKritikSaran").value.trim();
  const selectedRating = document.querySelector('input[name="ratingPelatihan"]:checked')?.value || "5";
  const nowFormatted = formatDateTime(new Date());

  // Check if existing record for this participant, batch, and meeting
  const existingIdx = rekapPresensi.findIndex(
    r => r.idPeserta.toUpperCase() === currentMatchedPeserta.id.toUpperCase() &&
         (r.batch || "Batch 1").toLowerCase() === activeBatch.toLowerCase() &&
         Number(r.pertemuan || 1) === Number(selectedPesertaPertemuan)
  );

  const attendanceData = {
    idPeserta: currentMatchedPeserta.id,
    nama: currentMatchedPeserta.nama,
    whatsapp: currentMatchedPeserta.whatsapp || currentMatchedPeserta.email || "-",
    instansi: currentMatchedPeserta.instansi,
    track: currentMatchedPeserta.track,
    batch: activeBatch,
    pertemuan: Number(selectedPesertaPertemuan),
    materi: `Pertemuan ${selectedPesertaPertemuan}`,
    timestamp: nowFormatted,
    kritikSaran: kritikSaran || "Belum ada catatan kritik/saran.",
    rating: selectedRating,
    status: "HADIR"
  };

  // Kirim data presensi ke server hosting agar otomatis tersimpan di database dan sinkron ke Laptop Admin
  if (IS_SERVER_ENV) {
    try {
      const res = await fetch(`${PRESENSI_API_URL}?action=submit_absen`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(attendanceData)
      });
      const result = await res.json();
      if (!res.ok || result.status !== "success") {
        console.warn("Respon server presensi:", result);
      } else {
        console.log("Presensi berhasil disimpan ke server:", result.storage);
      }
    } catch (err) {
      console.warn("Gagal kirim absen ke server:", err);
    }
  }

  if (existingIdx !== -1) {
    // Update existing
    rekapPresensi[existingIdx] = attendanceData;
  } else {
    // Push new
    rekapPresensi.unshift(attendanceData);
  }

  saveRekapData();

  if (submitBtn) {
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalBtnContent;
  }

  // Play pleasant chime
  playCelebrationChime();

  // Show Success Modal with animated checkmark and full celebration
  showSuccessModal(attendanceData);
}

let lastConfirmedPresensi = null;

function showSuccessModal(data) {
  lastConfirmedPresensi = data;
  const modal = document.getElementById("modalSuccess");
  if (!modal) return;

  const elNama = document.getElementById("modalPesertaNama");
  if (elNama) elNama.textContent = data.nama;

  const elId = document.getElementById("modalPesertaId");
  if (elId) elId.textContent = data.idPeserta;

  const elTrack = document.getElementById("modalPesertaTrack");
  if (elTrack) elTrack.textContent = data.track;

  const elInstansi = document.getElementById("modalPesertaInstansi");
  if (elInstansi) elInstansi.textContent = data.instansi || "Peserta Halacoding";

  const elTimestamp = document.getElementById("modalTimestamp");
  if (elTimestamp) elTimestamp.textContent = data.timestamp;

  // Batch & Pertemuan details in pass
  const elBatchPertemuan = document.getElementById("modalBatchPertemuan");
  if (elBatchPertemuan) {
    elBatchPertemuan.textContent = `${data.batch || activeBatch} • Pertemuan ${data.pertemuan || selectedPesertaPertemuan}`;
  }

  const elProgress = document.getElementById("modalProgressHadir");
  if (elProgress) {
    const attendedCount = [1, 2, 3, 4].filter(p => 
      rekapPresensi.some(r => 
        r.idPeserta.toUpperCase() === data.idPeserta.toUpperCase() &&
        (r.batch || "Batch 1").toLowerCase() === (data.batch || activeBatch).toLowerCase() &&
        Number(r.pertemuan || 1) === p
      )
    ).length;
    elProgress.textContent = `${attendedCount} / 4 Sesi (${Math.round((attendedCount / 4) * 100)}%)`;
  }

  const feedbackRow = document.getElementById("modalFeedbackRow");
  if (feedbackRow) {
    if (data.kritikSaran && data.kritikSaran !== "Belum ada catatan kritik/saran.") {
      feedbackRow.style.display = "block";
      const elFbText = document.getElementById("modalFeedbackText");
      if (elFbText) elFbText.textContent = data.kritikSaran;
    } else {
      feedbackRow.style.display = "none";
    }
  }

  modal.classList.add("open");

  // Play pleasant chime
  playCelebrationChime();

  // Trigger full-screen celebratory confetti burst
  launchConfetti();
}

function copyAttendanceProof() {
  if (!lastConfirmedPresensi) return;
  const text = `*BUKTI PRESENSI RESMI - PELATIHAN HALACODING*\n` +
    `━━━━━━━━━━━━━━━━━━━━━━━━━\n` +
    `👤 *Nama:* ${lastConfirmedPresensi.nama}\n` +
    `🆔 *ID Peserta:* ${lastConfirmedPresensi.idPeserta}\n` +
    `📚 *Program:* ${lastConfirmedPresensi.track}\n` +
    `🏛️ *Asal Institusi:* ${lastConfirmedPresensi.instansi || "-"}\n` +
    `⏰ *Waktu Catat:* ${lastConfirmedPresensi.timestamp}\n` +
    `✅ *Status:* TERVERIFIKASI HADIR (Database Server)\n` +
    `━━━━━━━━━━━━━━━━━━━━━━━━━\n` +
    `Sistem Presensi Halacoding: https://portofolio.corefive.my.id/halacoding/`;

  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text).then(() => {
      showToastNotification("Membuka WhatsApp dan bukti tersalin...", "📲");
    }).catch(() => {
      fallbackCopyText(text);
    });
  } else {
    fallbackCopyText(text);
  }

  // Arahkan langsung ke WhatsApp dengan teks bukti terisi
  const waUrl = "https://api.whatsapp.com/send?text=" + encodeURIComponent(text);
  const win = window.open(waUrl, "_blank");
  if (!win || win.closed || typeof win.closed === "undefined") {
    window.location.href = waUrl;
  }
}

function sendAttendanceProofWA() {
  copyAttendanceProof();
}

function fallbackCopyText(text) {
  try {
    const ta = document.createElement("textarea");
    ta.value = text;
    ta.style.position = "fixed";
    ta.style.left = "-9999px";
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    document.execCommand("copy");
    document.body.removeChild(ta);
    showToastNotification("Bukti presensi tersalin! Siap dikirim ke WhatsApp.", "📋");
  } catch (err) {
    showToastNotification("Gagal menyalin otomatis. Silakan screenshot layar ini.", "ℹ️");
  }
}

function closeSuccessModal() {
  const modal = document.getElementById("modalSuccess");
  if (modal) modal.classList.remove("open");
  resetFormPeserta();
}

function closeModalOnBackdrop(event) {
  if (event.target === document.getElementById("modalSuccess")) {
    closeSuccessModal();
  }
}

function goToAdminRekap() {
  closeSuccessModal();
  switchRole("admin");
}

// ========================================================
// 6. ADMIN DASHBOARD & REKAP
// ========================================================
function updateTrackFilterOptions() {
  const select = document.getElementById("filterTrack");
  if (!select) return;
  const currentVal = select.value;
  const uniqueTracks = [...new Set(masterPeserta.map(p => p.track).filter(Boolean))];
  const tracksToUse = uniqueTracks.length > 0 
    ? uniqueTracks 
    : ["Web Programing Dasar", "Fullstack Web Developer", "Mobile App Development"];
  select.innerHTML = `<option value="ALL">Semua Kelas / Track</option>` +
    tracksToUse.map(t => `<option value="${escapeHtml(t)}">${escapeHtml(t)}</option>`).join("");
  if (tracksToUse.includes(currentVal)) {
    select.value = currentVal;
  } else {
    select.value = "ALL";
  }
}

function renderAdminDashboard() {
  updateTrackFilterOptions();
  renderAdminStats();
  renderAdminTable();
}

function renderAdminStats() {
  const totalTerdaftar = masterPeserta.length;
  const filterBatchVal = document.getElementById("filterBatch")?.value || activeBatch;

  // Rekap data sesuai batch yang difilter
  const batchRecords = rekapPresensi.filter(r => 
    filterBatchVal === "all" || (r.batch || "Batch 1").toLowerCase() === filterBatchVal.toLowerCase()
  );

  // Hadir di sesi pertemuan aktif hari ini
  const hadirSesiAktif = masterPeserta.filter(p => 
    batchRecords.some(r => 
      r.idPeserta.toUpperCase() === p.id.toUpperCase() && 
      Number(r.pertemuan || 1) === Number(activePertemuan)
    )
  ).length;

  const belumHadirSesiAktif = Math.max(0, totalTerdaftar - hadirSesiAktif);

  const totalFeedbackWithText = batchRecords.filter(
    r => r.kritikSaran && r.kritikSaran !== "Belum ada catatan kritik/saran." && r.kritikSaran !== "-"
  ).length;

  const elTotal = document.getElementById("statTotalPeserta");
  const elHadir = document.getElementById("statTotalHadir");
  const elBelum = document.getElementById("statBelumHadir");
  const elFeedback = document.getElementById("statTotalFeedback");

  if (elTotal) elTotal.textContent = totalTerdaftar;
  if (elHadir) elHadir.textContent = hadirSesiAktif;
  if (elBelum) elBelum.textContent = belumHadirSesiAktif;
  if (elFeedback) elFeedback.textContent = totalFeedbackWithText;
}

function renderAdminTable() {
  const tbody = document.getElementById("adminTableBody");
  const tableHead = document.getElementById("adminTableHead");
  const emptyState = document.getElementById("tableEmptyState");
  const searchQuery = (document.getElementById("adminSearchInput")?.value || "").toLowerCase().trim();
  const filterTrack = document.getElementById("filterTrack")?.value || "ALL";
  const filterStatus = document.getElementById("filterStatus")?.value || "ALL";
  const filterBatchVal = document.getElementById("filterBatch")?.value || activeBatch;
  const filterPertemuanVal = document.getElementById("filterPertemuan")?.value || "matrix";

  if (!tbody) return;

  // ==========================================
  // MODE 1: MATRIKS 4 PERTEMUAN (P1 - P4)
  // ==========================================
  if (filterPertemuanVal === "matrix") {
    if (tableHead) {
      tableHead.innerHTML = `
        <tr>
          <th style="width: 48px; text-align: center;">No</th>
          <th style="width: 110px;">ID Peserta</th>
          <th>Peserta</th>
          <th>Kelas & Instansi</th>
          <th style="text-align: center; width: 85px;">P1</th>
          <th style="text-align: center; width: 85px;">P2</th>
          <th style="text-align: center; width: 85px;">P3</th>
          <th style="text-align: center; width: 85px;">P4</th>
          <th style="text-align: center; width: 140px;">Total Hadir</th>
          <th style="text-align: center; width: 100px;">Aksi</th>
        </tr>
      `;
    }

    let list = masterPeserta.map(p => {
      const pRecords = [1, 2, 3, 4].map(pNum => {
        return rekapPresensi.find(r => 
          r.idPeserta.toUpperCase() === p.id.toUpperCase() &&
          (filterBatchVal === "all" || (r.batch || "Batch 1").toLowerCase() === filterBatchVal.toLowerCase()) &&
          Number(r.pertemuan || 1) === pNum
        );
      });
      const attendedCount = pRecords.filter(Boolean).length;
      return {
        ...p,
        pRecords,
        attendedCount,
        percent: Math.round((attendedCount / 4) * 100)
      };
    });

    // Filter Track
    if (filterTrack !== "ALL") {
      list = list.filter(item => item.track === filterTrack || item.instansi === filterTrack);
    }

    // Filter Status (HADIR: minimal hadir 1x, BELUM: belum pernah hadir sama sekali)
    if (filterStatus === "HADIR") {
      list = list.filter(item => item.attendedCount > 0);
    } else if (filterStatus === "BELUM") {
      list = list.filter(item => item.attendedCount === 0);
    }

    // Filter Search
    if (searchQuery) {
      list = list.filter(item => 
        item.nama.toLowerCase().includes(searchQuery) ||
        item.id.toLowerCase().includes(searchQuery) ||
        (item.whatsapp && item.whatsapp.toLowerCase().includes(searchQuery)) ||
        item.instansi.toLowerCase().includes(searchQuery) ||
        item.track.toLowerCase().includes(searchQuery)
      );
    }

    tbody.innerHTML = "";

    if (list.length === 0) {
      if (emptyState) emptyState.style.display = "block";
      return;
    }
    if (emptyState) emptyState.style.display = "none";

    list.forEach((item, index) => {
      const tr = document.createElement("tr");

      // Bangun tombol pill untuk masing-masing P1, P2, P3, P4
      const pCellsHtml = [1, 2, 3, 4].map((pNum, idx) => {
        const rec = item.pRecords[idx];
        if (rec) {
          const escapedFeedback = encodeURIComponent(rec.kritikSaran || "-");
          const escapedNama = encodeURIComponent(item.nama);
          const escapedMeta = encodeURIComponent(`${item.id} • Pertemuan ${pNum} (${rec.timestamp})`);
          const ratingText = getRatingText(rec.rating);
          return `
            <td style="text-align: center;">
              <span class="matrix-pill attended" 
                    title="Hadir pada: ${rec.timestamp}&#10;Klik untuk lihat masukan"
                    onclick="openFeedbackDetail('${escapedNama}', '${escapedMeta}', '${escapedFeedback}', '${encodeURIComponent(ratingText)}', '${item.color}')">
                ✓ Hadir
              </span>
            </td>
          `;
        } else {
          return `
            <td style="text-align: center;">
              <span class="matrix-pill absent" 
                    title="Belum Hadir - Klik untuk tandai Hadir P${pNum}"
                    onclick="quickAbsenMatriks('${item.id}', ${pNum})">
                + P${pNum}
              </span>
            </td>
          `;
        }
      }).join("");

      // Kolom Total Sesi & Progress Bar
      const totalColHtml = `
        <td style="text-align: center;">
          <div class="matrix-total-badge">
            <span class="matrix-total-num">${item.attendedCount} / 4 (${item.percent}%)</span>
            <div class="matrix-progress-track">
              <div class="matrix-progress-fill" style="width: ${item.percent}%; ${item.percent === 100 ? 'background: #10b981;' : item.percent >= 50 ? 'background: #3b82f6;' : 'background: #f59e0b;'}"></div>
            </div>
          </div>
        </td>
      `;

      // Aksi: Hadirkan ke Sesi Aktif Hari Ini (atau Hapus jika sudah hadir)
      const isAttendedActiveSession = !!item.pRecords[activePertemuan - 1];
      const targetBatchAction = filterBatchVal === "all" ? activeBatch : filterBatchVal;
      const actionHtml = isAttendedActiveSession
        ? `<button class="table-action-btn" onclick="hapusPresensiSingle('${item.id}', '${escapeHtml(targetBatchAction)}', ${activePertemuan})" title="Batalkan presensi P${activePertemuan}">Batal P${activePertemuan}</button>`
        : `<button class="table-action-btn" onclick="quickAbsenMatriks('${item.id}', ${activePertemuan})" title="Tandai hadir pada P${activePertemuan}">+ P${activePertemuan}</button>`;

      tr.innerHTML = `
        <td style="text-align: center;">${index + 1}</td>
        <td><strong>${item.id}</strong></td>
        <td>
          <div class="user-cell">
            <div class="user-avatar-tiny" style="background:${item.color}20; color:${item.color}">
              ${getInitials(item.nama)}
            </div>
            <div>
              <div class="user-meta-name">${escapeHtml(item.nama)}</div>
              <div class="user-meta-whatsapp">📱 ${escapeHtml(item.whatsapp || '-')}</div>
            </div>
          </div>
        </td>
        <td>
          <div style="font-weight: 600; font-size: 0.85rem;">${escapeHtml(item.track)}</div>
          <div style="font-size: 0.75rem; color: var(--text-muted);">${escapeHtml(item.instansi)}</div>
        </td>
        ${pCellsHtml}
        ${totalColHtml}
        <td style="text-align: center;">${actionHtml}</td>
      `;
      tbody.appendChild(tr);
    });

    return;
  }

  // ==========================================
  // MODE 2: PERTEMUAN TERTENTU (1, 2, 3, 4) ATAU SEMUA RIWAYAT
  // ==========================================
  const selectedPNum = Number(filterPertemuanVal);
  const isSingleMeetingMode = selectedPNum >= 1 && selectedPNum <= 4;

  if (tableHead) {
    tableHead.innerHTML = `
      <tr>
        <th style="width: 48px; text-align: center;">No</th>
        <th style="width: 110px;">ID Peserta</th>
        <th>Peserta</th>
        <th>${isSingleMeetingMode ? 'Sesi' : 'Batch & Sesi'}</th>
        <th>Kelas & Instansi</th>
        <th>Waktu Presensi</th>
        <th style="text-align: center;">Status</th>
        <th>Kritik & Saran</th>
        <th style="text-align: center;">Aksi</th>
      </tr>
    `;
  }

  let list = masterPeserta.map(p => {
    // Cari record presensi
    let record = null;
    if (isSingleMeetingMode) {
      record = rekapPresensi.find(r => 
        r.idPeserta.toUpperCase() === p.id.toUpperCase() &&
        (filterBatchVal === "all" || (r.batch || "Batch 1").toLowerCase() === filterBatchVal.toLowerCase()) &&
        Number(r.pertemuan || 1) === selectedPNum
      );
    } else {
      record = rekapPresensi.find(r => 
        r.idPeserta.toUpperCase() === p.id.toUpperCase() &&
        (filterBatchVal === "all" || (r.batch || "Batch 1").toLowerCase() === filterBatchVal.toLowerCase())
      );
    }

    return {
      id: p.id,
      nama: p.nama,
      whatsapp: p.whatsapp || p.email || "-",
      instansi: p.instansi,
      track: p.track,
      color: p.color,
      isAttended: !!record,
      batch: record ? (record.batch || activeBatch) : activeBatch,
      pertemuan: isSingleMeetingMode ? selectedPNum : (record ? Number(record.pertemuan || 1) : activePertemuan),
      timestamp: record ? record.timestamp : "-",
      kritikSaran: record ? record.kritikSaran : "-",
      rating: record ? record.rating : null
    };
  });

  // Apply track filter
  if (filterTrack !== "ALL") {
    list = list.filter(item => item.track === filterTrack || item.instansi === filterTrack);
  }

  // Apply status filter
  if (filterStatus === "HADIR") {
    list = list.filter(item => item.isAttended);
  } else if (filterStatus === "BELUM") {
    list = list.filter(item => !item.isAttended);
  }

  // Apply search query
  if (searchQuery) {
    list = list.filter(item => 
      item.nama.toLowerCase().includes(searchQuery) ||
      item.id.toLowerCase().includes(searchQuery) ||
      (item.whatsapp && item.whatsapp.toLowerCase().includes(searchQuery)) ||
      item.instansi.toLowerCase().includes(searchQuery) ||
      item.track.toLowerCase().includes(searchQuery)
    );
  }

  tbody.innerHTML = "";

  if (list.length === 0) {
    if (emptyState) emptyState.style.display = "block";
    return;
  }
  if (emptyState) emptyState.style.display = "none";

  list.forEach((item, index) => {
    const tr = document.createElement("tr");

    const statusBadgeHtml = item.isAttended
      ? `<span class="badge-status badge-hadir">✓ Hadir</span>`
      : `<span class="badge-status badge-belum">Belum</span>`;

    let feedbackHtml = `<span style="color: var(--text-light);">-</span>`;
    if (item.isAttended && item.kritikSaran && item.kritikSaran !== "-") {
      const escapedFeedback = encodeURIComponent(item.kritikSaran);
      const escapedNama = encodeURIComponent(item.nama);
      const escapedMeta = encodeURIComponent(`${item.id} • Pertemuan ${item.pertemuan}`);
      const ratingText = getRatingText(item.rating);
      feedbackHtml = `
        <div>
          <div class="feedback-preview-text" title="${item.kritikSaran}">${escapeHtml(item.kritikSaran)}</div>
          <button class="feedback-btn-link" onclick="openFeedbackDetail('${escapedNama}', '${escapedMeta}', '${escapedFeedback}', '${encodeURIComponent(ratingText)}', '${item.color}')">
            Lihat Masukan ↗
          </button>
        </div>
      `;
    }

    const actionHtml = item.isAttended
      ? `<button class="table-action-btn" onclick="hapusPresensiSingle('${item.id}', '${escapeHtml(item.batch)}', ${item.pertemuan})" title="Batalkan presensi">Batal</button>`
      : `<button class="table-action-btn" onclick="quickAbsenMatriks('${item.id}', ${item.pertemuan})" title="Tandai hadir">+ Hadir</button>`;

    tr.innerHTML = `
      <td style="text-align: center;">${index + 1}</td>
      <td><strong>${item.id}</strong></td>
      <td>
        <div class="user-cell">
          <div class="user-avatar-tiny" style="background:${item.color}20; color:${item.color}">
            ${getInitials(item.nama)}
          </div>
          <div>
            <div class="user-meta-name">${escapeHtml(item.nama)}</div>
            <div class="user-meta-whatsapp">📱 ${escapeHtml(item.whatsapp || '-')}</div>
          </div>
        </div>
      </td>
      <td>
        <div style="font-weight: 700; color: #1e293b; font-size: 0.85rem;">${escapeHtml(item.batch)}</div>
        <div style="font-size: 0.75rem; color: #0284c7; font-weight: 600;">Pertemuan ${item.pertemuan}</div>
      </td>
      <td>
        <div style="font-weight: 600; font-size: 0.85rem;">${escapeHtml(item.track)}</div>
        <div style="font-size: 0.75rem; color: var(--text-muted);">${escapeHtml(item.instansi)}</div>
      </td>
      <td style="font-size: 0.825rem; font-weight: 500;">${item.timestamp}</td>
      <td style="text-align: center;">${statusBadgeHtml}</td>
      <td>${feedbackHtml}</td>
      <td style="text-align: center;">${actionHtml}</td>
    `;
    tbody.appendChild(tr);
  });
}

// ========================================================
// 6.2. ADMIN ACTIVE SESSION MANAGEMENT & ACTIONS
// ========================================================
async function handleAdminActiveSessionChange() {
  const sel = document.getElementById("adminActiveBatchSelect");
  if (!sel) return;
  activeBatch = sel.value;
  await saveSessionSettingsToServer(activeBatch, activePertemuan);
  updateSessionUI();
  renderAdminDashboard();
  showToastNotification(`Sesi aktif dialihkan ke ${activeBatch} (Pertemuan ${activePertemuan})`, "⚙️");
}

async function setAdminActivePertemuan(pNum) {
  activePertemuan = Number(pNum);
  selectedPesertaPertemuan = activePertemuan;
  await saveSessionSettingsToServer(activeBatch, activePertemuan);
  updateSessionUI();
  renderAdminDashboard();
  showToastNotification(`Pertemuan aktif hari ini diatur ke Pertemuan ${activePertemuan}`, "📅");
}

async function saveSessionSettingsToServer(batch, pertemuan) {
  if (IS_SERVER_ENV) {
    try {
      await fetch(`${PRESENSI_API_URL}?action=set_active_session`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ batch, pertemuan })
      });
    } catch (e) {
      console.warn("Gagal simpan session settings ke server:", e);
    }
  }
}

async function promptAddNewBatch() {
  const nextNum = availableBatches.length + 1;
  const name = prompt(`Masukkan nama Batch / Gelombang baru:`, `Batch ${nextNum}`);
  if (!name || !name.trim()) return;
  const trimmed = name.trim();
  if (availableBatches.map(b => b.toLowerCase()).includes(trimmed.toLowerCase())) {
    alert(`Batch "${trimmed}" sudah ada dalam daftar.`);
    return;
  }

  availableBatches.push(trimmed);
  activeBatch = trimmed;
  activePertemuan = 1;
  selectedPesertaPertemuan = 1;

  if (IS_SERVER_ENV) {
    try {
      await fetch(`${PRESENSI_API_URL}?action=add_batch`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ batch_name: trimmed, keterangan: `Pelatihan ${trimmed}` })
      });
      await saveSessionSettingsToServer(activeBatch, activePertemuan);
    } catch (e) {
      console.warn("Gagal simpan batch baru ke server:", e);
    }
  }

  updateSessionUI();
  renderAdminDashboard();
  showToastNotification(`Batch "${trimmed}" berhasil dibuat dan diaktifkan!`, "🎉");
}

// Quick action to attend from matrix / admin
function quickAbsenMatriks(id, pertemuanNum) {
  const match = masterPeserta.find(p => p.id === id);
  if (!match) return;

  const fBatch = document.getElementById("filterBatch")?.value || activeBatch;
  const targetBatch = fBatch === "all" ? activeBatch : fBatch;
  const p = Number(pertemuanNum) || activePertemuan;

  const newRecord = {
    idPeserta: match.id,
    nama: match.nama,
    whatsapp: match.whatsapp || match.email || "-",
    instansi: match.instansi,
    track: match.track,
    batch: targetBatch,
    pertemuan: p,
    materi: `Pertemuan ${p}`,
    timestamp: formatDateTime(new Date()),
    kritikSaran: "Tercatat langsung oleh Admin",
    rating: "5",
    status: "HADIR"
  };

  const existingIdx = rekapPresensi.findIndex(r => 
    r.idPeserta.toUpperCase() === id.toUpperCase() &&
    (r.batch || "Batch 1").toLowerCase() === targetBatch.toLowerCase() &&
    Number(r.pertemuan || 1) === p
  );

  if (existingIdx !== -1) {
    rekapPresensi[existingIdx] = newRecord;
  } else {
    rekapPresensi.unshift(newRecord);
  }

  saveRekapData();
  renderAdminDashboard();
  showToastNotification(`Peserta ${match.nama} ditandai hadir pada P${p}!`, "✅");

  if (IS_SERVER_ENV) {
    fetch(`${PRESENSI_API_URL}?action=submit_absen`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(newRecord)
    }).catch(err => console.warn("Gagal absen di server:", err));
  }
}

function quickAbsenDariAdmin(id) {
  quickAbsenMatriks(id, activePertemuan);
}

// Remove single attendance (with batch & pertemuan awareness)
function hapusPresensiSingle(id, batchName, pertemuanNum) {
  const b = batchName || activeBatch;
  const p = pertemuanNum ? Number(pertemuanNum) : null;
  const msg = p 
    ? `Batalkan presensi untuk peserta ID ${id} pada ${b} Pertemuan ${p}?`
    : `Batalkan riwayat presensi peserta ID ${id} pada ${b}?`;

  if (confirm(msg)) {
    rekapPresensi = rekapPresensi.filter(r => {
      const matchId = r.idPeserta.toUpperCase() === id.toUpperCase();
      const matchBatch = (r.batch || "Batch 1").toLowerCase() === b.toLowerCase();
      const matchP = p === null || Number(r.pertemuan || 1) === p;
      return !(matchId && matchBatch && matchP);
    });

    saveRekapData();
    renderAdminDashboard();
    showToastNotification(`Presensi ID ${id} berhasil dibatalkan.`, "🗑️");

    if (IS_SERVER_ENV) {
      let url = `${PRESENSI_API_URL}?action=delete_absen&id=${encodeURIComponent(id)}&batch=${encodeURIComponent(b)}`;
      if (p !== null) url += `&pertemuan=${p}`;
      fetch(url).catch(err => console.warn("Gagal hapus di server:", err));
    }
  }
}

// Reset/Clear all participants (Kosongkan Peserta)
function confirmResetPeserta() {
  if (masterPeserta.length === 0) {
    alert("Data peserta saat ini sudah kosong.");
    return;
  }
  if (confirm(`Apakah Anda yakin ingin MENGOSONGKAN SELURUH DATA PESERTA (${masterPeserta.length} orang)?\n\nTindakan ini juga akan mereset rekap presensi. Seluruh master peserta akan dihapus.`)) {
    masterPeserta = [];
    rekapPresensi = [];
    localStorage.setItem(STORAGE_PESERTA_KEY, JSON.stringify([]));
    localStorage.setItem(STORAGE_REKAP_KEY, JSON.stringify([]));
    updateBadgeCounts();
    renderAdminDashboard();
    showToastNotification("Seluruh data peserta telah berhasil dikosongkan!", "🗑️");

    if (IS_SERVER_ENV) {
      fetch(`${PRESENSI_API_URL}?action=reset_rekap`, { method: "POST" })
        .catch(err => console.warn("Gagal reset di server:", err));
    }
  }
}

// Reset all attendance
function confirmResetData() {
  if (rekapPresensi.length === 0) {
    alert("Data rekap presensi saat ini masih kosong.");
    return;
  }
  const fBatch = document.getElementById("filterBatch")?.value || activeBatch;
  const isAll = fBatch === "all";
  const promptMsg = isAll
    ? "Apakah Anda yakin ingin MENGHAPUS SEMUA DATA REKAP presensi seluruh batch? Tindakan ini tidak dapat dibatalkan."
    : `Apakah Anda yakin ingin MENGHAPUS DATA REKAP presensi untuk ${fBatch}? Tindakan ini tidak dapat dibatalkan.`;

  if (confirm(promptMsg)) {
    if (isAll) {
      rekapPresensi = [];
    } else {
      rekapPresensi = rekapPresensi.filter(r => (r.batch || "Batch 1").toLowerCase() !== fBatch.toLowerCase());
    }
    localStorage.setItem(STORAGE_REKAP_KEY, JSON.stringify(rekapPresensi));
    updateBadgeCounts();
    renderAdminDashboard();
    showToastNotification(`Data rekap presensi ${isAll ? 'seluruh batch' : fBatch} telah berhasil direset!`, "🧹");

    if (IS_SERVER_ENV) {
      const payload = isAll ? {} : { batch: fBatch };
      fetch(`${PRESENSI_API_URL}?action=reset_rekap`, { 
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      }).catch(err => console.warn("Gagal reset di server:", err));
    }
  }
}

// Open Feedback Detail Modal
function openFeedbackDetail(namaEncoded, metaEncoded, feedbackEncoded, ratingEncoded, color) {
  const nama = decodeURIComponent(namaEncoded);
  const meta = decodeURIComponent(metaEncoded);
  const feedback = decodeURIComponent(feedbackEncoded);
  const rating = decodeURIComponent(ratingEncoded);

  document.getElementById("detailPesertaNama").textContent = nama;
  document.getElementById("detailPesertaMeta").textContent = meta;
  document.getElementById("detailFeedbackContent").textContent = feedback;
  document.getElementById("detailRatingValue").textContent = rating;

  const avatar = document.getElementById("detailAvatarSmall");
  avatar.style.background = color ? `${color}20` : "var(--primary-light)";
  avatar.style.color = color || "var(--primary)";
  avatar.textContent = getInitials(nama);

  document.getElementById("modalDetailFeedback").classList.add("open");
}

function closeFeedbackDetailModalDirect() {
  document.getElementById("modalDetailFeedback").classList.remove("open");
}

function closeFeedbackDetailModal(event) {
  if (event.target === document.getElementById("modalDetailFeedback")) {
    closeFeedbackDetailModalDirect();
  }
}

// ========================================================
// 6.4. EXPORT DATA KE EXCEL MULTI-SHEET & CSV
// ========================================================
function exportDataExcel() {
  if (rekapPresensi.length === 0 && masterPeserta.length === 0) {
    alert("Belum ada data untuk diekspor.");
    return;
  }

  if (typeof XLSX === "undefined") {
    exportDataCSV();
    return;
  }

  const targetBatch = document.getElementById("filterBatch")?.value || activeBatch;
  const workbook = XLSX.utils.book_new();

  // ----------------------------------------------------
  // SHEET 1: MATRIKS 4 PERTEMUAN (P1 - P4)
  // ----------------------------------------------------
  const matrixRows = masterPeserta.map((p, idx) => {
    const pRecords = [1, 2, 3, 4].map(pNum => 
      rekapPresensi.find(r => 
        r.idPeserta.toUpperCase() === p.id.toUpperCase() &&
        (targetBatch === "all" || (r.batch || "Batch 1").toLowerCase() === targetBatch.toLowerCase()) &&
        Number(r.pertemuan || 1) === pNum
      )
    );
    const totalHadir = pRecords.filter(Boolean).length;
    const percent = Math.round((totalHadir / 4) * 100);

    return {
      "No": idx + 1,
      "ID Peserta": p.id,
      "Nama Peserta": p.nama,
      "No. WhatsApp": p.whatsapp || p.email || "-",
      "Track Pelatihan": p.track,
      "Instansi": p.instansi,
      "Batch": targetBatch === "all" ? (p.batch || "Batch 1") : targetBatch,
      "Pertemuan 1": pRecords[0] ? `HADIR (${pRecords[0].timestamp})` : "TIDAK HADIR",
      "Pertemuan 2": pRecords[1] ? `HADIR (${pRecords[1].timestamp})` : "TIDAK HADIR",
      "Pertemuan 3": pRecords[2] ? `HADIR (${pRecords[2].timestamp})` : "TIDAK HADIR",
      "Pertemuan 4": pRecords[3] ? `HADIR (${pRecords[3].timestamp})` : "TIDAK HADIR",
      "Total Hadir (dari 4)": totalHadir,
      "Persentase Kehadiran (%)": `${percent}%`,
      "Status Kelulusan": percent >= 75 ? "MEMENUHI SYARAT" : "BELUM LENGKAP"
    };
  });

  const wsMatrix = XLSX.utils.json_to_sheet(matrixRows);
  wsMatrix["!cols"] = [
    { wch: 6 },  // No
    { wch: 14 }, // ID
    { wch: 28 }, // Nama
    { wch: 18 }, // WA
    { wch: 30 }, // Track
    { wch: 28 }, // Instansi
    { wch: 14 }, // Batch
    { wch: 25 }, // P1
    { wch: 25 }, // P2
    { wch: 25 }, // P3
    { wch: 25 }, // P4
    { wch: 16 }, // Total Hadir
    { wch: 22 }, // %
    { wch: 20 }  // Status Kelulusan
  ];
  XLSX.utils.book_append_sheet(workbook, wsMatrix, "Matriks 4 Pertemuan");

  // ----------------------------------------------------
  // SHEET 2: RIWAYAT DETAIL PRESENSI & ULASAN
  // ----------------------------------------------------
  const filteredRekap = rekapPresensi.filter(r => 
    targetBatch === "all" || (r.batch || "Batch 1").toLowerCase() === targetBatch.toLowerCase()
  );

  const detailRows = filteredRekap.map((r, idx) => ({
    "No": idx + 1,
    "ID Peserta": r.idPeserta,
    "Nama Peserta": r.nama,
    "No. WhatsApp": r.whatsapp || r.email || "-",
    "Track Pelatihan": r.track,
    "Instansi": r.instansi,
    "Batch": r.batch || "Batch 1",
    "Pertemuan": `Pertemuan ${r.pertemuan || 1}`,
    "Waktu Presensi": r.timestamp,
    "Kepuasan (Rating)": getRatingLabel(r.rating),
    "Kritik & Saran": r.kritikSaran || "-",
    "Status": r.status || "HADIR"
  }));

  const wsDetail = XLSX.utils.json_to_sheet(detailRows.length > 0 ? detailRows : [{ "Status": "Belum ada riwayat" }]);
  wsDetail["!cols"] = [
    { wch: 6 },  // No
    { wch: 14 }, // ID Peserta
    { wch: 26 }, // Nama Peserta
    { wch: 20 }, // No. WhatsApp
    { wch: 30 }, // Track Pelatihan
    { wch: 28 }, // Instansi
    { wch: 14 }, // Batch
    { wch: 16 }, // Pertemuan
    { wch: 24 }, // Waktu Presensi
    { wch: 22 }, // Kepuasan (Rating)
    { wch: 45 }, // Kritik & Saran
    { wch: 12 }  // Status
  ];
  XLSX.utils.book_append_sheet(workbook, wsDetail, "Riwayat Detail Masukan");

  const batchFileSuffix = targetBatch === "all" ? "Semua_Batch" : targetBatch.replace(/\s+/g, "_");
  XLSX.writeFile(workbook, `Rekap_Presensi_Halacoding_${batchFileSuffix}_${new Date().toISOString().slice(0, 10)}.xlsx`);
  showToastNotification("Data rekap presensi multi-pertemuan berhasil diekspor ke Excel!", "📥");
}

// Export data to CSV (Matriks format)
function exportDataCSV() {
  const targetBatch = document.getElementById("filterBatch")?.value || activeBatch;
  const headers = ["No", "ID Peserta", "Nama Peserta", "No. WhatsApp", "Track", "Instansi", "Batch", "Pertemuan 1", "Pertemuan 2", "Pertemuan 3", "Pertemuan 4", "Total Hadir", "Persentase"];
  
  const rows = masterPeserta.map((p, idx) => {
    const pRecords = [1, 2, 3, 4].map(pNum => 
      rekapPresensi.find(r => 
        r.idPeserta.toUpperCase() === p.id.toUpperCase() &&
        (targetBatch === "all" || (r.batch || "Batch 1").toLowerCase() === targetBatch.toLowerCase()) &&
        Number(r.pertemuan || 1) === pNum
      )
    );
    const totalHadir = pRecords.filter(Boolean).length;
    const percent = Math.round((totalHadir / 4) * 100);

    return [
      idx + 1,
      `"${p.id}"`,
      `"${(p.nama || '').replace(/"/g, '""')}"`,
      `"${p.whatsapp || p.email || '-'}"`,
      `"${(p.track || '').replace(/"/g, '""')}"`,
      `"${(p.instansi || '').replace(/"/g, '""')}"`,
      `"${targetBatch === 'all' ? (p.batch || 'Batch 1') : targetBatch}"`,
      `"${pRecords[0] ? 'HADIR' : 'TIDAK'}"`,
      `"${pRecords[1] ? 'HADIR' : 'TIDAK'}"`,
      `"${pRecords[2] ? 'HADIR' : 'TIDAK'}"`,
      `"${pRecords[3] ? 'HADIR' : 'TIDAK'}"`,
      totalHadir,
      `"${percent}%"`
    ];
  });

  const csvContent = "\uFEFF" + [headers.join(","), ...rows.map(e => e.join(","))].join("\r\n");
  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.setAttribute("href", url);
  link.setAttribute("download", `Rekap_Presensi_Halacoding_${new Date().toISOString().slice(0, 10)}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
  showToastNotification("Data rekap presensi berhasil diekspor ke CSV!", "📥");
}

// ========================================================
// 6.5. EXCEL / CSV TEMPLATE & PARTICIPANT IMPORT LOGIC
// ========================================================

/**
 * Generate and download official Excel template (.xlsx) using SheetJS
 */
function downloadTemplateExcel() {
  try {
    if (typeof XLSX === "undefined") {
      downloadTemplateCSV();
      return;
    }

    const templateRows = [
      {
        "ID Peserta": "HC-111",
        "Nama Lengkap": "Ahmad Dani Ramadhan",
        "No. WhatsApp": "0812-3456-7811",
        "Instansi": "Universitas Indonesia",
        "Track Pelatihan": "Fullstack Web (React & Node)",
        "Mentor": "Kak Dimas (Senior Fullstack Lead)",
        "Batch": "Batch 4 - 2026"
      },
      {
        "ID Peserta": "HC-112",
        "Nama Lengkap": "Dewi Sartika Putri",
        "No. WhatsApp": "0813-4567-8912",
        "Instansi": "Institut Teknologi Bandung",
        "Track Pelatihan": "Python & AI Engineering",
        "Mentor": "Kak Sarah (AI Specialist)",
        "Batch": "Batch 4 - 2026"
      },
      {
        "ID Peserta": "HC-113",
        "Nama Lengkap": "Rian Kurniawan",
        "No. WhatsApp": "0856-7890-1213",
        "Instansi": "Telkom University",
        "Track Pelatihan": "Mobile App (Flutter)",
        "Mentor": "Kak Andi (Mobile Architect)",
        "Batch": "Batch 4 - 2026"
      },
      {
        "ID Peserta": "HC-114",
        "Nama Lengkap": "Cindy Clarissa",
        "No. WhatsApp": "0878-1234-5614",
        "Instansi": "Binus University",
        "Track Pelatihan": "UI/UX & Product Design",
        "Mentor": "Kak Bella (Head of Product Design)",
        "Batch": "Batch 4 - 2026"
      },
      {
        "ID Peserta": "HC-115",
        "Nama Lengkap": "Gilang Pratama Putra",
        "No. WhatsApp": "0819-2345-6715",
        "Instansi": "Universitas Gadjah Mada",
        "Track Pelatihan": "Cloud & DevOps",
        "Mentor": "Kak Kevin (Cloud Architect)",
        "Batch": "Batch 4 - 2026"
      }
    ];

    const worksheet = XLSX.utils.json_to_sheet(templateRows);

    // Set practical column widths for Excel
    worksheet["!cols"] = [
      { wch: 15 }, // ID Peserta
      { wch: 28 }, // Nama Lengkap
      { wch: 20 }, // No. WhatsApp
      { wch: 32 }, // Instansi
      { wch: 34 }, // Track Pelatihan
      { wch: 34 }, // Mentor
      { wch: 18 }  // Batch
    ];

    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Data Peserta");

    XLSX.writeFile(workbook, "Template_Import_Peserta_Halacoding.xlsx");
    showToastNotification("Template Excel (.xlsx) berhasil diunduh!", "📥");
  } catch (error) {
    console.error("Error generating Excel template:", error);
    downloadTemplateCSV();
  }
}

/**
 * Generate and download CSV template (.csv) with UTF-8 BOM
 */
function downloadTemplateCSV() {
  const headers = ["ID Peserta", "Nama Lengkap", "No. WhatsApp", "Instansi", "Track Pelatihan", "Mentor", "Batch"];
  const rows = [
    ["HC-111", "Ahmad Dani Ramadhan", "0812-3456-7811", "Universitas Indonesia", "Fullstack Web (React & Node)", "Kak Dimas (Senior Fullstack Lead)", "Batch 4 - 2026"],
    ["HC-112", "Dewi Sartika Putri", "0813-4567-8912", "Institut Teknologi Bandung", "Python & AI Engineering", "Kak Sarah (AI Specialist)", "Batch 4 - 2026"],
    ["HC-113", "Rian Kurniawan", "0856-7890-1213", "Telkom University", "Mobile App (Flutter)", "Kak Andi (Mobile Architect)", "Batch 4 - 2026"],
    ["HC-114", "Cindy Clarissa", "0878-1234-5614", "Binus University", "UI/UX & Product Design", "Kak Bella (Head of Product Design)", "Batch 4 - 2026"],
    ["HC-115", "Gilang Pratama Putra", "0819-2345-6715", "Universitas Gadjah Mada", "Cloud & DevOps", "Kak Kevin (Cloud Architect)", "Batch 4 - 2026"]
  ];

  const csvContent = "data:text/csv;charset=utf-8,\uFEFF" + 
    [headers.join(","), ...rows.map(r => r.map(c => `"${c.replace(/"/g, '""')}"`).join(","))].join("\n");

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", "Template_Import_Peserta_Halacoding.csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  showToastNotification("Template CSV (.csv) berhasil diunduh!", "📄");
}

/**
 * Open Import Modal
 */
function openImportModal() {
  resetImportFile();
  const modal = document.getElementById("modalImportPeserta");
  if (modal) {
    modal.classList.add("open");
  }
}

/**
 * Close Import Modal
 */
function closeImportModal() {
  const modal = document.getElementById("modalImportPeserta");
  if (modal) {
    modal.classList.remove("open");
  }
}

function closeImportModalOnBackdrop(event) {
  if (event.target === document.getElementById("modalImportPeserta")) {
    closeImportModal();
  }
}

/**
 * File Input Trigger & Reset
 */
function triggerImportFileInput() {
  const fileInput = document.getElementById("fileImportPeserta");
  if (fileInput) fileInput.click();
}

function resetImportFile() {
  const fileInput = document.getElementById("fileImportPeserta");
  if (fileInput) fileInput.value = "";

  const dropzone = document.getElementById("importDropzone");
  const fileLoadedCard = document.getElementById("fileLoadedCard");
  const previewArea = document.getElementById("importPreviewArea");
  const btnConfirm = document.getElementById("btnConfirmImportAction");

  if (dropzone) dropzone.style.display = "block";
  if (fileLoadedCard) fileLoadedCard.style.display = "none";
  if (previewArea) previewArea.style.display = "none";
  if (btnConfirm) btnConfirm.disabled = true;

  pendingImportData = [];
}

/**
 * Drag and Drop handlers
 */
function handleImportDragOver(e) {
  e.preventDefault();
  e.stopPropagation();
  const dropzone = document.getElementById("importDropzone");
  if (dropzone) dropzone.classList.add("dragover");
}

function handleImportDragLeave(e) {
  e.preventDefault();
  e.stopPropagation();
  const dropzone = document.getElementById("importDropzone");
  if (dropzone) dropzone.classList.remove("dragover");
}

function handleImportDrop(e) {
  e.preventDefault();
  e.stopPropagation();
  const dropzone = document.getElementById("importDropzone");
  if (dropzone) dropzone.classList.remove("dragover");

  if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
    processImportFile(e.dataTransfer.files[0]);
  }
}

function handleImportFileSelect(e) {
  if (e.target.files && e.target.files.length > 0) {
    processImportFile(e.target.files[0]);
  }
}

/**
 * Process uploaded file (Excel .xlsx, .xls or CSV)
 */
function processImportFile(file) {
  if (!file) return;

  const validExts = [".xlsx", ".xls", ".csv"];
  const fileName = file.name.toLowerCase();
  const isValid = validExts.some(ext => fileName.endsWith(ext));

  if (!isValid) {
    alert("Format file tidak didukung! Silakan unggah file Excel (.xlsx, .xls) atau CSV (.csv).");
    resetImportFile();
    return;
  }

  // Update UI to show loaded file details
  const dropzone = document.getElementById("importDropzone");
  const fileLoadedCard = document.getElementById("fileLoadedCard");
  const fileNameEl = document.getElementById("importFileName");
  const fileMetaEl = document.getElementById("importFileMeta");

  if (dropzone) dropzone.style.display = "none";
  if (fileLoadedCard) fileLoadedCard.style.display = "flex";
  if (fileNameEl) fileNameEl.textContent = file.name;
  if (fileMetaEl) fileMetaEl.textContent = `${(file.size / 1024).toFixed(1)} KB • Membaca data...`;

  const reader = new FileReader();

  if (fileName.endsWith(".csv") && typeof XLSX === "undefined") {
    // Pure CSV fallback reader
    reader.onload = function(e) {
      try {
        const text = e.target.result;
        const rows = parseCSVText(text);
        handleParsedRawRows(rows, file.size);
      } catch (err) {
        console.error("Error reading CSV:", err);
        alert("Gagal membaca file CSV: " + err.message);
        resetImportFile();
      }
    };
    reader.readAsText(file);
  } else {
    // SheetJS reader (handles .xlsx, .xls, and .csv seamlessly)
    reader.onload = function(e) {
      try {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: "array" });
        if (!workbook.SheetNames || workbook.SheetNames.length === 0) {
          throw new Error("File tidak memiliki sheet data.");
        }
        const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
        const jsonRows = XLSX.utils.sheet_to_json(firstSheet, { defval: "" });
        handleParsedRawRows(jsonRows, file.size);
      } catch (err) {
        console.error("Error reading Excel:", err);
        alert("Gagal memproses file: " + err.message);
        resetImportFile();
      }
    };
    reader.readAsArrayBuffer(file);
  }
}

/**
 * Robust CSV parser fallback supporting comma or semicolon delimiters and quotes
 */
function parseCSVText(text) {
  const lines = text.split(/\r?\n/).filter(line => line.trim().length > 0);
  if (lines.length < 2) return [];

  // Detect delimiter: semicolon or comma
  const headerLine = lines[0];
  const delim = (headerLine.split(";").length > headerLine.split(",").length) ? ";" : ",";

  function parseCSVLine(line) {
    const values = [];
    let current = "";
    let inQuotes = false;
    for (let i = 0; i < line.length; i++) {
      const char = line[i];
      if (char === '"') {
        if (inQuotes && line[i + 1] === '"') {
          current += '"';
          i++;
        } else {
          inQuotes = !inQuotes;
        }
      } else if (char === delim && !inQuotes) {
        values.push(current.trim());
        current = "";
      } else {
        current += char;
      }
    }
    values.push(current.trim());
    return values;
  }

  const headers = parseCSVLine(lines[0]);
  const result = [];

  for (let i = 1; i < lines.length; i++) {
    const cols = parseCSVLine(lines[i]);
    if (cols.length === 0 || cols.every(c => c === "")) continue;
    const rowObj = {};
    headers.forEach((h, idx) => {
      rowObj[h] = cols[idx] !== undefined ? cols[idx] : "";
    });
    result.push(rowObj);
  }

  return result;
}

/**
 * Flexible mapping helper for participant row
 */
function extractParticipantFromRow(row, fallbackIndex) {
  let id = "";
  let nama = "";
  let whatsapp = "";
  let instansi = "";
  let track = "";
  let mentor = "";
  let batch = "";

  for (const [rawKey, rawVal] of Object.entries(row)) {
    const key = String(rawKey).toLowerCase().replace(/[^a-z0-9]/g, "");
    const val = String(rawVal || "").trim();
    if (!val) continue;

    if (!id && (key === "id" || key.includes("idpeserta") || key.includes("kodepeserta") || key.includes("nopeserta") || key === "nim" || key === "nis")) {
      id = val;
    } else if (!nama && (key.includes("nama") || key === "name" || key.includes("fullname"))) {
      nama = val;
    } else if (!whatsapp && (key.includes("whatsapp") || key.includes("wa") || key.includes("nohp") || key.includes("hp") || key.includes("telepon") || key.includes("phone") || key.includes("telp") || key.includes("kontak") || key.includes("email"))) {
      whatsapp = val;
    } else if (!instansi && (key.includes("instansi") || key.includes("institusi") || key.includes("kampus") || key.includes("sekolah") || key.includes("univ") || key.includes("organisasi") || key.includes("asal"))) {
      instansi = val;
    } else if (!track && (key.includes("track") || key.includes("kelas") || key.includes("program") || key.includes("kursus") || key.includes("bidang"))) {
      track = val;
    } else if (!mentor && (key.includes("mentor") || key.includes("pembimbing") || key.includes("instruktur") || key.includes("tutor"))) {
      mentor = val;
    } else if (!batch && (key.includes("batch") || key.includes("angkatan") || key.includes("gelombang") || key.includes("periode"))) {
      batch = val;
    }
  }

  // Row is invalid if neither ID nor Nama is present
  if (!id && !nama) return null;

  // If nama exists but ID missing, generate automatic ID
  if (!id && nama) {
    id = "HC-" + (200 + fallbackIndex);
  }
  // If id exists but Nama missing, invalid
  if (!nama) return null;

  const PALETTE = ["#4f46e5", "#059669", "#d97706", "#db2777", "#7c3aed", "#2563eb", "#0d9488", "#e11d48", "#ea580c", "#4338ca"];
  // Deterministic color generation based on id/nama
  let hash = 0;
  const str = id + nama;
  for (let i = 0; i < str.length; i++) {
    hash = (hash << 5) - hash + str.charCodeAt(i);
    hash |= 0;
  }
  const color = PALETTE[Math.abs(hash) % PALETTE.length];

  return {
    id: id.trim().toUpperCase(),
    nama: nama.trim(),
    whatsapp: whatsapp.trim() || "-",
    instansi: instansi.trim() || "Umum",
    track: track.trim() || "Fullstack Web (React & Node)",
    mentor: mentor.trim() || "Tim Mentor Halacoding",
    batch: batch.trim() || "Batch 4 - 2026",
    color: color
  };
}

/**
 * Handle parsed rows from file and build UI preview
 */
function handleParsedRawRows(rows, fileSize) {
  const fileMetaEl = document.getElementById("importFileMeta");
  const previewArea = document.getElementById("importPreviewArea");
  const tbody = document.getElementById("importPreviewTableBody");
  const badgeCount = document.getElementById("previewBadgeCount");
  const skippedText = document.getElementById("previewSkippedCount");
  const moreInfo = document.getElementById("previewMoreInfo");
  const btnConfirm = document.getElementById("btnConfirmImportAction");

  pendingImportData = [];
  let skippedCount = 0;

  rows.forEach((row, idx) => {
    const p = extractParticipantFromRow(row, idx + 1);
    if (p) {
      // Prevent duplicate IDs within the same import file
      const dupIdx = pendingImportData.findIndex(item => item.id.toUpperCase() === p.id.toUpperCase());
      if (dupIdx >= 0) {
        pendingImportData[dupIdx] = p; // override with last entry
      } else {
        pendingImportData.push(p);
      }
    } else {
      skippedCount++;
    }
  });

  if (fileMetaEl) {
    fileMetaEl.textContent = `${(fileSize / 1024).toFixed(1)} KB • ${pendingImportData.length} peserta terbaca`;
  }

  if (pendingImportData.length === 0) {
    if (previewArea) previewArea.style.display = "none";
    if (btnConfirm) btnConfirm.disabled = true;
    alert("Tidak ditemukan data peserta yang valid dalam file ini. Mohon gunakan template Excel yang telah disediakan.");
    return;
  }

  // Populate preview table (up to 5 items)
  if (tbody) {
    tbody.innerHTML = "";
    const previewList = pendingImportData.slice(0, 5);

    previewList.forEach((item, i) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${i + 1}</td>
        <td><strong>${escapeHtml(item.id)}</strong></td>
        <td>${escapeHtml(item.nama)}</td>
        <td>${escapeHtml(item.whatsapp || '-')}</td>
        <td>${escapeHtml(item.track)}</td>
        <td>${escapeHtml(item.instansi)}</td>
        <td>${escapeHtml(item.mentor)}</td>
        <td>${escapeHtml(item.batch)}</td>
      `;
      tbody.appendChild(tr);
    });
  }

  if (badgeCount) {
    badgeCount.textContent = `✓ ${pendingImportData.length} Peserta Valid Siap Diimpor`;
  }

  if (skippedText) {
    if (skippedCount > 0) {
      skippedText.style.display = "inline-block";
      skippedText.textContent = `⚠️ ${skippedCount} baris diabaikan (ID/Nama kosong)`;
    } else {
      skippedText.style.display = "none";
    }
  }

  if (moreInfo) {
    if (pendingImportData.length > 5) {
      moreInfo.style.display = "block";
      moreInfo.textContent = `+ ${pendingImportData.length - 5} peserta lainnya akan ikut diimpor...`;
    } else {
      moreInfo.style.display = "none";
    }
  }

  if (previewArea) previewArea.style.display = "block";
  if (btnConfirm) btnConfirm.disabled = false;
}

/**
 * Execute final import into master participants
 */
function executeImportParticipants() {
  if (pendingImportData.length === 0) {
    alert("Belum ada data peserta yang siap diimpor.");
    return;
  }

  const selectedMode = document.querySelector('input[name="importMergeMode"]:checked')?.value || "merge";

  let addedCount = 0;
  let updatedCount = 0;

  if (selectedMode === "replace") {
    const conf = confirm(
      `PERINGATAN: Opsi ini akan MENGGANTIKAN seluruh ${masterPeserta.length} data peserta master dengan ${pendingImportData.length} peserta baru dari file ini.\n\nApakah Anda yakin ingin melanjutkan?`
    );
    if (!conf) return;

    masterPeserta = [...pendingImportData];
    addedCount = pendingImportData.length;
  } else {
    // Merge mode (default)
    pendingImportData.forEach(newP => {
      const existingIdx = masterPeserta.findIndex(p => p.id.toUpperCase() === newP.id.toUpperCase());
      if (existingIdx >= 0) {
        masterPeserta[existingIdx] = {
          ...masterPeserta[existingIdx],
          ...newP,
          color: masterPeserta[existingIdx].color || newP.color
        };
        updatedCount++;
      } else {
        masterPeserta.push(newP);
        addedCount++;
      }
    });
  }

  // Persist updated master participants
  saveMasterPeserta();

  // Re-render admin dashboard stats and table
  renderAdminDashboard();

  // Close modal
  closeImportModal();

  // Audio feedback
  playCelebrationChime();

  // Feedback toast
  if (selectedMode === "replace") {
    showToastNotification(`Berhasil impor ${masterPeserta.length} peserta! Klik tombol "Download data-peserta.js" untuk menyimpan ke file kode.`, "⚡");
  } else {
    showToastNotification(
      `Berhasil impor ${addedCount} baru, ${updatedCount} diperbarui! Klik "Download data-peserta.js" untuk menyimpan ke file kode.`,
      "🎉"
    );
  }
}

/**
 * Sleek Toast Notification
 */
function showToastNotification(message, icon = "✓") {
  const toast = document.getElementById("toastNotification");
  const msgEl = document.getElementById("toastMessage");
  const iconEl = document.getElementById("toastIcon");

  if (!toast || !msgEl) return;

  if (toastTimeout) {
    clearTimeout(toastTimeout);
  }

  msgEl.textContent = message;
  if (iconEl) iconEl.textContent = icon;

  toast.style.display = "flex";

  toastTimeout = setTimeout(() => {
    toast.style.display = "none";
  }, 4000);
}

// ========================================================
// 7. UTILITY & AUDIO / ANIMATION HELPERS
// ========================================================
function formatDateTime(date) {
  const options = { 
    day: "numeric", 
    month: "short", 
    year: "numeric", 
    hour: "2-digit", 
    minute: "2-digit",
    hour12: false
  };
  return date.toLocaleDateString("id-ID", options).replace(",", " •") + " WIB";
}

function getInitials(name) {
  if (!name) return "HC";
  const parts = name.trim().split(" ");
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

function getRatingLabel(val) {
  const str = String(val || "").trim();
  if (str === "5" || str.startsWith("5") || str.includes("Sangat")) return "🔥 Sangat Puas";
  if (str === "4" || str.startsWith("4") || str.includes("Bagus")) return "👍 Bagus & Jelas";
  if (str === "3" || str.startsWith("3") || str.includes("Cukup")) return "👌 Cukup Baik";
  if (!str || str === "-") return "-";
  return str;
}

function getRatingText(val) {
  return getRatingLabel(val);
}

function escapeHtml(string) {
  if (!string) return "";
  const entityMap = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;"
  };
  return String(string).replace(/[&<>"']/g, s => entityMap[s]);
}

// Synthesized audio chime for success celebration (Pure Web Audio API, zero audio file required)
function playCelebrationChime() {
  try {
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!AudioContext) return;
    const ctx = new AudioContext();

    const notes = [523.25, 659.25, 783.99, 1046.50]; // C5, E5, G5, C6 (Major Chord Arpeggio)
    notes.forEach((freq, idx) => {
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();

      osc.type = "sine";
      osc.frequency.setValueAtTime(freq, ctx.currentTime + idx * 0.08);

      gain.gain.setValueAtTime(0, ctx.currentTime + idx * 0.08);
      gain.gain.linearRampToValueAtTime(0.18, ctx.currentTime + idx * 0.08 + 0.02);
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + idx * 0.08 + 0.45);

      osc.connect(gain);
      gain.connect(ctx.destination);

      osc.start(ctx.currentTime + idx * 0.08);
      osc.stop(ctx.currentTime + idx * 0.08 + 0.5);
    });
  } catch (err) {
    // Audio might be blocked until user gesture, safe to ignore
    console.log("Audio notice:", err);
  }
}

// Fullscreen Celebratory Confetti Particle Canvas Animation
function launchConfetti() {
  const canvas = document.getElementById("confettiCanvas");
  if (!canvas) return;
  const ctx = canvas.getContext("2d");

  // Fullscreen dimensions
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;

  const particles = [];
  const colors = [
    "#10b981", "#059669", "#34d399", "#6ee7b7", 
    "#06b6d4", "#3b82f6", "#8b5cf6", "#f59e0b", "#fbbf24", "#f43f5e"
  ];
  const particleCount = 110;

  for (let i = 0; i < particleCount; i++) {
    const angle = Math.random() * Math.PI * 2;
    const speed = Math.random() * 14 + 4;
    particles.push({
      x: window.innerWidth / 2,
      y: window.innerHeight / 2 - 40,
      r: Math.random() * 5 + 3,
      color: colors[Math.floor(Math.random() * colors.length)],
      tilt: Math.random() * 10 - 5,
      tiltAngle: Math.random() * Math.PI * 2,
      tiltAngleInc: Math.random() * 0.12 + 0.05,
      vx: Math.cos(angle) * speed * (Math.random() * 1.5 + 0.5),
      vy: Math.sin(angle) * speed - Math.random() * 8 - 4,
      gravity: 0.28,
      friction: 0.975,
      shape: Math.random() > 0.4 ? "rect" : "circle",
      width: Math.random() * 11 + 6,
      height: Math.random() * 8 + 4,
      rotation: Math.random() * 360,
      rotSpeed: (Math.random() - 0.5) * 10,
      opacity: 1
    });
  }

  let animationFrame;
  const startTime = Date.now();

  function draw() {
    const elapsed = Date.now() - startTime;
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    particles.forEach(p => {
      p.x += p.vx;
      p.y += p.vy;
      p.vy += p.gravity;
      p.vx *= p.friction;
      p.rotation += p.rotSpeed;
      p.tiltAngle += p.tiltAngleInc;
      p.tilt = Math.sin(p.tiltAngle) * 12;
      p.opacity = Math.max(0, 1 - (elapsed - 1200) / 1600);

      ctx.save();
      ctx.globalAlpha = Math.min(1, p.opacity);
      ctx.translate(p.x, p.y);
      ctx.rotate((p.rotation * Math.PI) / 180);
      ctx.fillStyle = p.color;

      if (p.shape === "rect") {
        ctx.fillRect(-p.width / 2, -p.height / 2, p.width, p.height);
      } else {
        ctx.beginPath();
        ctx.ellipse(0, 0, p.r, p.r * 1.3, (p.tilt * Math.PI) / 180, 0, Math.PI * 2);
        ctx.fill();
      }
      ctx.restore();
    });

    if (elapsed < 2800) {
      animationFrame = requestAnimationFrame(draw);
    } else {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      cancelAnimationFrame(animationFrame);
    }
  }

  draw();
}
