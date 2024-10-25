<?php
require_once('../includes/config.php');
require_once('../includes/functions.php');

// Ambil ID event dari parameter URL
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$event_id) {
    header('Location: index.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT e.*, COUNT(r.id) as total_registrants 
        FROM events e 
        LEFT JOIN registrations r ON e.id = r.event_id AND r.status = 'registered'  -- Tambahkan kondisi status di sini
        WHERE e.id = ? 
        GROUP BY e.id
    ");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    
    if (!$event) {
        header('Location: index.php');
        exit;
    }

    $stmt = $pdo->prepare("
    SELECT u.username, u.email, r.registration_date, r.status
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    WHERE r.event_id = ? AND r.status = 'registered'  -- Tambahkan kondisi status
    ORDER BY r.registration_date DESC
    ");
    $stmt->execute([$event_id]);
    $participants = $stmt->fetchAll();


} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Handle export to CSV
if (isset($_POST['export_csv'])) {
    // Set headers untuk file CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="participants_' . $event_id . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Tambahkan UTF-8 BOM agar karakter khusus tampil dengan benar di Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Tampilkan nama event di bagian atas file CSV
    fputcsv($output, ['Event:', htmlspecialchars($event['name'])], ';');
    fputcsv($output, [], ';'); // Baris kosong untuk spasi
    
    // Header CSV
    fputcsv($output, ['Username', 'Email', 'Tanggal Registrasi', 'Status'], ';');
    
    // Isi data peserta ke CSV
    foreach ($participants as $participant) {
        fputcsv($output, [
            $participant['username'],
            $participant['email'],
            $participant['registration_date'],
            $participant['status']
        ], ';');
    }
    
    fclose($output);
    exit; // Hentikan eksekusi agar HTML tidak dirender
}

// Jika tidak mengekspor CSV, tampilkan halaman HTML
require_once('../includes/admin_header.php');
?>

<div class="container mx-auto my-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <div class="mb-4 md:mb-0">
                <h2 class="text-2xl font-bold"><?= htmlspecialchars($event['name']) ?></h2>
                <p class="text-gray-600">
                    <?= format_tanggal($event['event_date']) ?> | 
                    <?= format_waktu($event['event_time']) ?> | 
                    <?= htmlspecialchars($event['location']) ?>
                </p>
            </div>
            <div class="flex flex-col md:flex-row gap-4">
                <form method="POST">
                    <button type="submit" name="export_csv" 
                        class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                        Export CSV
                    </button>
                </form>
                <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                    Kembali
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white p-6 rounded-lg shadow border">
                <h3 class="text-lg font-semibold text-gray-700">Total Peserta</h3>
                <p class="text-3xl font-bold text-red-600"><?= $event['total_registrants'] ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow border">
                <h3 class="text-lg font-semibold text-gray-700">Kapasitas</h3>
                <p class="text-3xl font-bold text-red-600"><?= $event['max_participants'] ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow border">
                <h3 class="text-lg font-semibold text-gray-700">Status</h3>
                <p class="text-3xl font-bold text-red-600"><?= ucfirst($event['status']) ?></p>
            </div>
        </div>

        <!-- Participants Table -->
        <div class="overflow-x-auto">
            <table id="participantsTable" class="min-w-full bg-white">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Username
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal Registrasi
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($participants)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                Belum ada peserta yang terdaftar
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($participants as $participant): ?>
                            <tr class="odd:bg-white even:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($participant['username']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?= htmlspecialchars($participant['email']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?= format_tanggal_waktu($participant['registration_date']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= $participant['status'] === 'registered' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= ucfirst($participant['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#participantsTable').DataTable({
        "order": [[2, "desc"]], // Sort by registration date by default
        "pageLength": 25
    });
});
</script>
