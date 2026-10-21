/**
 * HalaCoding Attendance System
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
    if (data.status === "success" && Array.isArray(data.rekap)) {
      const changed = JSON.stringify(rekapPresensi) !== JSON.stringify(data.rekap);
      if (changed) {
        rekapPresensi = data.rekap;
        localStorage.setItem(STORAGE_REKAP_KEY, JSON.stringify(rekapPresensi));
        updateBadgeCounts();

        // Jika halaman admin sedang terbuka, render ulang tabel agar langsung sinkron
        if (currentRole === "admin" && isAdminAuthenticated) {
          renderAdminDashboard();
        }
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
function switchRole(role) {
  currentRole = role;
  const btnPeserta = document.getElementById("btnRolePeserta");
  const btnAdmin = document.getElementById("btnRoleAdmin");
  const viewPeserta = document.getElementById("viewPeserta");
  const viewAdminLogin = document.getElementById("viewAdminLogin");
  const viewAdmin = document.getElementById("viewAdmin");

  if (role === "peserta") {
    btnPeserta.classList.add("active");
    btnAdmin.classList.remove("active");
    viewPeserta.style.display = "block";
    if (viewAdminLogin) viewAdminLogin.style.display = "none";
    if (viewAdmin) viewAdmin.style.display = "none";
    // Refocus input peserta
    setTimeout(() => {
      const idInput = document.getElementById("inputPesertaId");
      if (idInput) idInput.focus();
    }, 100);
  } else {
    btnAdmin.classList.add("active");
    btnPeserta.classList.remove("active");
    viewPeserta.style.display = "none";

    // Check if admin is authenticated
    if (isAdminAuthenticated) {
      if (viewAdminLogin) viewAdminLogin.style.display = "none";
      if (viewAdmin) viewAdmin.style.display = "block";
      renderAdminDashboard();
    } else {
      // Show Admin Login Page
      if (viewAdmin) viewAdmin.style.display = "none";
      if (viewAdminLogin) {
        viewAdminLogin.style.display = "block";
        const errAlert = document.getElementById("loginErrorAlert");
        if (errAlert) errAlert.style.display = "none";
        setTimeout(() => {
          const userField = document.getElementById("inputAdminUsername");
          if (userField) userField.focus();
        }, 100);
      }
    }
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

    // Check if participant has already attended today
    const existingRecord = rekapPresensi.find(r => r.idPeserta.toUpperCase() === match.id.toUpperCase());
    const attendedAlert = document.getElementById("alreadyAttendedAlert");
    const statusBadge = document.getElementById("profileStatusBadge");
    const statusText = document.getElementById("profileStatusText");

    if (existingRecord) {
      attendedAlert.style.display = "flex";
      document.getElementById("prevAttendedTime").textContent = existingRecord.timestamp;
      statusBadge.className = "status-indicator-badge attended";
      statusText.textContent = "Sudah Tercatat";
      // Prefill previous feedback if any
      const feedbackInput = document.getElementById("inputKritikSaran");
      if (!feedbackInput.value && existingRecord.kritikSaran) {
        feedbackInput.value = existingRecord.kritikSaran;
      }
    } else {
      attendedAlert.style.display = "none";
      statusBadge.className = "status-indicator-badge";
      statusText.textContent = "Siap Presensi";
    }

    helperText.innerHTML = `<span style="color: var(--success); font-weight: 600;">✓ Data ditemukan: ${match.nama}</span>`;
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
function handlePresensiSubmit(event) {
  event.preventDefault();

  if (!currentMatchedPeserta) {
    alert("Silakan masukkan ID Peserta yang valid terlebih dahulu.");
    return;
  }

  const kritikSaran = document.getElementById("inputKritikSaran").value.trim();
  const selectedRating = document.querySelector('input[name="ratingPelatihan"]:checked')?.value || "5";
  const nowFormatted = formatDateTime(new Date());

  // Check if existing record
  const existingIdx = rekapPresensi.findIndex(
    r => r.idPeserta.toUpperCase() === currentMatchedPeserta.id.toUpperCase()
  );

  const attendanceData = {
    idPeserta: currentMatchedPeserta.id,
    nama: currentMatchedPeserta.nama,
    whatsapp: currentMatchedPeserta.whatsapp || currentMatchedPeserta.email || "-",
    instansi: currentMatchedPeserta.instansi,
    track: currentMatchedPeserta.track,
    timestamp: nowFormatted,
    kritikSaran: kritikSaran || "Belum ada catatan kritik/saran.",
    rating: selectedRating,
    status: "HADIR"
  };

  if (existingIdx !== -1) {
    // Update existing
    rekapPresensi[existingIdx] = attendanceData;
  } else {
    // Push new
    rekapPresensi.unshift(attendanceData);
  }

  saveRekapData();

  // Kirim data presensi ke server hosting agar otomatis sinkron ke Laptop Admin
  if (IS_SERVER_ENV) {
    fetch(`${PRESENSI_API_URL}?action=submit_absen`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(attendanceData)
    }).catch(err => console.warn("Gagal kirim absen ke server:", err));
  }

  // Play pleasant chime
  playCelebrationChime();

  // Show Success Modal with animated checkmark and confetti
  showSuccessModal(attendanceData);
}

function showSuccessModal(data) {
  const modal = document.getElementById("modalSuccess");
  document.getElementById("modalPesertaNama").textContent = data.nama;
  document.getElementById("modalPesertaId").textContent = data.idPeserta;
  document.getElementById("modalPesertaTrack").textContent = data.track;
  document.getElementById("modalTimestamp").textContent = data.timestamp;

  const feedbackRow = document.getElementById("modalFeedbackRow");
  if (data.kritikSaran && data.kritikSaran !== "Belum ada catatan kritik/saran.") {
    feedbackRow.style.display = "flex";
    document.getElementById("modalFeedbackText").textContent = `"${data.kritikSaran}"`;
  } else {
    feedbackRow.style.display = "none";
  }

  modal.classList.add("open");

  // Trigger confetti burst
  launchConfetti();
}

function closeSuccessModal() {
  const modal = document.getElementById("modalSuccess");
  modal.classList.remove("open");
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
function renderAdminDashboard() {
  renderAdminStats();
  renderAdminTable();
}

function renderAdminStats() {
  const totalTerdaftar = masterPeserta.length;
  const totalHadir = rekapPresensi.length;
  const belumHadir = Math.max(0, totalTerdaftar - totalHadir);
  const totalFeedbackWithText = rekapPresensi.filter(
    r => r.kritikSaran && r.kritikSaran !== "Belum ada catatan kritik/saran."
  ).length;

  document.getElementById("statTotalPeserta").textContent = totalTerdaftar;
  document.getElementById("statTotalHadir").textContent = totalHadir;
  document.getElementById("statBelumHadir").textContent = belumHadir;
  document.getElementById("statTotalFeedback").textContent = totalFeedbackWithText;
}

function renderAdminTable() {
  const tbody = document.getElementById("adminTableBody");
  const emptyState = document.getElementById("tableEmptyState");
  const searchQuery = (document.getElementById("adminSearchInput")?.value || "").toLowerCase().trim();
  const filterTrack = document.getElementById("filterTrack")?.value || "ALL";
  const filterStatus = document.getElementById("filterStatus")?.value || "ALL";

  // Build combined view: all master participants with attendance status
  let combinedList = masterPeserta.map(p => {
    const record = rekapPresensi.find(r => r.idPeserta.toUpperCase() === p.id.toUpperCase());
    return {
      id: p.id,
      nama: p.nama,
      whatsapp: p.whatsapp || p.email || "-",
      instansi: p.instansi,
      track: p.track,
      color: p.color,
      isAttended: !!record,
      timestamp: record ? record.timestamp : "-",
      kritikSaran: record ? record.kritikSaran : "-",
      rating: record ? record.rating : null
    };
  });

  // Apply track filter
  if (filterTrack !== "ALL") {
    combinedList = combinedList.filter(item => item.track === filterTrack);
  }

  // Apply status filter
  if (filterStatus === "HADIR") {
    combinedList = combinedList.filter(item => item.isAttended);
  } else if (filterStatus === "BELUM") {
    combinedList = combinedList.filter(item => !item.isAttended);
  }

  // Apply search query
  if (searchQuery) {
    combinedList = combinedList.filter(item => 
      item.nama.toLowerCase().includes(searchQuery) ||
      item.id.toLowerCase().includes(searchQuery) ||
      (item.whatsapp && item.whatsapp.toLowerCase().includes(searchQuery)) ||
      item.instansi.toLowerCase().includes(searchQuery) ||
      item.track.toLowerCase().includes(searchQuery)
    );
  }

  tbody.innerHTML = "";

  if (combinedList.length === 0) {
    emptyState.style.display = "block";
    return;
  }
  emptyState.style.display = "none";

  combinedList.forEach((item, index) => {
    const tr = document.createElement("tr");

    const statusBadgeHtml = item.isAttended
      ? `<span class="badge-status badge-hadir">✓ Hadir</span>`
      : `<span class="badge-status badge-belum">Belum</span>`;

    let feedbackHtml = `<span style="color: var(--text-light);">-</span>`;
    if (item.isAttended && item.kritikSaran && item.kritikSaran !== "-") {
      const escapedFeedback = encodeURIComponent(item.kritikSaran);
      const escapedNama = encodeURIComponent(item.nama);
      const escapedMeta = encodeURIComponent(`${item.id} • ${item.track}`);
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
      ? `<button class="table-action-btn" onclick="hapusPresensiSingle('${item.id}')" title="Batalkan presensi peserta ini">Hapus</button>`
      : `<button class="table-action-btn" onclick="quickAbsenDariAdmin('${item.id}')" title="Tandai hadir sekarang">+ Hadir</button>`;

    tr.innerHTML = `
      <td>${index + 1}</td>
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
      <td style="font-size: 0.825rem; font-weight: 500;">${item.timestamp}</td>
      <td>${statusBadgeHtml}</td>
      <td>${feedbackHtml}</td>
      <td>${actionHtml}</td>
    `;
    tbody.appendChild(tr);
  });
}

// Quick action to attend from admin
function quickAbsenDariAdmin(id) {
  const match = masterPeserta.find(p => p.id === id);
  if (!match) return;

  const newRecord = {
    idPeserta: match.id,
    nama: match.nama,
    whatsapp: match.whatsapp || match.email || "-",
    instansi: match.instansi,
    track: match.track,
    timestamp: formatDateTime(new Date()),
    kritikSaran: "Tercatat langsung oleh Admin",
    rating: "5",
    status: "HADIR"
  };

  rekapPresensi.unshift(newRecord);
  saveRekapData();
  renderAdminDashboard();

  if (IS_SERVER_ENV) {
    fetch(`${PRESENSI_API_URL}?action=submit_absen`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(newRecord)
    }).catch(err => console.warn("Gagal absen di server:", err));
  }
}

// Remove single attendance
function hapusPresensiSingle(id) {
  if (confirm(`Batalkan presensi untuk peserta ID ${id}?`)) {
    rekapPresensi = rekapPresensi.filter(r => r.idPeserta.toUpperCase() !== id.toUpperCase());
    saveRekapData();
    renderAdminDashboard();

    if (IS_SERVER_ENV) {
      fetch(`${PRESENSI_API_URL}?action=delete_absen&id=${encodeURIComponent(id)}`)
        .catch(err => console.warn("Gagal hapus di server:", err));
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
  if (confirm("Apakah Anda yakin ingin MENGHAPUS SEMUA DATA REKAP presensi? Tindakan ini tidak dapat dibatalkan.")) {
    rekapPresensi = [];
    localStorage.setItem(STORAGE_REKAP_KEY, JSON.stringify([]));
    updateBadgeCounts();
    renderAdminDashboard();
    showToastNotification("Data rekap presensi telah berhasil direset!", "🧹");

    if (IS_SERVER_ENV) {
      fetch(`${PRESENSI_API_URL}?action=reset_rekap`, { method: "POST" })
        .catch(err => console.warn("Gagal reset di server:", err));
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

// Export data to Excel (.xlsx) using SheetJS
function exportDataExcel() {
  if (rekapPresensi.length === 0) {
    alert("Belum ada data presensi yang tercatat untuk diekspor.");
    return;
  }

  if (typeof XLSX === "undefined") {
    exportDataCSV();
    return;
  }

  const excelRows = rekapPresensi.map((r, idx) => ({
    "No": idx + 1,
    "ID Peserta": r.idPeserta,
    "Nama Peserta": r.nama,
    "No. WhatsApp": r.whatsapp || r.email || "-",
    "Track Pelatihan": r.track,
    "Instansi": r.instansi,
    "Waktu Presensi": r.timestamp,
    "Kepuasan (Rating)": getRatingLabel(r.rating),
    "Kritik & Saran": r.kritikSaran || "-",
    "Status": r.status || "HADIR"
  }));

  const worksheet = XLSX.utils.json_to_sheet(excelRows);
  worksheet["!cols"] = [
    { wch: 6 },  // No
    { wch: 14 }, // ID Peserta
    { wch: 26 }, // Nama Peserta
    { wch: 20 }, // No. WhatsApp
    { wch: 32 }, // Track Pelatihan
    { wch: 28 }, // Instansi
    { wch: 24 }, // Waktu Presensi
    { wch: 22 }, // Kepuasan (Rating)
    { wch: 45 }, // Kritik & Saran
    { wch: 12 }  // Status
  ];

  const workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, "Rekap Presensi");
  XLSX.writeFile(workbook, `Rekap_Presensi_HalaCoding_${new Date().toISOString().slice(0, 10)}.xlsx`);
  showToastNotification("Data rekap presensi berhasil diekspor ke Excel (.xlsx)!", "📥");
}

// Export data to CSV
function exportDataCSV() {
  if (rekapPresensi.length === 0) {
    alert("Belum ada data presensi yang tercatat untuk diekspor.");
    return;
  }

  const headers = ["No", "ID Peserta", "Nama Peserta", "No. WhatsApp", "Track Pelatihan", "Instansi", "Waktu Presensi", "Kepuasan (Rating)", "Kritik & Saran", "Status"];
  const rows = rekapPresensi.map((r, idx) => [
    idx + 1,
    `"${r.idPeserta}"`,
    `"${(r.nama || '').replace(/"/g, '""')}"`,
    `"${r.whatsapp || r.email || '-'}"`,
    `"${(r.track || '').replace(/"/g, '""')}"`,
    `"${(r.instansi || '').replace(/"/g, '""')}"`,
    `"${r.timestamp}"`,
    `"${getRatingLabel(r.rating)}"`,
    `"${(r.kritikSaran || '').replace(/"/g, '""')}"`,
    `"${r.status || 'HADIR'}"`
  ]);

  const csvContent = "\uFEFF" + [headers.join(","), ...rows.map(e => e.join(","))].join("\r\n");
  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.setAttribute("href", url);
  link.setAttribute("download", `Rekap_Presensi_HalaCoding_${new Date().toISOString().slice(0, 10)}.csv`);
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

    XLSX.writeFile(workbook, "Template_Import_Peserta_HalaCoding.xlsx");
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
  link.setAttribute("download", "Template_Import_Peserta_HalaCoding.csv");
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
    mentor: mentor.trim() || "Tim Mentor HalaCoding",
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

// Lightweight Confetti Particle Canvas Animation
function launchConfetti() {
  const canvas = document.getElementById("confettiCanvas");
  if (!canvas) return;
  const ctx = canvas.getContext("2d");

  // Resize canvas to parent wrapper
  const rect = canvas.parentElement.getBoundingClientRect();
  canvas.width = rect.width;
  canvas.height = rect.height;

  const particles = [];
  const colors = ["#10b981", "#059669", "#34d399", "#6ee7b7", "#047857", "#14b8a6", "#fbbf24"];
  const particleCount = 48;

  for (let i = 0; i < particleCount; i++) {
    particles.push({
      x: canvas.width / 2,
      y: canvas.height / 2 + 10,
      r: Math.random() * 4 + 2,
      color: colors[Math.floor(Math.random() * colors.length)],
      tilt: Math.random() * 10 - 5,
      tiltAngle: 0,
      tiltAngleInc: Math.random() * 0.08 + 0.04,
      vx: (Math.random() - 0.5) * 10,
      vy: (Math.random() - 0.7) * 11,
      opacity: 1
    });
  }

  let animationFrame;
  let startTime = Date.now();

  function draw() {
    const elapsed = Date.now() - startTime;
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    particles.forEach(p => {
      p.x += p.vx;
      p.y += p.vy;
      p.vy += 0.22; // gravity
      p.vx *= 0.98; // friction
      p.tiltAngle += p.tiltAngleInc;
      p.tilt = Math.sin(p.tiltAngle) * 8;
      p.opacity = Math.max(0, 1 - elapsed / 1500);

      ctx.save();
      ctx.globalAlpha = p.opacity;
      ctx.fillStyle = p.color;
      ctx.beginPath();
      ctx.ellipse(p.x, p.y, p.r, p.r * 1.5, p.tilt * Math.PI / 180, 0, Math.PI * 2);
      ctx.fill();
      ctx.restore();
    });

    if (elapsed < 1600) {
      animationFrame = requestAnimationFrame(draw);
    } else {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      cancelAnimationFrame(animationFrame);
    }
  }

  draw();
}
