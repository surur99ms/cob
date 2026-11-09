<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rekap Transfer & Salin Rekening | SantriKoding Corefive</title>
  <!-- Favicon -->
  <link rel="icon" href="../favicon.png" type="image/png">
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Fonts: Inter & JetBrains Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- SheetJS (Excel Parser & Generator) -->
  <script src="xlsx.full.min.js"></script>

  <style>
    body { font-family: 'Inter', system-ui, sans-serif; background-color: #f8fafc; color: #1e293b; }
    .font-mono-num { font-family: 'JetBrains Mono', monospace; }
    /* Excel Table Styling matching the screenshot */
    .table-cell-header {
      background-color: #ffffff;
      border: 1px solid #000000;
      color: #000000;
      font-weight: 700;
      text-align: center;
      padding: 10px 14px;
    }
    .table-cell-data {
      border: 1px solid #000000;
      padding: 8px 12px;
      vertical-align: middle;
    }
    .row-peach {
      background-color: #FCE5CD; /* Warna peach persis seperti gambar Bos Surur */
    }
    .row-white {
      background-color: #ffffff;
    }
  </style>
</head>
<body class="min-h-screen flex flex-col">

  <!-- Header / Navigation -->
  <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <div class="flex items-center gap-4">
        <a href="../katalog.php" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors flex items-center gap-2 border border-slate-300">
          <i class="fas fa-arrow-left"></i> Katalog Aplikasi
        </a>
        <div class="h-4 w-px bg-slate-300 hidden sm:block"></div>
        <h1 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
          <i class="fas fa-money-check-alt text-emerald-600"></i> Rekap Transfer & Salin Rekening
        </h1>
      </div>

      <div class="flex items-center gap-2">
        <button onclick="openAddModal()" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-sm flex items-center gap-2">
          <i class="fas fa-plus"></i> <span class="hidden sm:inline">Tambah Data</span>
        </button>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

    <!-- Top Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <!-- Total Penerima -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600 text-xl">
          <i class="fas fa-users"></i>
        </div>
        <div>
          <span class="text-xs text-slate-500 font-medium block">Total Penerima</span>
          <span id="statTotalItems" class="text-xl sm:text-2xl font-bold text-slate-800 font-mono-num">0</span>
        </div>
      </div>

      <!-- Total Nominal -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 text-xl">
          <i class="fas fa-wallet"></i>
        </div>
        <div>
          <span class="text-xs text-slate-500 font-medium block">Total Nominal</span>
          <span id="statTotalNominal" class="text-xl sm:text-2xl font-bold text-slate-800 font-mono-num">Rp 0</span>
        </div>
      </div>

      <!-- Sudah Transfer -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-green-50 border border-green-200 flex items-center justify-center text-green-600 text-xl">
          <i class="fas fa-check-circle"></i>
        </div>
        <div>
          <span class="text-xs text-slate-500 font-medium block">Sudah Ditransfer</span>
          <span id="statSudahTf" class="text-xl sm:text-2xl font-bold text-emerald-600 font-mono-num">0</span>
        </div>
      </div>

      <!-- Belum Transfer -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 text-xl">
          <i class="fas fa-clock"></i>
        </div>
        <div>
          <span class="text-xs text-slate-500 font-medium block">Belum Ditransfer</span>
          <span id="statBelumTf" class="text-xl sm:text-2xl font-bold text-rose-600 font-mono-num">0</span>
        </div>
      </div>
    </div>

    <!-- Action Bar & Controls -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
      <!-- Search Input -->
      <div class="w-full md:w-80 relative">
        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input type="text" id="searchInput" placeholder="Cari nama / nomor rekening..." 
          class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all">
      </div>

      <!-- Filters & Excel Buttons -->
      <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto justify-end">
        <!-- Filter Status -->
        <select id="filterStatus" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
          <option value="all">Semua Status</option>
          <option value="SUDAH">Status: SUDAH</option>
          <option value="BELUM">Status: BELUM</option>
        </select>

        <!-- Filter Wilayah -->
        <select id="filterWilayah" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs sm:text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
          <option value="all">Semua Wilayah</option>
        </select>

        <!-- Import Excel -->
        <button onclick="openImportModal()" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-sm flex items-center gap-2">
          <i class="fas fa-file-import text-emerald-400"></i> Import Excel
        </button>

        <!-- Export Excel -->
        <button onclick="exportToExcel()" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-sm flex items-center gap-2">
          <i class="fas fa-file-export"></i> Export Excel
        </button>
      </div>
    </div>

    <!-- Table Container (Mirip Format Gambar Bos Surur) -->
    <div class="bg-white rounded-2xl border border-slate-300 shadow-sm overflow-hidden mb-8">
      <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm">
          <thead>
            <tr>
              <th class="table-cell-header w-14">No</th>
              <th class="table-cell-header text-left">NAMA</th>
              <th class="table-cell-header w-24">WILAYAH</th>
              <th class="table-cell-header text-right w-44">NOMINAL</th>
              <th class="table-cell-header text-left w-64">REKENING</th>
              <th class="table-cell-header w-28">KET. TF</th>
              <th class="table-cell-header w-28 text-center">AKSI</th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <!-- Data baris akan di-render oleh JS -->
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div id="emptyState" class="hidden text-center py-16">
        <i class="fas fa-folder-open text-slate-300 text-5xl mb-3"></i>
        <h3 class="text-base font-bold text-slate-700">Tidak ada data ditemukan</h3>
        <p class="text-xs text-slate-500">Silakan tambah data manual atau import dari file Excel.</p>
      </div>
    </div>

  </main>

  <!-- Toast Notification (Feedback Salin Rekening) -->
  <div id="toastNotification" class="fixed bottom-6 right-6 z-50 transform translate-y-24 opacity-0 transition-all duration-300 bg-slate-900 text-white px-5 py-3.5 rounded-2xl shadow-2xl flex items-center gap-3 border border-slate-700">
    <div class="w-7 h-7 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs font-bold">
      <i class="fas fa-check"></i>
    </div>
    <div>
      <h4 id="toastTitle" class="font-bold text-xs text-emerald-400">Nomor Rekening Tersalin!</h4>
      <p id="toastMessage" class="text-xs text-slate-300">Siap di-paste ke m-Banking.</p>
    </div>
  </div>

  <!-- Modal: Import Excel -->
  <div id="importModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200">
      <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
          <i class="fas fa-file-excel text-emerald-600"></i> Import Data dari Excel
        </h3>
        <button onclick="closeImportModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="space-y-4 mb-6">
        <p class="text-xs text-slate-500">Pilih file Excel (<strong>.xlsx</strong> atau <strong>.csv</strong>) yang berisi kolom: <em>NAMA, WILAYAH, NOMINAL, REKENING, KET. TF</em>.</p>
        
        <div class="border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center hover:border-emerald-500 transition-colors cursor-pointer bg-slate-50" onclick="document.getElementById('excelFileInput').click()">
          <i class="fas fa-cloud-upload-alt text-4xl text-emerald-500 mb-2"></i>
          <span class="block text-sm font-semibold text-slate-700">Klik untuk memilih file Excel</span>
          <span id="chosenFileName" class="text-xs text-slate-400 mt-1 block">Mendukung file .xlsx dan .csv</span>
        </div>
        <input type="file" id="excelFileInput" accept=".xlsx, .xls, .csv" class="hidden" onchange="handleFileChosen(event)">

        <div class="pt-2">
          <label class="text-xs font-semibold text-slate-700 block mb-2">Metode Impor:</label>
          <div class="grid grid-cols-2 gap-3">
            <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-xl text-xs cursor-pointer bg-slate-50 hover:bg-slate-100">
              <input type="radio" name="importMode" value="append" checked class="text-emerald-600">
              <span>Tambahkan ke data yang ada (Append)</span>
            </label>
            <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-xl text-xs cursor-pointer bg-slate-50 hover:bg-slate-100">
              <input type="radio" name="importMode" value="replace" class="text-emerald-600">
              <span>Ganti semua data lama (Replace)</span>
            </label>
          </div>
        </div>
      </div>

      <div class="flex gap-3">
        <button onclick="closeImportModal()" class="w-1/2 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50">Batal</button>
        <button id="btnProcessImport" onclick="processImport()" disabled class="w-1/2 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
          Mulai Impor
        </button>
      </div>
    </div>
  </div>

  <!-- Modal: Tambah / Edit Data Manual -->
  <div id="dataModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-200">
      <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
        <h3 id="modalFormTitle" class="text-lg font-bold text-slate-900">Tambah Data Transfer</h3>
        <button onclick="closeDataModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form id="dataForm" onsubmit="handleFormSubmit(event)" class="space-y-4">
        <input type="hidden" id="formId" value="">

        <div>
          <label class="text-xs font-semibold text-slate-700 block mb-1">Nama Lengkap</label>
          <input type="text" id="formNama" required placeholder="Contoh: ABD MAJID" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-xs font-semibold text-slate-700 block mb-1">Wilayah</label>
            <input type="text" id="formWilayah" placeholder="Contoh: J-1" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
          </div>
          <div>
            <label class="text-xs font-semibold text-slate-700 block mb-1">Status Transfer</label>
            <select id="formStatus" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
              <option value="SUDAH">SUDAH</option>
              <option value="BELUM">BELUM</option>
            </select>
          </div>
        </div>

        <div>
          <label class="text-xs font-semibold text-slate-700 block mb-1">Nominal Transfer (Rp)</label>
          <input type="number" id="formNominal" required placeholder="Contoh: 272750" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-mono-num focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <div>
          <label class="text-xs font-semibold text-slate-700 block mb-1">Nomor Rekening / Virtual Account</label>
          <input type="text" id="formRekening" required placeholder="Contoh: 17451201715123" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-mono-num focus:outline-none focus:ring-2 focus:ring-emerald-500">
          <span class="text-[11px] text-slate-400 mt-1 block">Hanya angka (10–16 digit), tanpa tanda titik/koma.</span>
        </div>

        <div class="pt-4 flex gap-3">
          <button type="button" onclick="closeDataModal()" class="w-1/2 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50">Batal</button>
          <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- JavaScript Logic -->
  <script>
    let rawData = [];
    let parsedExcelRows = [];

    // --- 1. FETCH & RENDER DATA ---
    async function loadData() {
      const q = document.getElementById('searchInput').value;
      const status = document.getElementById('filterStatus').value;
      const wilayah = document.getElementById('filterWilayah').value;

      try {
        const res = await fetch(`api.php?action=list&q=${encodeURIComponent(q)}&status=${encodeURIComponent(status)}&wilayah=${encodeURIComponent(wilayah)}`);
        const json = await res.json();

        if (json.status === 'success') {
          rawData = json.data || [];
          renderTable(rawData);
          renderStats(json.stats);
          updateWilayahFilter(json.wilayah_list, wilayah);
        }
      } catch (e) {
        console.error("Gagal memuat data:", e);
      }
    }

    function renderTable(items) {
      const tbody = document.getElementById('tableBody');
      const empty = document.getElementById('emptyState');
      tbody.innerHTML = '';

      if (items.length === 0) {
        empty.classList.remove('hidden');
        return;
      }
      empty.classList.add('hidden');

      items.forEach((item, index) => {
        const isPeach = index % 2 === 0;
        const rowClass = isPeach ? 'row-peach' : 'row-white';

        const isSudah = (item.status_tf || '').toUpperCase() === 'SUDAH';
        const badgeClass = isSudah 
          ? 'bg-emerald-100 text-emerald-800 border-emerald-300' 
          : 'bg-amber-100 text-amber-800 border-amber-300';

        const formattedNominal = formatRupiah(item.nominal);

        const tr = document.createElement('tr');
        tr.className = `${rowClass} hover:opacity-90 transition-opacity`;
        tr.innerHTML = `
          <td class="table-cell-data text-center font-bold text-slate-800">${index + 1}</td>
          <td class="table-cell-data font-semibold text-slate-900">${escapeHtml(item.nama)}</td>
          <td class="table-cell-data text-center font-bold text-slate-700">${escapeHtml(item.wilayah || '-')}</td>
          <td class="table-cell-data text-right font-mono-num font-bold text-slate-900">
            <div class="flex items-center justify-end gap-2">
              <span>${formattedNominal}</span>
              <button onclick="copyToClipboard('${item.nominal}', 'Nominal Rp ${formattedNominal} Disalin!')" title="Salin Nominal murni" class="w-6 h-6 rounded bg-black/5 hover:bg-black/15 flex items-center justify-center text-slate-600 text-xs transition-colors">
                <i class="far fa-copy"></i>
              </button>
            </div>
          </td>
          <td class="table-cell-data font-mono-num font-bold text-slate-900">
            <div class="flex items-center justify-between gap-2">
              <span class="tracking-wide">${escapeHtml(item.rekening)}</span>
              <button onclick="copyToClipboard('${item.rekening}', 'Nomor Rekening Tersalin!')" title="Salin Rekening" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-semibold transition-all shadow-sm flex items-center gap-1.5 active:scale-95">
                <i class="fas fa-copy text-[10px]"></i> Salin
              </button>
            </div>
          </td>
          <td class="table-cell-data text-center">
            <button onclick="toggleStatus(${item.id})" title="Klik untuk mengubah status" class="px-3 py-1 rounded border text-xs font-bold transition-all shadow-xs ${badgeClass}">
              ${escapeHtml(item.status_tf)}
            </button>
          </td>
          <td class="table-cell-data text-center">
            <div class="flex items-center justify-center gap-2">
              <button onclick="openEditModal(${item.id})" class="text-blue-600 hover:text-blue-800 text-xs font-bold p-1">
                <i class="fas fa-edit"></i>
              </button>
              <button onclick="deleteItem(${item.id})" class="text-rose-600 hover:text-rose-800 text-xs font-bold p-1">
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          </td>
        `;
        tbody.appendChild(tr);
      });
    }

    function renderStats(stats) {
      if (!stats) return;
      document.getElementById('statTotalItems').textContent = stats.total_items || 0;
      document.getElementById('statTotalNominal').textContent = 'Rp ' + formatRupiah(stats.total_nominal);
      document.getElementById('statSudahTf').textContent = stats.count_sudah || 0;
      document.getElementById('statBelumTf').textContent = stats.count_belum || 0;
    }

    function updateWilayahFilter(wilayahList, selectedVal) {
      const sel = document.getElementById('filterWilayah');
      const current = selectedVal || sel.value;
      sel.innerHTML = '<option value="all">Semua Wilayah</option>';
      (wilayahList || []).forEach(w => {
        const opt = document.createElement('option');
        opt.value = w;
        opt.textContent = 'Wilayah: ' + w;
        if (w === current) opt.selected = true;
        sel.appendChild(opt);
      });
    }

    // --- 2. 1-CLICK CLIPBOARD COPY WITH TOAST ---
    function copyToClipboard(text, message) {
      const cleanText = String(text).replace(/[^0-9]/g, '');
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(cleanText).then(() => {
          showToast(message, cleanText);
        }).catch(() => fallbackCopy(cleanText, message));
      } else {
        fallbackCopy(cleanText, message);
      }
    }

    function fallbackCopy(text, message) {
      const ta = document.createElement('textarea');
      ta.value = text;
      ta.style.position = 'fixed';
      ta.style.opacity = '0';
      document.body.appendChild(ta);
      ta.focus();
      ta.select();
      try {
        document.execCommand('copy');
        showToast(message, text);
      } catch (err) {}
      document.body.removeChild(ta);
    }

    let toastTimer = null;
    function showToast(title, msg) {
      const toast = document.getElementById('toastNotification');
      document.getElementById('toastTitle').textContent = title;
      document.getElementById('toastMessage').textContent = msg;

      toast.classList.remove('translate-y-24', 'opacity-0');
      clearTimeout(toastTimer);
      toastTimer = setTimeout(() => {
        toast.classList.add('translate-y-24', 'opacity-0');
      }, 2500);
    }

    // --- 3. TOGGLE STATUS, SAVE & DELETE ---
    async function toggleStatus(id) {
      try {
        const res = await fetch('api.php?action=toggle_status', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id })
        });
        const json = await res.json();
        if (json.status === 'success') {
          loadData();
        }
      } catch (e) {
        alert("Gagal mengubah status: " + e.getMessage());
      }
    }

    function openAddModal() {
      document.getElementById('modalFormTitle').textContent = 'Tambah Data Transfer';
      document.getElementById('formId').value = '';
      document.getElementById('formNama').value = '';
      document.getElementById('formWilayah').value = 'J-1';
      document.getElementById('formNominal').value = '';
      document.getElementById('formRekening').value = '';
      document.getElementById('formStatus').value = 'BELUM';
      document.getElementById('dataModal').classList.remove('hidden');
    }

    function openEditModal(id) {
      const item = rawData.find(d => d.id === id);
      if (!item) return;

      document.getElementById('modalFormTitle').textContent = 'Edit Data Transfer';
      document.getElementById('formId').value = item.id;
      document.getElementById('formNama').value = item.nama;
      document.getElementById('formWilayah').value = item.wilayah;
      document.getElementById('formNominal').value = item.nominal;
      document.getElementById('formRekening').value = item.rekening;
      document.getElementById('formStatus').value = item.status_tf;
      document.getElementById('dataModal').classList.remove('hidden');
    }

    function closeDataModal() {
      document.getElementById('dataModal').classList.add('hidden');
    }

    async function handleFormSubmit(e) {
      e.preventDefault();
      const payload = {
        id: document.getElementById('formId').value,
        nama: document.getElementById('formNama').value,
        wilayah: document.getElementById('formWilayah').value,
        nominal: document.getElementById('formNominal').value,
        rekening: document.getElementById('formRekening').value,
        status_tf: document.getElementById('formStatus').value
      };

      try {
        const res = await fetch('api.php?action=save', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (json.status === 'success') {
          closeDataModal();
          loadData();
          showToast("Data Berhasil Disimpan", payload.nama);
        } else {
          alert("Error: " + json.message);
        }
      } catch (e) {
        alert("Gagal menyimpan data");
      }
    }

    async function deleteItem(id) {
      if (!confirm("Apakah Anda yakin ingin menghapus baris data ini?")) return;

      try {
        const res = await fetch('api.php?action=delete', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id })
        });
        const json = await res.json();
        if (json.status === 'success') {
          loadData();
          showToast("Data Terhapus", "Baris berhasil dihapus");
        }
      } catch (e) {
        alert("Gagal menghapus data");
      }
    }

    // --- 4. EXPORT TO EXCEL ---
    function exportToExcel() {
      if (rawData.length === 0) {
        alert("Belum ada data untuk diekspor!");
        return;
      }

      const rowsForExcel = rawData.map((item, idx) => ({
        "No": idx + 1,
        "NAMA": item.nama,
        "WILAYAH": item.wilayah,
        "NOMINAL": parseFloat(item.nominal),
        "REKENING": item.rekening,
        "KET. TF": item.status_tf
      }));

      const ws = XLSX.utils.json_to_sheet(rowsForExcel);
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, "Rekap Transfer");

      const today = new Date().toISOString().slice(0, 10);
      XLSX.writeFile(wb, `Rekap_Transfer_Rekening_${today}.xlsx`);
    }

    // --- 5. IMPORT FROM EXCEL ---
    function openImportModal() {
      parsedExcelRows = [];
      document.getElementById('excelFileInput').value = '';
      document.getElementById('chosenFileName').textContent = 'Mendukung file .xlsx dan .csv';
      document.getElementById('btnProcessImport').disabled = true;
      document.getElementById('importModal').classList.remove('hidden');
    }

    function closeImportModal() {
      document.getElementById('importModal').classList.add('hidden');
    }

    function handleFileChosen(e) {
      const file = e.target.files[0];
      if (!file) return;

      document.getElementById('chosenFileName').textContent = file.name;

      const reader = new FileReader();
      reader.onload = function(evt) {
        try {
          const data = new Uint8Array(evt.target.result);
          const workbook = XLSX.read(data, { type: 'array' });
          const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
          const jsonSheet = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });

          if (jsonSheet.length < 2) {
            alert("File Excel kosong atau tidak memiliki baris data!");
            return;
          }

          // Cari index kolom
          const headers = jsonSheet[0].map(h => String(h || '').trim().toUpperCase());
          let namaIdx = headers.findIndex(h => h.includes('NAMA'));
          let wilayahIdx = headers.findIndex(h => h.includes('WILAYAH'));
          let nominalIdx = headers.findIndex(h => h.includes('NOMINAL') || h.includes('JUMLAH'));
          let rekeningIdx = headers.findIndex(h => h.includes('REKENING') || h.includes('NO'));
          let statusIdx = headers.findIndex(h => h.includes('KET') || h.includes('STATUS') || h.includes('TF'));

          // Fallback jika tidak ada header
          if (namaIdx === -1) namaIdx = 1;
          if (wilayahIdx === -1) wilayahIdx = 2;
          if (nominalIdx === -1) nominalIdx = 3;
          if (rekeningIdx === -1) rekeningIdx = 4;
          if (statusIdx === -1) statusIdx = 5;

          parsedExcelRows = [];
          for (let i = 1; i < jsonSheet.length; i++) {
            const row = jsonSheet[i];
            if (!row || row.length === 0) continue;

            const nama = String(row[namaIdx] || '').trim();
            const wilayah = String(row[wilayahIdx] || '-').trim();
            const nominal = String(row[nominalIdx] || '0').replace(/[^0-9]/g, '');
            const rekening = String(row[rekeningIdx] || '').replace(/[^0-9]/g, '');
            let statusTf = String(row[statusIdx] || 'BELUM').trim().toUpperCase();
            if (statusTf !== 'SUDAH') statusTf = 'BELUM';

            if (nama !== '' && rekening !== '') {
              parsedExcelRows.push({
                nama: nama,
                wilayah: wilayah,
                nominal: nominal,
                rekening: rekening,
                status_tf: statusTf
              });
            }
          }

          if (parsedExcelRows.length > 0) {
            document.getElementById('chosenFileName').textContent = `${file.name} (${parsedExcelRows.length} data terbaca)`;
            document.getElementById('btnProcessImport').disabled = false;
          } else {
            alert("Tidak ada data valid yang bisa dibaca dari file Excel tersebut.");
          }
        } catch (err) {
          alert("Gagal membaca file Excel: " + err.message);
        }
      };
      reader.readAsArrayBuffer(file);
    }

    async function processImport() {
      if (parsedExcelRows.length === 0) return;

      const mode = document.querySelector('input[name="importMode"]:checked').value;
      const btn = document.getElementById('btnProcessImport');
      btn.textContent = "Mengimpor...";
      btn.disabled = true;

      try {
        const res = await fetch('api.php?action=import', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            mode: mode,
            rows: parsedExcelRows
          })
        });
        const json = await res.json();

        if (json.status === 'success') {
          closeImportModal();
          loadData();
          showToast("Impor Berhasil!", `${json.inserted} data berhasil dimasukkan`);
        } else {
          alert("Gagal impor: " + json.message);
        }
      } catch (err) {
        alert("Gagal menghubungi server saat impor data.");
      } finally {
        btn.textContent = "Mulai Impor";
        btn.disabled = false;
      }
    }

    // --- 6. UTILITIES ---
    function formatRupiah(val) {
      const num = parseFloat(val) || 0;
      return num.toLocaleString('en-US'); // Format 272,750 seperti pada gambar
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text || '';
      return div.innerHTML;
    }

    // Event listeners
    document.getElementById('searchInput').addEventListener('input', loadData);
    document.getElementById('filterStatus').addEventListener('change', loadData);
    document.getElementById('filterWilayah').addEventListener('change', loadData);

    // Initial load
    document.addEventListener('DOMContentLoaded', loadData);
  </script>
</body>
</html>

