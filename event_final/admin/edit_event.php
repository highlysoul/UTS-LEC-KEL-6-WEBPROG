<?php
// admin/edit_event.php
require_once('../includes/config.php');
require_once('../includes/functions.php');
require_once('../includes/admin_header.php');

// Ambil ID event dari parameter URL
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$event_id) {
    header('Location: index.php');
    exit;
}

// Ambil data event
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    
    if (!$event) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validasi input
    $required_fields = ['name', 'event_date', 'event_time', 'location', 'max_participants'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst($field) . " harus diisi";
        }
    }
    
    // Validasi format tanggal
    if (!empty($_POST['event_date'])) {
        $date = date_create_from_format('Y-m-d', $_POST['event_date']);
        if (!$date || $date->format('Y-m-d') !== $_POST['event_date']) {
            $errors[] = "Format tanggal tidak valid";
        }
    }
    
    // Validasi format waktu
    if (!empty($_POST['event_time'])) {
        // Validasi format waktu
        if (!empty($_POST['event_time'])) {
            $time = $_POST['event_time'];
            
            // Coba parse format 24 jam
            $parsed_time_24 = date_parse_from_format('H:i:s', $time);
            
            // Coba parse format 12 jam
            $parsed_time_12 = date_parse_from_format('h:i A', $time);
            
            if ($parsed_time_24['error_count'] == 0) {
                // Format waktu valid 24 jam, konversi ke 12 jam
                $formatted_time = date('h:i A', strtotime($time));
                $_POST['event_time'] = $formatted_time;
            } elseif ($parsed_time_12['error_count'] == 0) {
                // Format waktu valid 12 jam, gunakan apa adanya
                $formatted_time = date('h:i A', strtotime($time));
                $_POST['event_time'] = $formatted_time;
            } else {
                $errors[] = "Format waktu tidak valid. Gunakan format HH:MM (24 jam) atau HH:MM AM/PM (12 jam)";
            }
        }
    }
    
    // Handle banner image upload jika ada
    $banner_image = $event['banner_image']; // Gunakan banner yang sudah ada sebagai default
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['banner_image']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Tipe file tidak valid. Hanya JPG, PNG, WEBP dan GIF yang diperbolehkan.";
        } else {
            $file_name = time() . '_' . $_FILES['banner_image']['name'];
            $upload_path = '../uploads/event_banners/' . $file_name;
            
            if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $upload_path)) {
                // Hapus banner lama jika ada
                if ($event['banner_image'] && file_exists('../uploads/event_banners/' . $event['banner_image'])) {
                    unlink('../uploads/event_banners/' . $event['banner_image']);
                }
                $banner_image = $file_name;
            } else {
                $errors[] = "Gagal mengunggah gambar";
            }
        }
    }
    
    // Update database jika tidak ada error
    if (empty($errors)) {
        try {
            $sql = "UPDATE events SET 
                    name = ?, 
                    event_date = ?, 
                    event_time = ?, 
                    location = ?, 
                    description = ?, 
                    max_participants = ?, 
                    banner_image = ?,
                    status = ?
                    WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['name'],
                $_POST['event_date'],
                $_POST['event_time'],
                $_POST['location'],
                $_POST['description'],
                $_POST['max_participants'],
                $banner_image,
                $_POST['status'],
                $event_id
            ]);
            
            header('Location: index.php?success=2');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="container mx-auto my-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">Edit Event</h2>
            <a href="index.php" class="text-gray-600 hover:text-gray-800">
                Kembali ke Dashboard
            </a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <ul class="mt-2">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="edit_event.php?id=<?= $event_id ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Event -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                        Nama Event *
                    </label>
                    <input type="text" name="name" id="name" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        value="<?= htmlspecialchars($event['name']) ?>">
                </div>

                <!-- Tanggal Event -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="event_date">
                        Tanggal Event *
                    </label>
                    <input type="date" name="event_date" id="event_date" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        value="<?= htmlspecialchars($event['event_date']) ?>">
                </div>

                <!-- Waktu Event -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="event_time">
                        Waktu Event *
                    </label>
                     <input type="text" name="event_time" id="event_time" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        value="<?= htmlspecialchars($event['event_time']) ?>"
                        placeholder="HH:MM atau HH:MM AM/PM">
                </div>

                <!-- Lokasi -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="location">
                        Lokasi *
                    </label>
                    <input type="text" name="location" id="location" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        value="<?= htmlspecialchars($event['location']) ?>">
                </div>

                <!-- Status Event -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="status">
                        Status Event *
                    </label>
                    <select name="status" id="status" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="open" <?= $event['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="closed" <?= $event['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                        <option value="canceled" <?= $event['status'] === 'canceled' ? 'selected' : '' ?>>Canceled</option>
                    </select>
                </div>

                <!-- Jumlah Maksimal Peserta -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="max_participants">
                        Jumlah Maksimal Peserta *
                    </label>
                    <input type="number" name="max_participants" id="max_participants" required min="1"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        value="<?= htmlspecialchars($event['max_participants']) ?>">
                </div>

                <!-- Banner Event -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="banner_image">
                        Banner Event
                    </label>
                    
                    <!-- Current Banner Preview -->
                    <?php if ($event['banner_image'] && file_exists("../uploads/event_banners/" . $event['banner_image'])): ?>
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-2">Banner saat ini:</p>
                            <img src="../uploads/event_banners/<?= htmlspecialchars($event['banner_image']) ?>" 
                                 alt="Current banner"
                                 class="max-w-xs rounded-lg shadow-lg">
                        </div>
                    <?php endif; ?>
                    
                    <!-- New Banner Input and Preview -->
                    <input type="file" name="banner_image" id="banner_image" accept="image/*"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           onchange="previewImage(this)">
                    
                    <!-- New Banner Preview Container -->
                    <div id="imagePreview" class="hidden mt-4">
                        <p class="text-sm text-gray-600 mb-2">Preview banner baru:</p>
                        <img id="preview" src="#" alt="Preview" 
                             class="max-w-xs rounded-lg shadow-lg">
                        <button type="button" onclick="removeImage()" 
                                class="mt-2 bg-red-100 text-red-600 px-3 py-1 rounded text-sm hover:bg-red-200">
                            Hapus Gambar Baru
                        </button>
                    </div>
                </div>
            </div>

            <!-- Deskripsi -->
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                    Deskripsi Event
                </label>
                <textarea name="description" id="description" rows="4"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                ><?= htmlspecialchars($event['description']) ?></textarea>
            </div>

            <div class="flex items-center justify-end gap-4">
                <a href="index.php" 
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Batal
                </a>
                <button type="submit"
                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.classList.remove('hidden');
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    const input = document.getElementById('banner_image');
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreview');
    
    input.value = ''; // Clear file input
    preview.src = '#';
    previewContainer.classList.add('hidden');
}
</script>

</body>
</html>