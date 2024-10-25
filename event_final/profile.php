<?php
ob_start();
// profile.php
require_once('includes/config.php');
require_once('includes/functions.php');
require_once('includes/user_header.php');

// Ambil data user
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Handle form update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $errors = [];

    // Validasi username baru
    if (!empty($_POST['username']) && $_POST['username'] !== $user['username']) {
        // Cek username sudah digunakan atau belum
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$_POST['username'], $_SESSION['user_id']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Username sudah digunakan.";
        }
    }

    // Validasi email baru
    if (!empty($_POST['email']) && $_POST['email'] !== $user['email']) {
        // Cek email sudah digunakan atau belum
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$_POST['email'], $_SESSION['user_id']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Email sudah digunakan.";
        }
    }

    // Validasi password
    if (!empty($_POST['new_password'])) {
        if (strlen($_POST['new_password']) < 6) {
            $errors[] = "Password minimal 6 karakter.";
        }
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $errors[] = "Konfirmasi password tidak cocok.";
        }
    }

    // Update profil jika tidak ada error
if (empty($errors)) {
    try {
        // Update username
        if (!empty($_POST['username']) && $_POST['username'] !== $user['username']) {
            $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt->execute([$_POST['username'], $_SESSION['user_id']]);
            // Update session username agar langsung berubah di navbar
            $_SESSION['username'] = $_POST['username'];
        }

        // Update email
        if (!empty($_POST['email']) && $_POST['email'] !== $user['email']) {
            $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->execute([$_POST['email'], $_SESSION['user_id']]);
        }

        // Update password
        if (!empty($_POST['new_password'])) {
            $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $_SESSION['user_id']]);
        }

        $_SESSION['success'] = "Profil berhasil diperbarui!";
        header("Location: profile.php");
        exit;
    } catch (PDOException $e) {
        $errors[] = "Terjadi kesalahan saat memperbarui profil.";
    }
}
}
ob_end_flush();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Event Management</title>
    <!-- Tambahkan CSS untuk Cropper.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

    <style>
