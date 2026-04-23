<?php 
require_once 'config/koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tracking Order | Erzyy Boost</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #0b0c10; color: #9ca3af; font-family: 'Inter', sans-serif; }
        .glass-card { background: rgba(30, 32, 41, 0.6); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
    </style>
</head>
<body class="pt-24 px-6">
    <div class="max-w-3xl mx-auto">
        <a href="index.php" class="text-gray-500 hover:text-white mb-8 inline-block transition"><i class="fas fa-arrow-left mr-2"></i> Kembali</a>
        
        <div class="glass-card p-10 rounded-[2.5rem] mb-10">
            <h2 class="text-3xl font-extrabold text-white mb-6">Lacak Pesanan</h2>
            <form action="" method="GET" class="flex gap-4">
                <input type="text" name="kode" placeholder="Masukkan Kode Pesanan (ERZ-XXXXX)" required class="flex-1 bg-black/20 border border-white/10 rounded-xl px-5 py-4 text-white focus:border-indigo-500 outline-none">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-8 py-4 rounded-xl transition">Cari</button>
            </form>

            <?php
            if (isset($_GET['kode'])) {
                $kode = mysqli_real_escape_string($conn, $_GET['kode']);
                $res = mysqli_query($conn, "SELECT * FROM orders WHERE kode_order = '$kode'");
                
                if (mysqli_num_rows($res) > 0) {
                    $d = mysqli_fetch_assoc($res);
                    $p = 0; $color = "bg-gray-700";
                    if($d['status'] == 'Pending') { $p = 30; $color = "bg-yellow-500"; }
                    if($d['status'] == 'Diproses') { $p = 65; $color = "bg-blue-500"; }
                    if($d['status'] == 'Selesai') { $p = 100; $color = "bg-green-500"; }
            ?>
                <div class="mt-12 border-t border-white/5 pt-10">
                    <div class="flex justify-between items-end mb-4">
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Status Pesanan</span>
                            <h3 class="text-2xl font-bold text-white"><?php echo $d['status']; ?></h3>
                        </div>
                        <span class="text-sm font-medium text-gray-400">Progress: <?php echo $p; ?>%</span>
                    </div>
                    <div class="w-full bg-white/5 rounded-full h-3 mb-10 overflow-hidden">
                        <div class="<?php echo $color; ?> h-full transition-all duration-1000" style="width: <?php echo $p; ?>%"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-8 text-sm">
                        <div class="bg-black/10 p-4 rounded-xl">
                            <p class="text-gray-500 mb-1">Nickname</p>
                            <p class="text-white font-bold"><?php echo $d['nickname']; ?></p>
                        </div>
                        <div class="bg-black/10 p-4 rounded-xl">
                            <p class="text-gray-500 mb-1">Target</p>
                            <p class="text-white font-bold"><?php echo $d['rank_tujuan']; ?> (<?php echo $d['bintang_tujuan']; ?>★)</p>
                        </div>
                    </div>
                </div>
            <?php } else { echo "<p class='mt-10 text-red-400'>Pesanan tidak ditemukan. Cek kembali kode Anda.</p>"; } } ?>
        </div>
    </div>
</body>
</html>