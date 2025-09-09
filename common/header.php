<?php
require_once 'config.php';
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get user wallet balance
$user_balance = getUserWalletBalance($_SESSION['user_id'], $pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expo Tournament - Gaming Platform</title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#e94560">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Expo Tournament">
    <meta name="msapplication-TileColor" content="#1a1a2e">
    <meta name="msapplication-tap-highlight" content="no">
    
    <!-- App Icons -->
    <link rel="icon" type="image/png" sizes="192x192" href="uploads/app-icon-192.png">
    <link rel="apple-touch-icon" sizes="192x192" href="uploads/app-icon-192.png">
    <link rel="shortcut icon" href="uploads/logo.png">
    
    <!-- Manifest -->
    <link rel="manifest" href="manifest.json">
    
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
                        <img src="uploads/logo.png" alt="Expo Tournament Logo" class="h-10 w-10 rounded-lg shadow-md" onerror="this.style.display='none'">
                    </div>
                    <h1 class="text-xl font-bold text-white">Expo Tournament</h1>
                </div>
                <div class="bg-accent px-3 py-1 rounded-full">
                    <span class="text-sm font-semibold">
                        <i class="fas fa-wallet mr-1"></i>
                        <?php echo formatCurrency($user_balance); ?>
                    </span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pb-20">
