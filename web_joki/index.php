<?php 
session_start(); 
require_once 'config/koneksi.php'; // Pastiin file koneksi lu bener namanya

// LOGIKA 1: Nangkep Submit Rating (Khusus Pembeli yang Ordernya SELESAI)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_rating'])) {
    $kode_order = mysqli_real_escape_string($conn, $_POST['kode_order']);
    $star_rate  = (int)$_POST['rating'];
    $msg_rate   = mysqli_real_escape_string($conn, $_POST['rate_pesan']);

    // Cek kode order sekaligus statusnya
    $cek_order = mysqli_query($conn, "SELECT nickname, status FROM orders WHERE kode_order = '$kode_order'");
    if ($cek_order && mysqli_num_rows($cek_order) > 0) {
        $data_order = mysqli_fetch_assoc($cek_order);
        
        // Validasi: Cuma yang statusnya 'Selesai' yang boleh rate
        if ($data_order['status'] == 'Selesai') {
            $nick_rate = $data_order['nickname'];

            if($star_rate > 0) {
                $query_rate = "INSERT INTO ratings (nickname, bintang, pesan) VALUES ('$nick_rate', $star_rate, '$msg_rate')";
                if(mysqli_query($conn, $query_rate)) {
                    $_SESSION['rate_success'] = "Ulasan berhasil dikirim. Terima kasih!";
                    header("Location: index.php#rating-section");
                    exit();
                }
            }
        } else {
            $_SESSION['rate_error'] = "Order belum selesai! Rating hanya bisa diisi jika pesanan sudah berstatus Selesai.";
            header("Location: index.php#rating-section");
            exit();
        }
    } else {
        $_SESSION['rate_error'] = "Kode Order tidak ditemukan! Cek kembali kode pesananmu.";
        header("Location: index.php#rating-section");
        exit();
    }
}

