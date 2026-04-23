<?php
session_start();
require_once '../config/koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

// Fitur Update Status
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'proses') $status = 'Diproses';
    elseif ($action == 'selesai') $status = 'Selesai';
    
    mysqli_query($conn, "UPDATE orders SET status = '$status' WHERE id = '$id'");
    header("Location: index.php");
}

// Fitur Hapus Order
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM orders WHERE id = '$id'");
    header("Location: index.php");
}

$query = mysqli_query($conn, "SELECT * FROM orders ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Erzyy Admin | Order Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], },
                    colors: {
                        takaDark: '#12141a',
                        takaCard: '#1a1c23',
                        takaYellow: '#facc15',
                        takaVibrant: '#8b5cf6',
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0b0c10; color: #9ca3af; }
        /* Style ala Takapedia Dashboard */
        .taka-panel { background-color: #1a1c23; border: 1px solid rgba(255, 255, 255, 0.05); }
        .status-badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Custom Scrollbar untuk tabel */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #12141a; }
        ::-webkit-scrollbar-thumb { background: #374151; border-radius: 10px; }
    </style>
</head>
<body class="p-4 md:p-8">

    <div class="max-w-[1400px] mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 bg-[#1a1c23] p-6 rounded-2xl border border-white/5 shadow-lg">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-takaVibrant/20 text-takaVibrant flex items-center justify-center text-2xl">
                    <i class="fas fa-bolt"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-white tracking-tight">Erzyy <span class="text-takaVibrant">Admin</span></h1>
                    <p class="text-xs text-gray-400 mt-1">Management Order & Tracking System</p>
                </div>
            </div>
            <a href="logout.php" class="px-5 py-2.5 rounded-lg bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white transition font-bold text-sm border border-red-500/20">
                <i class="fas fa-sign-out-alt mr-2"></i>Keluar
            </a>
        </div>

        <div class="taka-panel rounded-2xl overflow-hidden shadow-2xl">
            <div class="p-6 border-b border-white/5 flex justify-between items-center">
                <h2 class="text-lg font-bold text-white"><i class="fas fa-list-alt mr-2 text-takaYellow"></i> Daftar Pesanan Joki</h2>
                <span class="px-3 py-1 bg-white/5 text-white text-xs font-bold rounded-lg border border-white/10">Total: <?php echo mysqli_num_rows($query); ?> Order</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-black/20 text-gray-400 text-[11px] uppercase tracking-wider font-bold">
                            <th class="p-5 border-b border-white/5">Info Pesanan</th>
                            <th class="p-5 border-b border-white/5">Detail Akun</th>
                            <th class="p-5 border-b border-white/5">Target Rank</th>
                            <th class="p-5 border-b border-white/5 w-64">Catatan Khusus</th>
                            <th class="p-5 border-b border-white/5">Tagihan</th>
                            <th class="p-5 border-b border-white/5 text-center">Status</th>
                            <th class="p-5 border-b border-white/5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php while($row = mysqli_fetch_assoc($query)): ?>
                        <tr class="hover:bg-white/[0.02] transition border-b border-white/5 last:border-0">
                            
                            <td class="p-5">
                                <span class="block text-takaVibrant font-bold text-xs mb-1 font-mono"><?php echo $row['kode_order']; ?></span>
                                <span class="text-white font-bold block mb-2"><?php echo htmlspecialchars($row['nickname']); ?></span>
                                
                                <?php if(isset($row['tipe_layanan']) && $row['tipe_layanan'] == 'Mabar VIP'): ?>
                                    <span class="inline-block text-[10px] bg-takaYellow/20 text-takaYellow border border-takaYellow/20 px-2 py-0.5 rounded font-bold"><i class="fas fa-crown mr-1"></i> Mabar VIP</span>
                                <?php else: ?>
                                    <span class="inline-block text-[10px] bg-blue-500/20 text-blue-400 border border-blue-500/20 px-2 py-0.5 rounded font-bold"><i class="fas fa-gamepad mr-1"></i> Piloting</span>
                                <?php endif; ?>
                            </td>

                            <td class="p-5">
                                <?php if(isset($row['tipe_layanan']) && $row['tipe_layanan'] == 'Mabar VIP'): ?>
                                    <span class="block text-xs text-gray-500 italic"><i class="fas fa-user-secret mr-1"></i> Privasi Aman (Mabar)</span>
                                <?php else: ?>
                                    <span class="block text-xs text-gray-400 mb-1">Via: <b class="text-white"><?php echo $row['login_via']; ?></b></span>
                                    <span class="block text-xs text-gray-400 mb-1">User: <b class="text-white"><?php echo htmlspecialchars($row['email']); ?></b></span>
                                    <span class="block text-xs text-gray-400">Pass: <b class="text-takaYellow font-mono tracking-wider"><?php echo htmlspecialchars($row['password_akun']); ?></b></span>
                                <?php endif; ?>
                            </td>

                            <td class="p-5">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-gray-400 text-xs"><?php echo $row['rank_awal']; ?></span>
                                    <i class="fas fa-chevron-right text-[10px] text-takaVibrant"></i>
                                    <span class="text-white font-bold text-xs"><?php echo $row['rank_tujuan']; ?></span>
                                </div>
                                <span class="text-[11px] text-takaYellow font-bold bg-takaYellow/10 px-2 py-0.5 rounded inline-block"><i class="fas fa-star text-[9px] mr-1"></i><?php echo $row['bintang_tujuan']; ?> Bintang/Poin</span>
                            </td>

                            <td class="p-5 whitespace-normal">
                                <?php if(!empty($row['catatan'])): ?>
                                    <div class="bg-black/30 p-2.5 rounded border border-white/5 text-xs text-gray-400 leading-relaxed max-w-xs">
                                        <?php echo htmlspecialchars($row['catatan']); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs text-gray-600 italic">Tidak ada catatan</span>
                                <?php endif; ?>
                            </td>

                            <td class="p-5">
                                <span class="block text-white font-bold text-sm mb-1">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></span>
                                <span class="text-[10px] bg-white/10 px-2 py-0.5 rounded text-gray-300 font-bold tracking-wide uppercase border border-white/5"><?php echo $row['payment']; ?></span>
                            </td>

                            <td class="p-5 text-center">
                                <?php 
                                    $bgClass = "bg-yellow-500/10 text-yellow-500 border-yellow-500/20";
                                    if($row['status'] == 'Diproses') $bgClass = "bg-blue-500/10 text-blue-500 border-blue-500/20";
                                    if($row['status'] == 'Selesai') $bgClass = "bg-green-500/10 text-green-500 border-green-500/20";
                                ?>
                                <span class="status-badge border <?php echo $bgClass; ?> inline-block"><?php echo $row['status']; ?></span>
                            </td>

                            <td class="p-5">
                                <div class="flex justify-center gap-2">
                                    <?php if($row['status'] == 'Pending'): ?>
                                        <a href="?action=proses&id=<?php echo $row['id']; ?>" class="w-8 h-8 flex items-center justify-center rounded bg-blue-500/10 text-blue-500 hover:bg-blue-500 hover:text-white transition border border-blue-500/20" title="Proses Pesanan"><i class="fas fa-play text-xs"></i></a>
                                    <?php elseif($row['status'] == 'Diproses'): ?>
                                        <a href="?action=selesai&id=<?php echo $row['id']; ?>" class="w-8 h-8 flex items-center justify-center rounded bg-green-500/10 text-green-500 hover:bg-green-500 hover:text-white transition border border-green-500/20" title="Selesaikan Pesanan"><i class="fas fa-check text-xs"></i></a>
                                    <?php endif; ?>
                                    
                                    <a href="?delete=<?php echo $row['id']; ?>" class="w-8 h-8 flex items-center justify-center rounded bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white transition border border-red-500/20" onclick="return confirm('Yakin ingin menghapus data order ini?')" title="Hapus"><i class="fas fa-trash-alt text-xs"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if(mysqli_num_rows($query) == 0): ?>
            <div class="p-10 text-center text-gray-500">
                <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
                <p class="text-sm">Belum ada pesanan yang masuk.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>