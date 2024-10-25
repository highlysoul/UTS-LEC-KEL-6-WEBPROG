<?php
//user_header.php
$isLoggedIn = isset($_SESSION['user_id']);
$user = [];
if ($isLoggedIn) {
    $user = [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'profile_image' => $_SESSION['profile_image'] ?? ''
    ];
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <style>
        /* Base styles - ganti warna background */
    body {
        font-family: 'Arial', sans-serif;
        background-color: #e8e8e8;
    }

    /* Navbar background */
    nav {
        background: linear-gradient(to bottom,
            #ffffff 0%,
            #e6e6e6 20%,
            #cccccc 40%,
            #e6e6e6 80%,
            #ffffff 100%
        );
        box-shadow: 
            inset 0 1px 0 rgba(255,255,255,0.8),
            0 2px 4px rgba(0,0,0,0.1);
    }

        @media (max-width: 640px) {
            nav {
                padding: 0.75rem;
            }
        }

        nav a {
            font-family: monospace;
            text-decoration: none;
            color: white;
            transition: color 0.3s ease-in-out;
        }

        nav a:hover {
            color: #FBA0E3; /* Hover effect for links */
        }

        /* Add hover styles for Events and My Events */
.nav-link {
    padding: 0.5rem 1rem;
    transition: background-color 0.3s, color 0.3s;
    border-radius: 0.375rem; /* Rounded corners */
    display: inline-block; /* Ensure proper spacing */
}

/* Nav links hover */
.nav-link:hover,
    .nav-link.active {
        background: linear-gradient(to bottom,
            #ffffff 0%,
            #f2f2f2 50%,
            #e6e6e6 51%,
            #ffffff 100%
        );
        color: black;
        box-shadow: 
            inset 0 1px 0 rgba(255,255,255,1),
            0 2px 4px rgba(0,0,0,0.2);
    }

/* Glass Morphism Mobile Menu */

/* For mobile menu */

/* Mobile menu links hover */
#mobileMenu .absolute.right-0 a:hover {
    background: linear-gradient(to bottom,
        #ffffff 0%,
        #f2f2f2 100%
    );
    color: #FBA0E3;  /* Ini warna PINK ketika hover yang bisa diubah */
}

.mobile-menu-overlay {
    backdrop-filter: blur(8px);
    background-color: rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease-in-out;
    opacity: 0;
}

.mobile-menu-overlay.show {
    opacity: 1;
}

.mobile-menu-container {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border-left: 1px solid rgba(255, 255, 255, 0.5);
    transform: translateX(100%);
    transition: transform 0.3s ease-in-out;
}

.mobile-menu-container.show {
    transform: translateX(0);
}

/* Mobile menu items styling */
.mobile-menu-item {
    position: relative;
    transition: all 0.3s ease;
    background: transparent;
    overflow: hidden;
    font-weight: normal; /* Default font weight normal */
}

/* Hover state */
.mobile-menu-item:hover {
    background: rgba(255, 255, 255, 0.2);
    font-weight: bold;
}

.mobile-menu-item::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to right,
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.8) 50%,
        rgba(255, 255, 255, 0) 100%
    );
    transform: translateX(-100%);
    transition: transform 0.6s ease;
}

.mobile-menu-item:hover::after {
    transform: translateX(100%);
}

/* Active state */
.mobile-menu-item.active {
    background: rgba(255, 255, 255, 0.3);
    color: #000;
    font-weight: bold;
    box-shadow: 
        inset -2px -2px 6px rgba(255,255,255,0.7),
        inset 2px 2px 6px rgba(94,104,121,0.3);
}

        @media (max-width: 768px) {
            .mobile-menu {
                display: block;
            }
            
            .desktop-menu {
                display: none;
            }
        }

        .dropdown-menu {
            transform: scale(0.95);
            opacity: 0;
            transition: transform 0.1s ease-in-out, opacity 0.1s ease-in-out;
        }

        .dropdown-menu.show {
            transform: scale(1);
            opacity: 1;
        }

        .profile-dropdown {
            position: relative;
        }

        .profile-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background-color: white;
            border-radius: 0.375rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 0.5rem;
            min-width: 200px;
            z-index: 50;
        }

        .profile-menu.active {
            display: block;
        }

        .profile-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            cursor: pointer;
        }

        /* Styling untuk logo Event Management */
