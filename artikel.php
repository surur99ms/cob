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

    <!-- Article Detail Fullscreen Reader Modal -->
    <div id="articleModal" class="fixed inset-0 z-50 hidden bg-[#FBF9F6] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Sticky Top Navigation Bar -->
        <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-black/5 shadow-sm">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                <button onclick="closeModal()" class="inline-flex items-center gap-2 text-ink hover:text-accent font-medium text-sm transition-colors py-2 px-3 rounded-xl hover:bg-gray-100">
                    <i class="fas fa-arrow-left"></i>
                    <span class="font-sans">Kembali ke Kumpulan Artikel</span>
                </button>
                <div class="flex items-center gap-2">
                    <button id="closeModalBtn" class="w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 text-ink flex items-center justify-center transition-colors shadow-sm" title="Tutup (Esc)">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>
        </header>

        <!-- Fullscreen Article Container -->
        <main class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-14">
            <!-- Article Header Meta -->
            <div class="max-w-3xl mx-auto mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <span id="modalCategory" class="bg-accent/10 text-accent font-bold px-3.5 py-1.5 rounded-full text-xs uppercase tracking-wider"></span>
                    <span id="modalDate" class="text-sm text-gray-500 font-medium"></span>
                </div>
                <h1 id="modalTitle" class="text-3xl sm:text-4xl lg:text-5xl font-serif font-bold text-ink leading-tight mb-6"></h1>
                
                <!-- Author Info -->
                <div class="flex items-center gap-3 pt-2 pb-4 border-b border-black/5">
                    <div class="w-11 h-11 rounded-full bg-accent/15 text-accent flex items-center justify-center font-bold text-base font-serif shadow-sm">
                        M
                    </div>
                    <div>
                        <p class="text-sm font-bold text-ink">Misbahussurur</p>
                        <p class="text-xs text-gray-500">Santri, Penulis, Pionir Santri Koding</p>
                    </div>
                </div>
            </div>

            <!-- Featured Image -->
            <div class="max-w-3xl mx-auto mb-10 overflow-hidden rounded-2xl sm:rounded-3xl shadow-lg border border-black/5 bg-gray-100">
                <img id="modalMainImg" src="" alt="Thumbnail" class="w-full h-64 sm:h-96 md:h-[440px] object-cover">
            </div>

            <!-- Article Content -->
            <div class="max-w-3xl mx-auto">
                <div id="modalContent" class="text-inkLight font-serif text-lg sm:text-xl leading-relaxed space-y-6">
                </div>

                <!-- Footer Navigation inside Article -->
                <div class="mt-16 pt-8 border-t border-black/10 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <button onclick="closeModal()" class="w-full sm:w-auto px-8 py-3.5 bg-ink hover:bg-inkLight text-white font-medium rounded-xl transition-all shadow-md inline-flex items-center justify-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali ke Kumpulan Artikel
                    </button>
                    <button onclick="document.getElementById('articleModal').scrollTo({top: 0, behavior: 'smooth'});" class="w-full sm:w-auto px-6 py-3.5 bg-white border border-gray-200 hover:bg-gray-50 text-ink font-medium rounded-xl transition-all shadow-sm inline-flex items-center justify-center gap-2">
                        <i class="fas fa-arrow-up"></i> Kembali ke Atas
                    </button>
                </div>
            </div>
        </main>
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
                content: `<p>Sebagai santri, kita sering kali dihadapkan dengan kerumitan gramatika Arab klasik seperti Nahwu dan Sharaf. Mulai dari lantunan wazan yang terus diulang di ruang kelas hingga perdebatan panjang soal kedudukan satu kata dalam forum musyawarah, seluruh proses itu kerap dipandang oleh orang luar sebagai tradisi usang yang jauh dari gegap gempita dunia teknologi. Padahal, jika kita mau menengok sedikit lebih dalam, logika struktural yang kita pelajari saat men-tashrif kata atau meng-i'rab kalimat sesungguhnya sangat mirip dengan logika pemrograman komputer.</p>
<p>Pemahaman mendalam tentang pola bahasa Arab itu justru bisa menjadi modal kuat bagi seorang santri untuk menguasai bahasa pemrograman modern seperti PHP, Python, atau JavaScript dengan lebih cepat. Di balik perbedaan wajahnya—yang satu beraksara Arab di atas kertas kuning, yang satu beraksara Latin di layar monitor gelap—keduanya bertumpu pada fondasi yang sama: ketelitian syntax, kepekaan membaca pola, dan alur berpikir yang sistematis.</p>
<p>Lihatlah bagaimana Ilmu Sharaf bekerja membentuk nalar kita. Di pesantren, seorang santri tidak pernah disuruh menghafal jutaan kosakata Arab satu per satu secara terpisah. Itu mustahil dan tidak efisien. Sebagai gantinya, santri dilatih menghafal wazan atau timbangan pola baku, lalu memasukkan akar kata (mauzun) ke dalamnya untuk melahirkan berbagai bentuk makna baru. Dari tiga huruf akar saja, sebuah kata bisa bertransformasi menjadi kata kerja lampau, kata kerja sekarang, subjek pelaku, objek sasaran, hingga keterangan tempat dan waktu.</p>
<p>Di dunia rekayasa perangkat lunak, cara kerja semacam ini adalah inti dari konsep function dan class. Seorang programmer tidak menulis ulang instruksi dari nol setiap kali membutuhkan hasil yang serupa; ia membuat sebuah cetakan kode yang disebut blueprint, menentukan parameter masukannya, lalu membiarkan mesin mengolahnya menjadi output yang diinginkan. Santri yang terbiasa men-tashrif sebenarnya sudah sejak lama melatih otaknya untuk berpikir secara modular dan generatif. Ketika suatu saat ia berhadapan dengan baris kode yang memanggil sebuah fungsi dengan berbagai argumen, nalarnya tidak akan terlalu asing—di kepalanya, itu hanyalah perkara wazan dan mauzun dalam rupa yang berbeda.</p>
<p>Kedekatan logika itu terasa semakin nyata saat kita melangkah ke wilayah Ilmu Nahwu. Jika Sharaf mengurus anatomi internal sebuah kata, Nahwu mengatur hubungan antar-kata di dalam bangunan kalimat. Dalam Nahwu, harakat akhir sebuah kata tidak pernah berubah secara acak; ia tunduk pada amil yang memasukinya. Sebuah kata benda yang mulanya berstatus rafa' bisa seketika berubah menjadi nashab atau jar begitu disentuh oleh amil tertentu. Mekanisme ini sejajar dengan cara kerja operator dan manajemen status (state) dalam pemrograman, di mana nilai atau sifat sebuah variabel akan termutasi sesuai fungsi atau kondisi yang memprosesnya.</p>
<p>Begitu pula ketika kita membedah struktur jumlah fi'liyyah yang terdiri dari fi'il (kata kerja), fa'il (pelaku), dan maf'ul bih (objek). Kemampuan otak untuk memilah mana entitas yang bertindak, apa aksi yang dilakukan, dan siapa yang menerima dampak aksi tersebut merupakan pondasi dasar dari paradigma Object-Oriented Programming. Bahkan, logika percabangan sebab-akibat yang menjadi nyawa dari hampir semua aplikasi komputer—yang biasa dikenal dengan struktur if-else—sudah sangat akrab bagi santri melalui bab Adawat asy-Syarth. Kaidah klasik seperti man jadda wajada mengajarkan bahwa setiap kondisi syarat (fi'il syarth) selalu terikat secara ketat dengan konsekuensi hasil (jawab syarth). Aturan pen-jazm-an yang hanya berlaku dalam batas kalimat syarat itu pun secara tidak langsung mengenalkan kita pada konsep scope atau cakupan wilayah kerja sebuah kode.</p>
<p>Namun, keunggulan paling sunyi dari tradisi belajar kitab kuning justru terletak pada kebiasaan membaca kitab gundul—teks Arab tanpa harakat sama sekali. Saat seorang santri menatap lembaran teks gundul dan mulai membacanya dengan lancar, otaknya sebenarnya sedang menjalankan proses yang identik dengan compiler pada komputer. Dalam hitungan detik, ia memindai setiap kata untuk menentukan jenisnya, memeriksa keterkaitan struktur antar-kalimat, lalu memutuskan harakat yang tepat agar maknanya lurus.</p>
<p>Di tengah proses membaca itu, santri juga terbiasa melakukan pelacakan marji' dhamir. Ketika bertemu sebuah kata ganti, ia harus menelusuri baris-baris sebelumnya untuk memastikan ke mana kata ganti itu merujuk agar tidak terjadi kekacauan makna. Bagi seorang programmer, aktivitas ini persis sama dengan melacak referensi variabel dan alur data di dalam ratusan baris kode. Lebih dari itu, santri paham betul betapa fatalnya satu kesalahan kecil. Salah meletakkan satu harakat fathah atau dhammah bisa membalikkan posisi pelaku menjadi korban dan merusak pesan seluruh paragraf. Sensitivitas terhadap detail inilah yang menjadi kunci utama debugging dalam pemrograman, di mana satu titik koma yang tertinggal atau satu tanda kurung yang lupa ditutup sudah cukup untuk membuat seluruh sistem berhenti bekerja.</p>
<p>Bahkan ketika berhadapan dengan hal-hal yang tidak terduga, santri sudah dibekali mental yang matang. Dalam Nahwu Sharaf, selalu ada kaidah umum (qiyas) dan selalu ada pula pengecualian (syadz atau ghairu munsharif). Kenyataan bahwa tidak semua kata mau tunduk pada aturan standar melatih kita untuk selalu waspada terhadap anomali. Di dunia coding, kepekaan serupa sangat dibutuhkan untuk menangani edge cases dan menyusun exception handling agar sebuah aplikasi tidak lumpuh ketika menerima data di luar kebiasaan.</p>
<p>Pada akhirnya, pemrograman sering kali tampak menakutkan bukan karena logikanya mustahil dijangkau, melainkan karena istilah bahasa Inggris dan tampilannya yang terasa asing. Jika selubung istilah teknis itu kita singkap, coding sesungguhnya bukanlah ilmu sihir modern, melainkan seni menyusun instruksi yang teratur, presisi, dan bebas dari ambiguitas.</p>
<p>Bagi para santri, otot logika untuk melakukan semua itu sebenarnya sudah ditempa bertahun-tahun di balik lembar-lembar Al-Jurumiyyah, Imrithi, hingga Alfiyah. Ketelitian menimbang kedudukan kata, kesabaran merunut rujukan, serta ketajaman menangkap pola di balik kerumitan teks klasik adalah modal intelektual yang mahal. Yang tersisa kini hanyalah keberanian untuk melangkah keluar dari sekat anggapan lama, lalu menerjemahkan ketajaman nalar pesantren itu ke dalam bahasa mesin. Sebab baik saat meng-i'rab sebuah teks warisan ulama maupun saat merangkai algoritma di layar komputer, keduanya bermuara pada satu ikhtiar yang sama: merawat keteraturan akal demi melahirkan karya yang bermanfaat.</p>`,
                image: "https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=1200&auto=format&fit=crop"
            },
            {
                id: 2,
                title: "Roadmap Menjadi Web Developer Modern Tahun Ini",
                category: "Pemrograman",
                date: "1 Sep 2026",
                excerpt: "Panduan langkah demi langkah dari nol hingga mahir membuat website profesional dengan teknologi terkini.",
                content: `<p>Dunia pengembangan web bergerak sangat cepat. Apa yang populer lima tahun lalu mungkin sudah tergantikan oleh framework baru hari ini, dan teknologi yang hari ini dipuja-puja bisa saja mulai ditinggalkan beberapa tahun mendatang. Di tengah arus perubahan yang begitu deras, pemula sering kali merasa kewalahan bahkan sebelum sempat menulis baris kode pertama mereka. Setiap hari selalu ada istilah baru, pustaka baru, dan perdebatan baru di media sosial tentang bahasa pemrograman mana yang paling unggul. Namun, di balik segala hiruk-pikuk itu, ada satu kebenaran yang tidak pernah lekang oleh waktu: fundamentalnya tetap sama, yakni HTML, CSS, dan JavaScript.</p>
<p>Menguasai tiga serangkai dasar ini bukan sekadar formalitas sebelum melompat ke teknologi yang lebih canggih, melainkan pondasi yang menentukan seberapa kokoh bangunan karier kita kelak. HTML mengajarkan bagaimana menyusun kerangka informasi secara semantik agar mudah dipahami baik oleh manusia maupun mesin pencari. CSS melatih kepekaan visual dan tata letak, memastikan sebuah halaman tampil rapi dan responsif di berbagai ukuran layar, dari ponsel mungil hingga monitor lebar. Sementara itu, JavaScript adalah nyawa yang membuat halaman web menjadi hidup dan interaktif. Banyak pembelajar yang terburu-buru melompat ke framework besar tanpa memahami cara kerja dasar Document Object Model (DOM) atau bagaimana browser berkomunikasi dengan server melalui protokol HTTP. Akibatnya, ketika muncul masalah teknis yang sedikit rumit, mereka mudah terjebak dan kebingungan. Memahami fundamental dengan sabar justru akan mempercepat langkah kita saat nanti harus beradaptasi dengan alat apa pun yang muncul di masa depan.</p>
<p>Setelah pondasi dasar tersebut benar-benar tertanam kuat, langkah selanjutnya adalah memilih spesialisasi yang sesuai dengan minat dan cara berpikir kita: Frontend atau Backend. Peta jalan ini dirancang untuk membimbing Anda agar tidak tersesat di tengah lautan informasi teknologi yang begitu luas, karena mencoba menguasai segalanya sekaligus di awal perjalanan hanya akan menguras energi dan memicu kelelahan mental.</p>
<p>Bagi mereka yang menikmati pertemuan antara logika dan estetika, jalur Frontend menawarkan ruang ekspresi yang luas. Seorang pengembang frontend bertanggung jawab atas segala sesuatu yang dilihat, disentuh, dan dirasakan langsung oleh pengguna di layar. Di sini, pemahaman tentang prinsip UI/UX menjadi sangat penting—bukan hanya soal membuat tampilan yang indah, tetapi juga intuitif, cepat dimuat, dan ramah bagi semua pengguna. Setelah mantap dengan JavaScript murni, barulah masuk akal untuk mempelajari framework atau pustaka modern seperti React atau Vue. Alat-alat ini membantu mengelola antarmuka yang kompleks menjadi komponen-komponen kecil yang rapi dan mudah dirawat. Di titik ini pula, pengembang frontend mulai belajar mengelola status aplikasi (state management), mengatur gaya secara efisien, dan memastikan data yang ditarik dari server tersaji dengan mulus tanpa membuat pengguna menunggu lama.</p>
<p>Di sisi lain, bagi mereka yang lebih tertarik pada arsitektur sistem, pengolahan data, dan logika di balik layar, jalur Backend adalah medan yang menantang sekaligus memuaskan. Jika frontend adalah ruang pamer sebuah toko, maka backend adalah gudang, kasir, dan sistem logistik yang memastikan semuanya berjalan tanpa hambatan. Di wilayah ini, fokus utama bergeser pada bagaimana merancang Database—baik yang relasional seperti MySQL dan PostgreSQL maupun non-relasional—agar data tersimpan dengan aman dan cepat diakses. Pengembang backend juga bertugas membangun API sebagai jembatan komunikasi antara server dan antarmuka pengguna, menangani sistem autentikasi, serta menjaga keamanan aplikasi dari berbagai celah serangan. Pilihan bahasanya pun beragam dan matang; Node.js menawarkan kemudahan bagi yang sudah akrab dengan JavaScript, sementara PHP—dengan ekosistem modernnya—tetap menjadi tulang punggung yang sangat andal dan pragmatis bagi jutaan aplikasi web di seluruh dunia.</p>
<p>Namun, menjadi pengembang web modern tahun ini tidak lagi cukup hanya dengan bisa menulis kode di komputer sendiri. Ada perangkat pendukung yang wajib menjadi kebiasaan sehari-hari sejak dini, salah satunya adalah Git dan platform seperti GitHub untuk mengelola versi kode serta berkolaborasi dengan orang lain. Memahami cara mengunggah aplikasi ke server (deployment) agar benar-benar bisa diakses oleh publik di internet juga merupakan tonggak penting yang sering kali memberi kepuasan tersendiri. Selain itu, lanskap pengembangan web saat ini juga sangat lekat dengan kehadiran asisten kecerdasan buatan. Alih-alih menghindarinya atau justru bergantung penuh padanya, pengembang modern perlu menempatkan AI sebagai rekan diskusi—alat untuk mempercepat pencarian solusi dan memeriksa kesalahan, sembari tetap memegang kendali penuh atas logika dan kualitas kode yang dihasilkan.</p>
<p>Pada akhirnya, roadmap terbaik bukanlah daftar panjang teknologi yang harus dicentang satu per satu demi terlihat canggih, melainkan kompas yang menjaga kita tetap bergerak maju. Kesalahan paling umum dalam belajar pemrograman adalah terjebak terlalu lama menonton tutorial tanpa pernah membangun apa pun dari nol. Cara tercepat untuk benar-benar paham adalah dengan mulai membuat proyek nyata, sekecil apa pun itu—entah sebuah halaman profil sederhana, aplikasi pencatat keuangan harian, atau sistem katalog kecil untuk usaha di sekitar kita. Dari setiap error yang ditemui dan setiap solusi yang berhasil ditemukan sendiri, di situlah keahlian sejati terbentuk. Dunia web mungkin akan terus berubah dengan kecepatan tinggi, tetapi selama kita memiliki fundamental yang kokoh, fokus yang jelas, dan kebiasaan untuk terus berkarya, kita tidak akan pernah tersesat di dalamnya.</p>`,
                image: "https://images.unsplash.com/photo-1498050108023-c5249f4df085?q=80&w=1200&auto=format&fit=crop"
            },
            {
                id: 3,
                title: "5 Ekstensi VS Code Wajib untuk Programmer Pemula",
                category: "Tips & Trik",
                date: "20 Agu 2026",
                excerpt: "Tingkatkan produktivitas koding Anda dengan rekomendasi ekstensi Visual Studio Code pilihan berikut ini.",
                content: `<p>Visual Studio Code (VS Code) adalah editor teks paling populer saat ini di kalangan pengembang perangkat lunak, mulai dari pelajar yang baru menulis baris HTML pertama hingga insinyur senior di perusahaan teknologi besar. Salah satu alasan utamanya adalah ekosistem ekstensinya yang sangat kaya. Tanpa ekstensi, VS Code hanyalah ruang tulis yang ringan dan polos; namun dengan tambahan beberapa ekstensi yang tepat, ia bertransformasi menjadi ruang kerja cerdas yang mampu mendampingi setiap langkah kita. Jika Anda baru mulai belajar koding, ada beberapa ekstensi yang wajib Anda pasang sejak hari pertama, sebab penggunaan alat yang tepat bisa menghemat waktu Anda hingga berjam-jam saat melakukan debugging.</p>
<p>Ekstensi pertama yang hampir selalu menjadi rekomendasi utama adalah Prettier. Bagi pemula, menjaga kerapian kode sering kali terasa seperti beban tambahan di tengah upaya keras memahami logika pemrograman. Spasi yang tidak rata, indentasi yang berantakan, atau tanda kutip yang tidak konsisten mungkin terlihat sepele, tetapi kode yang kusut sangat menyulitkan mata saat kita harus mencari kesalahan. Prettier menyelesaikan masalah ini secara otomatis. Begitu Anda menekan tombol simpan, seluruh baris kode akan dirapikan seketika mengikuti standar industri. Selain membuat proyek tampak profesional, kebiasaan melihat kode yang rapi sejak awal akan melatih kepekaan visual Anda terhadap struktur program.</p>
<p>Setelah kode terasa rapi, tantangan berikutnya yang kerap memutus konsentrasi pemula adalah proses bolak-balik antara editor dan peramban web. Di sinilah Live Server memainkan peran penting. Tanpa ekstensi ini, setiap kali Anda mengubah warna tombol atau menggeser posisi teks di file HTML dan CSS, Anda harus menyimpan file, berpindah jendela ke browser, lalu menekan tombol muat ulang berulang kali. Live Server memangkas kerumitan itu dengan menjalankan server lokal sederhana yang memperbarui tampilan browser secara real-time begitu kode disimpan. Perubahan kecil ini terasa sangat membebaskan karena menjaga alur belajar dan eksperimen tetap mengalir tanpa jeda yang menjengkelkan.</p>
<p>Masalah klasik lain yang hampir pasti dialami setiap programmer pemula adalah kehilangan jejak tanda kurung, terutama saat kode mulai bertingkat dan panjang. Untuk itulah Bracket Pair Colorizer menjadi sangat populer dan begitu membantu dalam melacak kurung kurawal atau tanda kurung biasa yang hilang. Dengan memberikan warna yang berbeda dan serasi pada setiap pasangan kurung pembuka dan penutup, mata kita bisa langsung menangkap di mana sebuah blok kode dimulai dan di mana ia berakhir. Saking pentingnya fungsi pewarnaan pasangan kurung ini, VS Code bahkan kini sudah mengadopsi fitur serupa secara bawaan, namun konsep yang dipopulerkan ekstensi ini tetap menjadi penyelamat utama agar kita tidak lagi menghabiskan satu jam penuh hanya untuk mencari satu tanda kurung yang tertinggal.</p>
<p>Melengkapi ketiga alat tersebut, ekstensi keempat yang sangat meringankan pekerjaan di wilayah pengembangan web adalah Auto Rename Tag. Saat membangun kerangka halaman dengan HTML, kita sering kali perlu mengubah sebuah elemen—misalnya mengganti tag pembuka &lt;div&gt; menjadi &lt;section&gt; atau mengubah &lt;h1&gt; menjadi &lt;h2&gt;. Dalam struktur halaman yang panjang, sangat mudah bagi kita untuk lupa mengubah tag penutupnya di bagian bawah, yang akhirnya merusak tata letak halaman secara diam-diam. Auto Rename Tag bekerja dalam senyap: begitu Anda menyunting tag pembuka, tag pasangannya akan ikut berubah secara otomatis. Ini adalah detail kecil yang mencegah banyak kesalahan tidak perlu.</p>
<p>Sebagai penutup dari lima daftar wajib ini, ada Error Lens yang berfungsi layaknya mentor sabar di samping layar Anda. Secara bawaan, VS Code hanya menandai kesalahan penulisan dengan garis bawah bergelombang tipis berwarna merah atau kuning yang terkadang luput dari perhatian pemula. Error Lens mengangkat pesan kesalahan tersebut dan menampilkannya langsung di samping baris kode yang bermasalah secara mencolok. Dengan begitu, Anda tidak perlu menunggu sampai program dijalankan dan mengalami crash hanya untuk menyadari adanya salah ketik pada nama variabel atau titik koma yang terlupa. Anda langsung tahu apa yang salah pada detik itu juga.</p>
<p>Pada akhirnya, memasang ekstensi di VS Code bukanlah soal membuat editor terlihat penuh dan canggih, melainkan soal menyingkirkan hambatan teknis yang sepele agar energi pikiran kita bisa tercurah sepenuhnya pada pemecahan masalah dan logika. Tentu saja, godaan untuk memasang puluhan ekstensi sekaligus selalu ada, tetapi terlalu banyak alat justru bisa membuat editor menjadi berat dan membingungkan. Mulailah dengan lima ekstensi mendasar ini, rasakan bagaimana mereka mengubah ritme kerja Anda menjadi lebih tenang dan teratur, lalu biarkan fokus Anda kembali pada hal yang paling penting: terus menulis kode dan membangun karya.</p>`,
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
            modal.scrollTop = 0;
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
        if (modalBackdrop) modalBackdrop.addEventListener('click', closeModal);
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
