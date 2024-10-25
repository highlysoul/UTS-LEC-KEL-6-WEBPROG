<?php
// admin/add_hot_event.php
require_once('../includes/config.php');
require_once('../includes/functions.php');
require_once('../includes/admin_header.php');

// Ambil semua event yang statusnya 'open'
try {
    $stmt = $pdo->prepare("
        SELECT e.*,
        (SELECT COUNT(*) FROM registrations WHERE event_id = e.id AND status = 'registered') as registered_count
        FROM events e
        WHERE e.status = 'open'
        ORDER BY e.event_date ASC
    ");
    $stmt->execute();
    $events = $stmt->fetchAll();

    // Ambil Hot Events yang sudah dipilih
    $stmt = $pdo->query("SELECT event_id FROM hot_events");
    $hot_events_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<div class="container mx-auto px-4 py-1">
    <h2 class="text-xl font-bold mb-6">Pilih Event untuk Dijadikan Hot Event</h2>
    <form action="save_hot_event.php" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($events as $event): ?>
                <div class="bg-white rounded-lg shadow-lg p-4">
                    <input type="checkbox" name="hot_events[]" value="<?= $event['id'] ?>" id="event_<?= $event['id'] ?>" 
                           <?= in_array($event['id'], $hot_events_ids) ? 'checked' : '' ?>>
                    <label for="event_<?= $event['id'] ?>" class="cursor-pointer">
                        <h3 class="font-bold text-lg"><?= htmlspecialchars($event['name']) ?></h3>
                        <p class="flex items-center mb-2">
                            <!-- Date Icon -->
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <?= format_tanggal($event['event_date']) ?>
                        </p>
                        <p class="flex items-center mb-2">
                            <!-- Time Icon -->
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <?= format_waktu($event['event_time']) ?>
                        </p>
                        <p class="flex items-center mb-2">
                            <!-- Location Icon -->
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <?= htmlspecialchars($event['location']) ?>
                        </p>
                        <p class="flex items-center mb-2">
                            <!-- Participants Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 text-gray-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                            <?= htmlspecialchars($event['registered_count']) ?>/<?= htmlspecialchars($event['max_participants']) ?> peserta
                        </p>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-4">
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Simpan Hot Event</button>
        </div>
    </form>
</div>