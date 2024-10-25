<?php
// register.php
require_once('includes/config.php');
require_once('includes/functions.php');

// Get role from URL
$role = isset($_GET['role']) ? $_GET['role'] : 'user';

// Validasi role
if (!in_array($role, ['admin', 'user'])) {
    header('Location: index.php');
    exit;
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $errors = [];

    // Validasi input
    if (empty($username)) {
        $errors[] = "Username harus diisi.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username hanya boleh berisi huruf, angka, dan underscore.";
    }

    if (empty($email)) {
        $errors[] = "Email harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }

    if (empty($password)) {
        $errors[] = "Password harus diisi.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }

    // Check if username or email already exists
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Username atau email sudah digunakan.";
            }
        } catch (PDOException $e) {
            $errors[] = "Terjadi kesalahan saat memeriksa data.";
        }
    }

    // Register user if no errors
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Gunakan role dari parameter URL
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password, role) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$username, $email, $hashed_password, $role]);

            $_SESSION['register_success'] = "Registrasi berhasil! Silakan login.";
            header('Location: login.php?role=' . $role);
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Terjadi kesalahan saat mendaftar.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register <?= ucfirst($role) ?> - Event Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-red-600 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <span class="text-white text-2xl font-bold">Event Management</span>
            <a href="index.php" class="text-white hover:text-gray-200">Kembali</a>
        </div>
    </nav>

    <!-- Register Form -->
    <div class="flex-grow flex items-center justify-center p-4">
        <div class="max-w-md w-full">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Register <?= ucfirst($role) ?></h1>
                    <p class="text-gray-600 mt-2">
                        <?= $role === 'admin' ? 'Daftar sebagai administrator' : 'Daftar untuk mulai mengikuti event' ?>
                    </p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <?php foreach ($errors as $error): ?>
                            <p class="text-sm"><?= $error ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">
                            Username
                        </label>
                        <input type="text" id="username" name="username" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                               value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email
                        </label>
                        <input type="email" id="email" name="email" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required minlength="6"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5C6.75 4.5 3 12 3 12s3.75 7.5 9 7.5 9-7.5 9-7.5-3.75-7.5-9-7.5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15.75a3 3 0 100-6 3 3 0 000 6z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">
                            Konfirmasi Password
                        </label>
                        <div class="relative">
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                            <button type="button" id="toggleConfirmPassword" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg id="eyeIconConfirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5C6.75 4.5 3 12 3 12s3.75 7.5 9 7.5 9-7.5 9-7.5-3.75-7.5-9-7.5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15.75a3 3 0 100-6 3 3 0 000 6z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Register
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Sudah punya akun? 
                        <a href="login.php?role=<?= $role ?>" class="font-medium text-red-600 hover:text-red-500">
                            Login
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<script>
document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    // Toggle the type attribute
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);

    // Toggle the eye icon
    eyeIcon.setAttribute('d', type === 'password' ? 'M12 4.5C6.75 4.5 3 12 3 12s3.75 7.5 9 7.5 9-7.5 9-7.5-3.75-7.5-9-7.5z' : 'M12 15.75a3 3 0 100-6 3 3 0 000 6z M12 4.5C6.75 4.5 3 12 3 12s3.75 7.5 9 7.5 9-7.5 9-7.5-3.75-7.5-9-7.5z');
});

document.getElementById('toggleConfirmPassword').addEventListener('click', function () {
    const confirmPasswordInput = document.getElementById('confirm_password');
    const eyeIconConfirm = document.getElementById('eyeIconConfirm');

    // Toggle the type attribute
    const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    confirmPasswordInput.setAttribute('type', type);

    // Toggle the eye icon
    eyeIconConfirm.setAttribute('d', type === 'password' ? 'M12 4.5C6.75 4.5 3 12 3 12s3.75 7.5 9 7.5 9-7.5 9-7.5-3.75-7.5-9-7.5z' : 'M12 15.75a3 3 0 100-6 3 3 0 000 6z M12 4.5C6.75 4.5 3 12 3 12s3.75 7.5 9 7.5 9-7.5 9-7.5-3.75-7.5-9-7.5z');
});
</script>
