<?php
// config/koneksi.php
$conn = mysqli_connect("localhost", "root", "", "erzyy_boost");
if (!$conn) die("Koneksi gagal: " . mysqli_connect_error());
?>