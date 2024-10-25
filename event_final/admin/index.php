<?php
// admin/index.php
require_once('../includes/config.php');
require_once('../includes/functions.php');
require_once('../includes/admin_header.php');

// Ambil status dari URL (jika ada)
$status = isset($_GET['status']) ? $_GET['status'] : null;

// Query untuk mendapatkan statistik event
$stmt = $pdo->query("
    SELECT 
        COUNT(DISTINCT e.id) as total_events,
        COUNT(DISTINCT CASE WHEN e.status = 'open' THEN e.id END) as active_events,
        COUNT(DISTINCT CASE WHEN e.status = 'closed' THEN e.id END) as closed_events,
        COUNT(DISTINCT CASE WHEN e.status = 'canceled' THEN e.id END) as canceled_events,
        COALESCE(COUNT(DISTINCT r.user_id), 0) as total_participants
    FROM events e
    LEFT JOIN registrations r ON e.id = r.event_id
");

$stats = $stmt->fetch();

// Query untuk mendapatkan daftar event dengan filter status jika ada
$query = "
    SELECT 
        e.*,
        COUNT(CASE WHEN r.status = 'registered' THEN 1 END) as registered_participants
    FROM events e 
    LEFT JOIN registrations r ON e.id = r.event_id
";

if ($status) {
    $query .= " WHERE e.status = :status ";
}

$query .= " GROUP BY e.id 
            ORDER BY 
            CASE e.status 
                WHEN 'open' THEN 1 
                WHEN 'closed' THEN 2 
                WHEN 'canceled' THEN 3 
                ELSE 4 
            END, 
            e.event_date DESC";

$stmt = $pdo->prepare($query);

if ($status) {
    $stmt->execute(['status' => $status]);
} else {
    $stmt->execute();
}

$events = $stmt->fetchAll();
?>

<head>
    <!-- Other head elements -->

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<!-- Overview Cards -->
<div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow-lg cursor-pointer" onclick="window.location.href='index.php'">
        <h3 class="text-lg font-semibold text-gray-700">Total Events</h3>
        <p class="text-3xl font-bold text-red-600"><?= $stats['total_events'] ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-lg cursor-pointer" onclick="window.location.href='index.php?status=open'">
        <h3 class="text-lg font-semibold text-gray-700">Active Events</h3>
        <p class="text-3xl font-bold text-red-600"><?= $stats['active_events'] ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-lg cursor-pointer" onclick="window.location.href='index.php?status=closed'">
        <h3 class="text-lg font-semibold text-gray-700">Closed Events</h3>
        <p class="text-3xl font-bold text-red-600"><?= $stats['closed_events'] ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-lg cursor-pointer" onclick="window.location.href='index.php?status=canceled'">
        <h3 class="text-lg font-semibold text-gray-700">Canceled Events</h3>
        <p class="text-3xl font-bold text-red-600"><?= $stats['canceled_events'] ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-lg cursor-pointer" onclick="window.location.href='all_participants.php'">
        <h3 class="text-lg font-semibold text-gray-700">Total Participants</h3>
        <p class="text-3xl font-bold text-red-600"><?= $stats['total_participants'] ?></p>
    </div>
</div>

<!-- Event Management Section -->
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold">Event Management</h2>
        <div class="flex gap-2">
            <a href="add_hot_event.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                <i class="fa-solid fa-fire"></i> Add Hot Event
            </a> 
            <a href="add_event.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                <i class="fas fa-plus"></i> Add New Event
            </a>

        </div>
    </div>


     <!-- Other Events Grid -->
     <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($events as $event): ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="relative">
                    <?php if ($event['banner_image']): ?>
                        <img src="../uploads/event_banners/<?= htmlspecialchars($event['banner_image']) ?>" 
                             alt="<?= htmlspecialchars($event['name']) ?>"
                             class="w-full h-48 object-cover">
                    <?php else: ?>
                        <div class="w-full h-48 bg-gray-300 flex items-center justify-center">
                            <span class="text-gray-500">No Image</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-lg mb-2"><?= htmlspecialchars($event['name']) ?></h3>
                    <div class="text-sm text-gray-600 space-y-1">
                        <div class="flex items-center mb-2">
                            <!-- Date Icon -->
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <?= format_tanggal($event['event_date']) ?>
                        </div>
                        <div class="flex items-center mb-2">
                            <!-- Time Icon -->
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <?= format_waktu($event['event_time']) ?>
                        </div>
                        <div class="flex items-center mb-2">
                            <!-- Location Icon -->
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <?= htmlspecialchars($event['location']) ?>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600">Kapasitas:</span>
                            <span class="text-sm font-medium"><?= $event['registered_participants'] ?>/<?= $event['max_participants'] ?></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <?php $percentage = min(($event['registered_participants'] / $event['max_participants']) * 100, 100); ?>
                            <div class="bg-red-600 h-2 rounded-full" style="width: <?= $percentage ?>%"></div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between">
                        <div>
                            <?= get_event_status_badge($event['status']) ?>
                        </div>
                        <div class="flex gap-2">
                            <a href="view_participants.php?id=<?= $event['id'] ?>" 
                               class="text-blue-600 hover:text-blue-800">View</a>
                            <a href="edit_event.php?id=<?= $event['id'] ?>" 
                               class="text-yellow-600 hover:text-yellow-800">Edit</a>
                            <button onclick="deleteEvent(<?= $event['id'] ?>)" 
                                    class="text-red-600 hover:text-red-800">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function deleteEvent(eventId) {
    if (confirm('Apakah Anda yakin ingin menghapus event ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete_event.php';
        
        const eventIdInput = document.createElement('input');
        eventIdInput.type = 'hidden';
        eventIdInput.name = 'event_id';
        eventIdInput.value = eventId;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?= generate_csrf_token() ?>';
        
        form.appendChild(eventIdInput);
        form.appendChild(csrfInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>