// LOGIKA 2: Ambil 3 Testimoni Terbaru dari Database
$get_rates = mysqli_query($conn, "SELECT * FROM ratings ORDER BY id DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erzyy Joki | Premium MLBB Joki & Mabar VIP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], },
                    colors: {
                        base: '#0b0c10', // Dark soft grey/black
                        surface: '#12141a', // Lighter grey
                        surfaceHover: '#1f222b',
                        accent: '#4f46e5', // Indigo
                        vibrant: '#8b5cf6', // Soft purple
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0b0c10; color: #9ca3af; overflow-x: hidden; }
        
        /* Web 3.0 Glassmorphism */
        .glass-nav { background: rgba(11, 12, 16, 0.7); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border-bottom: 1px solid rgba(255, 255, 255, 0.03); }
        .glass-card { background: linear-gradient(180deg, rgba(30, 32, 41, 0.6) 0%, rgba(18, 20, 26, 0.6) 100%); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4); }
        
        /* Modal Glassmorphism ala Takapedia */
        .glass-modal { background: rgba(18, 20, 26, 0.9); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8); }

        /* Vibrant Text & Buttons */
        .text-gradient { background: linear-gradient(135deg, #8b5cf6, #4f46e5); -webkit-background-clip: text; color: transparent; }
        .btn-gradient { background: linear-gradient(135deg, #4f46e5, #8b5cf6); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.5); }
        
        /* Forms */
        input, select, textarea { background: rgba(255,255,255,0.03) !important; border: 1px solid rgba(255,255,255,0.08) !important; color: #f3f4f6 !important; transition: 0.3s; }
        input:focus, select:focus, textarea:focus { border-color: #8b5cf6 !important; outline: none; box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1); background: rgba(255,255,255,0.05) !important; }
        input::placeholder, textarea::placeholder { color: #4b5563; }
        option { background-color: #12141a !important; color: #f3f4f6 !important; font-weight: 500; }
        
        /* Payment & Service Radio Style */
        .payment-radio:checked + div { border-color: #8b5cf6; background: rgba(139, 92, 246, 0.1); }
        .service-radio:checked + div { border-color: #8b5cf6; background: rgba(139, 92, 246, 0.1); box-shadow: 0 0 20px rgba(139, 92, 246, 0.3); transform: scale(1.03); transition: all 0.3s ease; }

        /* Rating Star Style */
        .rating-input { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 4px; }
        .rating-input input { display: none; }
        .rating-input label { cursor: pointer; color: #333344; font-size: 24px; transition: 0.2s; }
        .rating-input label:hover, .rating-input label:hover ~ label, .rating-input input:checked ~ label { color: #facc15; }

        /* Modal logic */
        .modal { transition: opacity 0.3s ease, visibility 0.3s ease; opacity: 0; visibility: hidden; }
        .modal.open { opacity: 1; visibility: visible; }
        .reveal {
    opacity: 0;
    transform: translateY(36px);
    transition: opacity 0.65s cubic-bezier(.4,0,.2,1), transform 0.65s cubic-bezier(.4,0,.2,1);
}
.reveal.visible {
    opacity: 1;
    transform: translateY(0);
}
/* Stagger delay untuk children (dipakai di grid cards) */
.reveal-stagger > *:nth-child(1) { transition-delay: 0s; }
.reveal-stagger > *:nth-child(2) { transition-delay: 0.10s; }
.reveal-stagger > *:nth-child(3) { transition-delay: 0.20s; }
.reveal-stagger > *:nth-child(4) { transition-delay: 0.30s; }
 
/* ============================================================
   2. SCROLL PROGRESS BAR — bar tipis di atas halaman
   ============================================================ */
#scroll-progress-bar {
    position: fixed;
    top: 0; left: 0;
    height: 3px;
    width: 0%;
    background: linear-gradient(90deg, #4f46e5, #8b5cf6, #4f46e5);
    background-size: 200% 100%;
    animation: shimmer-bar 2s linear infinite;
    z-index: 9999;
    transition: width 0.1s linear;
    box-shadow: 0 0 10px rgba(139, 92, 246, 0.6);
}
@keyframes shimmer-bar {
    0%   { background-position: 0% 0; }
    100% { background-position: 200% 0; }
}
 
/* ============================================================
   3. HERO SECTION — Animated gradient orbs di background
   ============================================================ */
.hero-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(90px);
    pointer-events: none;
    animation: orb-float 8s ease-in-out infinite alternate;
}
.hero-orb-1 {
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(79,70,229,0.18), transparent 70%);
    top: -150px; left: -100px;
    animation-delay: 0s;
}
.hero-orb-2 {
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(139,92,246,0.14), transparent 70%);
    top: 0; right: -80px;
    animation-delay: 2s;
}
.hero-orb-3 {
    width: 320px; height: 320px;
    background: radial-gradient(circle, rgba(79,70,229,0.1), transparent 70%);
    bottom: -80px; left: 35%;
    animation-delay: 4s;
}
@keyframes orb-float {
    from { transform: translate(0, 0) scale(1); }
    to   { transform: translate(18px, -22px) scale(1.06); }
}
 
/* ============================================================
   4. HERO TEXT — Typing cursor blink
   ============================================================ */
.typing-cursor {
    display: inline-block;
    width: 3px;
    height: 1em;
    background: #8b5cf6;
    margin-left: 3px;
    vertical-align: middle;
    border-radius: 2px;
    animation: cursor-blink 1s step-end infinite;
}
@keyframes cursor-blink { 50% { opacity: 0; } }
 
/* ============================================================
   5. FLOATING BADGE — Pulse dot pada badge "Premium MLBB Joki"
   ============================================================ */
.pulse-dot {
    display: inline-block;
    width: 7px; height: 7px;
    border-radius: 50%;
    background: #f97316;
    margin-right: 4px;
    animation: pulse-dot 2s ease-in-out infinite;
}
@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: 0.5; transform: scale(1.5); }
}
 
/* ============================================================
   6. FEATURE CARDS — Hover glow lift
   ============================================================ */
.hover-glow {
    transition: transform 0.3s cubic-bezier(.4,0,.2,1),
                box-shadow 0.3s cubic-bezier(.4,0,.2,1),
                border-color 0.3s ease !important;
}
.hover-glow:hover {
    transform: translateY(-6px) !important;
    box-shadow: 0 20px 48px -8px rgba(139, 92, 246, 0.2) !important;
    border-color: rgba(139, 92, 246, 0.35) !important;
}
 
/* ============================================================
   7. ANIMATED COUNTER (Stats section)
   ============================================================ */
.counter-animate {
    font-variant-numeric: tabular-nums;
    transition: color 0.3s;
}
 
/* ============================================================
   8. FLOATING ACTION BUTTON — WhatsApp di pojok kanan bawah
   ============================================================ */
#wa-fab {
    position: fixed;
    bottom: 28px; right: 24px;
    z-index: 500;
    width: 56px; height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 24px rgba(34, 197, 94, 0.4);
    transition: transform 0.3s cubic-bezier(.4,0,.2,1), box-shadow 0.3s;
    text-decoration: none;
    animation: fab-bounce 3s ease-in-out infinite;
}
#wa-fab:hover {
    transform: scale(1.12) translateY(-3px);
    box-shadow: 0 16px 36px rgba(34, 197, 94, 0.5);
    animation: none;
}
#wa-fab i { font-size: 1.5rem; color: #fff; }
#wa-fab .fab-tooltip {
    position: absolute;
    right: 68px;
    background: #1f2937;
    color: #fff;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 8px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s;
    border: 1px solid rgba(255,255,255,0.08);
}
#wa-fab:hover .fab-tooltip { opacity: 1; }
@keyframes fab-bounce {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-6px); }
}
 
/* ============================================================
   9. RATING CARDS — Stagger fade-in saat masuk viewport
   ============================================================ */
.rating-card-anim {
    opacity: 0;
    transform: translateY(24px) scale(0.97);
    transition: opacity 0.55s ease, transform 0.55s cubic-bezier(.4,0,.2,1);
}
.rating-card-anim.visible {
    opacity: 1;
    transform: translateY(0) scale(1);
}
 
/* ============================================================
   10. PRICE DISPLAY — Pulse saat angka berubah
   ============================================================ */
@keyframes price-pop {
    0%   { transform: scale(1); }
    40%  { transform: scale(1.08); color: #8b5cf6; }
    100% { transform: scale(1); }
}
.price-pop { animation: price-pop 0.35s cubic-bezier(.4,0,.2,1); }
 
/* ============================================================
   11. SECTION HEADER — Slide in dari bawah
   ============================================================ */
.section-reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.6s ease 0.05s, transform 0.6s cubic-bezier(.4,0,.2,1) 0.05s;
}
.section-reveal.visible { opacity: 1; transform: translateY(0); }
 
/* ============================================================
   12. NAV — Active link indicator
   ============================================================ */
.nav-link-active { color: #fff !important; }
.nav-link-active::after {
    content: '';
    display: block;
    height: 2px;
    background: linear-gradient(90deg, #4f46e5, #8b5cf6);
    border-radius: 2px;
    margin-top: 2px;
    animation: nav-underline .3s ease forwards;
}
@keyframes nav-underline {
    from { transform: scaleX(0); }
    to   { transform: scaleX(1); }
}
    </style>
</head>
<body class="antialiased selection:bg-accent selection:text-white">

    <nav class="fixed w-full z-50 glass-nav transition-all duration-300 py-4">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            <a href="#" class="text-2xl font-extrabold tracking-tight text-white flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-vibrant to-accent flex items-center justify-center shadow-lg shadow-vibrant/20">
                    <i class="fas fa-bolt text-sm text-white"></i>
                </div>
                Erzyy Boost
            </a>
            <div class="hidden md:flex items-center gap-8 text-sm font-medium">
                <a href="#order" class="text-gray-400 hover:text-white transition">Order</a>
                <a href="tracking.php" class="text-gray-400 hover:text-white transition">Cek Transaksi</a>
                <a href="javascript:void(0)" onclick="openModal('help')" class="text-gray-400 hover:text-white transition">Help</a>
                
                <?php if(!isset($_SESSION['admin_banned'])): ?>
                <a href="admin/login.php" class="px-5 py-2 rounded-full border border-gray-700 hover:border-vibrant hover:text-white transition bg-white/5">Login Admin</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

<section class="relative pt-32 pb-20 px-6 min-h-[80vh] flex items-center overflow-hidden">
        <div class="absolute inset-0 z-0 opacity-30 bg-cover bg-right bg-no-repeat" style="background-image: url('img/yss.png');"></div>
        <div class="absolute inset-0 z-10 bg-gradient-to-r from-base via-base/90 to-transparent pointer-events-none"></div>
        <div class="absolute inset-0 z-10 bg-gradient-to-t from-base via-transparent to-transparent pointer-events-none"></div>
        
        <div class="max-w-7xl mx-auto w-full relative z-20 flex flex-col items-start text-left mt-10">
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-vibrant/10 border border-vibrant/20 text-vibrant text-xs font-bold tracking-widest uppercase mb-6 backdrop-blur-sm">
                <i class="fas fa-fire text-orange-500"></i> Premium MLBB Joki
            </div>
            
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-black text-white mb-6 leading-[1.1] tracking-tight">
                Tinggalkan <span class="text-gray-600 line-through decoration-red-500/70">Lose Streak.</span><br>
                Raih <span class="text-gradient">Mythical Immortal</span><br>Sekarang.
            </h1>
            
            <p class="text-base md:text-lg text-gray-400 mb-10 max-w-xl font-medium leading-relaxed">
                Sistem joki high-end dengan keamanan enkripsi lapis ganda. Dikerjakan oleh Pro Player terverifikasi, bukan penjoki amatir.
            </p>
            
            <div class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
                <a href="#order" class="w-full sm:w-auto btn-gradient text-white px-8 py-4 rounded-xl font-bold text-base flex items-center justify-center gap-3 shadow-lg shadow-accent/20 hover:scale-[1.02] transition-transform">
                    <i class="fas fa-rocket"></i> Mulai Boosting
                </a>
                <a href="#rating-section" class="w-full sm:w-auto px-8 py-4 rounded-xl font-bold text-base text-white bg-white/5 border border-white/10 hover:bg-white/10 transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-star text-yellow-500"></i> Cek Ulasan
                </a>
            </div>
        </div>
    </section>

    <section class="py-20 px-6 max-w-7xl mx-auto">
        <div class="grid md:grid-cols-2 gap-6 mb-16">
            <div class="glass-card p-10 rounded-3xl opacity-60 transition hover:opacity-100 hover:border-red-500/20">
                <div class="w-14 h-14 rounded-2xl bg-red-500/10 flex items-center justify-center text-red-500 mb-6 text-2xl"><i class="fas fa-skull"></i></div>
                <h3 class="text-2xl font-bold text-white mb-4">Solo Player</h3>
                <p class="text-gray-400 leading-relaxed">Ketemu tim troll, AFK, waktu terbuang berjam-jam, mental terkuras, dan rank stuck atau malah turun drastis.</p>
            </div>
            <div class="glass-card p-10 rounded-3xl relative overflow-hidden border border-vibrant/30">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-vibrant/10 blur-3xl rounded-full"></div>
                <div class="w-14 h-14 rounded-2xl bg-vibrant/20 flex items-center justify-center text-vibrant mb-6 text-2xl"><i class="fas fa-crown float-3d text-gradient"></i></div>
                <h3 class="text-2xl font-bold text-white mb-4">Erzyy VIP</h3>
                <p class="text-gray-400 leading-relaxed">Fokus pada aktivitas real-life. Tim kami mengamankan winstreak dengan winrate 80%+. Tiba-tiba rank naik.</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="p-8 rounded-2xl bg-surface border border-white/5 hover:border-vibrant/50 transition duration-300">
                <i class="fas fa-user-ninja text-3xl text-gradient mb-5"></i>
                <h4 class="text-white font-semibold mb-2">Pro Booster</h4>
                <p class="text-sm text-gray-500">Mantan pro-scene & Top Global.</p>
            </div>
            <div class="p-8 rounded-2xl bg-surface border border-white/5 hover:border-vibrant/50 transition duration-300">
                <i class="fas fa-lock text-3xl text-gradient mb-5"></i>
                <h4 class="text-white font-semibold mb-2">Data Encrypted</h4>
                <p class="text-sm text-gray-500">Auto hapus data pasca selesai.</p>
            </div>
            <div class="p-8 rounded-2xl bg-surface border border-white/5 hover:border-vibrant/50 transition duration-300">
                <i class="fas fa-rocket text-3xl text-gradient mb-5"></i>
                <h4 class="text-white font-semibold mb-2">Proses Kilat</h4>
                <p class="text-sm text-gray-500">Selesai dalam hitungan jam.</p>
            </div>
            <div class="p-8 rounded-2xl bg-surface border border-white/5 hover:border-vibrant/50 transition duration-300">
                <i class="fas fa-headset text-3xl text-gradient mb-5"></i>
                <h4 class="text-white font-semibold mb-2">24/7 Support</h4>
                <p class="text-sm text-gray-500">Pantau progres real-time.</p>
            </div>
        </div>
    </section>

    <section id="order" class="py-24 px-6 max-w-5xl mx-auto relative">
        <div class="text-center mb-16 relative z-10">
            <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4">Pesan <span class="text-gradient">Boosting</span></h2>
            <p class="text-gray-400">Pilih target, isi data valid, dan biarkan sistem menghitung estimasi otomatis.</p>
        </div>

        <form action="order.php" method="POST" id="orderForm" class="glass-card rounded-[2.5rem] p-8 md:p-12 relative z-10">
            
            <div class="mb-12">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-surface border border-white/10 flex items-center justify-center text-white font-bold">1</div>
                    <h3 class="text-2xl font-bold text-white">Informasi Akun</h3>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-8">
                    <label class="cursor-pointer">
                        <input type="radio" name="tipe_layanan" value="Joki Piloting" class="service-radio sr-only" checked onchange="toggleLayanan()">
                        <div class="p-4 rounded-2xl border border-white/10 text-center transition bg-surface/50">
                            <span class="block text-white font-bold mb-1">Joki Piloting</span>
                            <span class="text-xs text-gray-500">Terima Beres</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="tipe_layanan" value="Mabar VIP" class="service-radio sr-only" onchange="toggleLayanan()">
                        <div class="p-4 rounded-2xl border border-white/10 text-center transition bg-surface/50">
                            <span class="block text-vibrant font-bold mb-1">Mabar VIP</span>
                            <span class="text-xs text-gray-500">Main Bareng Pro</span>
                        </div>
                    </label>
                </div>
                
                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Nickname MLBB</label>
                        <input type="text" name="nickname" placeholder="Masukkan nickname in-game" required class="w-full px-5 py-4 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">ID & Server</label>
                        <input type="text" name="id_server" placeholder="12345678 (1234)" required class="w-full px-5 py-4 rounded-xl">
                    </div>
                </div>

                <div id="privasiAkun" class="grid md:grid-cols-3 gap-6 transition-all duration-300">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Login Via</label>
                        <select name="login_via" required class="w-full px-5 py-4 rounded-xl appearance-none">
                            <option value="" disabled selected>Pilih Metode Login</option>
                            <option value="Moonton">Moonton</option>
                            <option value="Google">Google</option>
                            <option value="VK">VK</option>
                            <option value="TikTok">TikTok</option>
                            <option value="Facebook">Facebook</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Email / No HP</label>
                        <input type="text" name="email" placeholder="Email terkait" required class="w-full px-5 py-4 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Password Akun</label>
                        <input type="password" name="password_akun" placeholder="••••••••" required class="w-full px-5 py-4 rounded-xl">
                    </div>
                </div>
            </div>

            <hr class="border-white/5 mb-12">

            <div class="mb-12">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-surface border border-white/10 flex items-center justify-center text-white font-bold">2</div>
                    <h3 class="text-2xl font-bold text-white">Target Rank</h3>
                </div>
                
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="p-6 rounded-2xl bg-surface/50 border border-white/5 relative">
                        <label class="block text-sm font-bold text-white mb-3">Rank Saat Ini</label>
                        <select id="rankAwal" name="rank_awal" required class="w-full px-5 py-4 rounded-xl mb-5" onchange="hitungHarga()">
                            <option value="1" data-price="2000">Master</option>
                            <option value="2" data-price="3000">Grandmaster</option>
                            <option value="3" data-price="4500">Epic</option>
                            <option value="4" data-price="6000" selected>Legend</option>
                            <option value="5" data-price="9000">Mythic</option>
                            <option value="6" data-price="12000">Mythical Honor</option>
                            <option value="7" data-price="15000">Mythical Glory</option>
                            <option value="8" data-price="25000">Immortal</option>
                        </select>
                        <label class="block text-xs text-gray-500 mb-2">Bintang / Poin Awal</label>
                        <input type="number" id="bintangAwal" name="bintang_awal" min="0" value="0" required class="w-full px-5 py-4 rounded-xl" onchange="hitungHarga()" onkeyup="hitungHarga()">
                    </div>

                    <div class="p-6 rounded-2xl bg-surface/50 border border-vibrant/30 shadow-[0_0_30px_rgba(139,92,246,0.05)] relative">
                        <label class="block text-sm font-bold text-white mb-3">Target Pencapaian</label>
                        <select id="rankTujuan" name="rank_tujuan" required class="w-full px-5 py-4 rounded-xl mb-5" onchange="hitungHarga()">
                            <option value="1" data-price="2000">Master</option>
                            <option value="2" data-price="3000">Grandmaster</option>
                            <option value="3" data-price="4500">Epic</option>
                            <option value="4" data-price="6000">Legend</option>
                            <option value="5" data-price="9000" selected>Mythic</option>
                            <option value="6" data-price="12000">Mythical Honor</option>
                            <option value="7" data-price="15000">Mythical Glory</option>
                            <option value="8" data-price="25000">Immortal</option>
                        </select>
                        <label class="block text-xs text-gray-500 mb-2">Bintang / Poin Tujuan</label>
                        <input type="number" id="bintangTujuan" name="bintang_tujuan" min="1" value="15" required class="w-full px-5 py-4 rounded-xl" onchange="hitungHarga()" onkeyup="hitungHarga()">
                    </div>
                </div>

                <div class="mt-8">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Catatan Khusus (Req Hero, Jadwal Login, dll)</label>
                    <textarea name="catatan" rows="3" placeholder="Tolong pick assasin fanny, jadwal push jam 12 malam keatas" class="w-full px-5 py-4 rounded-xl"></textarea>
                </div>
            </div>

            <hr class="border-white/5 mb-12">

            <div class="mb-12">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-10 h-10 rounded-xl bg-surface border border-white/10 flex items-center justify-center text-white font-bold">3</div>
                    <h3 class="text-2xl font-bold text-white">Metode Pembayaran</h3>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <label class="cursor-pointer">
                        <input type="radio" name="payment" value="QRIS" class="payment-radio sr-only" required>
                        <div class="p-5 rounded-xl border border-white/10 text-center transition bg-surface/50 hover:bg-surface">
                            <span class="block text-white font-bold mb-1">QRIS</span>
                            <span class="text-xs text-gray-500">Scan All E-Wallet</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="payment" value="BCA" class="payment-radio sr-only" required>
                        <div class="p-5 rounded-xl border border-white/10 text-center transition bg-surface/50 hover:bg-surface">
                            <span class="block text-white font-bold mb-1">BCA</span>
                            <span class="text-xs text-gray-500">Transfer Bank</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="payment" value="DANA" class="payment-radio sr-only" required>
                        <div class="p-5 rounded-xl border border-white/10 text-center transition bg-surface/50 hover:bg-surface">
                            <span class="block text-white font-bold mb-1">DANA</span>
                            <span class="text-xs text-gray-500">E-Wallet</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="payment" value="GOPAY" class="payment-radio sr-only" required>
                        <div class="p-5 rounded-xl border border-white/10 text-center transition bg-surface/50 hover:bg-surface">
                            <span class="block text-white font-bold mb-1">GoPay</span>
                            <span class="text-xs text-gray-500">E-Wallet</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="p-8 rounded-2xl bg-surface border border-white/5 flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <span class="text-sm text-gray-500 font-medium block mb-1">Estimasi Total Harga</span>
                    <h2 class="text-4xl font-extrabold text-white tracking-tight" id="displayHarga">Rp 0</h2>
                    <input type="hidden" name="harga_total" id="inputHargaHidden" value="0">
                    <p class="text-sm text-red-500 mt-2 hidden" id="errorHarga"><i class="fas fa-exclamation-circle mr-1"></i> Target rank/bintang tidak valid!</p>
                </div>
                <button type="button" onclick="konfirmasiOrder()" class="w-full md:w-auto btn-gradient text-white px-12 py-5 rounded-xl font-bold text-lg">Checkout Order</button>
            </div>
        </form>
    </section>

    <section id="rating-section" class="py-24 px-6 max-w-7xl mx-auto border-t border-white/5">
        <h2 class="text-3xl md:text-4xl font-extrabold text-center text-white mb-4 tracking-tight">Trusted by <span class="text-gradient">Champions</span></h2>
        <p class="text-center text-gray-400 mb-16 font-light">Ulasan asli dari para pelanggan Erzyy Boost.</p>

        <div class="grid md:grid-cols-3 gap-6 mb-16">
            <?php 
            if($get_rates && mysqli_num_rows($get_rates) > 0) {
                while($rate = mysqli_fetch_assoc($get_rates)) {
            ?>
            <div class="glass-card p-8 rounded-2xl">
                <div class="flex text-vibrant text-sm mb-4 gap-1">
                    <?php 
                    for($i=1; $i<=5; $i++) {
                        if($i <= $rate['bintang']) { echo '<i class="fas fa-star text-yellow-400"></i>'; }
                        else { echo '<i class="fas fa-star text-gray-700"></i>'; }
                    }
                    ?>
                </div>
                <p class="text-gray-400 text-sm mb-6 leading-relaxed">"<?php echo htmlspecialchars($rate['pesan']); ?>"</p>
                <div class="flex items-center gap-3 border-t border-white/5 pt-4">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-surface to-base border border-white/5 flex items-center justify-center text-vibrant text-xs font-bold uppercase">
                        <?php echo substr($rate['nickname'], 0, 2); ?>
                    </div>
                    <div>
                        <h5 class="text-white font-semibold text-sm"><?php echo htmlspecialchars($rate['nickname']); ?></h5>
                        <span class="text-xs text-gray-600"><?php echo date('d M Y', strtotime($rate['tanggal'])); ?></span>
                    </div>
                </div>
            </div>
            <?php } } else { echo '<p class="text-center text-gray-600 col-span-3">Belum ada penilaian. Jadilah yang pertama!</p>'; } ?>
        </div>

        <div class="max-w-3xl mx-auto glass-card rounded-3xl p-10 md:p-12 border border-vibrant/20 relative overflow-hidden transition hover:border-vibrant/50">
            <div class="absolute inset-0 bg-gradient-to-r from-accent/10 to-vibrant/10 opacity-50"></div>
            
            <h3 class="text-2xl font-extrabold text-white mb-2 relative z-10 text-center tracking-tight">Beri Penilaian Layanan Kami</h3>
            <p class="text-gray-400 mb-8 relative z-10 font-light text-center text-sm">Hanya pelanggan dengan <strong class="text-white">Kode Order</strong> valid yang dapat memberikan ulasan.</p>
            
            <form action="index.php" method="POST" class="space-y-6 relative z-10 text-left">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 text-center">Kode Order Pembelian</label>
                    <input type="text" name="kode_order" placeholder="Contoh: ERZ-XXXXX" required class="w-full px-5 py-4 rounded-xl text-center font-mono tracking-wider text-white">
                </div>
                
                <div class="bg-black/20 p-5 rounded-xl flex flex-col items-center gap-3 border border-white/5">
                    <label class="text-xs text-gray-400 uppercase font-bold tracking-wider">Pilih Rating Bintang</label>
                    <div class="rating-input">
                        <input type="radio" name="rating" id="star5" value="5" required><label for="star5"><i class="fas fa-star"></i></label>
                        <input type="radio" name="rating" id="star4" value="4"><label for="star4"><i class="fas fa-star"></i></label>
                        <input type="radio" name="rating" id="star3" value="3"><label for="star3"><i class="fas fa-star"></i></label>
                        <input type="radio" name="rating" id="star2" value="2"><label for="star2"><i class="fas fa-star"></i></label>
                        <input type="radio" name="rating" id="star1" value="1"><label for="star1"><i class="fas fa-star"></i></label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 text-center">Pesan / Kesaksian</label>
                    <textarea name="rate_pesan" rows="3" placeholder="Gimana hasil jokinya bro? Tulis di sini..." required class="w-full px-5 py-4 rounded-xl text-white"></textarea>
                </div>
                
                <button type="submit" name="submit_rating" class="w-full btn-gradient text-white py-4 rounded-xl font-bold text-lg shadow-lg">Kirim Penilaian</button>
            </form>
        </div>
    </section>

    <footer class="border-t border-white/5 pt-20 pb-10 px-6 bg-[#08080b]">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-start gap-10">
            <div class="md:w-1/3">
                <div class="text-2xl font-extrabold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-bolt text-vibrant"></i> Erzyy_Boost
                </div>
                <p class="text-sm text-gray-500 mb-6 leading-relaxed max-w-xs">Platform Jasa Boosting Rank Mobile Legends dengan sistem keamanan terenkripsi dan garansi winrate tinggi.</p>
                <div class="flex gap-3">
                    <a href="https://tiktok.com/@ersyersy_" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-xl bg-surface border border-white/5 flex items-center justify-center text-gray-500 hover:text-white hover:border-vibrant transition hover:bg-white/5"><i class="fab fa-tiktok"></i></a>
                    <a href="https://instagram.com/erzyy_vessalius" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-xl bg-surface border border-white/5 flex items-center justify-center text-gray-500 hover:text-white hover:border-vibrant transition hover:bg-white/5"><i class="fab fa-instagram"></i></a>
                    <a href="https://api.whatsapp.com/send?phone=6281540068499" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-xl bg-surface border border-white/5 flex items-center justify-center text-gray-500 hover:text-white hover:border-vibrant transition hover:bg-white/5"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-10 md:w-1/3">
                <div>
                    <h4 class="text-white font-bold mb-5 text-sm uppercase tracking-wider">Informasi</h4>
                    <ul class="space-y-3 text-sm text-gray-500">
                        <li><a href="javascript:void(0)" onclick="openModal('help_center')" class="hover:text-vibrant transition">Help Center</a></li>
                        <li><a href="javascript:void(0)" onclick="openModal('cara_order')" class="hover:text-vibrant transition">Cara Order</a></li>
                        <li><a href="javascript:void(0)" onclick="openModal('daftar_harga')" class="hover:text-vibrant transition">Daftar Harga</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-5 text-sm uppercase tracking-wider">Legal</h4>
                    <ul class="space-y-3 text-sm text-gray-500">
                        <li><a href="javascript:void(0)" onclick="openModal('legalitas')" class="hover:text-vibrant transition">Legalitas</a></li>
                        <li><a href="javascript:void(0)" onclick="openModal('syarat_pribadi')" class="hover:text-vibrant transition">Syarat Pribadi</a></li>
                        <li><a href="javascript:void(0)" onclick="openModal('ketentuan_layanan')" class="hover:text-vibrant transition">Ketentuan Layanan</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="max-w-7xl mx-auto mt-16 pt-8 border-t border-white/5 text-center text-xs text-gray-600 font-medium tracking-wide">
            <p>&copy; 2026 Erzyy Boost. All rights reserved. Platform Joki Profesional.</p>
        </div>
    </footer>

    <div id="modal-container" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/70 modal">
        <div class="glass-modal rounded-2xl w-full max-w-lg p-8 relative transform scale-95 transition-transform duration-300" id="modal-content">
            <button onclick="closeModal()" class="absolute top-5 right-5 text-gray-600 hover:text-white transition"><i class="fas fa-times"></i></button>
            <div id="modal-body" class="text-gray-300 text-sm leading-relaxed space-y-4 max-h-[70vh] overflow-y-auto pr-2">
            </div>
        </div>
    </div>

    <script>
        // 1. Fungsi Toggle Mabar VIP
        function toggleLayanan() {
            const isMabar = document.querySelector('input[name="tipe_layanan"]:checked').value === 'Mabar VIP';
            const privasiBox = document.getElementById('privasiAkun');
            const inputs = privasiBox.querySelectorAll('input, select');
            
            if (isMabar) {
                privasiBox.style.display = 'none';
                inputs.forEach(input => input.removeAttribute('required'));
            } else {
                privasiBox.style.display = 'grid';
                inputs.forEach(input => input.setAttribute('required', 'true'));
            }
            hitungHarga();
        }

        // 2. Fungsi Hitung Harga
        function hitungHarga() {
            const selectAwal = document.getElementById('rankAwal');
            const selectTujuan = document.getElementById('rankTujuan');
            const bintangAwal = parseInt(document.getElementById('bintangAwal').value) || 0;
            const bintangTujuan = parseInt(document.getElementById('bintangTujuan').value) || 0;
            
            const valAwal = parseInt(selectAwal.value);
            const valTujuan = parseInt(selectTujuan.value);
            const hargaRate = parseInt(selectTujuan.options[selectTujuan.selectedIndex].getAttribute('data-price'));

            const errorText = document.getElementById('errorHarga');
            const display = document.getElementById('displayHarga');
            const hiddenInput = document.getElementById('inputHargaHidden');

            // Validasi: Tujuan harus lebih tinggi dari Awal
            if (valTujuan < valAwal || (valTujuan === valAwal && bintangTujuan <= bintangAwal)) {
                errorText.classList.remove('hidden');
                display.innerText = "Rp 0";
                hiddenInput.value = 0;
                return;
            }
            errorText.classList.add('hidden');

            let totalBintangDibutuhkan = 0;
            if (valAwal === valTujuan) {
                totalBintangDibutuhkan = bintangTujuan - bintangAwal;
            } else {
                totalBintangDibutuhkan = ((valTujuan - valAwal) * 5) + (bintangTujuan - bintangAwal);
            }

            if(totalBintangDibutuhkan < 1) totalBintangDibutuhkan = 1;

            let totalHarga = totalBintangDibutuhkan * hargaRate;
            
            // Tambahan harga untuk Mabar VIP (Naik 50%)
            const isMabar = document.querySelector('input[name="tipe_layanan"]:checked').value === 'Mabar VIP';
            if (isMabar) {
                totalHarga = totalHarga * 1.5;
            }

            display.innerText = "Rp " + totalHarga.toLocaleString('id-ID');
            hiddenInput.value = totalHarga; 
        }

        // 3. Fungsi Pop-up Konfirmasi Sebelum Beli
        function konfirmasiOrder() {
            const form = document.getElementById('orderForm');
            if(!form.checkValidity()) { form.reportValidity(); return; } // Cek form kalau ada yg kosong

            const nick = document.querySelector('input[name="nickname"]').value;
            const harga = document.getElementById('displayHarga').innerText;
            const tipe = document.querySelector('input[name="tipe_layanan"]:checked').value;

            Swal.fire({
                background: '#1c1c26', color: '#e0e0e6', title: 'Konfirmasi Order',
                html: `<div class="text-left text-sm space-y-2 mt-4 bg-black/20 p-4 rounded-xl border border-white/5">
                        <p><b>Layanan:</b> <span class="text-vibrant">${tipe}</span></p>
                        <p><b>Nickname:</b> ${nick}</p>
                        <p class="pt-2 border-t border-white/10 mt-2"><b>Total Tagihan:</b> <span class="text-xl font-bold text-white">${harga}</span></p>
                       </div>
                       <p class="mt-4 text-xs text-gray-400">Pastikan pesanan sudah benar sebelum lanjut ke pembayaran.</p>`,
                showCancelButton: true, confirmButtonColor: '#7a63eb', cancelButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Lanjut Bayar!', cancelButtonText: 'Batal',
                customClass: { popup: 'border border-white/5 rounded-4' }
            }).then((result) => {
                if (result.isConfirmed) { form.submit(); } // Kalo pencet Yes, form dikirim
            });
        }

        // 4. Modal System (Pop up Info)
        const modalContentData = {
            welcome: '<div class="text-center"><div class="w-20 h-20 bg-vibrant/20 text-vibrant rounded-full flex items-center justify-center mx-auto mb-6 text-4xl shadow-lg shadow-vibrant/20"><i class="fas fa-bullhorn"></i></div><h3 class="text-2xl font-bold text-white mb-2">Welcome to Erzyy Boost!</h3><p class="text-gray-400 mb-8 text-sm leading-relaxed">Pusat Joki Mobile Legends Terpercaya & Aman 100%. Pamerkan rank barumu sekarang juga!</p><button onclick="closeModal()" class="w-full btn-gradient text-white py-4 rounded-xl font-bold shadow-lg shadow-vibrant/20 hover:scale-[1.02] transition-all">Gas Order Sekarang</button></div>',
            help: '<h3 class="text-xl font-bold text-white mb-4">Butuh Bantuan?</h3><p>Halo Winners! Jika kamu mengalami kendala dalam pemesanan, pelacakan, atau pembayaran, jangan ragu untuk menghubungi Customer Service kami yang aktif 24/7.</p><a href="https://api.whatsapp.com/send?phone=6281540068499" target="_blank" class="inline-block bg-green-600 text-white px-5 py-2 rounded-lg font-bold mt-3 text-sm"><i class="fab fa-whatsapp mr-2"></i>Hubungi WA Admin</a>',
            help_center: '<h3 class="text-xl font-bold text-white mb-4">Help Center</h3><p>Pusat bantuan Erzyy Boost menyediakan jawaban atas pertanyaan umum (FAQ).</p><ul class="list-disc list-inside space-y-1"><li>Apakah joki aman? Aman 100%, sistem enkripsi.</li><li>Berapa lama proses? Est. 1-2 hari tergantung tier.</li><li>Bisa request hero? Bisa, tulis di catatan order.</li></ul>',
            cara_order: '<h3 class="text-xl font-bold text-white mb-4">Cara Order Joki</h3><ol class="list-decimal list-inside space-y-1"><li>Isi informasi akun MLBB kamu (Nick, ID, Login).</li><li>Pilih Rank saat ini dan Target Rank yang diinginkan.</li><li>Sistem otomatis menampilkan harga.</li><li>Pilih metode pembayaran (QRIS/Transfer).</li><li>Klik "Checkout Order".</li></ol>',
            daftar_harga: '<h3 class="text-xl font-bold text-white mb-4">Estimasi Daftar Harga (Per Bintang)</h3><ul class="space-y-1"><li><span class="font-bold text-white">Master:</span> Rp 2.000</li><li><span class="font-bold text-white">Grandmaster:</span> Rp 3.000</li><li><span class="font-bold text-white">Epic:</span> Rp 4.500</li><li><span class="font-bold text-white">Legend:</span> Rp 6.000</li><li><span class="font-bold text-white">Mythic + Honor:</span> Rp 9.000 - 12.000</li><li><span class="font-bold text-white">Glory + Immortal:</span> Rp 15.000 - 25.000</li></ul><p class="text-xs text-gray-600 mt-2">*Harga final mengikuti kalkulator di form order.</p>',
            legalitas: '<h3 class="text-xl font-bold text-white mb-4">Legalitas Perusahaan</h3><p>Erzyy Boost beroperasi di bawah naungan resmi sebagai penyedia layanan digital terdaftar.</p>',
            syarat_pribadi: '<h3 class="text-xl font-bold text-white mb-4">Kebijakan Privasi (Syarat Pribadi)</h3><p>Kami sangat menjaga privasi data kamu. Data login akun (email/password) hanya digunakan oleh penjoki profesional kami selama masa boosting.</p><p>Sistem kami akan <strong class="text-white">otomatis menghapus</strong> data login kamu dari database kami segera setelah status order dinyatakan <strong class="text-vibrant">Selesai</strong>.</p>',
            ketentuan_layanan: '<h3 class="text-xl font-bold text-white mb-4">Ketentuan Layanan (ToS)</h3><p>Dengan memesan di Erzyy Boost, kamu menyetujui:</p><ul class="list-disc list-inside space-y-1"><li>Pembayaran lunas di awal.</li><li>Tidak login ke akun selama jam joki berlangsung.</li><li>Garansi winrate jika akun tidak di-troll client selama joki.</li></ul>'
        };

        const modal = document.getElementById('modal-container');
        const modalBody = document.getElementById('modal-body');
        const modalContentElement = document.getElementById('modal-content');

        function openModal(key) {
            const content = modalContentData[key];
            if (content) {
                modalBody.innerHTML = content;
                modal.classList.add('open');
                setTimeout(() => {
                    modalContentElement.classList.remove('scale-95');
                    modalContentElement.classList.add('scale-100');
                }, 10);
                document.body.style.overflow = 'hidden'; 
            }
        }

        function closeModal() {
            modalContentElement.classList.remove('scale-100');
            modalContentElement.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.remove('open');
                document.body.style.overflow = ''; 
            }, 200);
        }

        modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
        document.addEventListener('keydown', function(e) { if (e.key === "Escape" && modal.classList.contains('open')) closeModal(); });

        // 5. Jalankan fungsi saat web pertama kali loading
        window.addEventListener('load', function() {
            toggleLayanan();
            hitungHarga();
            
            // Pop-up otomatis welcome (1x per browser)
            if (!sessionStorage.getItem('welcome_shown')) {
                setTimeout(function() {
                    openModal('welcome');
                }, 500); 
                sessionStorage.setItem('welcome_shown', 'true');
            }
        });
    </script>

    <?php if(isset($_SESSION['rate_success'])): ?>
    <script>
        window.addEventListener('load', function() {
            Swal.fire({
                background: '#1c1c26', color: '#e0e0e6', icon: 'success',
                title: 'Berhasil!', text: '<?php echo $_SESSION['rate_success']; ?>',
                confirmButtonColor: '#7a63eb', customClass: { popup: 'border border-white/5 rounded-4' }
            });
        });
    </script>
    <?php unset($_SESSION['rate_success']); endif; ?>

    <?php if(isset($_SESSION['rate_error'])): ?>
    <script>
        window.addEventListener('load', function() {
            Swal.fire({
                background: '#1c1c26', color: '#e0e0e6', icon: 'error',
                title: 'Gagal!', text: '<?php echo $_SESSION['rate_error']; ?>',
                confirmButtonColor: '#ef4444', customClass: { popup: 'border border-white/5 rounded-4' }
            });
        });
    </script>
    <?php unset($_SESSION['rate_error']); endif; ?>

    <?php if(isset($_SESSION['success'])): ?>
    <script>
        window.addEventListener('load', function() {
            Swal.fire({
                background: '#1c1c26', color: '#e0e0e6', icon: 'success',
                title: 'Pesanan Diterima!',
                html: `Ini adalah Kode Pesanan Anda:<br><br>
                       <div class="bg-black/50 p-4 rounded-xl border border-white/10 inline-block mb-2">
                           <b class="text-3xl text-vibrant tracking-widest select-all"><?php echo $_SESSION['success']; ?></b>
                       </div><br>
                       <p class="text-red-400 font-bold text-sm"><i class="fas fa-exclamation-triangle"></i> PENTING: Wajib Copy / Screenshot kode di atas!</p>
                       <p class="text-xs text-gray-400 mt-2">Kode ini digunakan untuk mengecek status pesanan dan memberikan rating nantinya.</p>`,
                confirmButtonText: 'Menuju Pembayaran <i class="fas fa-arrow-right ml-2"></i>',
                confirmButtonColor: '#7a63eb',
                allowOutsideClick: false,
                customClass: { popup: 'border border-white/5 rounded-4' }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tendang ke halaman tracking atau WA bayar
                    window.location.href = "tracking.php?kode=<?php echo $_SESSION['success']; ?>";
                }
            });
        });
    </script>
    <?php unset($_SESSION['success']); endif; ?>
    <script id="PASTE_BEFORE_CLOSING_BODY_TAG">
    (function() {
    'use strict';
 
    /* -------------------------------------------------------
       1. SCROLL PROGRESS BAR
    ------------------------------------------------------- */
    const progressBar = document.getElementById('scroll-progress-bar');
    if (progressBar) {
        window.addEventListener('scroll', function() {
            const total   = document.documentElement.scrollHeight - window.innerHeight;
            const current = window.scrollY;
            progressBar.style.width = ((current / total) * 100) + '%';
        }, { passive: true });
    }
 
    /* -------------------------------------------------------
       2. INTERSECTION OBSERVER — Scroll Reveal
    ------------------------------------------------------- */
    const revealObs = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                revealObs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });
 
    // Reveal semua elemen .reveal, .section-reveal, .rating-card-anim
    document.querySelectorAll('.reveal, .section-reveal, .rating-card-anim').forEach(function(el) {
        revealObs.observe(el);
    });
 
    // Auto-tambahkan .reveal ke section headers yang belum punya
    document.querySelectorAll('section > .text-center').forEach(function(el) {
        if (!el.classList.contains('reveal') && !el.classList.contains('section-reveal')) {
            el.classList.add('section-reveal');
            revealObs.observe(el);
        }
    });
 
    // Auto-tambahkan .reveal ke glass-card yang belum punya
    document.querySelectorAll('.glass-card').forEach(function(el, i) {
        if (!el.classList.contains('reveal') && !el.classList.contains('rating-card-anim')) {
            el.classList.add('rating-card-anim');
            el.style.transitionDelay = (i * 0.1) + 's';
            revealObs.observe(el);
        }
    });
 
    /* -------------------------------------------------------
       3. INJECT HERO ORBS ke hero section
    ------------------------------------------------------- */
    var heroSection = document.querySelector('section.relative.pt-32');
    if (heroSection) {
        ['hero-orb hero-orb-1', 'hero-orb hero-orb-2', 'hero-orb hero-orb-3'].forEach(function(cls) {
            var orb = document.createElement('div');
            orb.className = cls;
            heroSection.insertBefore(orb, heroSection.firstChild);
        });
    }
 
    /* -------------------------------------------------------
       4. INJECT SCROLL PROGRESS BAR ke body
    ------------------------------------------------------- */
    if (!document.getElementById('scroll-progress-bar')) {
        var bar = document.createElement('div');
        bar.id = 'scroll-progress-bar';
        document.body.insertBefore(bar, document.body.firstChild);
    }
 
    /* -------------------------------------------------------
       5. INJECT WHATSAPP FLOATING BUTTON
    ------------------------------------------------------- */
    if (!document.getElementById('wa-fab')) {
        var fab = document.createElement('a');
        fab.id = 'wa-fab';
        fab.href = 'https://api.whatsapp.com/send?phone=6281540068499';
        fab.target = '_blank';
        fab.rel = 'noopener noreferrer';
        fab.setAttribute('aria-label', 'Chat WhatsApp');
        fab.innerHTML = '<i class="fab fa-whatsapp"></i><span class="fab-tooltip">Chat Admin</span>';
        document.body.appendChild(fab);
    }
 
    /* -------------------------------------------------------
       6. ANIMATED PRICE — Pulse saat displayHarga berubah
    ------------------------------------------------------- */
    var displayHarga = document.getElementById('displayHarga');
    if (displayHarga) {
        var origHitungHarga = window.hitungHarga;
        if (typeof origHitungHarga === 'function') {
            window.hitungHarga = function() {
                origHitungHarga.apply(this, arguments);
                displayHarga.classList.remove('price-pop');
                // Trigger reflow supaya animasi restart
                void displayHarga.offsetWidth;
                displayHarga.classList.add('price-pop');
            };
        }
    }
 
    /* -------------------------------------------------------
       7. NAV ACTIVE LINK on scroll
    ------------------------------------------------------- */
    var sections  = document.querySelectorAll('section[id]');
    var navLinks  = document.querySelectorAll('.hidden.md\\:flex a[href^="#"]');
 
    window.addEventListener('scroll', function() {
        var current = '';
        sections.forEach(function(sec) {
            if (window.scrollY >= sec.offsetTop - 120) current = sec.id;
        });
        navLinks.forEach(function(link) {
            link.classList.toggle('nav-link-active', link.getAttribute('href') === '#' + current);
        });
    }, { passive: true });
 
    /* -------------------------------------------------------
       8. ANIMATED COUNTERS — Stats di Hero Section
    ------------------------------------------------------- */
    var stats = [
        { label: 'Order Selesai', target: 1250, suffix: '+' },
        { label: 'Rating Bintang', target: 98, suffix: '%' },
        { label: 'Aktif', target: 24, suffix: '/7' }
    ];
    // Tambahkan stats bar di bawah hero CTA jika belum ada
    var heroCTA = document.querySelector('section.relative.pt-32 .flex.flex-col.sm\\:flex-row');
    if (heroCTA && !document.getElementById('hero-stats-bar')) {
        var statsBar = document.createElement('div');
        statsBar.id = 'hero-stats-bar';
        statsBar.style.cssText = 'display:flex;gap:2rem;margin-top:2.5rem;flex-wrap:wrap;';
        stats.forEach(function(s) {
            var item = document.createElement('div');
            item.innerHTML = '<span style="font-size:1.6rem;font-weight:800;color:#fff;font-family:Inter,sans-serif" class="counter-animate" data-target="' + s.target + '">0</span>'
                           + '<span style="font-size:1.2rem;color:#8b5cf6;font-weight:700">' + s.suffix + '</span>'
                           + '<p style="font-size:.78rem;color:#6b7280;margin-top:.15rem;letter-spacing:.06em">' + s.label + '</p>';
            statsBar.appendChild(item);
        });
        heroCTA.parentNode.insertBefore(statsBar, heroCTA.nextSibling);
 
        // Animate counters sekali saat masuk viewport
        var counted = false;
        var counterObs = new IntersectionObserver(function(entries) {
            if (entries[0].isIntersecting && !counted) {
                counted = true;
                statsBar.querySelectorAll('.counter-animate').forEach(function(el) {
                    var target = parseInt(el.dataset.target, 10);
                    var current = 0;
                    var inc = Math.max(1, Math.ceil(target / 50));
                    var timer = setInterval(function() {
                        current = Math.min(current + inc, target);
                        el.textContent = current.toLocaleString('id-ID');
                        if (current >= target) clearInterval(timer);
                    }, 28);
                });
                counterObs.disconnect();
            }
        }, { threshold: 0.5 });
        counterObs.observe(statsBar);
    }
 
    /* -------------------------------------------------------
       9. HOVER GLOW — Auto-tambahkan ke feature cards
    ------------------------------------------------------- */
    document.querySelectorAll('.grid.grid-cols-2.md\\:grid-cols-4 > div').forEach(function(card) {
        card.classList.add('hover-glow');
    });
 
    /* -------------------------------------------------------
       10. SMOOTH HERO ENTRANCE — animasikan hero content masuk
    ------------------------------------------------------- */
    var heroContent = document.querySelector('section.relative.pt-32 .flex.flex-col.items-start');
    if (heroContent) {
        heroContent.style.opacity = '0';
        heroContent.style.transform = 'translateY(30px)';
        heroContent.style.transition = 'opacity 0.8s ease 0.15s, transform 0.8s cubic-bezier(.4,0,.2,1) 0.15s';
        // Tunggu window.load supaya tidak bentrok dengan loader yang sudah ada
        window.addEventListener('load', function() {
            setTimeout(function() {
                heroContent.style.opacity = '1';
                heroContent.style.transform = 'translateY(0)';
            }, 100);
        });
    }
 
    /* -------------------------------------------------------
       11. PULSE DOT — inject ke badge hero
    ------------------------------------------------------- */
    var badge = document.querySelector('section.relative.pt-32 .flex.items-center.gap-2.px-3');
    if (badge) {
        var icon = badge.querySelector('.fa-fire');
        if (icon && !badge.querySelector('.pulse-dot')) {
            var dot = document.createElement('span');
            dot.className = 'pulse-dot';
            badge.insertBefore(dot, badge.firstChild);
        }
    }
 
    /* -------------------------------------------------------
       12. STAGGER delay untuk rating cards
    ------------------------------------------------------- */
    document.querySelectorAll('#rating-section .glass-card').forEach(function(card, i) {
        card.style.transitionDelay = (i * 0.13) + 's';
    });
 
})();
</script>
</body>
</html>