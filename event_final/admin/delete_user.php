<?php
// admin/delete_user.php
require_once('../includes/config.php');
require_once('../includes/functions.php');

// Pastikan pengguna mengirimkan ID pengguna untuk dihapus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];

    // Hapus semua registrasi yang terkait dengan pengguna ini
    $stmt = $pdo->prepare("DELETE FROM registrations WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    // Hapus pengguna dari database
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    // Redirect kembali ke halaman peserta setelah menghapus
    header("Location: all_participants.php");
    exit();
}

// Jika tidak ada ID pengguna, redirect kembali
header("Location: all_participants.php");
exit();
