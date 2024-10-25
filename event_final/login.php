<?php
// login.php
require_once('includes/config.php');
require_once('includes/functions.php');

// Get role from URL parameter
$role = isset($_GET['role']) ? $_GET['role'] : '';

// Validasi role
if (!in_array($role, ['admin', 'user'])) {
    header('Location: index.php');
    exit;
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/index.php');
    } else {
        header('Location: home.php');
    }
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $errors = [];

    if (empty($email)) {
        $errors[] = "Email harus diisi.";
    }
    if (empty($password)) {
        $errors[] = "Password harus diisi.";
    }

    if (empty($errors)) {
        try {
            // Tambahkan pengecekan role dan ambil semua data user termasuk profile_image
            $stmt = $pdo->prepare("SELECT id, username, password, role, profile_image FROM users WHERE email = ? AND role = ?");
            $stmt->execute([$email, $role]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['profile_image'] = $user['profile_image']; // Tambahkan ini

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: admin/index.php');
                } else {
                    header('Location: home.php');
                }
                exit;
            } else {
                $errors[] = "Email atau password salah.";
            }
        } catch (PDOException $e) {
            $errors[] = "Terjadi kesalahan saat login.";
        }
    }
}

// Set first login flag setiap kali user berhasil login
$_SESSION['first_login'] = true;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ucfirst($role) ?> Login - Event Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-red-600 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <!-- Ganti link brand logo yang ada dengan ini -->
            <?php if ($role === 'user'): ?>
    <a class="portfolio-experiment text-decoration-none">
        <span class="experiment-title">Event Management</span>
    </a>
<?php else: ?>
    <span class="text-white text-2xl font-bold">Event Management</span>
<?php endif; ?>

<style>
.portfolio-experiment {
    display: inline-block;
    text-decoration: none; /* Pastikan tidak ada underline dari link */
}

.experiment-title {
    color: #fff; /* Warna teks putih */
    font-size: 1.5rem; /* Sesuaikan dengan ukuran yang diinginkan */
    font-family: "Roboto", monospace;
    font-weight: bold;
    display: inline-block;
    animation: crazyText 10s infinite steps(50);
    outline: none;
}

