<?php
session_start();
require_once 'config/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data & sanitasi (biar aman dari hacker)
    $tipe_layanan = $_POST['tipe_layanan'];
    $nickname       = mysqli_real_escape_string($conn, $_POST['nickname']);
    $id_server      = mysqli_real_escape_string($conn, $_POST['id_server']);
    $login_via      = mysqli_real_escape_string($conn, $_POST['login_via']);
    $email          = mysqli_real_escape_string($conn, $_POST['email']);
    $password_akun  = mysqli_real_escape_string($conn, $_POST['password_akun']);
    $rank_awal      = $_POST['rank_awal']; // Dari select, value teks
    $bintang_awal   = (int)$_POST['bintang_awal'];
    $rank_tujuan    = $_POST['rank_tujuan'];
    $bintang_tujuan = (int)$_POST['bintang_tujuan'];
    $catatan        = mysqli_real_escape_string($conn, $_POST['catatan']);
    $payment        = $_POST['payment'];
    $harga          = (int)$_POST['harga_total'];

    // Generate Kode Order (Contoh: ERZ-7X2A9)
    $kode_order = "ERZ-" . strtoupper(substr(md5(time()), 0, 5));

    // Query simpan ke database
    $query = "INSERT INTO orders (tipe_layanan, kode_order, nickname, id_server, login_via, email, password_akun, rank_awal, bintang_awal, rank_tujuan, bintang_tujuan, catatan, payment, harga) 
              VALUES ('$tipe_layanan', '$kode_order', '$nickname', '$id_server', '$login_via', '$email', '$password_akun', '$rank_awal', $bintang_awal, '$rank_tujuan', $bintang_tujuan, '$catatan', '$payment', $harga)";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = $kode_order;
        header("Location: index.php");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>