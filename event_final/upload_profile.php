<?php
//upload_profile.php
require_once('includes/config.php');
session_start();

// Set header to JSON
header('Content-Type: application/json');

// Konstanta untuk ukuran maksimal (dalam bytes)
define('MAX_FILE_SIZE', 100 * 1024 * 1024); // 100MB

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if (!isset($_FILES['profile_image'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'No file uploaded']));
}

try {
    $file = $_FILES['profile_image'];
    
    // Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        http_response_code(400);
        die(json_encode([
            'success' => false, 
            'message' => 'Ukuran file maksimal ' . (MAX_FILE_SIZE / (1024 * 1024)) . 'MB'
        ]));
    }

    // Validate file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file_type, $allowed)) {
        http_response_code(400);
        die(json_encode(['success' => false, 'message' => 'Tipe file harus JPG, PNG, GIF, atau WEBP']));
    }
    
    // Generate unique filename
    $filename = uniqid('profile_') . '.jpg';
    $upload_path = 'uploads/profile/' . $filename;
    
    // Ensure upload directory exists
    if (!file_exists('uploads/profile')) {
        mkdir('uploads/profile', 0777, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // Delete old profile image if exists
        $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $old_image = $stmt->fetchColumn();
        
        if ($old_image && file_exists('uploads/profile/' . $old_image)) {
            unlink('uploads/profile/' . $old_image);
        }
        
        // Update database
        $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        $stmt->execute([$filename, $_SESSION['user_id']]);
        
        // Update session
        $_SESSION['profile_image'] = $filename;
        
        http_response_code(200);
        echo json_encode([
            'success' => true, 
            'message' => 'Foto profil berhasil diupload'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal mengupload file']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}