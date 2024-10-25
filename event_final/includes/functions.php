<?php
// includes/functions.php

function format_tanggal($date) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $split = explode('-', $date);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

function format_waktu($time) {
    return date('H:i', strtotime($time)) . ' WIB';
}

function format_tanggal_waktu($datetime) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $timestamp = strtotime($datetime);
    $tanggal = date('d', $timestamp);
    $bulan_index = date('n', $timestamp);
    $tahun = date('Y', $timestamp);
    $waktu = date('H:i', $timestamp);
    
    return $tanggal . ' ' . $bulan[$bulan_index] . ' ' . $tahun . ' ' . $waktu . ' WIB';
}

// Fungsi-fungsi lainnya tetap sama
function get_registration_status_badge($status) {
    switch ($status) {
        case 'registered':
            return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Terdaftar</span>';
        case 'canceled':
            return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Dibatalkan</span>';
        default:
            return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>';
    }
}

function get_event_status_badge($status) {
    switch ($status) {
        case 'open':
            return '<span class="badge-neumorphic badge-neumorphic-open">Open</span>';
        case 'closed':
            return '<span class="badge-neumorphic badge-neumorphic-closed">Closed</span>';
        case 'canceled':
            return '<span class="badge-neumorphic badge-neumorphic-canceled">Canceled</span>';
            default:
            return '<span class="badge-neumorphic badge-neumorphic-open">' . ucfirst($status) . '</span>';
    }
}

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


// Tambahkan fungsi-fungsi ini ke includes/functions.php

/**
 * Generate CSRF token dan simpan di session
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifikasi CSRF token
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    if (!is_string($token)) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Regenerate CSRF token
 * Gunakan setelah aksi penting untuk mencegah replay attacks
 */
function regenerate_csrf_token() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

/**
 * Helper function untuk menampilkan input hidden CSRF
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generate_csrf_token()) . '">';
}
?>