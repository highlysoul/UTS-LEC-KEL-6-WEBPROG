<?php
// logout.php

// Hapus semua data session
$_SESSION = array();

// Hapus cookie session jika ada
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Hancurkan session
session_destroy();

// Set pesan sukses logout
session_start();
$_SESSION['success'] = "Anda telah berhasil logout.";

// Redirect ke halaman login
header('Location: login.php');
exit;
?>