.brand-logo {
  margin: 0;
  height: auto;
  background: transparent;
  padding: 0;
  border: none;
  cursor: pointer;
  --border-right: 6px;
  --text-stroke-color: rgba(255,255,255,0.6);
  --animation-color: #50C878;
  --fs-size: 1.5em;
  letter-spacing: 3px;
  text-decoration: none;
  font-family: "Arial";
  position: relative;
  text-transform: uppercase;
  color: transparent;
  -webkit-text-stroke: 1px var(--text-stroke-color);
  white-space: nowrap;
}

.brand-logo .actual-text {
  color: transparent;
  -webkit-text-stroke: 1px var(--text-stroke-color);
}

.brand-logo .hover-text {
  position: absolute;
  box-sizing: border-box;
  content: attr(data-text);
  color: var(--animation-color);
  width: 0%;
  inset: 0;
  border-right: var(--border-right) solid var(--animation-color);
  overflow: hidden;
  transition: 0.5s;
  -webkit-text-stroke: 1px var(--animation-color);
}

/* Hover effect untuk desktop */
@media (hover: hover) {
  .brand-logo:hover .hover-text {
    width: 100%;
    filter: drop-shadow(0 0 23px var(--animation-color))
  }
}

/* Touch effect untuk mobile */
@media (hover: none) {
  .brand-logo:active .hover-text {
    width: 100%;
    filter: drop-shadow(0 0 23px var(--animation-color))
  }
}

/* Responsive font sizes */
@media (max-width: 1024px) {
  .brand-logo {
    --fs-size: 1.25em;
  }
}

@media (max-width: 768px) {
  .brand-logo {
    --fs-size: 1em;
  }
}

@media (max-width: 480px) {
  .brand-logo {
    --fs-size: 0.875em;
    letter-spacing: 2px;
  }
}

/* Add transition delay for touch devices */
@media (hover: none) {
  .brand-logo {
    transition-delay: 0.1s; /* Delay before navigation */
  }
  
  .brand-logo:active {
    pointer-events: none; /* Prevent immediate navigation */
  }
  
  /* Re-enable pointer events after animation */
  .brand-logo:active::after {
    content: '';
    position: absolute;
    inset: 0;
    pointer-events: auto;
    animation: enablePointer 0.5s step-end forwards;
  }
}

@keyframes enablePointer {
  from {
    pointer-events: none;
  }
  to {
    pointer-events: auto;
  }
}
    </style>
</head>

<body class="bg-gray-100">
    <nav class="sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
            <a href="home.php" class="brand-logo">
    <span class="actual-text">&nbsp;Event&nbsp;Management&nbsp;</span>
    <span aria-hidden="true" class="hover-text">&nbsp;Event&nbsp;Management&nbsp;</span>
</a>
                
                <!-- Desktop Menu -->
<div class="hidden md:flex items-center space-x-6">
    <?php if ($isLoggedIn): ?>
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        ?>
        <a href="index.php" class="font-mono nav-link text-white <?= $current_page === 'home.php' ? 'active' : '' ?>"><strong>Events</strong></a>
        <a href="registered_events.php" class="font-mono nav-link text-white <?= $current_page === 'registered_events.php' ? 'active' : '' ?>"><strong>My Events</strong></a>                        
                        <div class="profile-dropdown flex items-center">
                            <div class="profile-trigger flex items-center gap-2 cursor-pointer">
                                <span class="font-mono text-white">Hi, <?= htmlspecialchars($user['username']) ?></span>
                                <?php if (!empty($user['profile_image'])): ?>
                                    <img src="uploads/profile/<?= htmlspecialchars($user['profile_image']) ?>" 
                                         alt="Profile" 
                                         class="profile-image">
                                <?php else: ?>
                                    <div class="profile-image bg-gray-300 flex items-center justify-center">
                                        <span class="text-gray-600 text-xl">
                                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Dropdown Menu -->
                                <div class="profile-menu">
                                    <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        <div class="flex items-center gap-2">
                                            <?php if (!empty($user['profile_image'])): ?>
                                                <img src="uploads/profile/<?= htmlspecialchars($user['profile_image']) ?>" 
                                                     alt="Profile" 
                                                     class="w-8 h-8 rounded-full">
                                            <?php else: ?>
                                                <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-gray-600 text-xl">
                                                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                            Profile
                                        </div>
                                    </a>
                                    <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Logout
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="text-white hover:text-gray-200">Login</a>
                        <a href="register.php" class="bg-red-700 text-white px-4 py-2 rounded hover:bg-red-800">Register</a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button dan Menu -->
