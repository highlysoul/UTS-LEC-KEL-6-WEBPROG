<?php
// reset_password.php
require_once('includes/config.php');
require_once('includes/functions.php');

$errors = [];
$success = '';
$validToken = false;
$token = isset($_GET['token']) ? $_GET['token'] : '';
error_log("Received Token: " . $token); // Debug line

// Validate token
if (!empty($token)) {
    try {
        // Cek token tanpa kondisi untuk debug
        $stmt = $pdo->prepare("
            SELECT pr.*, u.email 
            FROM password_resets pr 
            JOIN users u ON pr.user_id = u.id 
            WHERE pr.token = ?
        ");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();
        
        if ($reset) {
            error_log("Token found with details:");
            error_log("Expiry: " . $reset['expiry']);
            error_log("Used: " . $reset['used']);
            error_log("Current time: " . date('Y-m-d H:i:s'));
            
            // Cek kenapa token tidak valid
            if ($reset['used'] == 1) {
                $errors[] = "Token sudah pernah digunakan.";
                error_log("Token already used");
            } 
            else if (strtotime($reset['expiry']) < time()) {
                $errors[] = "Token sudah kadaluarsa.";
                error_log("Token expired. Expiry: " . $reset['expiry'] . ", Current: " . date('Y-m-d H:i:s'));
            } 
            else {
                $validToken = true;
                error_log("Token is valid");
            }
        } else {
            $errors[] = "Token tidak ditemukan.";
            error_log("Token not found in database: " . $token);
        }
    } catch (PDOException $e) {
        $errors[] = "Terjadi kesalahan sistem.";
        error_log($e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate password
    if (strlen($password) < 6) {
        $errors[] = "Password harus minimal 8 karakter.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }
    
    if (empty($errors)) {
        try {
            // Begin transaction
            $pdo->beginTransaction();
            
            // Update password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $reset['user_id']]);
            
            // Mark token as used
            $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
            $stmt->execute([$token]);
            
            // Commit transaction
            $pdo->commit();
            
            $success = "Password berhasil direset. Silakan login dengan password baru Anda.";
            
            // Set session message for login page
            $_SESSION['reset_success'] = "Password berhasil direset. Silakan login dengan password baru Anda.";
            
            // Redirect after 3 seconds
            header("refresh:3;url=login.php?role=user");
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Terjadi kesalahan saat mereset password.";
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
    <title>Reset Password - Event Management</title>
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
                    <h1 class="text-3xl font-bold text-gray-900">Reset Password</h1>
                    <p class="text-gray-600 mt-2">Masukkan password baru Anda</p>
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
                        <p class="text-sm"><?= htmlspecialchars($success) ?></p>
                    </div>
                <?php else: ?>
                    <?php if ($validToken): ?>
                        <form method="POST" class="space-y-6">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Password Baru</label>
                                <div class="relative">
                                    <input type="password" id="password" name="password" required
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                                    <button type="button" class="toggle-password absolute inset-y-0 right-0 flex items-center pr-3"
                                            data-target="password">
                                        <svg class="eye-icon h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M12 4.5C6.75 4.5 3 12 3 12s3.75 7.5 9 7.5 9-7.5 9-7.5-3.75-7.5-9-7.5z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M12 15.75a3 3 0 100-6 3 3 0 000 6z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700">
                                    Konfirmasi Password Baru
                                </label>
                                <div class="relative">
                                    <input type="password" id="confirm_password" name="confirm_password" required
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                                    <button type="button" class="toggle-password absolute inset-y-0 right-0 flex items-center pr-3"
                                            data-target="confirm_password">
                                        <svg class="eye-icon h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M12 4.5C6.75 4.5 3 12 3 12s3.75 7.5 9 7.5 9-7.5 9-7.5-3.75-7.5-9-7.5z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M12 15.75a3 3 0 100-6 3 3 0 000 6z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700 transition-colors">
                                Reset Password
                            </button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            // Update icon path
            const eyeIcon = this.querySelector('.eye-icon');
            if (type === 'text') {
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M3 12c0 0 3.75-7.5 9-7.5s9 7.5 9 7.5-3.75 7.5-9 7.5S3 12 3 12z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 12a3 3 0 1 0 6 0 3 3 0 0 0 6z" />
                `;
            } else {
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 4.5C6.75 4.5 3 12 3 12s3.75 7.5 9 7.5 9-7.5 9-7.5-3.75-7.5-9-7.5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 15.75a3 3 0 100-6 3 3 0 000 6z" />
                `;
            }
        });
    });
    </script>
</body>
</html>

<?php
// Add these functions to includes/functions.php

/**
 * Generate secure random token
 * @param int $length Length of the token
 * @return string Secure random token
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Clean expired tokens from database
 * @param PDO $pdo Database connection
 */
function cleanExpiredTokens($pdo) {
    try {
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE expiry < NOW() OR used = 1");
        $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error cleaning expired tokens: " . $e->getMessage());
    }
}

/**
 * Send password reset email
 * @param string $to Recipient email
 * @param string $username Username
 * @param string $resetLink Reset link
 * @return bool Whether email was sent successfully
 */
function sendPasswordResetEmail($to, $username, $resetLink) {
    $subject = "Reset Password - Event Management";
    
    // HTML version of the email
    $htmlMessage = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #dc2626;'>Reset Password</h2>
            <p>Halo " . htmlspecialchars($username) . ",</p>
            <p>Kami menerima permintaan untuk reset password akun Anda.</p>
            <p>Klik tombol di bawah ini untuk melanjutkan reset password:</p>
            <p style='margin: 25px 0;'>
                <a href='" . htmlspecialchars($resetLink) . "' 
                   style='background-color: #dc2626; color: #ffffff; padding: 12px 24px; 
                          text-decoration: none; border-radius: 5px; display: inline-block;'>
                    Reset Password
                </a>
            </p>
            <p>Link ini akan kadaluarsa dalam 1 jam.</p>
            <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
            <hr style='border: 1px solid #eee; margin: 20px 0;'>
            <p style='font-size: 12px; color: #666;'>
                Email ini dikirim secara otomatis. Mohon tidak membalas email ini.
            </p>
        </div>
    </body>
    </html>";
    
    // Plain text version of the email
    $textMessage = "Halo " . $username . ",\n\n" .
                  "Kami menerima permintaan untuk reset password akun Anda.\n\n" .
                  "Klik link berikut untuk reset password:\n" .
                  $resetLink . "\n\n" .
                  "Link ini akan kadaluarsa dalam 1 jam.\n\n" .
                  "Jika Anda tidak meminta reset password, abaikan email ini.";
    
    // Email headers
    $headers = array(
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: Event Management <noreply@eventmanagement.com>',
        'Reply-To: no-reply@eventmanagement.com',
        'X-Mailer: PHP/' . phpversion()
    );
    
    // Send email
    return mail($to, $subject, $htmlMessage, implode("\r\n", $headers));
}

// Add these to login.php after session checks
if (isset($_SESSION['reset_success'])) {
    $success = $_SESSION['reset_success'];
    unset($_SESSION['reset_success']);
}