/* File Upload Styling */
.upload-container {
  --transition: 350ms;
  --folder-W: 40px;  /* Dikecilkan dari 60px */
  --folder-H: 30px;  /* Dikecilkan dari 40px */
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  padding: 4px;     /* Dikecilkan dari 6px */
  background: linear-gradient(135deg, #e6e6e6, #b3b3b3);
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  height: calc(var(--folder-H) * 1.7);
  position: relative;
  width: 70px;      /* Dikecilkan dari 96px */
  margin-bottom: 4px;
}
/* File Upload Styling */
.folder {
  position: absolute;
  top: -8px;        
  left: calc(50% - 20px);
  animation: float 2.5s infinite ease-in-out;
  transition: transform var(--transition) ease;
  width: var(--folder-W);
  height: var(--folder-H);
}

.folder .front-side,
.folder .back-side {
  position: absolute;
  transition: transform var(--transition);
  transform-origin: bottom center;
  width: 100%;
  height: 100%;
}

.folder .back-side::before,
.folder .back-side::after {
  content: "";
  display: block;
  background-color: white;
  opacity: 0.5;
  width: 100%;
  height: 100%;
  position: absolute;
  transform-origin: bottom center;
  border-radius: 4px;
  transition: transform 350ms;
}

.folder .front-side {
  z-index: 1;
}

/* Perbaiki hover animations - gunakan .folder:hover alih-alih .upload-container:hover */
.folder:hover .back-side::before {
  transform: rotateX(-5deg) skewX(5deg);
}

.folder:hover .back-side::after {
  transform: rotateX(-15deg) skewX(12deg);
}

.folder:hover .front-side {
  transform: rotateX(-40deg) skewX(15deg);
}

.folder .tip {
  background: linear-gradient(135deg, #FBA0E3, #FF69B4);
  width: 25px;      /* Dikecilkan dari 40px */
  height: 8px;      /* Dikecilkan dari 10px */
  border-radius: 4px 4px 0 0;
  box-shadow: 0 2px 4px rgba(251, 160, 227, 0.3);
  position: absolute;
  top: -4px;
  left: calc(50% - 12.5px);
  z-index: 2;
}

.folder .cover {
  background: linear-gradient(135deg, #FBA0E3, #FF69B4);
  width: 100%;
  height: 100%;
  box-shadow: 0 4px 8px rgba(251, 160, 227, 0.3);
  border-radius: 4px;
}

.custom-file-upload {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  cursor: pointer;
  z-index: 3;
}

.custom-file-upload input[type="file"] {
  display: none;
}


.custom-file-upload:hover ~ .folder .back-side::before {
  transform: rotateX(-5deg) skewX(5deg);
}

.custom-file-upload:hover ~ .folder .back-side::after {
  transform: rotateX(-15deg) skewX(12deg);
}

.custom-file-upload:hover ~ .folder .front-side {
  transform: rotateX(-40deg) skewX(15deg);
}

@keyframes float {
  0% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-6px); /* Dikecilkan dari -10px */
  }
  100% {
    transform: translateY(0px);
  }
}

/* Sisanya tetap sama */

/* Modal Button Styling */
.modal-btn {
  padding: 1.3em 3em;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 2.5px;
  font-weight: 500;
  color: #000;
  background-color: #fff;
  border: none;
  border-radius: 45px;
  box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease 0s;
  cursor: pointer;
  outline: none;
}

.btn-cancel {
  background-color: #fff;
}

.btn-cancel:hover {
  background-color: #1C1C1C;
  box-shadow: 0px 15px 20px rgba(28, 28, 28, 0.4);
  color: #fff;
  transform: translateY(-7px);
}

.btn-save {
  background-color: #fff;
}

.btn-save:hover {
  background-color: #FBA0E3;
  box-shadow: 0px 15px 20px rgba(251, 160, 227, 0.4);
  color: #fff;
  transform: translateY(-7px);
}

.modal-btn:active {
  transform: translateY(-1px);
}
</style>

</head>
<body>
<div class="container mx-auto my-[-20] px-4">
    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= $_SESSION['success'] ?></span>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <?php foreach ($errors as $error): ?>
                <p><?= $error ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Profile Section -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6 mt-[-20] p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Profil Saya</h2>

        <!-- Profile Image Section -->
        <div class="mb-6">
            <div class="flex items-center space-x-6">
                <div class="flex-shrink-0">
                    <?php if (!empty($user['profile_image'])): ?>
                        <img src="uploads/profile/<?= htmlspecialchars($user['profile_image']) ?>" 
                             alt="Current profile photo" 
                             class="h-24 w-24 object-cover rounded-full border-2 border-gray-200">
                    <?php else: ?>
                        <div class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center">
                            <span class="text-2xl text-gray-500">
                                <?= strtoupper(substr($user['username'], 0, 1)) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                <div style="width: 200px;">
    <div class="upload-container">
        <label class="custom-file-upload" style="width: 100%; height: 100%; position: absolute; z-index: 3; margin: 0; cursor: pointer;">
            <input type="file" id="profile_image" accept="image/*" style="display: none;"/>
        </label>
        <div class="folder">
            <div class="front-side">
                <div class="tip"></div>
                <div class="cover"></div>
            </div>
            <div class="back-side cover"></div>
        </div>
        <div style="margin-top: auto; font-size: 0.6em; color: #ffffff; text-align: center;">
            Choose File
        </div>
    </div>
    <p class="mt-3 text-sm text-gray-500">
        PNG, JPG, atau GIF (Maks. 100MB)
    </p>
</div>
            </div>
        </div>

        <!-- Profile Form -->
        <form method="POST" class="space-y-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                    Username
                </label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" 
       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">

            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                    Email
                </label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="new_password">
                    Password Baru <span class="text-gray-500 font-normal">(kosongkan jika tidak ingin mengubah)</span>
                </label>
                <div class="relative">
                    <input type="password" id="new_password" name="new_password" minlength="6"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                    <button type="button" id="toggleNewPassword" class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg id="eyeIconNew" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5C6.75 4.5 3 12 3 12s3.75 7.5 9 7.5 9-7.5 9-7.5-3.75-7.5-9-7.5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15.75a3 3 0 100-6 3 3 0 000 6z" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="confirm_password">
                    Konfirmasi Password Baru
                </label>
                <div class="relative">
                    <input type="password" id="confirm_password" name="confirm_password" minlength="6"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                    <button type="button" id="toggleConfirmPassword" class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg id="eyeIconConfirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5C6.75 4.5 3 12 3 12s3.75 7.5 9 7.5 9-7.5 9-7.5-3.75-7.5-9-7.5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15.75a3 3 0 100-6 3 3 0 000 6z" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="flex justify-end">
                <style>
                .save-btn {
                  padding: 1.3em 3em;
                  font-size: 12px;
                  text-transform: uppercase;
                  letter-spacing: 2.5px;
                  font-weight: 500;
                  color: #000;
                  background-color: #E6E6E6;
                  border: none;
                  border-radius: 45px;
                  box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.1);
                  transition: all 0.3s ease 0s;
                  cursor: pointer;
                  outline: none;
                }

                .save-btn:hover {
                  background-color: #77D777;
                  box-shadow: 0px 15px 20px rgba(35, 196, 131, 0.4);
                  color: #fff;
                  transform: translateY(-7px);
                }

                .save-btn:active {
                  transform: translateY(-1px);
                }

                .save-btn:disabled {
                  background-color: #e0e0e0;
                  cursor: not-allowed;
                  transform: none;
                  box-shadow: none;
                }

                .save-btn:disabled:hover {
                  background-color: #e0e0e0;
                  transform: none;
                  box-shadow: none;
                  color: #666;
                }
                </style>
                <button type="submit" name="update_profile" class="save-btn">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Crop -->
