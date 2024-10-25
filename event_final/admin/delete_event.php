<?php
// admin/delete_event.php
require_once('../includes/config.php');
require_once('../includes/functions.php'); // Pastikan file ini ada dan berisi fungsi verify_csrf_token

// Verifikasi admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Verifikasi method dan token CSRF

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    header('Location: index.php?error=invalid_request');
    exit;
}


// Verifikasi ID event
$event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
if (!$event_id) {
    header('Location: index.php?error=invalid_id');
    exit;
}

try {
    // Mulai transaksi
    $pdo->beginTransaction();

    // Ambil informasi banner event
    $stmt = $pdo->prepare("SELECT banner_image FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();

    // Hapus registrasi terkait
    $stmt = $pdo->prepare("DELETE FROM registrations WHERE event_id = ?");
    $stmt->execute([$event_id]);

    // Hapus event
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$event_id]);

    // Commit transaksi
    $pdo->commit();

    // Hapus file banner jika ada
    if ($event && $event['banner_image']) {
        $banner_path = '../uploads/event_banners/' . $event['banner_image'];
        if (file_exists($banner_path)) {
            unlink($banner_path);
        }
    }

    header('Location: index.php?success=3'); // Event berhasil dihapus
    exit;

} catch (PDOException $e) {
    // Rollback transaksi jika terjadi error
    $pdo->rollBack();
    
    // Log error
    error_log('Error deleting event: ' . $e->getMessage());
    
    header('Location: index.php?error=delete_failed&message=' . urlencode($e->getMessage()));
    exit;
}
?>