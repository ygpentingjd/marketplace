<?php
session_start();

// Hapus semua data session
session_destroy();

// Hapus cookie session jika ada
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect ke halaman utama
header("Location: ada.php");
exit();
