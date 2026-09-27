<?php
// artikel.php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumpulan Artikel | Santri Koding</title>
    <!-- Favicon -->
    <link rel="icon" href="favicon.png" type="image/png">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Lora', 'serif'],
                    },
                    colors: {
                        paper: '#FBF9F6',
                        ink: '#18181B',
                        inkLight: '#3F3F46',
                        accent: '#E65A4B',
                        accentHover: '#CF4C3F',
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: theme('colors.paper'); color: theme('colors.inkLight'); }
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none;
            z-index: -1;
            background-image: url('pattern.jpg');
            background-size: 300px;
            background-repeat: repeat;
            opacity: 0.15;
            mix-blend-mode: multiply;
            -webkit-mask-image: linear-gradient(to bottom left, black 10%, transparent 80%);
            mask-image: linear-gradient(to bottom left, black 10%, transparent 80%);
        }
        h1, h2, h3, h4, h5, h6, .font-serif { color: theme('colors.ink'); }
        .thin-border { border: 1px solid rgba(0,0,0,0.08); }
        
        /* Custom scrollbar for modal */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col font-sans selection:bg-accent selection:text-white">

    <!-- Header / Navbar -->
    <header class="py-6 relative z-40 border-b border-black/5">
        <div class="max-w-6xl mx-auto px-6 sm:px-8 lg:px-12 flex flex-col xl:flex-row items-center justify-between gap-8">
            <div class="flex-shrink-0">
                <a href="index.php" class="inline-block">
                    <!-- Adjusted for visual consistency -->
                    <h1 class="text-3xl font-serif font-bold text-ink">Santri<span class="text-accent">Koding</span></h1>
                </a>
            </div>
            
            <nav class="flex flex-wrap items-center justify-center gap-x-6 gap-y-4">
                <a href="index.php" class="text-ink hover:text-accent font-medium text-sm transition-colors">Beranda</a>
                <a href="katalog.php" class="text-ink hover:text-accent font-medium text-sm transition-colors">Katalog Aplikasi</a>
                <a href="artikel.php" class="text-accent font-medium text-sm transition-colors">Artikel</a>
                <a href="podcast.php" class="text-ink hover:text-accent font-medium text-sm transition-colors">Podcast</a>
                <a href="video.php" class="text-ink hover:text-accent font-medium text-sm transition-colors">Video</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow pt-12 pb-24">
        <div class="max-w-6xl mx-auto px-6 sm:px-8 lg:px-12">
            
            <!-- Page Header -->
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-accent font-bold tracking-widest uppercase text-xs">Blog & Tulisan</span>
                <h1 class="text-5xl font-serif font-bold text-ink mt-4 mb-6">Kumpulan Artikel</h1>
                <p class="text-lg text-inkLight">Baca tulisan terbaru seputar pemrograman, teknologi, dan insight dari kehidupan pesantren.</p>
            </div>

            <!-- Search and Filter Section -->
            <div class="flex flex-col md:flex-row gap-6 justify-between items-center mb-12 bg-white p-4 rounded-2xl shadow-sm border border-black/5">
                <!-- Search Bar -->
                <div class="w-full md:w-1/3 relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Cari artikel..." 
                           class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent focus:bg-white transition-all text-ink">
                </div>

                <!-- Filters -->
                <div class="w-full md:w-auto flex overflow-x-auto pb-2 md:pb-0 gap-2 scrollbar-hide" id="filterContainer">
                    <button class="filter-btn px-5 py-2.5 rounded-xl font-medium text-sm transition-colors bg-ink text-white" data-filter="all">Semua</button>
                    <button class="filter-btn px-5 py-2.5 rounded-xl font-medium text-sm transition-colors bg-gray-100 text-inkLight hover:bg-gray-200" data-filter="Pemrograman">Pemrograman</button>
                    <button class="filter-btn px-5 py-2.5 rounded-xl font-medium text-sm transition-colors bg-gray-100 text-inkLight hover:bg-gray-200" data-filter="Pesantren">Pesantren</button>
                    <button class="filter-btn px-5 py-2.5 rounded-xl font-medium text-sm transition-colors bg-gray-100 text-inkLight hover:bg-gray-200 whitespace-nowrap" data-filter="Tips & Trik">Tips & Trik</button>
                </div>
            </div>

            <!-- Articles Grid -->
            <div id="articlesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Article Cards will be rendered here by JS -->
            </div>

            <!-- Empty State (Hidden by default) -->
            <div id="emptyState" class="hidden text-center py-20">
                <div class="text-gray-300 text-6xl mb-4"><i class="fas fa-newspaper"></i></div>
                <h3 class="text-2xl font-serif font-bold text-ink mb-2">Tidak ditemukan</h3>
                <p class="text-inkLight">Maaf, kami tidak menemukan artikel yang sesuai dengan pencarian Anda.</p>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-black/5 pt-16 pb-8">
        <div class="max-w-6xl mx-auto px-6 sm:px-8 lg:px-12 text-center">
            <h2 class="text-2xl font-serif font-bold text-ink mb-4">SantriKoding</h2>
            <p class="text-inkLight mb-8">Membangun Karir IT dari Pesantren.</p>
            <div class="border-t border-black/10 pt-8 flex justify-center text-sm font-medium">
                <p class="text-gray-500">© 2024 Misbahussurur All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Article Detail Modal -->
    <div id="articleModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-ink/80 backdrop-blur-sm transition-opacity" id="modalBackdrop"></div>

        <!-- Modal Panel -->
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl opacity-100 translate-y-0 sm:scale-100">
                    
                    <!-- Close Button -->
                    <button id="closeModalBtn" class="absolute top-4 right-4 w-10 h-10 bg-white/80 backdrop-blur rounded-full flex items-center justify-center text-ink hover:bg-gray-100 transition-colors z-10 shadow-sm border border-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>

                    <div class="max-h-[90vh] overflow-y-auto">
                        <!-- Header Image -->
                        <div class="w-full h-64 sm:h-80 relative">
                            <img id="modalMainImg" src="" alt="Thumbnail" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                        </div>

                        <!-- Article Content -->
                        <div class="p-8 lg:p-12 relative -mt-20 bg-white rounded-t-[40px]">
                            <div class="flex items-center gap-3 mb-4">
                                <span id="modalCategory" class="bg-accent/10 text-accent font-bold px-3 py-1 rounded-full text-xs uppercase tracking-wider"></span>
                                <span id="modalDate" class="text-sm text-gray-500 font-medium"></span>
                            </div>
                            
                            <h2 id="modalTitle" class="text-3xl lg:text-4xl font-serif font-bold text-ink mb-8 leading-tight"></h2>
                            
                            <div class="prose prose-lg text-inkLight mb-8 max-w-none font-serif leading-relaxed">
                                <p id="modalContent"></p>
                            </div>

                            <div class="mt-12 pt-8 border-t border-gray-100 text-center">
                                <button onclick="closeModal()" class="px-8 py-3 bg-ink hover:bg-inkLight text-white font-medium rounded-xl transition-colors">
                                    Tutup Artikel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- 1. MOCK DATA ARTIKEL ---
        const mockArticles = [
            {
                id: 1,
                title: "Memahami Logika Pemrograman Lewat Ilmu Nahwu Sharaf",
                category: "Pesantren",
                date: "5 Sep 2026",
                excerpt: "Bagaimana struktur bahasa Arab klasik membantu kita memahami cara kerja variabel, fungsi, dan algoritma dalam koding.",
                content: "Sebagai santri, kita sering kali dihadapkan dengan kerumitan gramatika Arab klasik seperti Nahwu dan Sharaf. Ternyata, logika struktural yang kita pelajari saat men-tashrif kata atau meng-i'rab kalimat sangat mirip dengan logika pemrograman komputer.<br><br>Dalam artikel ini, kita akan membedah bagaimana pemahaman mendalam tentang pola bahasa dapat menjadi modal kuat bagi seorang santri untuk menguasai bahasa pemrograman seperti PHP, Python, atau JavaScript dengan lebih cepat. Keduanya sama-sama menuntut ketelitian syntax dan alur berpikir yang sistematis.",
                image: "https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=1200&auto=format&fit=crop"
            },
            {
                id: 2,
                title: "Roadmap Menjadi Web Developer Modern Tahun Ini",
                category: "Pemrograman",
                date: "1 Sep 2026",
                excerpt: "Panduan langkah demi langkah dari nol hingga mahir membuat website profesional dengan teknologi terkini.",
                content: "Dunia pengembangan web bergerak sangat cepat. Apa yang populer lima tahun lalu mungkin sudah tergantikan oleh framework baru hari ini. Namun, fundamentalnya tetap sama: HTML, CSS, dan JavaScript.<br><br>Setelah menguasai dasar-dasar tersebut, langkah selanjutnya adalah memilih spesialisasi: Frontend (UI/UX, React, Vue) atau Backend (Database, API, Node.js, PHP). Roadmap ini dirancang untuk membimbing Anda agar tidak tersesat di tengah lautan informasi teknologi yang begitu luas.",
                image: "https://images.unsplash.com/photo-1498050108023-c5249f4df085?q=80&w=1200&auto=format&fit=crop"
            },
            {
                id: 3,
                title: "5 Ekstensi VS Code Wajib untuk Programmer Pemula",
                category: "Tips & Trik",
                date: "20 Agu 2026",
                excerpt: "Tingkatkan produktivitas koding Anda dengan rekomendasi ekstensi Visual Studio Code pilihan berikut ini.",
                content: "Visual Studio Code (VS Code) adalah editor teks paling populer saat ini. Salah satu alasan utamanya adalah ekosistem ekstensinya yang sangat kaya. Jika Anda baru mulai belajar koding, ada beberapa ekstensi yang wajib Anda install.<br><br>Mulai dari Prettier untuk merapikan kode otomatis, Live Server untuk melihat perubahan HTML secara real-time di browser, hingga Bracket Pair Colorizer yang sangat membantu melacak kurung kurawal yang hilang. Penggunaan ekstensi yang tepat bisa menghemat waktu Anda hingga berjam-jam saat melakukan debugging.",
                image: "https://images.unsplash.com/photo-1555099962-4199c345e5dd?q=80&w=1200&auto=format&fit=crop"
            },
            {
                id: 4,
                title: "Mengelola Waktu: Ngaji, Koding, dan Organisasi",
                category: "Pesantren",
                date: "15 Agu 2026",
                excerpt: "Tips menyeimbangkan waktu antara kewajiban mengaji, belajar skill IT, dan aktif di organisasi pondok.",
                content: "Menjadi santri sekaligus penggiat IT bukanlah hal yang mustahil. Tantangan terbesarnya bukan pada kemampuan otak, melainkan manajemen waktu. Jadwal pesantren yang padat dari subuh hingga malam menuntut kita untuk ekstra disiplin.<br><br>Kuncinya adalah memanfaatkan 'waktu mati' dan konsisten belajar minimal 1 jam setiap harinya. Jangan korbankan waktu mengaji utama Anda, jadikan koding sebagai aktivitas ekstrakurikuler yang produktif. Disiplin yang diajarkan di pondok justru adalah senjata rahasia terbaik seorang programmer.",
                image: "https://images.unsplash.com/photo-1455849318743-b2233052fcff?q=80&w=1200&auto=format&fit=crop"
            }
        ];

        // --- 2. ELEMENTS & STATE ---
        const articlesGrid = document.getElementById('articlesGrid');
        const emptyState = document.getElementById('emptyState');
        const searchInput = document.getElementById('searchInput');
        const filterBtns = document.querySelectorAll('.filter-btn');
        
        // Modal Elements
        const modal = document.getElementById('articleModal');
        const modalBackdrop = document.getElementById('modalBackdrop');
        const closeModalBtn = document.getElementById('closeModalBtn');
        
        let currentFilter = 'all';
        let currentSearch = '';

        // --- 3. RENDER LOGIC ---
        function renderArticles() {
            // Filter data
            const filteredArticles = mockArticles.filter(article => {
                const matchesCategory = currentFilter === 'all' || article.category === currentFilter;
                const matchesSearch = article.title.toLowerCase().includes(currentSearch.toLowerCase()) || 
                                      article.excerpt.toLowerCase().includes(currentSearch.toLowerCase());
                return matchesCategory && matchesSearch;
            });

            // Clear Grid
            articlesGrid.innerHTML = '';

            if (filteredArticles.length === 0) {
                articlesGrid.classList.add('hidden');
                emptyState.classList.remove('hidden');
                return;
            }

            articlesGrid.classList.remove('hidden');
            emptyState.classList.add('hidden');

            // Render HTML
            filteredArticles.forEach(article => {
                const card = document.createElement('div');
                card.className = "bg-white rounded-[24px] overflow-hidden border border-black/5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col group cursor-pointer";
                card.onclick = () => openModal(article.id);
                
                card.innerHTML = `
                    <div class="relative overflow-hidden aspect-[3/2]">
                        <img src="${article.image}" alt="${article.title}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold text-ink shadow-sm">
                            ${article.category}
                        </div>
                    </div>
                    <div class="p-6 flex flex-col flex-grow">
                        <span class="text-xs text-gray-500 font-medium mb-2">${article.date}</span>
                        <h3 class="font-serif font-bold text-xl text-ink mb-3 leading-snug group-hover:text-accent transition-colors">${article.title}</h3>
                        <p class="text-inkLight text-sm line-clamp-3 mb-6 leading-relaxed">${article.excerpt}</p>
                        
                        <div class="mt-auto border-t border-gray-100 pt-4">
                            <span class="inline-block text-ink text-sm font-bold group-hover:text-accent transition-colors flex items-center gap-1">
                                Baca selengkapnya <i class="fas fa-arrow-right text-[10px] mt-0.5"></i>
                            </span>
                        </div>
                    </div>
                `;
                articlesGrid.appendChild(card);
            });
        }

        // --- 4. EVENT LISTENERS FOR SEARCH & FILTER ---
        searchInput.addEventListener('input', (e) => {
            currentSearch = e.target.value;
            renderArticles();
        });

        filterBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                // Update active state
                filterBtns.forEach(b => {
                    b.classList.remove('bg-ink', 'text-white');
                    b.classList.add('bg-gray-100', 'text-inkLight');
                });
                const target = e.target;
                target.classList.remove('bg-gray-100', 'text-inkLight');
                target.classList.add('bg-ink', 'text-white');

                currentFilter = target.getAttribute('data-filter');
                renderArticles();
            });
        });

        // --- 5. MODAL LOGIC ---
        function openModal(articleId) {
            const article = mockArticles.find(a => a.id === articleId);
            if (!article) return;

            // Populate Data
            document.getElementById('modalMainImg').src = article.image;
            document.getElementById('modalCategory').textContent = article.category;
            document.getElementById('modalDate').textContent = article.date;
            document.getElementById('modalTitle').textContent = article.title;
            document.getElementById('modalContent').innerHTML = article.content;

            // Show Modal
            modal.classList.remove('hidden');
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        closeModalBtn.addEventListener('click', closeModal);
        modalBackdrop.addEventListener('click', closeModal);
        // Close on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        // --- 6. INITIAL RENDER ---
        renderArticles();

    </script>
</body>
</html>
