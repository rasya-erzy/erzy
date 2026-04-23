<?php
session_start();
require_once '../config/koneksi.php';

// CEK APAKAH SUDAH TERBLOKIR
if (isset($_SESSION['admin_banned']) && $_SESSION['admin_banned'] === true) {
    header("Location: ../index.php"); // Langsung tendang ke utama
    exit();
}

if (isset($_POST['login'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Cek akun admin di database
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username = '$user'");
    $data = mysqli_fetch_assoc($query);

    if ($data && md5($pass) == $data['password']) {
        $_SESSION['admin_logged'] = true;
        header("Location: index.php");
    } else {
        // GAGAL LOGIN = BLOKIR AKSES SESSION
        $_SESSION['admin_banned'] = true;
        header("Location: ../index.php"); // Halaman login menghilang, balik ke index
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Restricted | Erzyy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style> body { background: #0a0a0f; font-family: 'Inter', sans-serif; } </style>
</head>
<body class="flex items-center justify-center h-screen px-6">
    <div class="w-full max-w-md bg-[#12141a] p-10 rounded-[2rem] border border-white/5 shadow-2xl">
        <h2 class="text-2xl font-extrabold text-white mb-2 text-center">Admin Access</h2>
        <p class="text-xs text-red-500 text-center mb-8 uppercase tracking-widest font-bold">Peringatan: Hanya 1x Percobaan!</p>
        
        <form action="" method="POST" class="space-y-6">
            <input type="text" name="username" placeholder="Username" required class="w-full bg-black/20 border border-white/10 rounded-xl px-5 py-4 text-white outline-none focus:border-indigo-500">
            <input type="password" name="password" placeholder="Password" required class="w-full bg-black/20 border border-white/10 rounded-xl px-5 py-4 text-white outline-none focus:border-indigo-500">
            <button type="submit" name="login" class="w-full bg-white text-black font-bold py-4 rounded-xl hover:bg-gray-200 transition">Authorize</button>
        </form>
    </div>
</body>
</html>