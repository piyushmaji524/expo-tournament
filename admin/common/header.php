<?php
require_once '../common/config.php';

// Handle admin logout
if (isset($_GET['logout'])) {
    session_start();
    unset($_SESSION['admin_id']);
    session_destroy();
    header('Location: login.php?message=logged_out');
    exit();
}

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Expo Tournament</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1a1a2e',
                        secondary: '#16213e',
                        accent: '#0f3460',
                        highlight: '#e94560'
                    }
                }
            }
        }
    </script>
    <style>
        body {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
            -webkit-tap-highlight-color: transparent;
        }
        
        * {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
    </style>
</head>
<body class="bg-primary text-white select-none overflow-x-hidden">
    <!-- Header -->
    <header class="bg-secondary shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2">
                        <img src="../uploads/logo.png" alt="Expo Tournament Logo" class="h-10 w-10 rounded-lg shadow-md" onerror="this.style.display='none'">
                    </div>
                    <h1 class="text-xl font-bold text-white">Expo Tournament - Admin</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm">Welcome, Admin</span>
                    <a href="?logout=1" onclick="return confirm('Are you sure you want to logout?')" class="bg-highlight px-3 py-1 rounded text-sm hover:bg-red-600 transition">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="bg-accent shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex space-x-1 overflow-x-auto">
                <a href="index.php" class="px-4 py-3 text-sm whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-highlight text-white' : 'text-gray-300 hover:bg-secondary'; ?> transition">
                    <i class="fas fa-dashboard mr-1"></i>Dashboard
                </a>
                <a href="tournament.php" class="px-4 py-3 text-sm whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'tournament.php' ? 'bg-highlight text-white' : 'text-gray-300 hover:bg-secondary'; ?> transition">
                    <i class="fas fa-trophy mr-1"></i>Tournaments
                </a>
                <a href="user.php" class="px-4 py-3 text-sm whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'user.php' ? 'bg-highlight text-white' : 'text-gray-300 hover:bg-secondary'; ?> transition">
                    <i class="fas fa-users mr-1"></i>Users
                </a>
                <a href="banners.php" class="px-4 py-3 text-sm whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'banners.php' ? 'bg-highlight text-white' : 'text-gray-300 hover:bg-secondary'; ?> transition">
                    <i class="fas fa-images mr-1"></i>Banners
                </a>
                <a href="deposits.php" class="px-4 py-3 text-sm whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'deposits.php' ? 'bg-highlight text-white' : 'text-gray-300 hover:bg-secondary'; ?> transition">
                    <i class="fas fa-arrow-down mr-1"></i>Deposits
                </a>
                <a href="withdrawals.php" class="px-4 py-3 text-sm whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'withdrawals.php' ? 'bg-highlight text-white' : 'text-gray-300 hover:bg-secondary'; ?> transition">
                    <i class="fas fa-arrow-up mr-1"></i>Withdrawals
                </a>
                <a href="referral.php" class="px-4 py-3 text-sm whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'referral.php' ? 'bg-highlight text-white' : 'text-gray-300 hover:bg-secondary'; ?> transition">
                    <i class="fas fa-users mr-1"></i>Referrals
                </a>
                <a href="legal_pages.php" class="px-4 py-3 text-sm whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'legal_pages.php' ? 'bg-highlight text-white' : 'text-gray-300 hover:bg-secondary'; ?> transition">
                    <i class="fas fa-gavel mr-1"></i>Legal Pages
                </a>
                <a href="setting.php" class="px-4 py-3 text-sm whitespace-nowrap <?php echo basename($_SERVER['PHP_SELF']) == 'setting.php' ? 'bg-highlight text-white' : 'text-gray-300 hover:bg-secondary'; ?> transition">
                    <i class="fas fa-cog mr-1"></i>Settings
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-6">
