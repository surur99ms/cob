<?php
// katalog.php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Aplikasi | Santri Koding</title>
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
                <a href="katalog.php" class="text-accent font-medium text-sm transition-colors">Katalog Aplikasi</a>
                <a href="artikel.php" class="text-ink hover:text-accent font-medium text-sm transition-colors">Artikel</a>
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
                <span class="text-accent font-bold tracking-widest uppercase text-xs">Marketplace</span>
                <h1 class="text-5xl font-serif font-bold text-ink mt-4 mb-6">Katalog Aplikasi</h1>
                <p class="text-lg text-inkLight">Temukan berbagai macam source code dan aplikasi berkualitas untuk mendukung ekosistem pesantren dan bisnis Anda.</p>
            </div>

            <!-- Search and Filter Section -->
            <div class="flex flex-col md:flex-row gap-6 justify-between items-center mb-12 bg-white p-4 rounded-2xl shadow-sm border border-black/5">
                <!-- Search Bar -->
                <div class="w-full md:w-1/3 relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Cari aplikasi..." 
                           class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent focus:bg-white transition-all text-ink">
                </div>

                <!-- Filters -->
                <div class="w-full md:w-auto flex overflow-x-auto pb-2 md:pb-0 gap-2 scrollbar-hide" id="filterContainer">
                    <button class="filter-btn px-5 py-2.5 rounded-xl font-medium text-sm transition-colors bg-ink text-white" data-filter="all">Semua</button>
                    <button class="filter-btn px-5 py-2.5 rounded-xl font-medium text-sm transition-colors bg-gray-100 text-inkLight hover:bg-gray-200" data-filter="Web">Web</button>
                    <button class="filter-btn px-5 py-2.5 rounded-xl font-medium text-sm transition-colors bg-gray-100 text-inkLight hover:bg-gray-200" data-filter="Mobile">Mobile</button>
                    <button class="filter-btn px-5 py-2.5 rounded-xl font-medium text-sm transition-colors bg-gray-100 text-inkLight hover:bg-gray-200 whitespace-nowrap" data-filter="Sistem Manajemen Pesantren">Sistem Manajemen Pesantren</button>
                </div>
            </div>

            <!-- Products Grid -->
            <div id="productsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Product Cards will be rendered here by JS -->
            </div>

            <!-- Empty State (Hidden by default) -->
            <div id="emptyState" class="hidden text-center py-20">
                <div class="text-gray-300 text-6xl mb-4"><i class="fas fa-box-open"></i></div>
                <h3 class="text-2xl font-serif font-bold text-ink mb-2">Tidak ditemukan</h3>
                <p class="text-inkLight">Maaf, kami tidak menemukan aplikasi yang sesuai dengan pencarian Anda.</p>
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

    <!-- Product Detail Modal -->
    <div id="productModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-ink/70 backdrop-blur-sm transition-opacity" id="modalBackdrop"></div>

        <!-- Modal Panel -->
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl opacity-100 translate-y-0 sm:scale-100">
                    
                    <!-- Close Button -->
                    <button id="closeModalBtn" class="absolute top-4 right-4 w-10 h-10 bg-white/80 backdrop-blur rounded-full flex items-center justify-center text-ink hover:bg-gray-100 transition-colors z-10 shadow-sm border border-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>

                    <div class="flex flex-col lg:flex-row max-h-[90vh] overflow-y-auto">
                        <!-- Left Side: Images -->
                        <div class="w-full lg:w-1/2 bg-gray-50 p-6 lg:p-8 border-r border-gray-100">
                            <img id="modalMainImg" src="" alt="Thumbnail" class="w-full h-auto object-cover rounded-2xl shadow-md border border-black/5 aspect-video mb-4">
                            
                            <!-- Additional Screenshots (Mock) -->
                            <div class="grid grid-cols-3 gap-3">
                                <img src="https://images.unsplash.com/photo-1555066931-4365d14bab8c?q=80&w=400&auto=format&fit=crop" class="w-full h-24 object-cover rounded-xl shadow-sm border border-black/5 cursor-pointer hover:opacity-80 transition-opacity">
                                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=400&auto=format&fit=crop" class="w-full h-24 object-cover rounded-xl shadow-sm border border-black/5 cursor-pointer hover:opacity-80 transition-opacity">
                                <img src="https://images.unsplash.com/photo-1461749280684-dccba630e2f6?q=80&w=400&auto=format&fit=crop" class="w-full h-24 object-cover rounded-xl shadow-sm border border-black/5 cursor-pointer hover:opacity-80 transition-opacity">
                            </div>
                        </div>

                        <!-- Right Side: Details -->
                        <div class="w-full lg:w-1/2 p-6 lg:p-10 flex flex-col">
                            <span id="modalCategory" class="text-accent font-bold tracking-widest uppercase text-xs mb-2"></span>
                            <h2 id="modalTitle" class="text-3xl font-serif font-bold text-ink mb-4 leading-tight"></h2>
                            
                            <div class="flex items-center gap-2 mb-6">
                                <span class="bg-[#FAD961] text-ink text-xs font-bold px-2 py-1 rounded">Best Seller</span>
                                <div class="flex text-[#FAD961] text-[13px]">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                                </div>
                            </div>

                            <div class="prose prose-sm text-inkLight mb-8 flex-grow">
                                <h4 class="font-bold text-ink mb-2">Deskripsi Fitur:</h4>
                                <p id="modalDescription" class="leading-relaxed"></p>
                                
                                <h4 class="font-bold text-ink mt-6 mb-2">Tech Stack:</h4>
                                <div id="modalTechStack" class="flex flex-wrap gap-2">
                                    <!-- Tech stack tags injected here -->
                                </div>
                            </div>

                            <div class="mt-auto pt-6 border-t border-gray-100">
                                <div class="flex items-end justify-between mb-6">
                                    <span class="text-sm text-inkLight">Harga Spesial</span>
                                    <span id="modalPrice" class="text-3xl font-bold text-ink"></span>
                                </div>
                                <a href="#" id="modalCheckoutBtn" target="_blank" class="w-full block text-center px-8 py-4 bg-[#25D366] hover:bg-[#1ebe5d] text-white font-bold rounded-xl transition-colors shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                                    <i class="fab fa-whatsapp mr-2 text-xl"></i> Pesan via WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- 1. MOCK DATA ---
        const mockProducts = [
            {
                id: 1,
                title: "SIMAK - Sistem Informasi Akademik Pesantren",
                category: "Sistem Manajemen Pesantren",
                price: "Rp 1.500.000",
                numericPrice: 1500000,
                description: "Aplikasi berbasis web komprehensif untuk mengelola data santri, nilai, absensi, hingga keuangan pesantren. Dilengkapi dengan dashboard admin yang intuitif dan laporan yang dapat dicetak.",
                techStack: ["Laravel", "MySQL", "Tailwind CSS", "Alpine.js"],
                image: "https://images.unsplash.com/photo-1531403009284-440f080d1e12?q=80&w=800&auto=format&fit=crop"
            },
            {
                id: 2,
                title: "Koperasi Syariah POS",
                category: "Web",
                price: "Rp 850.000",
                numericPrice: 850000,
                description: "Point of Sale (POS) khusus untuk koperasi pondok pesantren. Mendukung fitur kasir, manajemen stok, laporan laba rugi, dan pencatatan utang santri secara digital.",
                techStack: ["CodeIgniter 4", "Bootstrap 5", "jQuery", "MariaDB"],
                image: "https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?q=80&w=800&auto=format&fit=crop"
            },
            {
                id: 3,
                title: "Hafalan Tracker Mobile",
                category: "Mobile",
                price: "Rp 2.000.000",
                numericPrice: 2000000,
                description: "Aplikasi Android & iOS untuk mencatat dan memantau progres hafalan Qur'an santri. Dilengkapi notifikasi pengingat murojaah dan akses wali santri untuk melihat laporan.",
                techStack: ["Flutter", "Firebase", "Node.js (API)"],
                image: "https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?q=80&w=800&auto=format&fit=crop"
            },
            {
                id: 4,
                title: "Landing Page Pondok Pesantren",
                category: "Web",
                price: "Rp 350.000",
                numericPrice: 350000,
                description: "Template website profile modern yang dioptimasi untuk SEO. Cocok untuk menampilkan informasi pendaftaran santri baru, fasilitas, dan kegiatan pondok.",
                techStack: ["HTML5", "Tailwind CSS", "Alpine.js", "Vite"],
                image: "https://images.unsplash.com/photo-1499951360447-b19be8fe80f5?q=80&w=800&auto=format&fit=crop"
            }
        ];

        // --- 2. ELEMENTS & STATE ---
        const productsGrid = document.getElementById('productsGrid');
        const emptyState = document.getElementById('emptyState');
        const searchInput = document.getElementById('searchInput');
        const filterBtns = document.querySelectorAll('.filter-btn');
        
        // Modal Elements
        const modal = document.getElementById('productModal');
        const modalBackdrop = document.getElementById('modalBackdrop');
        const closeModalBtn = document.getElementById('closeModalBtn');
        
        let currentFilter = 'all';
        let currentSearch = '';

        // --- 3. RENDER LOGIC ---
        function renderProducts() {
            // Filter data
            const filteredProducts = mockProducts.filter(product => {
                const matchesCategory = currentFilter === 'all' || product.category === currentFilter;
                const matchesSearch = product.title.toLowerCase().includes(currentSearch.toLowerCase()) || 
                                      product.description.toLowerCase().includes(currentSearch.toLowerCase());
                return matchesCategory && matchesSearch;
            });

            // Clear Grid
            productsGrid.innerHTML = '';

            if (filteredProducts.length === 0) {
                productsGrid.classList.add('hidden');
                emptyState.classList.remove('hidden');
                return;
            }

            productsGrid.classList.remove('hidden');
            emptyState.classList.add('hidden');

            // Render HTML
            filteredProducts.forEach(product => {
                const card = document.createElement('div');
                card.className = "bg-white rounded-[24px] overflow-hidden border border-black/5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col group";
                
                card.innerHTML = `
                    <div class="relative overflow-hidden aspect-video">
                        <img src="${product.image}" alt="${product.title}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold text-ink shadow-sm">
                            ${product.category}
                        </div>
                    </div>
                    <div class="p-6 flex flex-col flex-grow">
                        <h3 class="font-serif font-bold text-xl text-ink mb-2 leading-snug group-hover:text-accent transition-colors">${product.title}</h3>
                        <p class="text-inkLight text-sm line-clamp-2 mb-6">${product.description}</p>
                        
                        <div class="mt-auto flex items-center justify-between">
                            <span class="font-bold text-lg text-ink">${product.price}</span>
                            <button onclick="openModal(${product.id})" class="px-5 py-2.5 bg-paper hover:bg-ink hover:text-white text-ink font-medium text-sm rounded-xl transition-colors border border-black/10">
                                Lihat Detail
                            </button>
                        </div>
                    </div>
                `;
                productsGrid.appendChild(card);
            });
        }

        // --- 4. EVENT LISTENERS FOR SEARCH & FILTER ---
        searchInput.addEventListener('input', (e) => {
            currentSearch = e.target.value;
            renderProducts();
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
                renderProducts();
            });
        });

        // --- 5. MODAL LOGIC ---
        function openModal(productId) {
            const product = mockProducts.find(p => p.id === productId);
            if (!product) return;

            // Populate Data
            document.getElementById('modalMainImg').src = product.image;
            document.getElementById('modalCategory').textContent = product.category;
            document.getElementById('modalTitle').textContent = product.title;
            document.getElementById('modalDescription').textContent = product.description;
            document.getElementById('modalPrice').textContent = product.price;

            // Tech Stack
            const techContainer = document.getElementById('modalTechStack');
            techContainer.innerHTML = product.techStack.map(tech => 
                `<span class="px-3 py-1 bg-gray-100 text-inkLight rounded-full text-xs font-medium border border-gray-200">${tech}</span>`
            ).join('');

            // Setup WhatsApp URL
            // Format: "Halo Mas Misbah, saya tertarik untuk membeli source code/aplikasi [Nama Produk] dari Santri Koding dengan harga [Harga]. Boleh minta info lebih lanjut?"
            const phoneNumber = "6281234567890"; // Dummy number
            const messageText = `Halo Mas Misbah, saya tertarik untuk membeli source code/aplikasi *${product.title}* dari Santri Koding dengan harga *${product.price}*. Boleh minta info lebih lanjut?`;
            const encodedText = encodeURIComponent(messageText);
            const waUrl = `https://wa.me/${phoneNumber}?text=${encodedText}`;
            
            document.getElementById('modalCheckoutBtn').href = waUrl;

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
        renderProducts();

    </script>
</body>
</html>
