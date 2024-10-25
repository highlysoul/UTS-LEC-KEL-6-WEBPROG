<?php
// forgot_password.php
require_once('includes/config.php');
require_once('includes/functions.php');

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    } else {
        try {
            // Check if email exists and user role is 'user'
            $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ? AND role = 'user'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            // Modifikasi bagian pengiriman email di forgot-password.php

            if ($user) {
                try {
                    // Begin transaction
                    $pdo->beginTransaction();
                    
                    // Hapus token lama untuk user ini
                    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
                    $stmt->execute([$user['id']]);
                    
                    // Generate token baru
$token = bin2hex(random_bytes(32));
// Set expiry 1 jam dari sekarang dengan timezone yang benar
date_default_timezone_set('Asia/Jakarta'); // Sesuaikan dengan timezone Anda
$expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

error_log("Creating new token with expiry: " . $expiry);

// Store token baru
$stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expiry, used) VALUES (?, ?, ?, 0)");
$stmt->execute([$user['id'], $token, $expiry]);                    
                    // Commit transaction
                    $pdo->commit();
                    
                    // Buat reset link dengan encode URL yang benar
                    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
                    $resetLink = $baseUrl . str_replace(' ', '%20', dirname($_SERVER['PHP_SELF'])) . "/reset_password.php?token=" . urlencode($token);
                    
                    // Set success message dengan format yang benar
                    $success = sprintf(
                        'Link reset password telah dibuat. Klik link berikut untuk reset password Anda: 
                        <div class="mt-2">
                            <a href="%s" class="text-red-600 hover:text-red-500 underline">
                                Reset Password
                            </a>
                        </div>
                        <div class="mt-2 text-sm text-gray-600">
                            (Link ini hanya akan aktif selama 1 jam)
                        </div>',
                        htmlspecialchars($resetLink)
                    );
            
                    // Debug: Tampilkan token untuk memastikan
                    error_log("Generated Token: " . $token);
                    
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $errors[] = "Terjadi kesalahan sistem.";
                    error_log($e->getMessage());
                }
            } else {
                // Ubah dari success message menjadi error message
                $errors[] = "Email Belum Terdaftar";
            }
        } catch (PDOException $e) {
            $errors[] = "Terjadi kesalahan sistem.";
            error_log($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Event Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-red-600 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-white text-2xl font-bold">Event Management</a>
            <a href="login.php?role=user" class="text-white hover:text-gray-200">Kembali ke Login</a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex-grow flex items-center justify-center p-4">
        <div class="max-w-md w-full">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Lupa Password</h1>
                    <p class="text-gray-600 mt-2">Masukkan email Anda untuk reset password</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                        <?php foreach ($errors as $error): ?>
                            <p class="text-sm"><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
        <p class="text-sm"><?= $success ?></p>
    </div>
<?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                    </div>

                    <button type="submit" class="w-full bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700 transition-colors">
                        Reset Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>