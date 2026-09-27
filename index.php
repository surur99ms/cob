<?php
// index.php
session_start();

require_once 'koneksi.php';

$message = '';
$msgType = '';

// Ambil pesan dari session jika ada
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $msgType = $_SESSION['msgType'];
    // Hapus pesan setelah ditampilkan
    unset($_SESSION['message'], $_SESSION['msgType']);
}

// Proses Form YouTube Subscribe (Nama)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama'])) {
    $nama = htmlspecialchars($_POST['nama']);
    
    // URL Channel YouTube dengan konfirmasi subscribe (Silakan ganti URL-nya dengan yang asli)
    $youtubeLink = "https://www.youtube.com/@hayatake99?sub_confirmation=1"; 
    
    // Mengarahkan pengunjung ke YouTube
    header("Location: " . $youtubeLink);
    exit;
}

// Proses Form Newsletter (Email - untuk form di bagian bawah jika masih ada)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO subscribers (email) VALUES (:email)");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $_SESSION['message'] = "Terima kasih telah berlangganan!";
            $_SESSION['msgType'] = "success";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $_SESSION['message'] = "Email ini sudah terdaftar.";
            } else {
                $_SESSION['message'] = "Terjadi kesalahan. Silakan coba lagi.";
            }
            $_SESSION['msgType'] = "error";
        }
    } else {
        $_SESSION['message'] = "Format email tidak valid.";
        $_SESSION['msgType'] = "error";
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Ambil data konten terbaru
function getLatestContent($pdo, $type, $limit = 3) {
    $stmt = $pdo->prepare("SELECT * FROM contents WHERE type = :type ORDER BY created_at DESC LIMIT :limit");
    $stmt->bindParam(':type', $type);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

$articles = getLatestContent($pdo, 'article');
$podcasts = getLatestContent($pdo, 'podcast');
$videos = getLatestContent($pdo, 'video');

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Misbahussurur | Penulis & Programmer</title>
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
                        accent: '#E65A4B', // Warm reddish-orange
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
        /* Smooth arch shape for profile */
        .arch-image { border-radius: 9999px 9999px 24px 24px; }
        .thin-border { border: 1px solid rgba(0,0,0,0.08); }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col font-sans selection:bg-accent selection:text-white">

    <!-- Header / Navbar -->
    <header class="py-6 relative z-50">
        <div class="max-w-6xl mx-auto px-6 sm:px-8 lg:px-12 flex flex-col xl:flex-row items-center justify-between gap-8">
            <!-- Logo Section -->
            <div class="flex-shrink-0">
                <a href="#" class="inline-block">
                    <img src="logo-black.jpg" alt="Misbahussurur Logo" class="h-20 lg:h-28 w-auto object-contain">
                </a>
            </div>
            
            <!-- Navigation Links -->
            <nav class="flex flex-wrap items-center justify-center gap-x-6 gap-y-4">
                <a href="#newsletter" class="px-6 py-2.5 bg-accent hover:bg-accentHover text-white rounded-full font-medium transition-colors shadow-sm text-sm">
                    Gabung 10.000+ Subscribers
                </a>
                <a href="index.php#courses" class="text-ink hover:text-accent font-medium text-sm transition-colors">Aplikasi Baru</a>
                <a href="artikel.php" class="text-ink hover:text-accent font-medium text-sm transition-colors">Artikel</a>
                <a href="podcast.php" class="text-ink hover:text-accent font-medium text-sm transition-colors">Podcast</a>
                <a href="video.php" class="text-ink hover:text-accent font-medium text-sm transition-colors">Video</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        
        <!-- Hero Section -->
        <section class="relative overflow-hidden pt-4 pb-32">
            
            <div class="max-w-6xl mx-auto px-6 sm:px-8 lg:px-12 relative z-10">
                <div class="flex flex-col lg:flex-row items-center gap-16 lg:gap-24">
                    
                    <!-- Left: Image (Ali style blob) -->
                    <div class="w-full lg:w-5/12 flex justify-center lg:justify-start">
                        <div class="relative w-80 h-80 lg:w-[450px] lg:h-[450px]">
                            <!-- Yellow Blob Background -->
                            <div class="absolute inset-0 bg-[#FAD961] rounded-full transform -translate-x-4 translate-y-4"></div>
                            <!-- Speech bubble tail (yellow triangle) -->
                            <div class="absolute -right-4 top-1/2 w-16 h-16 bg-[#FAD961] transform rotate-45"></div>
                            
                            <!-- Profile Photo -->
                            <div class="absolute inset-0 bg-transparent rounded-full overflow-hidden flex items-end justify-center">
                                <img src="profile.jpg" alt="Misbahussurur" class="w-[95%] h-[95%] object-cover object-top rounded-full shadow-lg">
                            </div>
                            
                            <!-- White decorative strokes around blob -->
                            <svg class="absolute -left-6 top-1/4 w-12 h-24 text-[#FAD961]" viewBox="0 0 20 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 0 C 0 20, 0 30, 10 50" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Right: Text Content -->
                    <div class="w-full lg:w-7/12 space-y-8 text-center lg:text-left">
                        <h1 class="text-5xl lg:text-7xl font-serif font-bold text-ink leading-[1.1] relative inline-block whitespace-nowrap pr-12 lg:pr-20">
                            Halo Kang!
                            <span class="absolute top-0 right-0 text-5xl lg:text-7xl transform rotate-12 origin-bottom-right">👋</span>
                            <svg class="absolute w-[105%] h-auto bottom-1 -left-2 text-[#60C3D6] -z-10" viewBox="0 0 200 15" xmlns="http://www.w3.org/2000/svg"><path d="M0 10 Q 50 -5 100 10 T 200 5" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg>
                        </h1>
                        <p class="text-xl lg:text-2xl text-ink font-light leading-relaxed max-w-2xl pt-6">
                            Misbahussurur: Santri, Penulis, Pionir '<span class="font-bold border-b-2 border-ink">Santri Koding</span>'. Dia menerjemahkan kearifan lokal pesantren ke dalam baris-baris kode yang berdaya, menginspirasi gerakan koding di kalangan santri dan mengubah lanskap teknologi pendidikan.
                        </p>
                        
                        <!-- YouTube Subscribe Form -->
                        <div class="pt-8 max-w-lg mx-auto lg:mx-0">
                            <!-- Pesan sukses/error hanya untuk newsletter bawah (bisa diabaikan disini) -->
                            <form method="POST" action="" class="flex flex-col sm:flex-row gap-3">
                                <input type="text" name="nama" required placeholder="Nama Anda..." 
                                    class="flex-grow px-6 py-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition-shadow text-ink shadow-sm">
                                <button type="submit" 
                                    class="px-8 py-4 bg-ink hover:bg-inkLight text-white font-medium rounded-xl transition-colors shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                    <i class="fab fa-youtube text-lg"></i> Subscribe
                                </button>
                            </form>
                        </div>
                    </div>
                    
                </div>
            </div>
        </section>

        <!-- Content Pillar Grid Section -->
        <section id="articles" class="py-24 border-y border-black/5">
            <div class="max-w-6xl mx-auto px-6 sm:px-8 lg:px-12">
                <!-- CSS Columns for Masonry Layout -->
                <div class="columns-1 lg:columns-2 gap-8 space-y-8">
                    
                    <!-- 1. Title Block (Inside the Grid) -->
                    <div class="break-inside-avoid mb-12 mt-4 px-2 lg:px-0">
                        <h2 class="text-6xl lg:text-7xl font-serif font-medium leading-[1.1] text-ink tracking-tight">
                            Bagaimana <br/> Saya Bisa <br/>
                            <span class="relative inline-block mt-1">
                                Membantu Anda?
                                <svg class="absolute w-[110%] h-[130%] -top-1 -left-[5%] text-[#60C3D6] -z-10" viewBox="0 0 100 50" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M50,5 C80,5 95,15 95,25 C95,35 80,45 50,45 C20,45 5,35 5,25 C5,15 20,5 50,5 Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </h2>
                    </div>
                    
                    <!-- Articles Loop -->
                    <?php if (!empty($articles)): ?>
                        <?php foreach ($articles as $item): ?>
                            <div class="break-inside-avoid">
                                <a href="<?= htmlspecialchars($item['url']) ?>" class="group block p-8 lg:p-10 bg-white rounded-[32px] transition-all duration-300 hover:-translate-y-2 hover:shadow-xl hover:bg-[#81D4C6]">
                                    <div class="text-accent mb-6 text-3xl">
                                        <i class="fas fa-pen-nib"></i>
                                    </div>
                                    <h4 class="text-3xl font-serif font-bold text-ink mb-4 leading-snug">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </h4>
                                    <p class="text-inkLight text-lg leading-relaxed mb-8">
                                        <?= htmlspecialchars($item['excerpt']) ?>
                                    </p>
                                    <span class="inline-block border-b border-ink text-ink text-sm font-bold pb-0.5 group-hover:text-accent group-hover:border-accent transition-colors">
                                        Baca artikel &rarr;
                                    </span>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Podcasts Loop -->
                    <?php if (!empty($podcasts)): ?>
                        <?php foreach ($podcasts as $item): ?>
                            <div class="break-inside-avoid">
                                <a href="<?= htmlspecialchars($item['url']) ?>" class="group block p-8 lg:p-10 bg-white rounded-[32px] transition-all duration-300 hover:-translate-y-2 hover:shadow-xl hover:bg-[#F4B3CE]">
                                    <div class="text-accent mb-6 text-3xl">
                                        <i class="fas fa-headphones"></i>
                                    </div>
                                    <h4 class="text-3xl font-serif font-bold text-ink mb-4 leading-snug">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </h4>
                                    <p class="text-inkLight text-lg leading-relaxed mb-8">
                                        <?= htmlspecialchars($item['excerpt']) ?>
                                    </p>
                                    <span class="inline-block border-b border-ink text-ink text-sm font-bold pb-0.5 group-hover:text-accent group-hover:border-accent transition-colors">
                                        Dengarkan &rarr;
                                    </span>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Videos Loop -->
                    <?php if (!empty($videos)): ?>
                        <?php foreach ($videos as $item): ?>
                            <div class="break-inside-avoid">
                                <a href="<?= htmlspecialchars($item['url']) ?>" class="group block p-8 lg:p-10 bg-white rounded-[32px] transition-all duration-300 hover:-translate-y-2 hover:shadow-xl hover:bg-[#F7A667]">
                                    <div class="text-accent mb-6 text-3xl">
                                        <i class="fas fa-play-circle"></i>
                                    </div>
                                    <h4 class="text-3xl font-serif font-bold text-ink mb-4 leading-snug">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </h4>
                                    <p class="text-inkLight text-lg leading-relaxed mb-8">
                                        <?= htmlspecialchars($item['excerpt']) ?>
                                    </p>
                                    <span class="inline-block border-b border-ink text-ink text-sm font-bold pb-0.5 group-hover:text-accent group-hover:border-accent transition-colors">
                                        Tonton video &rarr;
                                    </span>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <!-- Static "and more" Card -->
                    <div class="break-inside-avoid">
                        <a href="#all-content" class="group block p-8 lg:p-10 bg-white rounded-[32px] transition-all duration-300 hover:-translate-y-2 hover:shadow-xl hover:bg-[#FAD961]">
                            <h4 class="text-3xl font-serif font-bold text-ink mb-8 mt-2 leading-snug">
                                ... and more!
                            </h4>
                            <span class="inline-block border-b border-ink text-ink text-sm font-bold pb-0.5 group-hover:text-accent group-hover:border-accent transition-colors">
                                Lihat semua konten &rarr;
                            </span>
                        </a>
                    </div>

                </div>
            </div>
        </section>

        <!-- Highlight Section (Book) -->
        <section id="courses" class="py-32">
            <div class="max-w-5xl mx-auto px-6 sm:px-8 lg:px-12">
                <div class="bg-ink rounded-3xl p-10 lg:p-16 flex flex-col md:flex-row items-center gap-16 relative overflow-hidden shadow-2xl">
                    <!-- Subtle background pattern inside the dark box -->
                    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
                    
                    <div class="w-full md:w-1/3 flex justify-center relative z-10">
                        <div class="w-56 h-72 bg-white rounded-md shadow-2xl flex items-center justify-center text-ink text-center p-8 transform -rotate-3 transition-transform duration-500 hover:rotate-0">
                            <div class="border border-ink/20 w-full h-full p-4 flex flex-col justify-center">
                                <h3 class="font-serif font-bold text-3xl leading-none mb-3">Santri<br/>Koding</h3>
                                <div class="w-12 h-1 bg-accent mx-auto mb-3"></div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-inkLight">Misbahussurur</p>
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-2/3 text-center md:text-left space-y-6 relative z-10">
                        <span class="text-accent font-bold tracking-widest uppercase text-xs">Aplikasi Baru</span>
                        <h2 class="text-4xl lg:text-5xl font-serif font-medium text-white leading-tight">Membangun Karir IT dari Pesantren.</h2>
                        <p class="text-gray-300 text-lg leading-relaxed max-w-xl">
                            Panduan komprehensif bagi santri yang ingin merambah dunia teknologi. Pelajari bagaimana logika nahwu sharaf sejalan dengan algoritma pemrograman.
                        </p>
                        <div class="pt-6 flex flex-row items-center justify-center md:justify-start gap-3 lg:gap-4 w-full">
                            <a href="https://wa.me/6285606373627?text=Halo%20Admin!%20%F0%9F%91%8B%20Aku%20lihat%20info%20tentang%20Web%20App%20terbarunya%20SantriKoding%20nih%20dan%20penasaran%20banget.%20Aku%20mau%20ikutan%20Pre-Order%20dong%20biar%20dapet%20akses%20promonya!%20Masih%20ada%20stoknya%20kan,%20Min?%20%F0%9F%A4%A9" target="_blank" class="px-5 lg:px-8 py-3 lg:py-4 bg-white text-ink font-semibold text-base lg:text-lg hover:bg-gray-100 transition-colors rounded-sm text-center whitespace-nowrap">
                                Pre-Order Sekarang
                            </a>
                            <a href="katalog.php" class="px-5 lg:px-8 py-3 lg:py-4 bg-transparent border-2 border-white text-white font-semibold text-base lg:text-lg hover:bg-white/10 transition-colors rounded-sm text-center whitespace-nowrap">
                                Katalog Aplikasi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Newsletter Section -->
        <section id="newsletter" class="py-24 border-t border-black/5">
            <div class="max-w-5xl mx-auto px-6 sm:px-8 lg:px-12">
                <div class="bg-white rounded-[40px] p-10 lg:p-16 flex flex-col md:flex-row items-center gap-12 lg:gap-20 shadow-sm border border-black/5">
                    
                    <!-- Left Side -->
                    <div class="w-full md:w-1/2 space-y-8">
                        <h2 class="text-5xl lg:text-6xl font-serif font-medium text-ink leading-[1.1] relative inline-block">
                            Subscribe <br/>
                            <span class="font-bold">Santri<span class="text-accent">Koding</span></span>
                            <!-- Paper Airplane SVG -->
                            <svg class="absolute -right-16 -top-4 w-16 h-16 text-[#CBA3F5] transform rotate-12" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22 2L11 13M22 2L15 22L11 13M22 2L2 9L11 13" stroke="#2D2D2D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </h2>
                        
                        <p class="text-ink text-xl font-medium max-w-sm">
                            Join a growing community of more than <span class="font-bold">10,000 friendly readers.</span>
                        </p>
                        
                        <!-- Reviews & Avatars -->
                        <div class="flex items-center gap-4 pt-2">
                            <div class="flex -space-x-3">
                                <div class="w-10 h-10 rounded-full border-2 border-white bg-gray-200 overflow-hidden"><img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=100&h=100&fit=crop" class="w-full h-full object-cover"></div>
                                <div class="w-10 h-10 rounded-full border-2 border-white bg-gray-300 overflow-hidden"><img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&h=100&fit=crop" class="w-full h-full object-cover"></div>
                                <div class="w-10 h-10 rounded-full border-2 border-white bg-gray-400 overflow-hidden"><img src="https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=100&h=100&fit=crop" class="w-full h-full object-cover"></div>
                            </div>
                            <div>
                                <div class="flex text-[#FAD961] text-[15px]">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                </div>
                                <p class="text-sm text-inkLight font-medium mt-1">200+ reviews</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Side -->
                    <div class="w-full md:w-1/2 space-y-8">
                        <p class="text-ink text-xl lg:text-[1.35rem] leading-relaxed font-medium">
                            Each week, I share actionable productivity tips, practical life advice, and highlights from my favourite books, directly to your inbox. It's free, and always will be.
                        </p>
                        
                        <div>
                            <?php if ($message): ?>
                                <div class="p-4 mb-4 rounded-xl <?= $msgType == 'success' ? 'bg-green-100 text-green-900 border border-green-200' : 'bg-red-100 text-red-900 border border-red-200' ?>">
                                    <?= htmlspecialchars($message) ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" class="flex flex-col sm:flex-row gap-2 bg-[#F8F7F3] rounded-full p-2 border border-black/5">
                                <input type="email" name="email" required placeholder="Your email" 
                                    class="flex-grow px-6 py-4 bg-transparent focus:outline-none text-ink placeholder-gray-500 font-medium text-lg">
                                <button type="submit" 
                                    class="px-8 py-4 bg-[#60C3D6] hover:bg-[#4eb3c6] text-ink font-semibold rounded-full transition-colors whitespace-nowrap shadow-sm text-lg">
                                    Join Now!
                                </button>
                            </form>
                            
                            <p class="text-xs text-inkLight mt-6 leading-relaxed px-2 font-medium">
                                By submitting this form, you'll be signed up to my free newsletter, which sometimes includes mentions of my books, apps and courses. You can opt-out at any time with no hard feelings 😌 Here's our privacy policy if you like reading.
                            </p>
                        </div>
                    </div>
                    
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-black/5 pt-20 pb-10">
        <div class="max-w-6xl mx-auto px-6 sm:px-8 lg:px-12">
            <div class="flex flex-col md:flex-row justify-between items-start gap-12 mb-16">
                <div class="max-w-sm">
                    <a href="#" class="text-2xl font-serif font-bold text-ink">Misbahussurur</a>
                    <p class="text-inkLight text-base mt-4 leading-relaxed">Membagikan tulisan tentang teknologi, koding, dan kearifan lokal. Berusaha menjadi jembatan antara dunia pesantren dan industri IT.</p>
                </div>
                <!-- Social Links -->
                <div class="flex gap-6">
                    <a href="#" class="text-inkLight hover:text-accent transition-colors text-2xl"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-inkLight hover:text-accent transition-colors text-2xl"><i class="fab fa-youtube"></i></a>
                    <a href="#" class="text-inkLight hover:text-accent transition-colors text-2xl"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-inkLight hover:text-accent transition-colors text-2xl"><i class="fas fa-envelope"></i></a>
                </div>
            </div>
            
            <div class="border-t border-black/10 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-sm font-medium">
                <p class="text-gray-500">© 2024 Misbahussurur All rights reserved.</p>
                <div class="flex gap-8 text-gray-500">
                    <a href="#" class="hover:text-ink transition-colors">Privacy</a>
                    <a href="#" class="hover:text-ink transition-colors">Terms</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