@keyframes crazyText {
    2% { font-weight: 100; font-style: normal; text-decoration: none; text-transform: none; }
    4% { font-weight: 700; font-style: italic; text-decoration: line-through; text-transform: uppercase; }
    6% { font-weight: 300; font-style: normal; text-decoration: underline; text-transform: lowercase; }
    8% { font-weight: 500; font-style: italic; text-decoration: none; text-transform: capitalize; }
    10% { font-weight: 200; font-style: normal; text-decoration: line-through; text-transform: none; }
    12% { font-weight: 600; font-style: italic; text-decoration: none; text-transform: uppercase; }
    14% { font-weight: 400; font-style: normal; text-decoration: underline; text-transform: lowercase; }
    16% { font-weight: 700; font-style: italic; text-decoration: none; text-transform: none; }
    18% { font-weight: 100; font-style: normal; text-decoration: line-through; text-transform: capitalize; }
    20% { font-weight: 500; font-style: italic; text-decoration: none; text-transform: uppercase; }
    22% { font-weight: 300; font-style: normal; text-decoration: underline; text-transform: none; }
    24% { font-weight: 600; font-style: italic; text-decoration: none; text-transform: lowercase; }
    26% { font-weight: 200; font-style: normal; text-decoration: line-through; text-transform: capitalize; }
    28% { font-weight: 700; font-style: italic; text-decoration: none; text-transform: uppercase; }
    30% { font-weight: 400; font-style: normal; text-decoration: underline; text-transform: none; }
    32% { font-weight: 100; font-style: italic; text-decoration: none; text-transform: lowercase; }
    34% { font-weight: 500; font-style: normal; text-decoration: line-through; text-transform: capitalize; }
    36% { font-weight: 300; font-style: italic; text-decoration: none; text-transform: uppercase; }
    38% { font-weight: 600; font-style: normal; text-decoration: underline; text-transform: none; }
    40% { font-weight: 200; font-style: italic; text-decoration: none; text-transform: lowercase; }
    42% { font-weight: 700; font-style: normal; text-decoration: line-through; text-transform: capitalize; }
    44% { font-weight: 400; font-style: italic; text-decoration: none; text-transform: uppercase; }
    46% { font-weight: 100; font-style: normal; text-decoration: underline; text-transform: none; }
    48% { font-weight: 500; font-style: italic; text-decoration: none; text-transform: lowercase; }
    50% { font-weight: 300; font-style: normal; text-decoration: line-through; text-transform: capitalize; }
    52% { font-weight: 600; font-style: italic; text-decoration: none; text-transform: uppercase; }
    54% { font-weight: 200; font-style: normal; text-decoration: underline; text-transform: none; }
    56% { font-weight: 700; font-style: italic; text-decoration: none; text-transform: lowercase; }
    58% { font-weight: 400; font-style: normal; text-decoration: line-through; text-transform: capitalize; }
    60% { font-weight: 100; font-style: italic; text-decoration: none; text-transform: uppercase; }
    62% { font-weight: 500; font-style: normal; text-decoration: underline; text-transform: none; }
    64% { font-weight: 300; font-style: italic; text-decoration: none; text-transform: lowercase; }
    66% { font-weight: 600; font-style: normal; text-decoration: line-through; text-transform: capitalize; }
    68% { font-weight: 200; font-style: italic; text-decoration: none; text-transform: uppercase; }
    70% { font-weight: 700; font-style: normal; text-decoration: underline; text-transform: none; }
    72% { font-weight: 400; font-style: italic; text-decoration: none; text-transform: lowercase; }
    74% { font-weight: 100; font-style: normal; text-decoration: line-through; text-transform: capitalize; }
    76% { font-weight: 500; font-style: italic; text-decoration: none; text-transform: uppercase; }
    78% { font-weight: 300; font-style: normal; text-decoration: underline; text-transform: none; }
    80% { font-weight: 600; font-style: italic; text-decoration: none; text-transform: lowercase; }
    82% { font-weight: 200; font-style: normal; text-decoration: line-through; text-transform: capitalize; }
    84% { font-weight: 700; font-style: italic; text-decoration: none; text-transform: uppercase; }
    86% { font-weight: 400; font-style: normal; text-decoration: underline; text-transform: none; }
    88% { font-weight: 100; font-style: italic; text-decoration: none; text-transform: lowercase; }
    90% { font-weight: 500; font-style: normal; text-decoration: line-through; text-transform: capitalize; }
    92% { font-weight: 300; font-style: italic; text-decoration: none; text-transform: uppercase; }
    94% { font-weight: 600; font-style: normal; text-decoration: underline; text-transform: none; }
    96% { font-weight: 200; font-style: italic; text-decoration: none; text-transform: lowercase; }
    98% { font-weight: 700; font-style: normal; text-decoration: line-through; text-transform: capitalize; }
    100% { font-weight: bold; font-style: normal; text-decoration: none; text-transform: none; }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .experiment-title {
        font-size: 1.2rem;
    }
}
</style>
            <a href="index.php" class="text-white hover:text-gray-200">Kembali</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="flex-grow flex items-center justify-center p-4">
        <div class="max-w-md w-full">
            <!-- Login Card -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">
                        Login <?= ucfirst($role) ?>
                    </h1>
                    <p class="text-gray-600 mt-2">
                        <?= $role === 'admin' ? 'Masuk sebagai administrator' : 'Masuk untuk mendaftar event' ?>
                    </p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <?php foreach ($errors as $error): ?>
                            <p class="text-sm"><?= $error ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['register_success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?= $_SESSION['register_success'] ?></span>
    </div>
    <?php unset($_SESSION['register_success']); ?>
<?php endif; ?>

                <form method="POST" class="space-y-6">
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
                            <input type="password" id="password" name="password" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5C6.75 4.5 3 12 3 12s3.75 7.5 9 7.5 9-7.5 9-7.5-3.75-7.5-9-7.5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15.75a3 3 0 100-6 3 3 0 000 6z" />
                                </svg>
                            </button>
                        </div>
                        <?php if ($role === 'user'): ?>
        <div class="mt-1 text-right">
            <a href="forgot_password.php" class="text-sm font-medium text-red-600 hover:text-red-500">
                Lupa Password?
            </a>
        </div>
    <?php endif; ?>
</div>
                    </div>

                    <div>
                    <div>
    <?php if ($role === 'user'): ?>
        <button type="submit" class="glitch-button">
            Login
            <span class="glitch-effect">Login</span>
        </button>
    <?php else: ?>
        <button type="submit" class="w-full bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700 transition-colors">
            Login
        </button>
    <?php endif; ?>
</div>

<style>
.glitch-button,
.glitch-button .glitch-effect {
  padding: 12px 20px;
  font-size: 16px;
  width: 100%;
  background: linear-gradient(45deg, transparent 5%, rgb(220, 38, 38) 5%);
  border: 0;
  color: #fff;
  letter-spacing: 2px;
  line-height: 1;
  box-shadow: 6px 0px 0px #ff9999;
  outline: transparent;
  position: relative;
  border-radius: 6px;
  font-weight: 500;
}

.glitch-button .glitch-effect {
  --slice-0: inset(50% 50% 50% 50%);
  --slice-1: inset(80% -6px 0 0);
  --slice-2: inset(50% -6px 30% 0);
  --slice-3: inset(10% -6px 85% 0);
  --slice-4: inset(40% -6px 43% 0);
  --slice-5: inset(80% -6px 5% 0);
  content: "Login";
  display: block;
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(
    45deg,
    transparent 3%,
    #ff9999 3%,
    #ff9999 5%,
    rgb(220, 38, 38) 5%
  );
  text-shadow: -3px -3px 0px #991b1b, 3px 3px 0px #ffcccc;
  clip-path: var(--slice-0);
  border-radius: 6px;
}

.glitch-button:hover .glitch-effect {
  animation: 1s glitch;
  animation-timing-function: steps(2, end);
}

@keyframes glitch {
  0% {
    clip-path: var(--slice-1);
    transform: translate(-20px, -10px);
  }
  10% {
    clip-path: var(--slice-3);
    transform: translate(10px, 10px);
  }
  20% {
    clip-path: var(--slice-1);
    transform: translate(-10px, 10px);
  }
  30% {
    clip-path: var(--slice-3);
    transform: translate(0px, 5px);
  }
  40% {
    clip-path: var(--slice-2);
    transform: translate(-5px, 0px);
  }
  50% {
    clip-path: var(--slice-3);
    transform: translate(5px, 0px);
  }
  60% {
    clip-path: var(--slice-4);
    transform: translate(5px, 10px);
  }
  70% {
    clip-path: var(--slice-2);
    transform: translate(-10px, 10px);
  }
  80% {
    clip-path: var(--slice-5);
    transform: translate(20px, -10px);
  }
  90% {
    clip-path: var(--slice-1);
    transform: translate(-10px, 0px);
  }
  100% {
    clip-path: var(--slice-1);
    transform: translate(0);
  }
}

.glitch-button:hover {
  box-shadow: 8px 0px 0px #ff9999;
}

/* Focus state for accessibility */
.glitch-button:focus {
  outline: 2px solid #ff9999;
  outline-offset: 2px;
}

/* Active state */
.glitch-button:active {
  transform: translateY(1px);
  box-shadow: 4px 0px 0px #ff9999;
}
</style>
                    </div>
                </form>

                <?php if ($role === 'user'): ?>
                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600">
                            Belum punya akun? 
                            <a href="register.php" class="font-medium text-red-600 hover:text-red-500">
                                Daftar Sekarang
                            </a>
                        </p>
                    </div>
                <?php endif; ?>
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
</script>
