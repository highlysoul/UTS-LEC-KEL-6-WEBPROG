<?php
ob_start();

require_once('includes/config.php');
require_once('includes/functions.php');
require_once('includes/user_header.php');

if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?= $_SESSION['success'] ?></span>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?= $_SESSION['error'] ?></span>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php
// Cek login
if (!$isLoggedIn) {
    header("Location: login.php");
    exit;
}

// Ambil event yang masih terdaftar
try {
    $stmt = $pdo->prepare("
        SELECT 
            e.*,
            r.registration_date,
            r.status as registration_status,
            (SELECT COUNT(*) FROM registrations WHERE event_id = e.id AND status = 'registered') as total_participants
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        WHERE r.user_id = ? AND r.status = 'registered'
        ORDER BY r.registration_date DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $registered_events = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Cek apakah ada request untuk menghapus semua history
if (isset($_POST['delete_all_history'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM registrations WHERE user_id = ? AND status = 'canceled'");
        $stmt->execute([$_SESSION['user_id']]);

        $_SESSION['success'] = "Semua history event yang dibatalkan telah dihapus.";
        header("Location: registered_events.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Gagal menghapus history: " . $e->getMessage();
    }
}

// Cek apakah ada request untuk menghapus satu event yang dibatalkan
if (isset($_POST['delete_event']) && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ? AND status = 'canceled'");
        $stmt->execute([$_SESSION['user_id'], $event_id]);

        $_SESSION['success'] = "Event telah dihapus dari history.";
        header("Location: registered_events.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Gagal menghapus event: " . $e->getMessage();
    }
}

// Ambil event yang dibatalkan (history)
try {
    $stmt = $pdo->prepare("
        SELECT 
            e.*,
            r.registration_date,
            r.status as registration_status,
            (SELECT COUNT(*) FROM registrations WHERE event_id = e.id AND status = 'registered') as total_participants
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        WHERE r.user_id = ? AND r.status = 'canceled'
        ORDER BY r.registration_date DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $canceled_events = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event yang Terdaftar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 bg-gray-100">
    <div class="py-2 mt-4">
        <h2 class="text-2xl font-bold text-gray-900">Event Terdaftar</h2>
    </div>

    <!-- Section Event Terdaftar -->
    <?php if (empty($registered_events)): ?>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-gray-600">
                Anda belum terdaftar di event apapun.
                <a href="index.php" class="text-red-600 hover:text-red-700 font-semibold ml-2">
                    Lihat Event yang Tersedia
                </a>
            </p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 gap-4 mb-8">
            <?php foreach ($registered_events as $event): ?>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900">
                                <?= htmlspecialchars($event['name']) ?>
                            </h3>
                            <div class="mt-2 space-y-1 text-gray-600">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <?= format_tanggal($event['event_date']) ?>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    <?= htmlspecialchars($event['location']) ?>
                                </div>
                                <div class="flex items-center text-sm">
                                    <span class="mr-2">Status Pendaftaran:</span>
                                    <?= get_registration_status_badge($event['registration_status']) ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    Didaftarkan pada: <?= format_tanggal_waktu($event['registration_date']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2 ml-4 min-w-[100px] text-right">
                            <a href="event_detail.php?id=<?= $event['id'] ?>" 
                               class="inline-block px-3 py-1 text-sm text-blue-600 hover:text-blue-800 border border-blue-600 rounded-md hover:bg-blue-50 transition-colors">
                                <center>Detail</center>
                            </a>
                            <?php if ($event['event_date'] >= date('Y-m-d') && $event['registration_status'] === 'registered'): ?>
                                <form method="POST" action="event_detail.php?id=<?= $event['id'] ?>" class="inline-block">
                                    <button type="submit" name="cancel" 
                                            class="w-full px-3 py-1 text-sm text-red-600 hover:text-red-800 border border-red-600 rounded-md hover:bg-red-50 transition-colors"
                                            onclick="return confirm('Anda yakin ingin membatalkan pendaftaran?')">
                                        Batalkan
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

   <!-- Section History (Event yang Dibatalkan) -->
<div class="py-4 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-900">History</h2>
    <?php if (!empty($canceled_events)): ?>
        <!-- Tombol Delete All History dengan style baru -->
        <form method="POST" action="" onsubmit="return confirm('Anda yakin ingin menghapus semua history event yang dibatalkan?')" class="inline-block">
            <button type="submit" name="delete_all_history" class="btn">
                <p class="paragraph">delete all</p> 
                <span class="icon-wrapper">
                    <svg class="icon" width="30px" height="30px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 7V18C6 19.1046 6.89543 20 8 20H16C17.1046 20 18 19.1046 18 18V7M6 7H5M6 7H8M18 7H19M18 7H16M10 11V16M14 11V16M8 7V5C8 3.89543 8.89543 3 10 3H14C15.1046 3 16 3.89543 16 5V7M8 7H16" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </span>
            </button>
        </form>
    <?php endif; ?>
</div>

<!-- Tambahkan CSS ini di bagian <style> atau file CSS terpisah -->
<style>
.btn {
    cursor: pointer;
    width: 50px;
    height: 50px;
    border: none;
    position: relative;
    border-radius: 10px;
    -webkit-box-shadow: 1px 1px 5px .2px #00000035;
    box-shadow: 1px 1px 5px .2px #00000035;
    -webkit-transition: .2s linear;
    transition: .2s linear;
    transition-delay: .2s;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: space-between;
    background: white;
}

.btn:hover {
    width: 150px;
    transition-delay: .2s;
}

.btn:hover > .paragraph {
    visibility: visible;
    opacity: 1;
    -webkit-transition-delay: .4s;
    transition-delay: .4s;
}

.btn:hover > .icon-wrapper .icon {
    transform: scale(1.1);
}

.btn:hover > .icon-wrapper .icon path {
    stroke: black;
}

.paragraph {
    color: black;
    visibility: hidden;
    opacity: 0;
    font-size: 13px;
    margin-right: 30px;
    padding-left: 20px;
    -webkit-transition: .2s linear;
    transition: .2s linear;
    font-weight: bold;
    text-transform: uppercase;
}

.icon-wrapper {
    width: 50px;
    height: 50px;
    position: absolute;
    top: 0;
    right: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon {
    transform: scale(.9);
    transition: .2s linear;
}

.icon path {
    stroke: #000;
    stroke-width: 2px;
    -webkit-transition: .2s linear;
    transition: .2s linear;
}

</style>

<?php if (empty($canceled_events)): ?>
    <div class="bg-white rounded-lg shadow p-4 text-center mb-10">
        <p class="text-gray-600">Belum ada event yang dibatalkan.</p>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 gap-4 mb-8">
        <?php foreach ($canceled_events as $event): ?>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-900">
                            <?= htmlspecialchars($event['name']) ?>
                        </h3>
                        <div class="mt-2 space-y-1 text-gray-600">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <?= format_tanggal($event['event_date']) ?>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                <?= htmlspecialchars($event['location']) ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                Didaftarkan pada: <?= format_tanggal_waktu($event['registration_date']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 ml-4 min-w-[100px] text-right">
                        <span class="inline-block px-3 py-1 text-sm text-red-500 border border-red-500 rounded-md bg-red-50">
                            Dibatalkan
                        </span>
                        <!-- Tombol Delete per Event -->
                        <form method="POST" action="">
                            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                            <button type="submit" name="delete_event" 
                                    class="w-full px-3 py-1 text-sm text-red-600 hover:text-red-800 border border-red-600 rounded-md hover:bg-red-50 transition-colors"
                                    onclick="return confirm('Anda yakin ingin menghapus event ini dari history?')">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</div>
</body>
</html>