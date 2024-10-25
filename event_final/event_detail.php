<?php
ob_start(); // Start output buffering
session_start();
// event_detail.php
require_once('includes/config.php');
require_once('includes/functions.php');
require_once('includes/user_header.php');

// Ambil ID event dari parameter URL
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$event_id) {
    header('Location: index.php');
    exit;
}

try {
    // Ambil detail event dan jumlah pendaftar
    $stmt = $pdo->prepare("
        SELECT 
            e.*,
            (SELECT COUNT(*) FROM registrations WHERE event_id = e.id AND status = 'registered') as registered_count
        FROM events e
        WHERE e.id = ?
    ");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    
    if (!$event) {
        header('Location: index.php');
        exit;
    }

    // Cek apakah user sudah mendaftar
    $stmt = $pdo->prepare("
        SELECT status 
        FROM registrations 
        WHERE user_id = ? AND event_id = ? AND status = 'registered'
    ");
    $stmt->execute([$_SESSION['user_id'], $event_id]);
    $registration = $stmt->fetch();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Handle pendaftaran event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    try {
        // Cek apakah event masih tersedia
        if ($event['status'] !== 'open') {
            $_SESSION['error'] = "Maaf, event ini sudah tidak menerima pendaftaran.";
        } 
        // Cek kapasitas
        elseif ($event['registered_count'] >= $event['max_participants']) {
            $_SESSION['error'] = "Maaf, kapasitas event sudah penuh.";
        }
        // Cek apakah sudah terdaftar dan statusnya 'registered'
        elseif ($registration) {
            $_SESSION['error'] = "Anda sudah terdaftar pada event ini.";
        }
        else {
            // Cek apakah pernah mendaftar sebelumnya
            $stmt = $pdo->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ?");
            $stmt->execute([$_SESSION['user_id'], $event_id]);
            $existingRegistration = $stmt->fetch();
            
            if ($existingRegistration) {
                // Update status pendaftaran yang sudah ada
                $stmt = $pdo->prepare("
                    UPDATE registrations 
                    SET status = 'registered', registration_date = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$existingRegistration['id']]);
            } else {
                // Buat pendaftaran baru
                $stmt = $pdo->prepare("
                    INSERT INTO registrations (event_id, user_id, status, registration_date) 
                    VALUES (?, ?, 'registered', NOW())
                ");
                $stmt->execute([$event_id, $_SESSION['user_id']]);
            }
            
            $_SESSION['success'] = "Berhasil mendaftar event!";
            header("Location: registered_events.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Terjadi kesalahan saat mendaftar.";
    }
}

// Handle pembatalan pendaftaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel'])) {
    try {
        $stmt = $pdo->prepare("
            UPDATE registrations 
            SET status = 'canceled' 
            WHERE user_id = ? AND event_id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $event_id]);
        
        $_SESSION['success'] = "Pendaftaran event dibatalkan.";
        header("Location: registered_events.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Terjadi kesalahan saat membatalkan pendaftaran.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Detail - <?= htmlspecialchars($event['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
    /* Existing styles */
    .register-btn {
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
        width: 100%;
    }

    .register-btn:hover {
        background-color: #FBA0E3;
        box-shadow: 0px 15px 20px rgba(251, 160, 227, 0.4);
        color: #fff;
        transform: translateY(-7px);
    }

    .register-btn:active {
        transform: translateY(-1px);
    }

    .register-btn:disabled {
        background-color: #e0e0e0;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .register-btn:disabled:hover {
        background-color: #e0e0e0;
        transform: none;
        box-shadow: none;
        color: #666;
    }

    .del {
        position: relative;
        top: 0;
        left: 0;
        width: 160px;
        height: 50px;
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .del div {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        background: none;
        box-shadow: 4px 4px 6px 0 rgba(255,255,255,.5),
                    -4px -4px 6px 0 rgba(116, 125, 136, .5), 
            inset -4px -4px 6px 0 rgba(255,255,255,.2),
            inset 4px 4px 6px 0 rgba(0, 0, 0, .4);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 30px;
        letter-spacing: 1px;
        color: #ff0000;
        z-index: 1;
        transition: .6s;
    }

    .del:hover div {
        letter-spacing: 4px;
        color: #fff;
        background: #ff0000;
    }

    /* New back button styles */
    .back-btn {
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

    .back-btn:hover {
        color: #666;
        box-shadow:
            0px 8px 15px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .back-btn:active {
        color: #666;
        box-shadow:
            0px 0px 0px #c5c5c5,
            0px 0px 0px #ffffff,
            inset 4px 4px 12px #c5c5c5,
            inset -4px -4px 12px #ffffff;
    }

    /* Responsive Styling */
    @media (max-width: 640px) {
        .event-header-container {
            flex-direction: column;
            gap: 1rem;
        }
        
        .event-title-container {
            text-align: center;
        }
        
        .back-btn-container {
            text-align: center;
            width: 100%;
        }
        
        .back-btn {
            width: 100%;
            text-align: center;
            justify-content: center;
        }
    }
    </style>
</head>
<body>
<div class="max-w-4xl mx-auto">
    <!-- Alert Error/Success -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Event Detail Card -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden m-8">
        <!-- Banner Image -->
        <?php if ($event['banner_image']): ?>
            <img src="uploads/event_banners/<?= htmlspecialchars($event['banner_image']) ?>" 
                 alt="<?= htmlspecialchars($event['name']) ?>"
                 class="w-full h-64 object-cover">
        <?php endif; ?>

        <!-- Content -->
        <div class="p-6">
            <!-- Modified Header Section -->
            <div class="flex justify-between items-start mb-4 event-header-container">
                <div class="event-title-container">
                    <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($event['name']) ?></h1>
                    <div class="mt-2 flex items-center">
                        <?= get_event_status_badge($event['status']) ?>
                        <span class="ml-2 text-sm text-gray-600">
                            <?= htmlspecialchars($event['registered_count']) ?>/<?= htmlspecialchars($event['max_participants']) ?> peserta
                        </span>
                    </div>
                </div>
                <div class="back-btn-container">
                    <a href="index.php" class="back-btn">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Kembali ke Daftar Event
                        </div>
                    </a>
                </div>
            </div>

            <!-- Event Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="text-gray-600 font-semibold mb-2">Waktu & Lokasi</h3>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <?= format_tanggal($event['event_date']) ?>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <?= format_waktu($event['event_time']) ?>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <?= htmlspecialchars($event['location']) ?>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-gray-600 font-semibold mb-2">Kapasitas Event</h3>
                    <div class="w-full bg-[#e8e8e8] rounded-full h-2.5 mb-2 shadow-[inset_3px_3px_6px_#b2b2b2,inset_-3px_-3px_6px_#ffffff]">
                        <?php $percentage = min(($event['registered_count'] / $event['max_participants']) * 100, 100); ?>
                        <div class="bg-[#FBA0E3] h-2.5 rounded-full transition-all duration-300" style="width: <?= $percentage ?>%"></div>
                    </div>
                    <p class="text-sm text-gray-600">
                        Tersisa <?= htmlspecialchars($event['max_participants'] - $event['registered_count']) ?> slot
                    </p>
                </div>
            </div>

            <!-- Event Description -->
            <div class="mb-6">
                <h3 class="text-gray-600 font-semibold mb-2">Deskripsi Event</h3>
                <div class="prose max-w-none text-justify">
                    <?= nl2br(htmlspecialchars($event['description'])) ?>
                </div>
            </div>

            <!-- Registration Action -->
            <div class="border-t pt-6">
                <?php if ($registration && $registration['status'] === 'registered'): ?>
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="bg-green-50 w-full md:w-auto text-center md:text-left rounded-lg px-4 py-2">
                            <span class="text-green-600 font-medium">
                                Anda sudah terdaftar pada event ini
                            </span>
                        </div>
                        <form method="POST" class="w-full md:w-auto">
                            <button type="submit" name="cancel" 
                                    class="del w-full md:w-[160px]" 
                                    onclick="return confirm('Yakin ingin membatalkan pendaftaran?')">
                                <div>
                                    Batalkan Pendaftaran
                                </div>
                            </button>
                        </form>
                    </div>
                <?php elseif ($event['status'] === 'open' && $event['registered_count'] < $event['max_participants']): ?>
                    <form method="POST">
                        <button type="submit" name="register" class="register-btn">
                            Daftar Event
                        </button>
                    </form>
                <?php else: ?>
                    <button disabled class="register-btn">
                        <?= $event['status'] !== 'open' ? 'Event Tidak Tersedia' : 'Event Penuh' ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once('includes/footer.php'); ?>

<script>
// Add any additional JavaScript functionality here
document.addEventListener('DOMContentLoaded', function() {
    // Handle responsive back button
    const backBtn = document.querySelector('.back-btn');
    const updateBackBtnText = () => {
        const btnText = backBtn.querySelector('div');
        if (window.innerWidth <= 640) {
            btnText.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="ml-1">Kembali</span>
            `;
        } else {
            btnText.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Daftar Event
            `;
        }
    };

    // Initial call
    updateBackBtnText();
    
    // Update on window resize
    window.addEventListener('resize', updateBackBtnText);
});
</script>

</body>
</html>