<div class="md:hidden">
    <button onclick="toggleDropdown()" class="text-white p-2 focus:outline-none hover:bg-white/10 rounded-lg transition-all duration-300">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
        </svg>
    </button>
    
    <!-- Mobile Menu -->
    <div id="mobileMenu" class="hidden fixed inset-0 z-50">
        <div class="mobile-menu-overlay absolute inset-0" onclick="toggleDropdown()"></div>
        <div class="mobile-menu-container absolute right-0 top-0 h-full w-64 py-4">
            <?php if ($isLoggedIn): ?>
                <div class="px-4 py-2 border-b border-white/20">
                    <div class="flex items-center gap-2">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="uploads/profile/<?= htmlspecialchars($user['profile_image']) ?>" 
                                 alt="Profile" 
                                 class="w-10 h-10 rounded-full shadow-lg">
                        <?php else: ?>
                            <div class="w-10 h-10 rounded-full bg-white/30 backdrop-blur flex items-center justify-center shadow-lg">
                                <span class="text-gray-800 text-xl">
                                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <span class="text-gray-800 font-medium">
                            Hi, <?= htmlspecialchars($user['username']) ?>
                        </span>
                    </div>
                </div>
                <!-- Mobile Menu Items -->
<a href="home.php" 
   class="font-mono mobile-menu-item block px-4 py-2 text-gray-800 <?= $current_page === 'home.php' ? 'active' : '' ?>">
    Events
</a>
<a href="registered_events.php" 
   class="font-mono mobile-menu-item block px-4 py-2 text-gray-800 <?= $current_page === 'registered_events.php' ? 'active' : '' ?>">
    My Events
</a>
<a href="profile.php" 
   class="font-mono mobile-menu-item block px-4 py-2 text-gray-800 <?= $current_page === 'profile.php' ? 'active' : '' ?>">
    Profile
</a>
<a href="logout.php" 
   class="font-mono mobile-menu-item block px-4 py-2 text-red-600">
    Logout
</a>    
        <?php else: ?>
        <a href="login.php" 
           class="font-mono mobile-menu-item block px-4 py-2 text-gray-800 <?= $current_page === 'login.php' ? 'active' : '' ?>">
            Login
        </a>
        <a href="register.php" 
           class="font-mono mobile-menu-item block px-4 py-2 text-red-600 <?= $current_page === 'register.php' ? 'active' : '' ?>">
            Register
        </a>            <?php endif; ?>
        </div>
    </div>
</div>
            </div>
        </div>
    </nav>

    <script>
        // Toggle mobile menu
        function toggleDropdown() {
    const mobileMenu = document.getElementById('mobileMenu');
    const overlay = mobileMenu.querySelector('.mobile-menu-overlay');
    const container = mobileMenu.querySelector('.mobile-menu-container');
    
    if (mobileMenu.classList.contains('hidden')) {
        mobileMenu.classList.remove('hidden');
        setTimeout(() => {
            overlay.classList.add('show');
            container.classList.add('show');
        }, 10);
    } else {
        overlay.classList.remove('show');
        container.classList.remove('show');
        setTimeout(() => {
            mobileMenu.classList.add('hidden');
        }, 300);
    }
}

        // Toggle profile menu
        function toggleProfileMenu(event) {
            event.stopPropagation();
            const profileMenu = document.querySelector('.profile-menu');
            profileMenu.classList.toggle('active');
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const mobileMenu = document.getElementById('mobileMenu');
            const menuButton = document.querySelector('[onclick="toggleDropdown()"]');
            
            if (!mobileMenu.contains(event.target) && !menuButton.contains(event.target)) {
                mobileMenu.classList.add('hidden');
            }
        });

        // Close profile menu when clicking outside
        document.addEventListener('click', function(event) {
            const profileMenu = document.querySelector('.profile-menu');
            const profileTrigger = document.querySelector('.profile-trigger');
            
            if (!profileTrigger.contains(event.target)) {
                profileMenu.classList.remove('active');
            }
        });

        // Add click event to profile trigger
        document.querySelector('.profile-trigger').addEventListener('click', toggleProfileMenu);
    </script>
</body>
</html>