<?php
// podcast.php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Podcast | Santri Koding</title>
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
                <a href="podcast.php" class="text-accent font-medium text-sm transition-colors">Podcast</a>
                <a href="video.php" class="text-ink hover:text-accent font-medium text-sm transition-colors">Video</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow pt-12 pb-24">
        <div class="max-w-6xl mx-auto px-6 sm:px-8 lg:px-12">
            
            <!-- Page Header -->
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-accent font-bold tracking-widest uppercase text-xs">Dengarkan Suara Saya</span>
                <h1 class="text-5xl font-serif font-bold text-ink mt-4 mb-6">Podcast Santri Koding01</h1>
                <p class="text-lg text-inkLight">Bincang-bincang inspiratif seputar lika-liku karir di dunia IT, pengalaman mondok, dan cerita sukses alumni pesantren.</p>
            </div>

            <!-- Search and Filter Section -->
            <div class="flex flex-col md:flex-row gap-6 justify-between items-center mb-12 bg-white p-4 rounded-2xl shadow-sm border border-black/5">
                <!-- Search Bar -->
                <div class="w-full md:w-1/3 relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Cari episode podcast..." 
                           class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent focus:bg-white transition-all text-ink">
                </div>

                <!-- Filters -->
                <div class="w-full md:w-auto flex overflow-x-auto pb-2 md:pb-0 gap-2 scrollbar-hide" id="filterContainer">
                    <button class="filter-btn px-5 py-2.5 rounded-xl font-medium text-sm transition-colors bg-ink text-white" data-filter="all">Semua Episode</button>
                    <button class="filter-btn px-5 py-2.5 rounded-xl font-medium text-sm transition-colors bg-gray-100 text-inkLight hover:bg-gray-200" data-filter="Inspirasi">Inspirasi</button>
                    <button class="filter-btn px-5 py-2.5 rounded-xl font-medium text-sm transition-colors bg-gray-100 text-inkLight hover:bg-gray-200" data-filter="Karir IT">Karir IT</button>
                    <button class="filter-btn px-5 py-2.5 rounded-xl font-medium text-sm transition-colors bg-gray-100 text-inkLight hover:bg-gray-200 whitespace-nowrap" data-filter="Bincang Santai">Bincang Santai</button>
                </div>
            </div>

            <!-- Podcasts Grid -->
            <div id="podcastsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Podcast Cards will be rendered here by JS -->
            </div>

            <!-- Empty State (Hidden by default) -->
            <div id="emptyState" class="hidden text-center py-20">
                <div class="text-gray-300 text-6xl mb-4"><i class="fas fa-microphone-slash"></i></div>
                <h3 class="text-2xl font-serif font-bold text-ink mb-2">Tidak ditemukan</h3>
                <p class="text-inkLight">Maaf, kami tidak menemukan episode yang sesuai dengan pencarian Anda.</p>
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

    <!-- Podcast Detail Modal -->
    <div id="podcastModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-ink/80 backdrop-blur-sm transition-opacity" id="modalBackdrop"></div>

        <!-- Modal Panel -->
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl opacity-100 translate-y-0 sm:scale-100">
                    
                    <!-- Close Button -->
                    <button id="closeModalBtn" class="absolute top-4 right-4 w-10 h-10 bg-white/80 backdrop-blur rounded-full flex items-center justify-center text-ink hover:bg-gray-100 transition-colors z-10 shadow-sm border border-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>

                    <div class="max-h-[90vh] overflow-y-auto">
                        <!-- Header Image -->
                        <div class="w-full aspect-video relative flex items-center justify-center">
                            <img id="modalMainImg" src="" alt="Thumbnail" class="absolute inset-0 w-full h-full object-cover blur-sm opacity-50">
                            <div class="absolute inset-0 bg-ink/40"></div>
                            
                            <!-- Fake Play Button for Visuals -->
                            <div class="relative z-10 w-20 h-20 bg-accent rounded-full flex items-center justify-center text-white shadow-xl hover:scale-110 transition-transform cursor-pointer">
                                <i class="fas fa-play text-2xl ml-1"></i>
                            </div>
                        </div>

                        <!-- Podcast Content -->
                        <div class="p-8 lg:p-10 relative bg-white">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <span id="modalCategory" class="bg-accent/10 text-accent font-bold px-3 py-1 rounded-full text-xs uppercase tracking-wider"></span>
                                    <span id="modalDuration" class="text-sm text-gray-500 font-medium flex items-center gap-1"><i class="far fa-clock"></i> </span>
                                </div>
                                <span id="modalDate" class="text-sm text-gray-500 font-medium"></span>
                            </div>
                            
                            <h2 id="modalTitle" class="text-2xl lg:text-3xl font-serif font-bold text-ink mb-6 leading-tight"></h2>
                            
                            <div class="prose prose-sm text-inkLight mb-8 max-w-none leading-relaxed">
                                <h4 class="font-bold text-ink mb-2">Show Notes:</h4>
                                <p id="modalContent"></p>
                            </div>

                            <div class="mt-8 pt-8 border-t border-gray-100 flex gap-4">
                                <a href="#" class="flex-1 text-center px-6 py-4 bg-ink hover:bg-inkLight text-white font-medium rounded-xl transition-colors shadow-sm flex justify-center items-center gap-2">
                                    <i class="fab fa-spotify text-green-400"></i> Dengarkan di Spotify
                                </a>
                                <a href="#" class="flex-1 text-center px-6 py-4 bg-white border-2 border-gray-200 hover:border-gray-300 text-ink font-medium rounded-xl transition-colors flex justify-center items-center gap-2">
                                    <i class="fab fa-apple text-ink"></i> Apple Podcasts
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- 1. MOCK DATA PODCAST ---
        const mockPodcasts = [
            {
                id: 1,
                title: "Ep 04: Dari Hafidz Qur'an Menjadi Senior Software Engineer di Startup Unicorn",
                category: "Inspirasi",
                date: "2 Sep 2026",
                duration: "45 Menit",
                excerpt: "Berbincang dengan Kang Fulan, alumni pesantren yang sukses menembus kerasnya persaingan industri tech di Jakarta.",
                content: "Di episode kali ini kita ngobrol bareng Kang Fulan. Ia berbagi cerita tentang bagaimana hafalan Al-Qur'an dan kedisiplinan yang dilatih di pesantren menjadi pondasi kuat saat ia mempelajari bahasa pemrograman dari nol.<br><br>Kita juga membahas tips interview, cara mengatasi imposter syndrome, dan pentingnya adab dalam dunia profesional.",
                image: "https://images.unsplash.com/photo-1589903308904-1010c2294adc?q=80&w=1200&auto=format&fit=crop"
            },
            {
                id: 2,
                title: "Ep 03: Tren Teknologi 2027: Masihkah Relevan Belajar PHP?",
                category: "Karir IT",
                date: "25 Agu 2026",
                duration: "32 Menit",
                excerpt: "Membahas mitos bahwa PHP sudah 'mati' dan mengapa bahasa ini masih sangat berkuasa di industri lokal maupun global.",
                content: "Banyak yang bilang PHP sudah ketinggalan zaman, tapi faktanya mayoritas website di dunia masih menggunakan PHP, terutama ekosistem WordPress dan Laravel. Kita akan bedah data pasar kerja terkini, alasan mengapa PHP masih menjadi pilihan rasional untuk pemula, dan bagaimana masa depan framework seperti Laravel atau CodeIgniter.",
                image: "https://images.unsplash.com/photo-1516280440502-86927918a5cb?q=80&w=1200&auto=format&fit=crop"
            },
            {
                id: 3,
                title: "Ep 02: Koding Sampai Pagi vs Tahajud: Menyeimbangkan Work-Life-Worship",
                category: "Bincang Santai",
                date: "18 Agu 2026",
                duration: "50 Menit",
                excerpt: "Diskusi santai tentang dilema programmer muslim yang sering begadang dan bagaimana mengatur ritme kerja islami.",
                content: "Deadline project sering kali memaksa programmer untuk lembur hingga larut malam. Lalu bagaimana nasib shalat Subuh dan Tahajud kita? Bersama Ustadz Developer, kita ngobrol santai tentang pentingnya keberkahan waktu, tips mengatur jam tidur (sleep hygiene), dan bagaimana meniatkan pekerjaan koding sebagai nilai ibadah.",
                image: "https://images.unsplash.com/photo-1601024925769-d450849206b1?q=80&w=1200&auto=format&fit=crop"
            }
        ];

        // --- 2. ELEMENTS & STATE ---
        const podcastsGrid = document.getElementById('podcastsGrid');
        const emptyState = document.getElementById('emptyState');
        const searchInput = document.getElementById('searchInput');
        const filterBtns = document.querySelectorAll('.filter-btn');
        
        // Modal Elements
        const modal = document.getElementById('podcastModal');
        const modalBackdrop = document.getElementById('modalBackdrop');
        const closeModalBtn = document.getElementById('closeModalBtn');
        
        let currentFilter = 'all';
        let currentSearch = '';

        // --- 3. RENDER LOGIC ---
        function renderPodcasts() {
            // Filter data
            const filteredPodcasts = mockPodcasts.filter(podcast => {
                const matchesCategory = currentFilter === 'all' || podcast.category === currentFilter;
                const matchesSearch = podcast.title.toLowerCase().includes(currentSearch.toLowerCase()) || 
                                      podcast.excerpt.toLowerCase().includes(currentSearch.toLowerCase());
                return matchesCategory && matchesSearch;
            });

            // Clear Grid
            podcastsGrid.innerHTML = '';

            if (filteredPodcasts.length === 0) {
                podcastsGrid.classList.add('hidden');
                emptyState.classList.remove('hidden');
                return;
            }

            podcastsGrid.classList.remove('hidden');
            emptyState.classList.add('hidden');

            // Render HTML
            filteredPodcasts.forEach(podcast => {
                const card = document.createElement('div');
                card.className = "bg-white rounded-[24px] overflow-hidden border border-black/5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col group cursor-pointer";
                card.onclick = () => openModal(podcast.id);
                
                card.innerHTML = `
                    <div class="relative overflow-hidden aspect-square sm:aspect-[4/3]">
                        <img src="${podcast.image}" alt="${podcast.title}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 filter brightness-90 group-hover:brightness-75">
                        
                        <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold text-ink shadow-sm">
                            ${podcast.category}
                        </div>
                        
                        <div class="absolute bottom-4 right-4 bg-ink/80 backdrop-blur text-white px-2 py-1 rounded text-xs font-medium flex items-center gap-1">
                            <i class="fas fa-play text-[10px]"></i> ${podcast.duration}
                        </div>
                        
                        <!-- Play Icon Overlay -->
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <div class="w-14 h-14 bg-accent rounded-full flex items-center justify-center text-white shadow-lg">
                                <i class="fas fa-play ml-1 text-lg"></i>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 flex flex-col flex-grow">
                        <span class="text-xs text-gray-500 font-medium mb-2">${podcast.date}</span>
                        <h3 class="font-serif font-bold text-xl text-ink mb-3 leading-snug group-hover:text-accent transition-colors">${podcast.title}</h3>
                        <p class="text-inkLight text-sm line-clamp-2 leading-relaxed">${podcast.excerpt}</p>
                    </div>
                `;
                podcastsGrid.appendChild(card);
            });
        }

        // --- 4. EVENT LISTENERS FOR SEARCH & FILTER ---
        searchInput.addEventListener('input', (e) => {
            currentSearch = e.target.value;
            renderPodcasts();
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
                renderPodcasts();
            });
        });

        // --- 5. MODAL LOGIC ---
        function openModal(podcastId) {
            const podcast = mockPodcasts.find(p => p.id === podcastId);
            if (!podcast) return;

            // Populate Data
            document.getElementById('modalMainImg').src = podcast.image;
            document.getElementById('modalCategory').textContent = podcast.category;
            document.getElementById('modalDate').textContent = podcast.date;
            document.getElementById('modalDuration').innerHTML = `<i class="far fa-clock"></i> ${podcast.duration}`;
            document.getElementById('modalTitle').textContent = podcast.title;
            document.getElementById('modalContent').innerHTML = podcast.content;

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
        renderPodcasts();

    </script>
</body>
</html>
