<?php
// admin/add_event.php
require_once('../includes/config.php');
require_once('../includes/admin_header.php');

// Menangani pengiriman form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi input
    $errors = [];
    
    // Field yang wajib diisi
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
        $time = date_create_from_format('H:i', $_POST['event_time']);
        if (!$time || $time->format('H:i') !== $_POST['event_time']) {
            $errors[] = "Format waktu tidak valid";
        }
    }
    
    // Validasi jumlah maksimal peserta
    if (!empty($_POST['max_participants']) && (!is_numeric($_POST['max_participants']) || $_POST['max_participants'] < 1)) {
        $errors[] = "Jumlah maksimal peserta harus berupa angka positif";
    }
    
    // Menangani upload gambar banner
    $banner_image = '';
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['banner_image']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Tipe file tidak valid. Hanya JPG, PNG, WEBP dan GIF yang diperbolehkan.";
        } else {
            $file_name = time() . '_' . $_FILES['banner_image']['name'];
            $upload_path = '../uploads/event_banners/' . $file_name;
            
            if (!move_uploaded_file($_FILES['banner_image']['tmp_name'], $upload_path)) {
                $errors[] = "Gagal mengunggah gambar";
            } else {
                $banner_image = $file_name;
            }
        }
    }
    
    // Jika tidak ada error, masukkan ke database
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO events (name, event_date, event_time, location, description, 
                    max_participants, banner_image, created_by, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'open')";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['name'],
                $_POST['event_date'],
                $_POST['event_time'],
                $_POST['location'],
                $_POST['description'],
                $_POST['max_participants'],
                $banner_image,
                $_SESSION['user_id']
            ]);
            
            // Redirect ke halaman admin setelah berhasil
            header('Location: index.php?success=1');
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
    <title>Tambah Event - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .image-preview-container {
            max-width: 300px;
            margin-top: 1rem;
        }
        .preview-image {
            width: 100%;
            height: auto;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<!-- Form Tambah Event -->
<div class="container mx-auto my-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">Tambah Event Baru</h2>
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

        <form action="add_event.php" method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Event -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                        Nama Event *
                    </label>
                    <input type="text" name="name" id="name" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                </div>

                <!-- Tanggal Event -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="event_date">
                        Tanggal Event *
                    </label>
                    <input type="date" name="event_date" id="event_date" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        value="<?= isset($_POST['event_date']) ? htmlspecialchars($_POST['event_date']) : '' ?>">
                </div>

                <!-- Waktu Event -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="event_time">
                        Waktu Event *
                    </label>
                    <input type="time" name="event_time" id="event_time" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        value="<?= isset($_POST['event_time']) ? htmlspecialchars($_POST['event_time']) : '' ?>">
                </div>

                <!-- Lokasi -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="location">
                        Lokasi *
                    </label>
                    <input type="text" name="location" id="location" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        value="<?= isset($_POST['location']) ? htmlspecialchars($_POST['location']) : '' ?>">
                </div>

                <!-- Jumlah Maksimal Peserta -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="max_participants">
                        Jumlah Maksimal Peserta *
                    </label>
                    <input type="number" name="max_participants" id="max_participants" required min="1"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        value="<?= isset($_POST['max_participants']) ? htmlspecialchars($_POST['max_participants']) : '' ?>">
                </div>

                <!-- Banner Event -->
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="banner_image">
                        Banner Event
                    </label>
                    <input type="file" name="banner_image" id="banner_image" accept="image/*"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        onchange="previewImage(this)">
                    
                    <!-- Image Preview Container -->
                    <div id="imagePreview" class="hidden mt-4">
                        <img id="preview" src="#" alt="Preview" class="max-w-full h-auto rounded-lg shadow-lg">
                        <button type="button" onclick="removeImage()" 
                                class="mt-2 bg-red-100 text-red-600 px-3 py-1 rounded text-sm hover:bg-red-200">
                            Hapus Gambar
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
                ><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
            </div>

            <div class="flex items-center justify-end gap-4">
                <a href="index.php" 
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Batal
                </a>
                <button type="submit"
                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Tambah Event
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

<?php require_once('../includes/footer.php'); ?>
</body>
</html>