<?php
// video.php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video | Santri Koding</title>
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
                <a href="artikel.php" class="text-ink hover:text-accent font-medium text-sm transition-colors">Artikel</a>
                <a href="podcast.php" class="text-ink hover:text-accent font-medium text-sm transition-colors">Podcast</a>
                <a href="video.php" class="text-accent font-medium text-sm transition-colors">Video</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow pt-12 pb-24">
        <div class="max-w-6xl mx-auto px-6 sm:px-8 lg:px-12">
            
            <!-- Page Header -->
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-accent font-bold tracking-widest uppercase text-xs">Visualisasi Pembelajaran</span>
                <h1 class="text-5xl font-serif font-bold text-ink mt-4 mb-6">Galeri Video</h1>
                <p class="text-lg text-inkLight">Tonton tutorial pemrograman, vlog keseharian santri, dan rekaman webinar secara gratis.</p>
            </div>

            <!-- Search and Filter Section -->
            <div class="flex flex-col md:flex-row gap-6 justify-between items-center mb-12 bg-white p-4 rounded-2xl shadow-sm border border-black/5">
                <!-- Search Bar -->
                <div class="w-full md:w-1/3 relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Cari video..." 
                           class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent focus:bg-white transition-all text-ink">
                </div>

                <!-- Filters -->
                <div class="w-full md:w-auto flex overflow-x-auto pb-2 md:pb-0 gap-2 scrollbar-hide" id="filterContainer">
                    <button class="filter-btn px-5 py-2.5 rounded-xl font-medium text-sm transition-colors bg-ink text-white" data-filter="all">Semua Video</button>
                    <button class="filter-btn px-5 py-2.5 rounded-xl font-medium text-sm transition-colors bg-gray-100 text-inkLight hover:bg-gray-200" data-filter="Tutorial">Tutorial</button>
                    <button class="filter-btn px-5 py-2.5 rounded-xl font-medium text-sm transition-colors bg-gray-100 text-inkLight hover:bg-gray-200" data-filter="Webinar">Webinar</button>
                    <button class="filter-btn px-5 py-2.5 rounded-xl font-medium text-sm transition-colors bg-gray-100 text-inkLight hover:bg-gray-200" data-filter="Vlog">Vlog</button>
                </div>
            </div>

            <!-- Videos Grid -->
            <div id="videosGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Video Cards will be rendered here by JS -->
            </div>

            <!-- Empty State (Hidden by default) -->
            <div id="emptyState" class="hidden text-center py-20">
                <div class="text-gray-300 text-6xl mb-4"><i class="fas fa-video-slash"></i></div>
                <h3 class="text-2xl font-serif font-bold text-ink mb-2">Tidak ditemukan</h3>
                <p class="text-inkLight">Maaf, kami tidak menemukan video yang sesuai dengan pencarian Anda.</p>
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

    <!-- Video Detail Modal -->
    <div id="videoModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-ink/90 backdrop-blur-sm transition-opacity" id="modalBackdrop"></div>

        <!-- Modal Panel -->
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl opacity-100 translate-y-0 sm:scale-100">
                    
                    <!-- Close Button -->
                    <button id="closeModalBtn" class="absolute top-4 right-4 w-10 h-10 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center transition-colors z-20">
                        <i class="fas fa-times text-xl"></i>
                    </button>

                    <div class="flex flex-col">
                        <!-- Video Player Area (Fake for Demo) -->
                        <div class="w-full aspect-video bg-black relative flex items-center justify-center group cursor-pointer">
                            <img id="modalMainImg" src="" alt="Video Thumbnail" class="w-full h-full object-cover opacity-60 group-hover:opacity-40 transition-opacity">
                            <div class="absolute z-10 w-24 h-24 bg-[#FF0000] rounded-full flex items-center justify-center text-white shadow-2xl group-hover:scale-110 transition-transform">
                                <i class="fas fa-play ml-2 text-3xl"></i>
                            </div>
                        </div>

                        <!-- Video Details -->
                        <div class="p-8 bg-white">
                            <div class="flex items-center gap-3 mb-4">
                                <span id="modalCategory" class="bg-accent/10 text-accent font-bold px-3 py-1 rounded-full text-xs uppercase tracking-wider"></span>
                                <span id="modalDuration" class="text-sm text-gray-500 font-medium bg-gray-100 px-2 py-1 rounded"></span>
                                <span id="modalDate" class="text-sm text-gray-400"></span>
                            </div>
                            
                            <h2 id="modalTitle" class="text-2xl lg:text-3xl font-serif font-bold text-ink mb-4 leading-tight"></h2>
                            
                            <p id="modalContent" class="text-inkLight leading-relaxed"></p>

                            <div class="mt-8 pt-6 border-t border-gray-100">
                                <a href="https://www.youtube.com/@hayatake99" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-[#FF0000] hover:bg-[#CC0000] text-white font-medium rounded-xl transition-colors shadow-md">
                                    <i class="fab fa-youtube text-lg"></i> Tonton di Channel YouTube
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- 1. MOCK DATA VIDEO ---
        const mockVideos = [
            {
                id: 1,
                title: "Membuat Aplikasi Kasir Sederhana dengan PHP & MySQL",
                category: "Tutorial",
                date: "Tayang 10 Sep 2026",
                duration: "45:20",
                excerpt: "Tutorial lengkap dari nol hingga jadi. Sangat cocok untuk tugas akhir sekolah atau pesantren.",
                content: "Di video tutorial kali ini, kita akan belajar membuat sistem kasir (Point of Sale) sederhana menggunakan PHP native dan database MySQL. Kita akan bahas mulai dari perancangan database, pembuatan form input barang, hingga logika perhitungan total belanja kasir.",
                image: "https://images.unsplash.com/photo-1633356122544-f134324a6cee?q=80&w=1200&auto=format&fit=crop"
            },
            {
                id: 2,
                title: "Live Coding: Slicing Figma ke Tailwind CSS",
                category: "Webinar",
                date: "Tayang 5 Sep 2026",
                duration: "1:20:15",
                excerpt: "Rekaman webinar bareng komunitas web developer membahas cara cepat menerjemahkan desain ke dalam kode.",
                content: "Rekaman ulang dari sesi live coding bulanan Santri Koding. Pada sesi ini, kita mengambil sebuah desain landing page dari Figma dan mengubahnya menjadi kode HTML yang responsif menggunakan framework Tailwind CSS. Banyak tips & trik shortcut VS Code yang dibagikan disini!",
                image: "https://images.unsplash.com/photo-1555066931-4365d14bab8c?q=80&w=1200&auto=format&fit=crop"
            },
            {
                id: 3,
                title: "A Day in Life: Santri Merangkap Web Developer",
                category: "Vlog",
                date: "Tayang 28 Agu 2026",
                duration: "12:45",
                excerpt: "Mengikuti keseharian Mas Misbah menyeimbangkan waktu antara mengaji kitab kuning dan ngoding project client.",
                content: "Banyak yang penasaran bagaimana cara santri membagi waktu. Di vlog santai kali ini, saya mengajak kalian melihat langsung keseharian saya di pondok. Mulai dari bangun tahajud, mengaji ba'da subuh, ngoding siang hari, hingga rapat dengan klien secara online.",
                image: "https://images.unsplash.com/photo-1498050108023-c5249f4df085?q=80&w=1200&auto=format&fit=crop"
            },
            {
                id: 4,
                title: "Setup Meja Kerja Minimalis dengan Budget Pelajar",
                category: "Vlog",
                date: "Tayang 15 Agu 2026",
                duration: "08:30",
                excerpt: "Tour desk setup sederhana yang nyaman digunakan berlama-lama tanpa harus menguras dompet.",
                content: "Tidak perlu alat mahal untuk mulai belajar koding. Di video ini saya memperlihatkan setup meja kerja saya saat ini: laptop standar, monitor bekas yang masih layak pakai, keyboard mekanikal murah, dan beberapa aksesoris pendukung kenyamanan lainnya.",
                image: "https://images.unsplash.com/photo-1486312338219-ce68d2c6f44d?q=80&w=1200&auto=format&fit=crop"
            }
        ];

        // --- 2. ELEMENTS & STATE ---
        const videosGrid = document.getElementById('videosGrid');
        const emptyState = document.getElementById('emptyState');
        const searchInput = document.getElementById('searchInput');
        const filterBtns = document.querySelectorAll('.filter-btn');
        
        // Modal Elements
        const modal = document.getElementById('videoModal');
        const modalBackdrop = document.getElementById('modalBackdrop');
        const closeModalBtn = document.getElementById('closeModalBtn');
        
        let currentFilter = 'all';
        let currentSearch = '';

        // --- 3. RENDER LOGIC ---
        function renderVideos() {
            // Filter data
            const filteredVideos = mockVideos.filter(video => {
                const matchesCategory = currentFilter === 'all' || video.category === currentFilter;
                const matchesSearch = video.title.toLowerCase().includes(currentSearch.toLowerCase()) || 
                                      video.excerpt.toLowerCase().includes(currentSearch.toLowerCase());
                return matchesCategory && matchesSearch;
            });

            // Clear Grid
            videosGrid.innerHTML = '';

            if (filteredVideos.length === 0) {
                videosGrid.classList.add('hidden');
                emptyState.classList.remove('hidden');
                return;
            }

            videosGrid.classList.remove('hidden');
            emptyState.classList.add('hidden');

            // Render HTML
            filteredVideos.forEach(video => {
                const card = document.createElement('div');
                card.className = "bg-white rounded-[24px] overflow-hidden border border-black/5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col group cursor-pointer";
                card.onclick = () => openModal(video.id);
                
                card.innerHTML = `
                    <div class="relative overflow-hidden aspect-video">
                        <img src="${video.image}" alt="${video.title}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 filter brightness-90 group-hover:brightness-75">
                        
                        <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold text-ink shadow-sm">
                            ${video.category}
                        </div>
                        
                        <div class="absolute bottom-3 right-3 bg-black/80 text-white px-2 py-1 rounded text-xs font-medium">
                            ${video.duration}
                        </div>
                        
                        <!-- Play Icon Overlay -->
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <div class="w-16 h-16 bg-[#FF0000] rounded-full flex items-center justify-center text-white shadow-lg">
                                <i class="fas fa-play ml-1 text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 flex flex-col flex-grow">
                        <span class="text-xs text-gray-500 font-medium mb-2">${video.date}</span>
                        <h3 class="font-serif font-bold text-xl text-ink mb-3 leading-snug group-hover:text-accent transition-colors">${video.title}</h3>
                        <p class="text-inkLight text-sm line-clamp-2 leading-relaxed">${video.excerpt}</p>
                    </div>
                `;
                videosGrid.appendChild(card);
            });
        }

        // --- 4. EVENT LISTENERS FOR SEARCH & FILTER ---
        searchInput.addEventListener('input', (e) => {
            currentSearch = e.target.value;
            renderVideos();
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
                renderVideos();
            });
        });

        // --- 5. MODAL LOGIC ---
        function openModal(videoId) {
            const video = mockVideos.find(v => v.id === videoId);
            if (!video) return;

            // Populate Data
            document.getElementById('modalMainImg').src = video.image;
            document.getElementById('modalCategory').textContent = video.category;
            document.getElementById('modalDate').textContent = video.date;
            document.getElementById('modalDuration').textContent = video.duration;
            document.getElementById('modalTitle').textContent = video.title;
            document.getElementById('modalContent').innerHTML = video.content;

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
        renderVideos();

    </script>
</body>
</html>
