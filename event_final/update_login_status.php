<?php
session_start();
require_once('includes/config.php');

// Update session
$_SESSION['first_login'] = false;

// Kirim response
header('Content-Type: application/json');
echo json_encode(['success' => true]);