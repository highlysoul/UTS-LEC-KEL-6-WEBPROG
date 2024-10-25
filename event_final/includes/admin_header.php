<?php
// admin/includes/header.php

// Cek session admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Event Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Navbar -->
    <nav class="bg-red-600 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="text-white text-2xl font-bold">Event Management</div>
            <div class="flex items-center gap-4">
                <span class="text-white">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="../logout.php" class="bg-red-700 text-white px-4 py-2 rounded">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Content Container -->
    <div class="container mx-auto my-8 px-4">
        