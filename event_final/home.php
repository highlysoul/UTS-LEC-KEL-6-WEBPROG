<?php
// home.php
require_once('includes/config.php');
require_once('includes/functions.php');
require_once('includes/user_header.php');

// Cek status foto profil user
$hasProfilePhoto = false;
try {
    $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $hasProfilePhoto = !empty($user['profile_image']);
} catch (PDOException $e) {
    // Handle error silently
}

// Tampilkan welcome message jika ada first_login flag
$showWelcome = isset($_SESSION['first_login']) && $_SESSION['first_login'] === true;

// Reset first_login flag setelah ditampilkan
if ($showWelcome) {
    $_SESSION['first_login'] = false;
}


// Fetch all open events
try {
    // Query for open events
    $stmt = $pdo->prepare("
        SELECT 
            e.*, 
            (SELECT COUNT(*) FROM registrations WHERE event_id = e.id AND status = 'registered') as registered_count 
        FROM events e 
        WHERE e.status = 'open' 
        ORDER BY e.event_date ASC
    ");
    $stmt->execute();
    $events = $stmt->fetchAll();

    // Fetch events registered by the user
    $stmt = $pdo->prepare("
        SELECT event_id 
        FROM registrations 
        WHERE user_id = ? AND status = 'registered'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $registered_events = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Fetch hot events
    $stmt = $pdo->prepare("
        SELECT e.*, COUNT(r.id) AS registered_count 
        FROM events e 
        LEFT JOIN registrations r ON e.id = r.event_id AND r.status = 'registered' 
        WHERE e.is_hot = 1 AND e.status = 'open' 
        GROUP BY e.id 
        ORDER BY registered_count DESC, e.event_date ASC
    ");
    $stmt->execute();
    $hot_events = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System</title>
    <link
      href="https://cdn.jsdelivr.net/npm/daisyui@2.6.0/dist/full.css"
      rel="stylesheet"
      type="text/css"
    />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>

    /* Overlay untuk welcome message */
    .welcome-text {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  font-size: clamp(2rem, 8vw, 4rem); /* Responsive font size */
  color: #1a1a1a;
  text-transform: uppercase;
  letter-spacing: clamp(0.2em, 2vw, 0.5em); /* Responsive letter spacing */
  opacity: 0;
  transform: translateY(20px);
  animation: textReveal 3s ease-in-out forwards;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
  text-align: center;
  padding: 0 1rem; /* Tambah padding untuk layar kecil */
  width: 100%; /* Pastikan text mengambil full width */
  max-width: 90vw; /* Batasi lebar maksimal */
}

/* Update overlay untuk memastikan posisi tengah sempurna */
.welcome-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  height: 100dvh; /* Support untuk mobile browser */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  opacity: 0;
  backdrop-filter: blur(8px);
  background: rgba(255, 255, 255, 0.1);
  animation: fadeInOut 3s ease-in-out forwards;
  padding: 1rem; /* Tambah padding untuk spacing */
}

/* Tambahkan media queries untuk penyesuaian khusus jika diperlukan */
@media screen and (max-width: 480px) {
  .welcome-text {
    font-size: clamp(1.5rem, 6vw, 2.5rem); /* Lebih kecil untuk mobile */
    letter-spacing: clamp(0.1em, 1.5vw, 0.3em);
  }
}

@media screen and (min-width: 1200px) {
  .welcome-text {
    font-size: 4rem; /* Ukuran maksimal untuk layar besar */
    letter-spacing: 0.5em;
  }
}

/* Profile photo prompt overlay */
.profile-prompt {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  opacity: 0;
  visibility: hidden;
  backdrop-filter: blur(8px);
  background: rgba(255, 255, 255, 0.1);
}

.profile-prompt.show {
  opacity: 1;
  visibility: visible;
  animation: fadeIn 0.5s ease-in-out forwards;
}

.prompt-card {
  background: rgba(255, 255, 255, 0.9);
  padding: 2rem;
  border-radius: 1rem;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  text-align: center;
  max-width: 400px;
  transform: translateY(20px);
  opacity: 0;
  animation: slideUp 0.5s ease-in-out 0.3s forwards;
}

.prompt-title {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  font-size: 1.5rem;
  margin-bottom: 1rem;
  color: #1a1a1a;
}

.prompt-button {
  background: #FBA0E3;
  color: white;
  border: none;
  padding: 0.75rem 2rem;
  border-radius: 9999px;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  cursor: pointer;
  transition: all 0.3s ease;
  margin: 0.5rem;
}

.prompt-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(251, 160, 227, 0.3);
}

.prompt-skip {
  background: transparent;
  color: #666;
}

@keyframes fadeInOut {
  0% { opacity: 0; }
  20% { opacity: 1; }
  80% { opacity: 1; }
  100% { opacity: 0; visibility: hidden; }
}

@keyframes textReveal {
  0% { 
    opacity: 0; 
    transform: translateY(clamp(10px, 2vw, 20px));
  }
  20% { 
    opacity: 1; 
    transform: translateY(0);
  }
  80% { 
    opacity: 1; 
    transform: translateY(0);
  }
  100% { 
    opacity: 0; 
    transform: translateY(clamp(-10px, -2vw, -20px));
  }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { 
    transform: translateY(20px);
    opacity: 0;
  }
  to { 
    transform: translateY(0);
    opacity: 1;
  }
}
    
    .btn-neumorphic {
        color: #090909;
        padding: 0.7em 1.7em;
        font-size: 10px;
        border-radius: 0.5em;
        background: #e8e8e8;
        cursor: pointer;
        border: 1px solid #e8e8e8;
        transition: all 0.3s;
        box-shadow:
            6px 6px 12px #c5c5c5,
            -6px -6px 12px #ffffff;
        text-decoration: none;
        display: inline-block;
    }

    .btn-neumorphic:active {
        color: #666;
        box-shadow:
            0px 0px 0px #c5c5c5,
            0px 0px 0px #ffffff,
            inset 4px 4px 12px #c5c5c5,
            inset -4px -4px 12px #ffffff;
    }

    .card-neumorphic {
        background: #e8e8e8 !important;
        border: 1px solid #e8e8e8;
    }

    .card-border-top {
        border-top: 1px solid #d1d1d1;
    }

    /* Status badge styles with inset neumorphic effect */
    .badge-neumorphic {
        padding: 0.7em 1.7em;
        font-size: 12px;
        border-radius: 0.5em;
        background: #e8e8e8;
        font-weight: 600;
        display: inline-block;
        box-shadow: inset -5px -5px 9px rgba(255,255,255,0.45), 
                    inset 3px 3px 6px rgba(94,104,121,0.3);
    }

    /* Open status */
    .badge-neumorphic-open {
        color: #4A7652 ; 
    }

    /* Full status */
    .badge-neumorphic-closed {
        color: #991b1b;
    }

    /* Closed status */
    .badge-neumorphic-canceled {
        color: #1E1E1E;
    }

.hot-event-badge {
    position: relative;
    background: linear-gradient(145deg, #FF9ED8, #FBA0E3, #FF83D5);  /* Gradient untuk efek metallic */
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    display: inline-block;
    box-shadow: 
        inset -2px -2px 6px rgba(255, 255, 255, 0.7),
        inset 2px 2px 6px rgba(174, 93, 156, 0.3),
        4px 4px 8px rgba(0, 0, 0, 0.1),
        -4px -4px 8px rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(251, 160, 227, 0.3);
    transition: all 0.3s ease;
}

/* Tambahkan efek hover */
.hot-event-badge:hover {
    box-shadow: 
        inset -1px -1px 4px rgba(255, 255, 255, 0.7),
        inset 1px 1px 4px rgba(174, 93, 156, 0.3),
        2px 2px 4px rgba(0, 0, 0, 0.1),
        -2px -2px 4px rgba(255, 255, 255, 0.9);
    transform: translateY(1px);
}

/* Tambahkan efek active */
.hot-event-badge:active {
    box-shadow: 
        inset 2px 2px 6px rgba(174, 93, 156, 0.4),
        inset -2px -2px 6px rgba(255, 255, 255, 0.8);
    transform: translateY(2px);
}

/* Tetap pertahankan style untuk text */
.hot-event-text {
    position: relative;
    background: linear-gradient(to right, 
        #9f9f9f 0%,
        #fff 10%,
        #868686 20%,
        #9f9f9f 30%
    );
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: smoothShine 2s linear infinite;
    font-weight: 600;
    font-size: 14px;
    white-space: nowrap;
    font-family: "Poppins", sans-serif;
}

@keyframes smoothShine {
    to {
        background-position: 200% center;
    }
}

@-webkit-keyframes smoothShine {
    to {
        background-position: 200% center;
    }
}

/* Hero styling */
.hero-title {
        font-family: 'Rajdhani', sans-serif;
        font-weight: 700;
        font-size: 3.5rem;
        letter-spacing: 2px;
        text-transform: lowercase;
        margin-bottom: 1.5rem;
        color: #333;
        position: relative;
    }

    .hero-subtitle {
        font-family: 'Rajdhani', sans-serif;
        font-size: 1.5rem;
        color: #666;
        position: relative;
        display: inline-block;
        padding-bottom: 0.5rem;
    }

    .hero-subtitle::before {
        content: '';
        width: 100%;
        height: 2px;
        border-radius: 2px;
        background-color: #666;
        position: absolute;
        bottom: -0.5rem;
        left: 0;
        transition: transform 0.4s, opacity 0.4s;
        opacity: 0;
    }

    .hero-subtitle:hover::before {
        transform: translateY(-0.25rem);
        opacity: 1;
    }

    /* Container untuk mengatur spacing */
    .hero-container {
        text-align: center;
        padding: 4rem 0;
        margin-bottom: 2rem;
    }

</style>
</head>
<body>

<!-- Welcome message overlay -->
<?php if ($showWelcome): ?>
<div class="welcome-overlay">
  <div class="welcome-text">Welcome</div>
</div>
<?php endif; ?>

<!-- Profile photo prompt -->
<div class="profile-prompt" id="profilePrompt">
  <div class="prompt-card">
    <h2 class="prompt-title">Ayo Pasang Foto Profil!</h2>
    <p class="mb-4 text-gray-600">Personalisasi profilmu dengan menambahkan foto profil</p>
    <button onclick="window.location.href='profile.php'" class="prompt-button">Pasang Foto</button>
    <button onclick="skipProfilePhoto()" class="prompt-button prompt-skip">Nanti Saja</button>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const hasProfilePhoto = <?php echo $hasProfilePhoto ? 'true' : 'false' ?>;
  
  // Jika user belum memiliki foto profil, tampilkan prompt setelah welcome message
  if (!hasProfilePhoto) {
    setTimeout(() => {
      const promptElement = document.getElementById('profilePrompt');
      promptElement.classList.add('show');
    }, 3500); // Tampilkan setelah welcome message selesai (3.5 detik)
  }
});

function skipProfilePhoto() {
  const promptElement = document.getElementById('profilePrompt');
  promptElement.style.animation = 'fadeOut 0.5s ease-in-out forwards';
  setTimeout(() => {
    promptElement.classList.remove('show');
  }, 500);
}
</script>

<!-- Main Container -->
<div class="container mx-auto px-4 py-8"> 
    <!-- Hero Section -->
    <div class="hero-container">
        <h1 class="hero-title">temukan event menarik</h1>
        <span class="hero-subtitle">Daftar dan ikuti berbagai event yang tersedia</span>
    </div>
</div>

<!-- Hot Events Section - Full Width -->
<?php if (count($hot_events) > 0): ?>

    <div class="text-center mb-4">
        <p class="font-mono text-gray-500 text-sm tracking-wide flex items-center justify-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 12 7.5 7.5" />
            </svg>
            SWIPE untuk melihat hot events tahun ini
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 12 7.5 7.5" />
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
        </p>
    </div>


    <div class="w-full mb-8"> <!-- Ubah dari card-neumorphic menjadi w-full -->
        <div class="carousel w-full h-96" id="hotEventsCarousel">
            <?php foreach ($hot_events as $index => $hot_event): ?>
                <div id="slide<?= $index + 1 ?>" class="carousel-item relative w-full">
                    <div class="relative w-full">
                        <a href="event_detail.php?id=<?= $hot_event['id'] ?>">
                            <?php if ($hot_event['banner_image']): ?>
                                <img src="uploads/event_banners/<?= htmlspecialchars($hot_event['banner_image']) ?>" 
                                     alt="<?= htmlspecialchars($hot_event['name']) ?>"
                                     class="w-full h-96 object-cover">
                            <?php else: ?>
                                <div class="w-full h-96 bg-gray-300 flex items-center justify-center">
                                    <span class="text-gray-500">No Image</span>
                                </div>
                            <?php endif; ?>
                            <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/70 to-transparent">
                                <h3 class="text-2xl font-bold text-white"><?= htmlspecialchars($hot_event['name']) ?></h3>
                            </div>
                            <div class="absolute top-4 right-4">
                                <div class="hot-event-badge">
                                    <span class="hot-event-text">HOT EVENT <?= date('Y') ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <div class="flex w-full justify-center gap-2 py-2 bg-white">
            <?php foreach ($hot_events as $index => $hot_event): ?>
                <a href="#slide<?= $index + 1 ?>" class="btn btn-xs"><?= $index + 1 ?></a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

    <div class="container mx-auto px-4 pb-8">
    <!-- Event List -->
    <h2 class="text-xl font-bold mb-4">Semua Event</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($events as $event): ?>
            <div class="card-neumorphic rounded-lg shadow-lg overflow-hidden flex flex-col">
                <?php if ($event['banner_image']): ?>
                    <img src="uploads/event_banners/<?= htmlspecialchars($event['banner_image']) ?>" 
                         alt="<?= htmlspecialchars($event['name']) ?>"
                         class="w-full h-48 object-cover">
                <?php else: ?>
                    <div class="w-full h-48 bg-gray-300 flex items-center justify-center">
                        <span class="text-gray-500">No Image</span>
                    </div>
                <?php endif; ?>
                <div class="p-6 flex-1 flex flex-col">
                    <h3 class="text-2xl font-bold mb-2"><?= htmlspecialchars($event['name']) ?></h3>
                    <div class="text-gray-600 space-y-1 flex-grow">
                        <div class="flex items-center mb-2">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <?= format_tanggal($event['event_date']) ?>
                        </div>
                        <div class="flex items-center mb-2">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <?= format_waktu($event['event_time']) ?>
                        </div>
                        <div class="flex items-center mb-2">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <?= htmlspecialchars($event['location']) ?>
                        </div>
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                            <?= $event['registered_count'] ?>/<?= $event['max_participants'] ?> peserta
                        </div>
                    </div>
                    <div class="mt-4">
                        <?= get_event_status_badge($event['status']) ?>
                    </div>
                </div>
                <div class="p-6 card-border-top text-center">
                    <a href="event_detail.php?id=<?= $event['id'] ?>" class="btn-neumorphic">Lihat Detail</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once('includes/footer.php'); ?>

</body>
</html>