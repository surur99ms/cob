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
  <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- SheetJS (Excel Parser & Generator) -->
  <script src="xlsx.full.min.js"></script>

  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
            mono: ['"JetBrains Mono"', 'monospace'],
          },
          colors: {
            brand: {
              50: '#ecfdf5',
              100: '#d1fae5',
              500: '#10b981',
              600: '#059669',
              700: '#047857',
            }
          }
        }
      }
    }
  </script>

  <style>
    body {
      font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
      background-color: #f8fafc;
      color: #0f172a;
    }
    .font-mono-num {
      font-family: 'JetBrains Mono', monospace;
    }
    /* Smooth modern scrollbar */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: #f1f5f9; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  </style>
</head>
<body class="min-h-screen flex flex-col bg-slate-50/50">

  <!-- Header / Navigation -->
  <header class="bg-white/80 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <div class="flex items-center gap-3 sm:gap-4">
        <a href="../katalog.php" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-all">
          <i class="fas fa-arrow-left text-[11px]"></i>
          <span class="hidden sm:inline">Katalog</span>
        </a>
        <div class="h-4 w-px bg-slate-200 hidden sm:block"></div>
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-sm shadow-emerald-500/20 flex-shrink-0">
            <i class="fas fa-money-check-alt text-sm"></i>
          </div>
          <div>
            <h1 class="text-sm sm:text-base font-bold text-slate-900 leading-tight">Rekap Transfer & Rekening</h1>
            <p class="text-[11px] text-slate-500 hidden sm:block">Salin nomor rekening 1-klik untuk m-Banking</p>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2 sm:gap-2.5">
        <button onclick="downloadTemplateExcel()" class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-all">
          <i class="fas fa-file-download text-emerald-600"></i> Unduh Template
        </button>
        <button onclick="openAddModal()" class="inline-flex items-center gap-1.5 sm:gap-2 px-3.5 sm:px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-sm shadow-emerald-600/25">
          <i class="fas fa-plus"></i>
          <span>Tambah</span>
        </button>
        <button onclick="openDeleteByJabatanModal()" class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-3.5 py-2 bg-rose-50 hover:bg-rose-100 active:scale-95 text-rose-600 border border-rose-200 rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-xs" title="Hapus data berdasarkan pilihan jabatan">
          <i class="fas fa-trash-alt text-xs"></i>
          <span>Hapus Data</span>
        </button>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="flex-grow max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-8 w-full">

    <!-- Top Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 mb-5 sm:mb-8">
      <!-- Total Penerima -->
      <div class="bg-white p-3.5 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-2 sm:mb-3">
          <span class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Penerima</span>
          <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs sm:text-sm">
            <i class="fas fa-users"></i>
          </div>
        </div>
        <div class="flex items-baseline gap-1.5 sm:gap-2">
          <span id="statTotalItems" class="text-xl sm:text-3xl font-extrabold text-slate-900 font-mono-num">0</span>
          <span class="text-[11px] sm:text-xs text-slate-500 font-medium">orang</span>
        </div>
      </div>

      <!-- Total Nominal -->
      <div class="bg-white p-3.5 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-2 sm:mb-3">
          <span class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Nominal</span>
          <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs sm:text-sm">
            <i class="fas fa-wallet"></i>
          </div>
        </div>
        <div class="flex items-baseline gap-1">
          <span class="text-[11px] sm:text-xs font-bold text-slate-400">Rp</span>
          <span id="statTotalNominal" class="text-lg sm:text-2xl font-extrabold text-slate-900 font-mono-num truncate">0</span>
        </div>
      </div>

      <!-- Sudah Transfer -->
      <div class="bg-white p-3.5 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-2 sm:mb-3">
          <span class="text-[11px] sm:text-xs font-semibold text-emerald-600 uppercase tracking-wider">Sudah Transfer</span>
          <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs sm:text-sm">
            <i class="fas fa-check-circle"></i>
          </div>
        </div>
        <div class="flex items-baseline gap-1.5 sm:gap-2">
          <span id="statSudahTf" class="text-xl sm:text-3xl font-extrabold text-emerald-600 font-mono-num">0</span>
          <span class="text-[11px] sm:text-xs text-slate-500 font-medium">selesai</span>
        </div>
      </div>

      <!-- Belum Transfer -->
      <div class="bg-white p-3.5 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-2 sm:mb-3">
          <span class="text-[11px] sm:text-xs font-semibold text-amber-600 uppercase tracking-wider">Belum Transfer</span>
          <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs sm:text-sm">
            <i class="fas fa-clock"></i>
          </div>
        </div>
        <div class="flex items-baseline gap-1.5 sm:gap-2">
          <span id="statBelumTf" class="text-xl sm:text-3xl font-extrabold text-amber-600 font-mono-num">0</span>
          <span class="text-[11px] sm:text-xs text-slate-500 font-medium">pending</span>
        </div>
      </div>
    </div>

    <!-- Action Bar & Controls -->
    <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-xs mb-5 flex flex-col gap-3">
      <!-- Top Row: Search & View Mode Switcher -->
      <div class="flex items-center justify-between gap-3">
        <!-- Search Input -->
        <div class="relative flex-grow max-w-md">
          <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
          <input type="text" id="searchInput" placeholder="Cari nama, jabatan, rekening..." 
            class="w-full pl-9 pr-4 py-2 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all text-slate-800 placeholder-slate-400">
        </div>

        <!-- View Mode Switcher (Kartu vs Tabel) -->
        <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200 flex-shrink-0">
          <button id="btnViewCard" onclick="setViewMode('card')" title="Tampilan Kartu (Sangat Nyaman di HP)" class="px-2.5 sm:px-3 py-1.5 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5">
            <i class="fas fa-th-large text-xs"></i>
            <span class="hidden sm:inline">Kartu</span>
          </button>
          <button id="btnViewTable" onclick="setViewMode('table')" title="Tampilan Tabel (Lebar)" class="px-2.5 sm:px-3 py-1.5 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5">
            <i class="fas fa-table text-xs"></i>
            <span class="hidden sm:inline">Tabel</span>
          </button>
        </div>
      </div>

      <!-- Bottom Row: Filters, Import & Export -->
      <div class="flex flex-wrap items-center gap-2 justify-between pt-1 border-t border-slate-100">
        <div class="flex flex-wrap items-center gap-2">
          <!-- Filter Status -->
          <select id="filterStatus" class="px-2.5 sm:px-3 py-1.5 sm:py-2 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all cursor-pointer">
            <option value="all">Semua Status</option>
            <option value="SUDAH">Status: SUDAH</option>
            <option value="BELUM">Status: BELUM</option>
          </select>

          <!-- Filter Jabatan -->
          <select id="filterJabatan" class="px-2.5 sm:px-3 py-1.5 sm:py-2 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all cursor-pointer">
            <option value="all">Semua Jabatan</option>
          </select>

          <!-- Filter Wilayah -->
          <select id="filterWilayah" class="px-2.5 sm:px-3 py-1.5 sm:py-2 bg-slate-50 hover:bg-slate-100/70 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all cursor-pointer">
            <option value="all">Semua Wilayah</option>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <!-- Import Excel -->
          <button onclick="openImportModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition-all shadow-xs">
            <i class="fas fa-file-import text-emerald-400 text-xs"></i>
            <span>Import</span>
          </button>

          <!-- Export Excel -->
          <button onclick="exportToExcel()" class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-xs font-semibold transition-all shadow-xs hover:border-slate-300">
            <i class="fas fa-file-excel text-emerald-600 text-xs"></i>
            <span>Export</span>
          </button>
        </div>
      </div>
    </div>

    <!-- VIEW CONTAINER 1: Tampilan Kartu (Default untuk Layar HP) -->
    <div id="cardsViewContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 mb-8">
      <!-- Injected by JavaScript -->
    </div>

    <!-- VIEW CONTAINER 2: Tampilan Tabel (Default untuk Laptop / Desktop) -->
    <div id="tableViewContainer" class="hidden bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-8">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] uppercase tracking-wider font-bold text-slate-500">
              <th class="py-3.5 px-4 text-center w-14">No</th>
              <th class="py-3.5 px-4">Nama Penerima</th>
              <th class="py-3.5 px-4 w-36">Jabatan</th>
              <th class="py-3.5 px-4 text-center w-28">Wilayah</th>
              <th class="py-3.5 px-4 text-right w-44">Nominal</th>
              <th class="py-3.5 px-4 w-64">Nomor Rekening</th>
              <th class="py-3.5 px-4 text-center w-32">Status TF</th>
              <th class="py-3.5 px-4 text-center w-24">Aksi</th>
            </tr>
          </thead>
          <tbody id="tableBody" class="divide-y divide-slate-100 text-sm">
            <!-- Rendered by JavaScript -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden bg-white rounded-2xl border border-slate-200/80 p-8 sm:p-12 text-center shadow-xs mb-8">
      <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-2xl">
        <i class="fas fa-inbox"></i>
      </div>
      <h3 class="text-sm font-bold text-slate-800 mb-1">Belum ada data transfer</h3>
      <p class="text-xs text-slate-500 max-w-sm mx-auto mb-4">Tambahkan data secara manual atau impor daftar penerima transfer dari file Excel Anda.</p>
      <div class="flex items-center justify-center gap-2">
        <button onclick="downloadTemplateExcel()" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
          <i class="fas fa-download text-emerald-600"></i> Download Template
        </button>
        <button onclick="openImportModal()" class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs">
          <i class="fas fa-file-import"></i> Import Sekarang
        </button>
      </div>
    </div>

  </main>

  <!-- Toast Notification -->
  <div id="toastNotification" class="fixed bottom-6 right-6 z-50 transform translate-y-24 opacity-0 transition-all duration-300 pointer-events-none">
    <div class="bg-slate-900/95 backdrop-blur-md text-white px-4 py-3 rounded-2xl shadow-xl border border-slate-700 flex items-center gap-3">
      <div class="w-7 h-7 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs font-bold border border-emerald-500/40">
        <i class="fas fa-check"></i>
      </div>
      <div>
        <h4 id="toastTitle" class="font-bold text-xs text-white leading-tight">Berhasil Disalin!</h4>
        <p id="toastMessage" class="text-[11px] text-slate-300 font-mono-num mt-0.5">Siap di-paste ke m-Banking</p>
      </div>
    </div>
  </div>

  <!-- Modal: Import Excel -->
  <div id="importModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-xl w-full p-5 sm:p-7 shadow-2xl border border-slate-200/80 animate-in fade-in zoom-in-95 duration-150 max-h-[92vh] overflow-y-auto">
      
      <!-- Modal Header -->
      <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
            <i class="fas fa-file-excel"></i>
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-900 leading-tight">Import Data dari Excel</h3>
            <p class="text-xs text-slate-500">Mendukung format .xlsx, .xls, dan .csv</p>
          </div>
        </div>
        <button onclick="closeImportModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
          <i class="fas fa-times text-xs"></i>
        </button>
      </div>

      <!-- Template Excel Download Banner -->
      <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/80 rounded-2xl p-4 mb-5 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-sm shadow-xs flex-shrink-0">
            <i class="fas fa-file-download"></i>
          </div>
          <div>
            <h4 class="text-xs font-bold text-slate-900">Belum punya format Excel?</h4>
            <p class="text-[11px] text-slate-600">Unduh template siap pakai agar susunan kolom sesuai.</p>
          </div>
        </div>
        <button type="button" onclick="downloadTemplateExcel()" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs flex-shrink-0 active:scale-95">
          <i class="fas fa-download text-[11px]"></i> Template
        </button>
      </div>

      <!-- File Upload Dropzone -->
      <div class="space-y-4 mb-6">
        <div class="border-2 border-dashed border-slate-200 hover:border-emerald-500 rounded-2xl p-5 text-center transition-all cursor-pointer bg-slate-50/70 hover:bg-emerald-50/30 group" onclick="document.getElementById('excelFileInput').click()">
          <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 group-hover:border-emerald-200 flex items-center justify-center mx-auto mb-2 text-emerald-600 shadow-xs transition-colors">
            <i class="fas fa-cloud-upload-alt text-xl"></i>
          </div>
          <span class="block text-xs sm:text-sm font-bold text-slate-800">Klik untuk memilih file Excel</span>
          <span id="chosenFileName" class="text-[11px] text-slate-400 mt-1 block">File .xlsx, .xls, atau .csv</span>
        </div>
        <input type="file" id="excelFileInput" accept=".xlsx, .xls, .csv" class="hidden" onchange="handleFileChosen(event)">

        <!-- Column Guide -->
        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 text-[11px] text-slate-600">
          <span class="font-bold text-slate-700 block mb-1">Susunan Kolom yang Dibaca:</span>
          <div class="flex flex-wrap gap-1.5">
            <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 font-mono text-slate-700">NAMA</span>
            <span class="px-2 py-0.5 rounded-md bg-indigo-50 border border-indigo-200 font-mono text-indigo-700 font-bold">JABATAN</span>
            <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 font-mono text-slate-700">WILAYAH</span>
            <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 font-mono text-slate-700">NOMINAL</span>
            <span class="px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200 font-mono text-emerald-700 font-bold">REKENING</span>
            <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 font-mono text-slate-700">KET. TF</span>
          </div>
        </div>

        <!-- Live Preview Table (Muncul saat file dipilih) -->
        <div id="importPreviewBox" class="hidden bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
              <i class="fas fa-check-circle text-emerald-600"></i> Preview Data Terbaca:
            </span>
            <span id="previewCountBadge" class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
              0 Data Terbaca
            </span>
          </div>
          <div class="overflow-x-auto max-h-48 border border-slate-200 rounded-xl bg-white shadow-xs">
            <table class="w-full text-left text-[11px] border-collapse">
              <thead class="bg-slate-100 text-slate-600 font-bold sticky top-0">
                <tr>
                  <th class="p-2 border-b">Nama</th>
                  <th class="p-2 border-b">Jabatan</th>
                  <th class="p-2 border-b">Wilayah</th>
                  <th class="p-2 border-b text-right">Nominal</th>
                  <th class="p-2 border-b text-emerald-700 font-mono">Nomor Rekening</th>
                </tr>
              </thead>
              <tbody id="previewTableBody" class="divide-y divide-slate-100">
                <!-- Preview items injected here -->
              </tbody>
            </table>
          </div>
        </div>

        <!-- Import Mode -->
        <div>
          <label class="text-xs font-bold text-slate-700 block mb-2">Metode Impor:</label>
          <div class="grid grid-cols-2 gap-2.5">
            <label class="flex items-start gap-2.5 p-3 border border-slate-200 rounded-xl text-xs cursor-pointer bg-slate-50 hover:bg-slate-100 transition-colors">
              <input type="radio" name="importMode" value="append" checked class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
              <div>
                <span class="font-bold text-slate-800 block">Append</span>
                <span class="text-[10px] text-slate-500">Tambah ke data yang sudah ada</span>
              </div>
            </label>
            <label class="flex items-start gap-2.5 p-3 border border-slate-200 rounded-xl text-xs cursor-pointer bg-slate-50 hover:bg-slate-100 transition-colors">
              <input type="radio" name="importMode" value="replace" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
              <div>
                <span class="font-bold text-slate-800 block">Replace</span>
                <span class="text-[10px] text-slate-500">Hapus data lama & ganti baru</span>
              </div>
            </label>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-2.5">
        <button type="button" onclick="closeImportModal()" class="w-1/2 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors">Batal</button>
        <button type="button" id="btnProcessImport" onclick="processImport()" disabled class="w-1/2 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition-all shadow-xs disabled:opacity-50 disabled:cursor-not-allowed">
          Mulai Impor
        </button>
      </div>
    </div>
  </div>

  <!-- Modal: Tambah / Edit Data Manual -->
  <div id="dataModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border border-slate-200/80 animate-in fade-in zoom-in-95 duration-150">
      <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
        <h3 id="modalFormTitle" class="text-base font-bold text-slate-900">Tambah Data Penerima</h3>
        <button onclick="closeDataModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
          <i class="fas fa-times text-xs"></i>
        </button>
      </div>

      <form id="dataForm" onsubmit="handleFormSubmit(event)" class="space-y-4">
        <input type="hidden" id="formId" value="">

        <div>
          <label class="text-xs font-bold text-slate-700 block mb-1.5">Nama Lengkap</label>
          <input type="text" id="formNama" required placeholder="Contoh: ABD MAJID" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all text-slate-900 font-semibold">
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-xs font-bold text-slate-700 block mb-1.5">Jabatan</label>
            <input type="text" id="formJabatan" placeholder="Contoh: Staff / Pengurus" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all text-slate-900 font-semibold">
          </div>
          <div>
            <label class="text-xs font-bold text-slate-700 block mb-1.5">Wilayah</label>
            <input type="text" id="formWilayah" placeholder="Contoh: J-1" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all text-slate-900 font-semibold">
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-xs font-bold text-slate-700 block mb-1.5">Status Transfer</label>
            <select id="formStatus" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all text-slate-900 font-semibold">
              <option value="BELUM">BELUM</option>
              <option value="SUDAH">SUDAH</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-bold text-slate-700 block mb-1.5">Nominal (Rp)</label>
            <input type="number" id="formNominal" required placeholder="272750" class="w-full pl-3.5 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-mono-num font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all text-slate-900">
          </div>
        </div>

        <div>
          <label class="text-xs font-bold text-slate-700 block mb-1.5">Nomor Rekening / Virtual Account</label>
          <input type="text" id="formRekening" required placeholder="Contoh: 17451201715123" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-mono-num font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all text-slate-900">
          <span class="text-[11px] text-slate-400 mt-1 block">Nomor rekening saja (10-16 digit) tanpa titik/spasi.</span>
        </div>

        <div class="pt-3 flex gap-2.5">
          <button type="button" onclick="closeDataModal()" class="w-1/2 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors">Batal</button>
          <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition-all shadow-xs">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal: Hapus Data Berdasarkan Jabatan -->
  <div id="deleteByJabatanModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-md w-full p-5 sm:p-7 shadow-2xl border border-slate-200/80 animate-in fade-in zoom-in-95 duration-150 max-h-[92vh] overflow-y-auto">
      
      <!-- Modal Header -->
      <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
            <i class="fas fa-trash-alt"></i>
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-900 leading-tight">Hapus Data Transfer</h3>
            <p class="text-xs text-slate-500">Pilih data yang ingin dihapus sesuai jabatan</p>
          </div>
        </div>
        <button onclick="closeDeleteByJabatanModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
          <i class="fas fa-times text-xs"></i>
        </button>
      </div>

      <!-- Jabatan Selection List -->
      <div class="space-y-3 mb-5">
        <div class="flex items-center justify-between">
          <label class="text-xs font-bold text-slate-700">Pilih Jabatan untuk Dihapus:</label>
          <button type="button" onclick="toggleSelectAllDeleteJabatan()" id="btnToggleSelectAllJabatan" class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700">
            Pilih Semua
          </button>
        </div>

        <!-- Container for Checkboxes -->
        <div id="deleteJabatanListContainer" class="space-y-2 max-h-56 overflow-y-auto pr-1">
          <!-- Injected dynamically by JS -->
        </div>

        <!-- Warning Alert Box -->
        <div class="bg-rose-50 border border-rose-200/80 rounded-2xl p-3.5 flex items-start gap-2.5 text-rose-800 text-xs">
          <i class="fas fa-exclamation-triangle text-rose-500 mt-0.5 text-sm flex-shrink-0"></i>
          <div>
            <span class="font-bold block">Tindakan ini tidak dapat dibatalkan!</span>
            <span class="text-[11px] text-rose-700">Data penerima transfer dari jabatan yang dicentang akan dihapus secara permanen dari database.</span>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-2.5">
        <button type="button" onclick="closeDeleteByJabatanModal()" class="w-1/2 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors">Batal</button>
        <button type="button" id="btnExecuteDeleteJabatan" onclick="executeDeleteByJabatan()" disabled class="w-1/2 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs transition-all shadow-xs disabled:opacity-50 disabled:cursor-not-allowed">
          Hapus Terpilih
        </button>
      </div>
    </div>
  </div>

  <!-- JavaScript Logic -->
  <script>
    let rawData = [];
    let parsedExcelRows = [];
    
    // View mode: default 'card' di HP (lebar < 768px), default 'table' di Laptop / Desktop
    let currentViewMode = window.innerWidth < 768 ? 'card' : 'table';

    function setViewMode(mode) {
      currentViewMode = mode;
      const btnCard = document.getElementById('btnViewCard');
      const btnTable = document.getElementById('btnViewTable');
      const cardsContainer = document.getElementById('cardsViewContainer');
      const tableContainer = document.getElementById('tableViewContainer');

      if (mode === 'card') {
        cardsContainer.classList.remove('hidden');
        tableContainer.classList.add('hidden');
        btnCard.className = "px-2.5 sm:px-3 py-1.5 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5 bg-white text-slate-900 shadow-xs";
        btnTable.className = "px-2.5 sm:px-3 py-1.5 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5 text-slate-500 hover:text-slate-800";
      } else {
        cardsContainer.classList.add('hidden');
        tableContainer.classList.remove('hidden');
        btnTable.className = "px-2.5 sm:px-3 py-1.5 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5 bg-white text-slate-900 shadow-xs";
        btnCard.className = "px-2.5 sm:px-3 py-1.5 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5 text-slate-500 hover:text-slate-800";
      }
    }

    // --- 1. FETCH & RENDER DATA ---
    async function loadData() {
      const q = document.getElementById('searchInput').value;
      const status = document.getElementById('filterStatus').value;
      const wilayah = document.getElementById('filterWilayah').value;
      const jabatan = document.getElementById('filterJabatan').value;

      try {
        const res = await fetch(`api.php?action=list&q=${encodeURIComponent(q)}&status=${encodeURIComponent(status)}&wilayah=${encodeURIComponent(wilayah)}&jabatan=${encodeURIComponent(jabatan)}`);
        const json = await res.json();

        if (json.status === 'success') {
          rawData = json.data || [];
          renderAllViews(rawData);
          renderStats(json.stats);
          updateWilayahFilter(json.wilayah_list, wilayah);
          updateJabatanFilter(json.jabatan_list, jabatan);
        }
      } catch (e) {
        console.error("Gagal memuat data:", e);
      }
    }

    function getInitials(name) {
      if (!name) return '??';
      const parts = name.trim().split(/\s+/);
      if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
      return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }

    function renderAllViews(items) {
      const empty = document.getElementById('emptyState');
      const cardsContainer = document.getElementById('cardsViewContainer');
      const tableContainer = document.getElementById('tableViewContainer');
      const tbody = document.getElementById('tableBody');

      cardsContainer.innerHTML = '';
      tbody.innerHTML = '';

      if (items.length === 0) {
        empty.classList.remove('hidden');
        cardsContainer.classList.add('hidden');
        tableContainer.classList.add('hidden');
        return;
      }
      empty.classList.add('hidden');

      // Terapkan view mode aktif
      setViewMode(currentViewMode);

      items.forEach((item, index) => {
        const isSudah = (item.status_tf || '').toUpperCase() === 'SUDAH';
        const formattedNominal = formatRupiah(item.nominal);
        const initials = getInitials(item.nama);

        // 1. RENDER KARTU UNTUK MOBILE / CARD VIEW (Semua data tampak tanpa perlu geser ke samping!)
        const card = document.createElement('div');
        card.className = "bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between gap-3 group";
        card.innerHTML = `
          <!-- Baris Atas: Nomor, Nama & Jabatan, dan Status Transfer -->
          <div class="flex items-start justify-between gap-2.5">
            <div class="flex items-start gap-2.5 min-w-0">
              <span class="text-xs font-bold text-slate-400 mt-1">#${index + 1}</span>
              <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 group-hover:bg-emerald-100 group-hover:text-emerald-700 transition-colors flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">
                ${initials}
              </div>
              <div class="min-w-0">
                <h4 class="font-bold text-slate-900 text-sm leading-snug group-hover:text-emerald-700 transition-colors break-words">${escapeHtml(item.nama)}</h4>
                <div class="flex flex-wrap items-center gap-1.5 mt-1">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                    <i class="fas fa-briefcase text-[9px] mr-1 opacity-70"></i> ${escapeHtml(item.jabatan || '-')}
                  </span>
                  <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">
                    ${escapeHtml(item.wilayah || '-')}
                  </span>
                </div>
              </div>
            </div>

            <!-- Tombol Status TF (1-Tap Toggle) -->
            <button onclick="toggleStatus(${item.id})" title="Klik untuk ubah status transfer" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold flex-shrink-0 transition-all shadow-xs active:scale-95 ${
              isSudah
                ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100'
                : 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100'
            }">
              <span class="w-1.5 h-1.5 rounded-full ${isSudah ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'}"></span>
              <span>${escapeHtml(item.status_tf)}</span>
            </button>
          </div>

          <!-- Baris Tengah: Nominal Transfer -->
          <div class="bg-slate-50/80 rounded-xl px-3 py-2 border border-slate-100 flex items-center justify-between">
            <span class="text-xs text-slate-500 font-medium">Nominal Transfer:</span>
            <div class="inline-flex items-center gap-1.5">
              <span class="text-xs text-slate-400 font-bold">Rp</span>
              <span class="text-sm font-extrabold text-slate-900 font-mono-num">${formattedNominal}</span>
              <button onclick="copyToClipboard('${item.nominal}', 'Nominal Rp ${formattedNominal} Disalin!')" title="Salin Nominal murni" class="p-1 text-slate-400 hover:text-slate-700 active:scale-90 transition-all">
                <i class="far fa-copy text-xs"></i>
              </button>
            </div>
          </div>

          <!-- Baris Bawah: Kotak Rekening & Tombol Salin Cepat -->
          <div class="bg-emerald-50/60 border border-emerald-200/80 rounded-xl p-2.5 flex items-center justify-between gap-2">
            <div class="min-w-0">
              <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">No. Rekening / VA:</span>
              <span class="font-mono-num font-extrabold text-slate-900 text-sm tracking-wide select-all block truncate">${escapeHtml(item.rekening)}</span>
            </div>
            <button onclick="copyToClipboard('${item.rekening}', 'Nomor Rekening Tersalin!')" title="Salin Nomor Rekening" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-xs flex-shrink-0 active:scale-95 transition-all">
              <i class="fas fa-copy text-xs"></i>
              <span>Salin</span>
            </button>
          </div>

          <!-- Aksi Edit & Hapus -->
          <div class="flex items-center justify-end gap-3 pt-1 text-xs text-slate-400 border-t border-slate-100">
            <button onclick="openEditModal(${item.id})" class="inline-flex items-center gap-1 hover:text-blue-600 font-semibold transition-colors">
              <i class="fas fa-pen text-[10px]"></i> Edit
            </button>
            <button onclick="deleteItem(${item.id})" class="inline-flex items-center gap-1 hover:text-rose-600 font-semibold transition-colors">
              <i class="fas fa-trash-alt text-[10px]"></i> Hapus
            </button>
          </div>
        `;
        cardsContainer.appendChild(card);

        // 2. RENDER BARIS TABEL UNTUK DESKTOP / TABLE VIEW
        const tr = document.createElement('tr');
        tr.className = `hover:bg-slate-50/80 transition-colors group`;
        tr.innerHTML = `
          <!-- No -->
          <td class="py-4 px-4 text-center font-bold text-slate-400 text-xs">
            ${index + 1}
          </td>

          <!-- Nama -->
          <td class="py-4 px-4">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 group-hover:bg-emerald-100 group-hover:text-emerald-700 transition-colors flex items-center justify-center font-bold text-xs flex-shrink-0">
                ${initials}
              </div>
              <span class="font-bold text-slate-900 group-hover:text-emerald-700 transition-colors">${escapeHtml(item.nama)}</span>
            </div>
          </td>

          <!-- Jabatan -->
          <td class="py-4 px-4">
            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
              <i class="fas fa-briefcase text-[10px] mr-1.5 opacity-70"></i>
              ${escapeHtml(item.jabatan || '-')}
            </span>
          </td>

          <!-- Wilayah -->
          <td class="py-4 px-4 text-center">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200/60">
              ${escapeHtml(item.wilayah || '-')}
            </span>
          </td>

          <!-- Nominal -->
          <td class="py-4 px-4 text-right">
            <div class="inline-flex items-center justify-end gap-1.5 font-mono-num font-bold text-slate-900">
              <span class="text-xs text-slate-400 font-sans">Rp</span>
              <span>${formattedNominal}</span>
              <button onclick="copyToClipboard('${item.nominal}', 'Nominal Rp ${formattedNominal} Disalin!')" title="Salin Nominal murni" class="opacity-0 group-hover:opacity-100 p-1 text-slate-400 hover:text-slate-700 rounded-md transition-all">
                <i class="far fa-copy text-xs"></i>
              </button>
            </div>
          </td>

          <!-- Rekening (1-Click Salin) -->
          <td class="py-4 px-4">
            <div class="inline-flex items-center gap-2 bg-slate-50 hover:bg-emerald-50/50 border border-slate-200/80 hover:border-emerald-300 px-3 py-1.5 rounded-xl transition-all">
              <span class="font-mono-num font-bold text-slate-900 tracking-wide text-xs sm:text-sm select-all">
                ${escapeHtml(item.rekening)}
              </span>
              <button onclick="copyToClipboard('${item.rekening}', 'Nomor Rekening Tersalin!')" title="Salin Nomor Rekening" class="ml-1 inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-xs active:scale-95">
                <i class="fas fa-copy text-[10px]"></i>
                <span>Salin</span>
              </button>
            </div>
          </td>

          <!-- Status Transfer (Click to Toggle) -->
          <td class="py-4 px-4 text-center">
            <button onclick="toggleStatus(${item.id})" title="Klik untuk ubah status transfer" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all cursor-pointer shadow-xs active:scale-95 ${
              isSudah
                ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100'
                : 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100'
            }">
              <span class="w-1.5 h-1.5 rounded-full ${isSudah ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'}"></span>
              <span>${escapeHtml(item.status_tf)}</span>
            </button>
          </td>

          <!-- Aksi (Edit & Delete) -->
          <td class="py-4 px-4 text-center">
            <div class="inline-flex items-center gap-1 text-slate-400">
              <button onclick="openEditModal(${item.id})" title="Edit data" class="p-1.5 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                <i class="fas fa-pen text-xs"></i>
              </button>
              <button onclick="deleteItem(${item.id})" title="Hapus data" class="p-1.5 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors">
                <i class="fas fa-trash-alt text-xs"></i>
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
      document.getElementById('statTotalNominal').textContent = formatRupiah(stats.total_nominal);
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

    function updateJabatanFilter(jabatanList, selectedVal) {
      const sel = document.getElementById('filterJabatan');
      const current = selectedVal || sel.value;
      sel.innerHTML = '<option value="all">Semua Jabatan</option>';
      (jabatanList || []).forEach(j => {
        const opt = document.createElement('option');
        opt.value = j;
        opt.textContent = 'Jabatan: ' + j;
        if (j === current) opt.selected = true;
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
        console.error("Gagal mengubah status:", e);
      }
    }

    function openAddModal() {
      document.getElementById('modalFormTitle').textContent = 'Tambah Data Penerima';
      document.getElementById('formId').value = '';
      document.getElementById('formNama').value = '';
      document.getElementById('formJabatan').value = '';
      document.getElementById('formWilayah').value = 'J-1';
      document.getElementById('formNominal').value = '';
      document.getElementById('formRekening').value = '';
      document.getElementById('formStatus').value = 'BELUM';
      document.getElementById('dataModal').classList.remove('hidden');
    }

    function openEditModal(id) {
      const item = rawData.find(d => d.id === id);
      if (!item) return;

      document.getElementById('modalFormTitle').textContent = 'Edit Data Penerima';
      document.getElementById('formId').value = item.id;
      document.getElementById('formNama').value = item.nama;
      document.getElementById('formJabatan').value = item.jabatan || '';
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
        jabatan: document.getElementById('formJabatan').value,
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
      if (!confirm("Apakah Anda yakin ingin menghapus data ini?")) return;

      try {
        const res = await fetch('api.php?action=delete', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id })
        });
        const json = await res.json();
        if (json.status === 'success') {
          loadData();
          showToast("Data Dihapus", "Baris berhasil dihapus");
        }
      } catch (e) {
        alert("Gagal menghapus data");
      }
    }

    // --- 3B. HAPUS DATA BERDASARKAN JABATAN ---
    function openDeleteByJabatanModal() {
      const container = document.getElementById('deleteJabatanListContainer');
      const btnDelete = document.getElementById('btnExecuteDeleteJabatan');
      const btnToggle = document.getElementById('btnToggleSelectAllJabatan');
      container.innerHTML = '';
      btnDelete.disabled = true;
      btnDelete.textContent = "Hapus Terpilih";
      if (btnToggle) btnToggle.textContent = 'Pilih Semua';

      if (!rawData || rawData.length === 0) {
        container.innerHTML = `
          <div class="p-4 text-center text-xs text-slate-400 bg-slate-50 rounded-xl border border-slate-200">
            Tidak ada data untuk dihapus.
          </div>
        `;
        document.getElementById('deleteByJabatanModal').classList.remove('hidden');
        return;
      }

      // Hitung ringkasan data per jabatan
      const summary = {};
      rawData.forEach(item => {
        const jab = (item.jabatan || '-').trim() || '-';
        if (!summary[jab]) {
          summary[jab] = { count: 0, nominal: 0 };
        }
        summary[jab].count += 1;
        summary[jab].nominal += parseFloat(item.nominal) || 0;
      });

      const jabatanKeys = Object.keys(summary).sort();

      jabatanKeys.forEach(jab => {
        const data = summary[jab];
        const label = document.createElement('label');
        label.className = "flex items-center justify-between p-3 border border-slate-200 rounded-xl text-xs cursor-pointer hover:bg-slate-50 transition-colors group";
        label.innerHTML = `
          <div class="flex items-center gap-2.5 min-w-0">
            <input type="checkbox" name="deleteJabatanCheckbox" value="${escapeHtml(jab)}" onchange="updateDeleteJabatanButtonState()" class="w-4 h-4 text-rose-600 rounded focus:ring-rose-500 border-slate-300">
            <div class="min-w-0">
              <span class="font-bold text-slate-800 block truncate group-hover:text-rose-600 transition-colors">
                ${escapeHtml(jab)}
              </span>
              <span class="text-[10px] text-slate-400">Total: Rp ${formatRupiah(data.nominal)}</span>
            </div>
          </div>
          <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 flex-shrink-0">
            ${data.count} orang
          </span>
        `;
        container.appendChild(label);
      });

      document.getElementById('deleteByJabatanModal').classList.remove('hidden');
    }

    function closeDeleteByJabatanModal() {
      document.getElementById('deleteByJabatanModal').classList.add('hidden');
    }

    function updateDeleteJabatanButtonState() {
      const checkboxes = document.querySelectorAll('input[name="deleteJabatanCheckbox"]:checked');
      const allCheckboxes = document.querySelectorAll('input[name="deleteJabatanCheckbox"]');
      const btnDelete = document.getElementById('btnExecuteDeleteJabatan');
      const btnToggle = document.getElementById('btnToggleSelectAllJabatan');
      const count = checkboxes.length;

      if (btnToggle && allCheckboxes.length > 0) {
        btnToggle.textContent = (count === allCheckboxes.length) ? 'Batal Pilih' : 'Pilih Semua';
      }

      if (count > 0) {
        let totalOrang = 0;
        const selectedJabs = Array.from(checkboxes).map(c => c.value);
        rawData.forEach(item => {
          const jab = (item.jabatan || '-').trim() || '-';
          if (selectedJabs.includes(jab)) totalOrang++;
        });

        btnDelete.disabled = false;
        btnDelete.textContent = `Hapus ${totalOrang} Data Terpilih`;
      } else {
        btnDelete.disabled = true;
        btnDelete.textContent = "Hapus Terpilih";
      }
    }

    function toggleSelectAllDeleteJabatan() {
      const checkboxes = document.querySelectorAll('input[name="deleteJabatanCheckbox"]');
      if (checkboxes.length === 0) return;

      const allChecked = Array.from(checkboxes).every(c => c.checked);
      checkboxes.forEach(c => c.checked = !allChecked);

      const btn = document.getElementById('btnToggleSelectAllJabatan');
      if (btn) btn.textContent = allChecked ? 'Pilih Semua' : 'Batal Pilih';
      updateDeleteJabatanButtonState();
    }

    async function executeDeleteByJabatan() {
      const checkboxes = document.querySelectorAll('input[name="deleteJabatanCheckbox"]:checked');
      const selected = Array.from(checkboxes).map(c => c.value);

      if (selected.length === 0) return;

      const allCheckboxes = document.querySelectorAll('input[name="deleteJabatanCheckbox"]');
      const isAll = (selected.length === allCheckboxes.length);

      let totalOrang = 0;
      rawData.forEach(item => {
        const jab = (item.jabatan || '-').trim() || '-';
        if (selected.includes(jab)) totalOrang++;
      });

      let confirmMsg = isAll 
        ? `PERINGATAN: Anda memilih SEMUA jabatan (${totalOrang} orang). Ini akan MENGOSONGKAN SELURUH data transfer! Apakah Anda yakin?`
        : `Apakah Anda yakin ingin menghapus ${totalOrang} data penerima dengan jabatan: "${selected.join(', ')}"?`;

      if (!confirm(confirmMsg)) return;

      const btn = document.getElementById('btnExecuteDeleteJabatan');
      btn.disabled = true;
      btn.textContent = "Menghapus...";

      try {
        const res = await fetch('api.php?action=delete_by_jabatan', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            jabatan: selected,
            delete_all: isAll
          })
        });

        const json = await res.json();
        if (json.status === 'success') {
          closeDeleteByJabatanModal();
          await loadData();
          showToast("Data Berhasil Dihapus", json.message || "Data jabatan terpilih berhasil dihapus");
        } else {
          alert("Gagal menghapus data: " + json.message);
        }
      } catch (e) {
        alert("Gagal menghubungi server saat menghapus data.");
      } finally {
        btn.disabled = false;
        btn.textContent = "Hapus Terpilih";
      }
    }

    // --- 4. DOWNLOAD TEMPLATE EXCEL ---
    function downloadTemplateExcel() {
      const templateData = [
        {
          "No": 1,
          "NAMA": "ABD MAJID",
          "JABATAN": "Staff",
          "WILAYAH": "J-1",
          "NOMINAL": 272750,
          "REKENING": "17451201715123",
          "KET. TF": "BELUM"
        },
        {
          "No": 2,
          "NAMA": "ABD WAHID",
          "JABATAN": "Pengurus",
          "WILAYAH": "J-1",
          "NOMINAL": 154950,
          "REKENING": "9881230208204356",
          "KET. TF": "BELUM"
        },
        {
          "No": 3,
          "NAMA": "AHMAD IDRIS",
          "JABATAN": "Staff",
          "WILAYAH": "J-1",
          "NOMINAL": 110550,
          "REKENING": "17453742019647",
          "KET. TF": "BELUM"
        },
        {
          "No": 4,
          "NAMA": "AHMAD ZAINI",
          "JABATAN": "Koordinator",
          "WILAYAH": "J-1",
          "NOMINAL": 335100,
          "REKENING": "115810018",
          "KET. TF": "SUDAH"
        }
      ];

      const ws = XLSX.utils.json_to_sheet(templateData);
      ws['!cols'] = [
        { wch: 6 },
        { wch: 26 },
        { wch: 18 },
        { wch: 12 },
        { wch: 15 },
        { wch: 24 },
        { wch: 12 }
      ];
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, "Template_Rekap");
      XLSX.writeFile(wb, "Template_Import_Rekening.xlsx");
      showToast("Template Diunduh!", "Template_Import_Rekening.xlsx siap diedit");
    }

    // --- 5. EXPORT TO EXCEL ---
    function exportToExcel() {
      if (rawData.length === 0) {
        alert("Belum ada data untuk diekspor!");
        return;
      }

      const rowsForExcel = rawData.map((item, idx) => ({
        "No": idx + 1,
        "NAMA": item.nama,
        "JABATAN": item.jabatan || '-',
        "WILAYAH": item.wilayah,
        "NOMINAL": parseFloat(item.nominal),
        "REKENING": item.rekening,
        "KET. TF": item.status_tf
      }));

      const ws = XLSX.utils.json_to_sheet(rowsForExcel);
      ws['!cols'] = [
        { wch: 6 },
        { wch: 26 },
        { wch: 18 },
        { wch: 12 },
        { wch: 15 },
        { wch: 24 },
        { wch: 12 }
      ];
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, "Rekap Transfer");

      const today = new Date().toISOString().slice(0, 10);
      XLSX.writeFile(wb, `Rekap_Transfer_Rekening_${today}.xlsx`);
      showToast("Export Berhasil!", `File Excel Rekap berhasil diunduh`);
    }

    // --- 6. IMPORT FROM EXCEL ---
    function openImportModal() {
      parsedExcelRows = [];
      document.getElementById('excelFileInput').value = '';
      document.getElementById('chosenFileName').textContent = 'File .xlsx, .xls, atau .csv';
      document.getElementById('btnProcessImport').disabled = true;
      document.getElementById('importPreviewBox').classList.add('hidden');
      document.getElementById('previewTableBody').innerHTML = '';
      document.getElementById('importModal').classList.remove('hidden');
    }

    function closeImportModal() {
      document.getElementById('importModal').classList.add('hidden');
    }

    function extractCleanRekening(cellVal) {
      if (cellVal === null || cellVal === undefined) return '';
      let str = String(cellVal).trim();
      if (/^[0-9.]+[eE]\+[0-9]+$/.test(str)) {
        try {
          str = BigInt(Math.round(Number(str))).toString();
        } catch (e) {}
      }
      return str.replace(/[^0-9]/g, '');
    }

    function parseNominal(val) {
      if (typeof val === 'number') return Math.round(val);
      let str = String(val || '').trim();
      str = str.replace(/rp/gi, '').replace(/\s+/g, '');
      if (str.includes(',') && str.includes('.')) {
        str = str.replace(/,/g, '');
        return Math.round(parseFloat(str) || 0);
      }
      if (str.includes(',')) {
        const parts = str.split(',');
        if (parts.length === 2 && (parts[1] === '00' || parts[1] === '0')) {
          return parseInt(parts[0].replace(/[^0-9]/g, ''), 10) || 0;
        }
        return parseInt(str.replace(/[^0-9]/g, ''), 10) || 0;
      }
      if (str.includes('.')) {
        const parts = str.split('.');
        if (parts.length === 2 && (parts[1] === '00' || parts[1] === '0')) {
          return parseInt(parts[0].replace(/[^0-9]/g, ''), 10) || 0;
        }
        return parseInt(str.replace(/[^0-9]/g, ''), 10) || 0;
      }
      return parseInt(str.replace(/[^0-9]/g, ''), 10) || 0;
    }

    function handleFileChosen(e) {
      const file = e.target.files[0];
      if (!file) return;

      document.getElementById('chosenFileName').textContent = file.name;
      const previewBox = document.getElementById('importPreviewBox');
      const previewTbody = document.getElementById('previewTableBody');
      const previewCountBadge = document.getElementById('previewCountBadge');

      const reader = new FileReader();
      reader.onload = function(evt) {
        try {
          const data = new Uint8Array(evt.target.result);
          const workbook = XLSX.read(data, { type: 'array', cellText: true, raw: false });
          const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
          const jsonSheet = XLSX.utils.sheet_to_json(firstSheet, { header: 1, raw: false, defval: '' });

          if (jsonSheet.length < 2) {
            alert("File Excel kosong atau tidak memiliki baris data!");
            return;
          }

          const headers = jsonSheet[0].map(h => String(h || '').trim());
          const cleanHeader = (h) => String(h || '').toUpperCase().replace(/[^A-Z0-9]/g, '');

          // 1. NAMA
          let namaIdx = headers.findIndex(h => {
            const c = cleanHeader(h);
            return c.includes('NAMA') || c.includes('NAME') || c.includes('PENERIMA');
          });

          // 2. JABATAN
          let jabatanIdx = headers.findIndex(h => {
            const c = cleanHeader(h);
            return c.includes('JABATAN') || c.includes('POSISI') || c.includes('DIVISI') || c.includes('ROLE');
          });

          // 3. WILAYAH
          let wilayahIdx = headers.findIndex(h => {
            const c = cleanHeader(h);
            return c.includes('WILAYAH') || c.includes('WIL') || c.includes('DAERAH') || c.includes('RAYON') || c.includes('CABANG');
          });

          // 4. NOMINAL
          let nominalIdx = headers.findIndex(h => {
            const c = cleanHeader(h);
            return c.includes('NOMINAL') || c.includes('JUMLAH') || c.includes('TOTAL') || c === 'RP';
          });

          // 5. REKENING (PENTING: Jangan sampai mencocokkan 'NO' urutan!)
          let rekeningIdx = headers.findIndex(h => {
            const c = cleanHeader(h);
            if (c.includes('REKENING') || c.includes('REK')) return true;
            if (c.includes('ACCOUNT') || c.includes('ACC')) return true;
            if (c === 'VA' || c.includes('VIRTUAL') || c.includes('NOVA')) return true;
            return false;
          });

          // 6. STATUS TF
          let statusIdx = headers.findIndex(h => {
            const c = cleanHeader(h);
            return c.includes('KET') || c.includes('STATUS') || c.includes('TF') || c.includes('TRANSFER');
          });

          const sampleRow = jsonSheet[1] || [];

          if (rekeningIdx === -1) {
            for (let col = 0; col < sampleRow.length; col++) {
              if (col === namaIdx || col === jabatanIdx || col === wilayahIdx || col === nominalIdx) continue;
              const val = String(sampleRow[col] || '').replace(/[^0-9]/g, '');
              if (val.length >= 8) {
                rekeningIdx = col;
                break;
              }
            }
          }

          if (namaIdx === -1) {
            for (let col = 0; col < sampleRow.length; col++) {
              if (col === rekeningIdx || col === nominalIdx) continue;
              const val = String(sampleRow[col] || '').trim();
              if (/[a-zA-Z]/.test(val)) {
                namaIdx = col;
                break;
              }
            }
          }

          if (rekeningIdx === -1) {
            alert("Kolom Nomor Rekening tidak ditemukan! Pastikan ada kolom berjudul 'REKENING', 'NO REK', atau kolom berisi nomor rekening (10-16 digit).");
            return;
          }

          parsedExcelRows = [];
          for (let i = 1; i < jsonSheet.length; i++) {
            const row = jsonSheet[i];
            if (!row || row.length === 0) continue;

            const nama = (namaIdx !== -1 && row[namaIdx]) ? String(row[namaIdx]).trim() : '';
            const jabatan = (jabatanIdx !== -1 && row[jabatanIdx]) ? String(row[jabatanIdx]).trim() : '-';
            const wilayah = (wilayahIdx !== -1 && row[wilayahIdx]) ? String(row[wilayahIdx]).trim() : '-';
            const nominal = parseNominal(nominalIdx !== -1 ? row[nominalIdx] : 0);
            const rekening = extractCleanRekening(rekeningIdx !== -1 ? row[rekeningIdx] : '');
            let statusTf = (statusIdx !== -1 && row[statusIdx]) ? String(row[statusIdx]).trim().toUpperCase() : 'BELUM';
            if (statusTf !== 'SUDAH') statusTf = 'BELUM';

            if (nama !== '' && rekening !== '') {
              parsedExcelRows.push({
                nama: nama,
                jabatan: jabatan || '-',
                wilayah: wilayah || '-',
                nominal: nominal,
                rekening: rekening,
                status_tf: statusTf
              });
            }
          }

          if (parsedExcelRows.length > 0) {
            document.getElementById('chosenFileName').textContent = `${file.name} (${parsedExcelRows.length} baris valid)`;
            document.getElementById('btnProcessImport').disabled = false;

            previewTbody.innerHTML = '';
            parsedExcelRows.slice(0, 5).forEach((item) => {
              const tr = document.createElement('tr');
              tr.innerHTML = `
                <td class="p-2 font-semibold text-slate-800">${escapeHtml(item.nama)}</td>
                <td class="p-2 text-slate-600">${escapeHtml(item.jabatan)}</td>
                <td class="p-2 text-slate-600">${escapeHtml(item.wilayah)}</td>
                <td class="p-2 text-right font-mono text-slate-700">Rp ${formatRupiah(item.nominal)}</td>
                <td class="p-2 font-mono font-bold text-emerald-700 bg-emerald-50/60">${escapeHtml(item.rekening)}</td>
              `;
              previewTbody.appendChild(tr);
            });
            previewCountBadge.textContent = `${parsedExcelRows.length} Data Terbaca`;
            previewBox.classList.remove('hidden');
          } else {
            previewBox.classList.add('hidden');
            alert("Tidak ada baris data valid yang memiliki Nama dan Nomor Rekening.");
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

    // --- 7. UTILITIES ---
    function formatRupiah(val) {
      const num = parseFloat(val) || 0;
      return num.toLocaleString('id-ID');
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text || '';
      return div.innerHTML;
    }

    // Event listeners
    document.getElementById('searchInput').addEventListener('input', loadData);
    document.getElementById('filterStatus').addEventListener('change', loadData);
    document.getElementById('filterJabatan').addEventListener('change', loadData);
    document.getElementById('filterWilayah').addEventListener('change', loadData);

    // Initial load
    document.addEventListener('DOMContentLoaded', loadData);
  </script>
</body>
</html>