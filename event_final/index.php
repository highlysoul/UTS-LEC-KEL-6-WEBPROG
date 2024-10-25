<?php
// index.php (landing page)
require_once('includes/config.php');

// Jika sudah login, redirect sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/index.php');
    } else {
        header('Location: home.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full bg-gray-100">
    <!-- Navbar -->
    <nav class="bg-red-600 p-4">
        <div class="container mx-auto">
            <span class="text-white text-2xl font-bold">Event Management</span>
        </div>
    </nav>

    <div class="min-h-[calc(100vh-64px)] flex items-center justify-center p-4">
        <div class="max-w-4xl w-full">
            <!-- Welcome Text -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Selamat Datang di Event Management</h1>
                <p class="text-xl text-gray-600">Silakan pilih cara masuk ke sistem</p>
            </div>

            <!-- Role Selection Cards -->
            <div class="grid md:grid-cols-2 gap-8 max-w-2xl mx-auto">
                <!-- Admin Card -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-8">
                        <div class="text-center mb-6">
                            <svg class="w-16 h-16 text-red-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h2 class="text-2xl font-bold text-gray-900">Admin</h2>
                            <p class="text-gray-600 mt-2">Masuk sebagai administrator sistem</p>
                        </div>
                        <div class="space-y-3">
                            <a href="login.php?role=admin" 
                               class="block w-full bg-red-600 text-white text-center py-2 px-4 rounded hover:bg-red-700">
                                Login Admin
                            </a>
                        </div>
                    </div>
                </div>

                <!-- User Card -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-8">
                        <div class="text-center mb-6">
                            <svg class="w-16 h-16 text-red-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <h2 class="text-2xl font-bold text-gray-900">User</h2>
                            <p class="text-gray-600 mt-2">Masuk sebagai pengguna biasa</p>
                        </div>
                        <div class="space-y-3">
                            <a href="login.php?role=user" class="ui-btn">
                                <span>Login User</span>
                            </a>
                            <a href="register.php?role=user" 
                               class="block w-full border border-red-600 text-red-600 text-center py-2 px-4 rounded hover:bg-red-50">
                                Register User
                            </a>
                        </div>
                    </div>
                </div>

<style>
.ui-btn {
  --btn-default-bg: rgb(220, 38, 38);
  --btn-padding: 12px 20px;
  --btn-hover-bg: rgb(185, 28, 28);
  --btn-transition: .3s;
  --btn-letter-spacing: .1rem;
  --btn-animation-duration: 1.2s;
  --btn-shadow-color: rgba(220, 38, 38, 0.2);
  --btn-shadow: 0 2px 10px 0 var(--btn-shadow-color);
  --hover-btn-color: #ffffff;
  --default-btn-color: #fff;
  --font-size: 14px;
  --font-weight: 600;
  --font-family: system-ui, -apple-system, sans-serif;
  width: 100%;
  text-decoration: none;
}

.ui-btn {
  box-sizing: border-box;
  padding: var(--btn-padding);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--default-btn-color);
  font: var(--font-weight) var(--font-size) var(--font-family);
  background: var(--btn-default-bg);
  cursor: pointer;
  transition: var(--btn-transition);
  overflow: hidden;
  box-shadow: var(--btn-shadow);
  border-radius: 8px;
  border: 2px solid transparent;
}

.ui-btn span {
  letter-spacing: var(--btn-letter-spacing);
  transition: var(--btn-transition);
  box-sizing: border-box;
  position: relative;
  background: inherit;
}

.ui-btn span::before {
  box-sizing: border-box;
  position: absolute;
  content: "";
  background: inherit;
}

.ui-btn:hover, .ui-btn:focus {
  background: var(--btn-default-bg);
  box-shadow: 0px 0px 10px 0px rgba(220, 38, 38, 0.7);
  border: 2px solid #ffffff;
}

.ui-btn:hover span, .ui-btn:focus span {
  color: var(--hover-btn-color);
}

.ui-btn:hover span::before, .ui-btn:focus span::before {
  animation: chitchat linear both var(--btn-animation-duration);
}

@keyframes chitchat {
  0% { content: "#"; }
  5% { content: "."; }
  10% { content: "^{"; }
  15% { content: "-!"; }
  20% { content: "#$_"; }
  25% { content: "№:0"; }
  30% { content: "#{+."; }
  35% { content: "@}-?"; }
  40% { content: "?{4@%"; }
  45% { content: "=.,^!"; }
  50% { content: "?2@%"; }
  55% { content: "\;1}]"; }
  60% { content: "?{%:%"; right: 0; }
  65% { content: "|{f[4"; right: 0; }
  70% { content: "{4%0%"; right: 0; }
  75% { content: "'1_0<"; right: 0; }
  80% { content: "{0%"; right: 0; }
  85% { content: "]>'"; right: 0; }
  90% { content: "4"; right: 0; }
  95% { content: "2"; right: 0; }
  100% { content: ""; right: 0; }
}
</style>
</body>
</html>