<div id="cropModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black opacity-50"></div>
        
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full">
            <div class="p-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Sesuaikan Foto Profil</h3>
            </div>
            
            <div class="p-4">
                <div class="max-h-96 overflow-hidden">
                    <img id="cropperImage" src="" alt="Image to crop">
                </div>
            </div>
            
            <div class="p-4 border-t flex justify-end space-x-3">
    <button type="button" id="cancelCrop" class="modal-btn btn-cancel">
        Batal
    </button>
    <button type="button" id="saveCrop" class="modal-btn btn-save">
        Simpan
    </button>
</div>
        </div>
    </div>
</div>

<script>
    // Toggle password visibility
    const togglePasswordVisibility = (inputId, iconId) => {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('fill', 'green');
        } else {
            input.type = 'password';
            icon.setAttribute('fill', 'gray');
        }
    };

    document.getElementById('toggleNewPassword').addEventListener('click', () => {
        togglePasswordVisibility('new_password', 'eyeIconNew');
    });

    document.getElementById('toggleConfirmPassword').addEventListener('click', () => {
        togglePasswordVisibility('confirm_password', 'eyeIconConfirm');
    });

    // Image cropper functionality
    let cropper;
    const modal = document.getElementById('cropModal');
    const cropperImage = document.getElementById('cropperImage');
    
    // Handle file input change
    document.getElementById('profile_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (max 100MB)
            if (file.size > 100 * 1024 * 1024) {
                alert('Ukuran file terlalu besar. Maksimal 100MB.');
                this.value = '';
                return;
            }
            
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Tipe file tidak didukung. Gunakan JPG, PNG, atau GIF.');
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                cropperImage.src = e.target.result;
                modal.classList.remove('hidden');
                
                // Initialize cropper
                if (cropper) {
                    cropper.destroy();
                }
                cropper = new Cropper(cropperImage, {
                    aspectRatio: 1,
                    viewMode: 2,
                    dragMode: 'move',
                    autoCropArea: 1,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                });
            };
            reader.readAsDataURL(file);
        }
    });

    // Cancel crop
    document.getElementById('cancelCrop').addEventListener('click', function() {
        modal.classList.add('hidden');
        document.getElementById('profile_image').value = '';
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    });

    // Save crop
    document.getElementById('saveCrop').addEventListener('click', function() {
    if (!cropper) return;

    const canvas = cropper.getCroppedCanvas({
        width: 400,
        height: 400,
        fillColor: '#fff',
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });
    
    canvas.toBlob(function(blob) {
        const formData = new FormData();
        formData.append('profile_image', blob, 'profile.jpg');
        
        // Show loading state
        const saveButton = document.getElementById('saveCrop');
        const originalText = saveButton.textContent;
        saveButton.textContent = 'Menyimpan...';
        saveButton.disabled = true;

        fetch('upload_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Tambahkan notifikasi sukses
                window.location.reload(); // Reload halaman setelah sukses
            } else {
                throw new Error(data.message || 'Terjadi kesalahan saat mengupload foto.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Jika error tapi bukan dari response server, tampilkan pesan generic
            if (!error.response) {
                window.location.reload(); // Reload jika kemungkinan berhasil tapi ada error di client
            } else {
                alert('Terjadi kesalahan saat mengupload foto.');
            }
        })
        .finally(() => {
            // Reset loading state
            saveButton.textContent = originalText;
            saveButton.disabled = false;
            
            modal.classList.add('hidden');
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        });
    }, 'image/jpeg', 0.9); // 90% quality JPEG
});

    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            document.getElementById('cancelCrop').click();
        }
    });

    // Prevent modal close when clicking modal content
    modal.querySelector('.relative').addEventListener('click', function(e) {
        e.stopPropagation();
    });
</script>

</body>
</html>