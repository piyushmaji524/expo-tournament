<?php
require_once '../common/config.php';

$error = '';
$success = '';

// Handle logout message
if (isset($_GET['message']) && $_GET['message'] == 'logged_out') {
    $success = 'You have been successfully logged out.';
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php?message=logged_out');
    exit();
}

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: index.php');
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Expo Tournament</title>
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
<body class="bg-primary text-white select-none min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto bg-secondary rounded-lg shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="text-center py-8 bg-gradient-to-r from-accent to-highlight">
                <i class="fas fa-shield-alt text-white text-5xl mb-4"></i>
                <h1 class="text-3xl font-bold text-white">Admin Panel</h1>
                <p class="text-blue-100 mt-2">Expo Tournament Management</p>
            </div>

            <div class="p-6">
                <!-- Success Message -->
                <?php if ($success): ?>
                    <div class="bg-green-600 text-white p-4 rounded-lg mb-6 text-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Error Message -->
                <?php if ($error): ?>
                    <div class="bg-red-600 text-white p-4 rounded-lg mb-6 text-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">
                            <i class="fas fa-user mr-2"></i>Admin Username
                        </label>
                        <input type="text" name="username" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="Enter admin username">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">
                            <i class="fas fa-lock mr-2"></i>Password
                        </label>
                        <input type="password" name="password" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="Enter password">
                    </div>

                    <button type="submit" class="w-full bg-highlight hover:bg-red-600 transition text-white font-semibold py-3 px-4 rounded-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i>Admin Login
                    </button>
                </form>

                <div class="mt-6 text-center text-sm text-gray-400">
                    <p>Default Admin: <strong>admin</strong> / <strong>admin123</strong></p>
                </div>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="../login.php" class="text-gray-400 hover:text-white transition text-sm">
                <i class="fas fa-arrow-left mr-1"></i>Back to User Login
            </a>
        </div>
    </div>

    <script>
        // Disable right-click, text selection, and zoom
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('selectstart', e => e.preventDefault());
        document.addEventListener('wheel', e => { if (e.ctrlKey) e.preventDefault(); });
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && (e.keyCode === 65 || e.keyCode === 67 || e.keyCode === 86 || e.keyCode === 88 || e.keyCode === 90)) {
                e.preventDefault();
            }
            if (e.keyCode === 123 || (e.ctrlKey && e.shiftKey && e.keyCode === 73) || (e.ctrlKey && e.keyCode === 85)) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
