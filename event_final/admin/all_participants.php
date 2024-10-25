<?php
// admin/all_participants.php
require_once('../includes/config.php');
require_once('../includes/functions.php');
require_once('../includes/admin_header.php');

// Ambil seluruh peserta, event yang mereka ikuti, dan statusnya
$stmt = $pdo->query("
    SELECT 
        u.id as user_id,
        u.username,
        u.email,
        e.name as event_name,
        r.status as event_status
    FROM users u
    JOIN registrations r ON u.id = r.user_id
    JOIN events e ON r.event_id = e.id
    ORDER BY u.username ASC, e.event_date DESC
");

$participants = [];
while ($row = $stmt->fetch()) {
    $participants[$row['user_id']]['username'] = $row['username'];
    $participants[$row['user_id']]['email'] = $row['email'];
    $participants[$row['user_id']]['events'][] = [
        'event_name' => $row['event_name'],
        'event_status' => $row['event_status']
    ];
}
?>

<!-- Wrapper Utama untuk Struktur Halaman -->
<div class="flex flex-col">
    <!-- Konten Utama -->
    <div class="container mx-auto my-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">All Participants</h2>
                <a href="index.php" class="text-gray-600 hover:text-gray-800">
                    Kembali ke Dashboard
                </a>
            </div>

            <div class="overflow-x-auto">
                <table id="allParticipantsTable" class="min-w-full bg-white">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Events</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($participants)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                    No participants registered
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($participants as $user_id => $participant): ?>
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
                                        <button class="text-blue-600 hover:text-blue-800" onclick="showEventsModal(<?= $user_id ?>)">
                                            View Events
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form action="delete_user.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
                                            <input type="hidden" name="user_id" value="<?= $user_id ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Template -->
<div id="eventsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden flex items-center justify-center">
    <div class="relative p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modalTitle"></h3>
            <div class="mt-2">
                <ul id="modalEventList" class="list-disc list-inside text-left text-gray-700"></ul>
            </div>
            <div class="items-center px-4 py-3">
                <button class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-600" onclick="closeModal()">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Script untuk menghandle modal -->
<script>
const participants = <?= json_encode(array_map(function($participant) {
    return [
        'username' => htmlspecialchars($participant['username']),
        'events' => array_map(function($event) {
            return [
                'event_name' => htmlspecialchars($event['event_name']),
                'event_status' => htmlspecialchars($event['event_status'])
            ];
        }, $participant['events'])
    ];
}, $participants)) ?>;

function showEventsModal(user_id) {
    const participant = participants[user_id];
    document.getElementById('modalTitle').textContent = `Events for ${participant.username}`;
    
    const eventList = participant.events.map(event => {
        return `<li>${event.event_name} - <strong>${event.event_status}</strong></li>`;
    }).join('');
    
    document.getElementById('modalEventList').innerHTML = eventList;
    document.getElementById('eventsModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('eventsModal').classList.add('hidden');
}

$(document).ready(function() {
    $('#allParticipantsTable').DataTable({
        "order": [[0, "asc"]],
        "pageLength": 10,
        "lengthMenu": [5, 10, 25, 50],
        "searching": true,
        "paging": true,
        "info": true
    });
});
</script>