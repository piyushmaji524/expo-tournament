<?php
require_once 'common/config.php';

$error = '';
$success = '';
$activeTab = 'login';
$referralCode = '';
$referralReadonly = false;

// Handle referral parameter
if (isset($_GET['ref'])) {
    $referralCode = trim($_GET['ref']);
    $referralReadonly = true;
    $activeTab = 'signup'; // Switch to signup tab if coming from referral link
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Get admin referral code for "no refer code" button
$adminReferralCode = 'ADM001'; // Default fallback
try {
    if (function_exists('getReferralSettings')) {
        $referralSettings = getReferralSettings($pdo);
        $adminReferralCode = $referralSettings['admin_referral_code'] ?? 'ADM001';
    }
} catch (Exception $e) {
    // Use default if function fails
    $adminReferralCode = 'ADM001';
}

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        $activeTab = 'login';
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        if (empty($username) || empty($password)) {
            $error = 'Please fill in all fields.';
        } else {
            $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        }
    } elseif (isset($_POST['signup'])) {
        $activeTab = 'signup';
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $referralCode = trim($_POST['referral_code'] ?? '');
        
        if (empty($username) || empty($email) || empty($password)) {
            $error = 'Please fill in all fields.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $error = 'Username or email already exists.';
            } else {
                // Check referral code if provided
                $referrerId = null;
                if (!empty($referralCode)) {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE referral_id = ?");
                    $stmt->execute([$referralCode]);
                    $referrer = $stmt->fetch();
                    
                    if ($referrer) {
                        $referrerId = $referrer['id'];
                    } else {
                        $error = 'Invalid referral code.';
                    }
                }
                
                if (!$error) {
                    try {
                        $pdo->beginTransaction();
                        
                        // Create user account
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Generate referral ID (simple version)
                        $prefix = strtoupper(substr($username, 0, 3));
                        $suffix = str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
                        $referralId = $prefix . $suffix;
                        
                        // Ensure uniqueness
                        $attempts = 0;
                        while ($attempts < 10) {
                            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE referral_id = ?");
                            $checkStmt->execute([$referralId]);
                            if (!$checkStmt->fetch()) {
                                break; // ID is unique
                            }
                            $suffix = str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
                            $referralId = $prefix . $suffix;
                            $attempts++;
                        }
                        
                        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, wallet_balance, referral_id, referred_by) VALUES (?, ?, ?, 0.00, ?, ?)");
                        $stmt->execute([$username, $email, $hashed_password, $referralId, $referrerId]);
                        
                        $newUserId = $pdo->lastInsertId();
                        
                        // Process referral reward if there's a referrer
                        if ($referrerId) {
                            // Get referral settings
                            $referrerReward = 50; // Default
                            $referredReward = 25; // Default
                            
                            try {
                                $settingsStmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('referral_reward_referrer', 'referral_reward_referred')");
                                $settingsStmt->execute();
                                $settings = $settingsStmt->fetchAll();
                                
                                foreach ($settings as $setting) {
                                    if ($setting['setting_key'] == 'referral_reward_referrer') {
                                        $referrerReward = (float)$setting['setting_value'];
                                    } elseif ($setting['setting_key'] == 'referral_reward_referred') {
                                        $referredReward = (float)$setting['setting_value'];
                                    }
                                }
                            } catch (Exception $e) {
                                // Use defaults if settings not found
                            }
                            
                            // Add rewards to both users
                            $updateReferrerStmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ?, total_referrals = total_referrals + 1, referral_earnings = referral_earnings + ? WHERE id = ?");
                            $updateReferrerStmt->execute([$referrerReward, $referrerReward, $referrerId]);
                            
                            $updateReferredStmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
                            $updateReferredStmt->execute([$referredReward, $newUserId]);
                            
                            // Add transaction records
                            $transactionStmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'credit', ?)");
                            $transactionStmt->execute([$referrerId, $referrerReward, 'Referral reward - New user referred']);
                            $transactionStmt->execute([$newUserId, $referredReward, 'Referral welcome bonus']);
                            
                            // Insert referral record
                            $referralStmt = $pdo->prepare("INSERT INTO referrals (referrer_id, referred_id, referrer_reward, referred_reward) VALUES (?, ?, ?, ?)");
                            $referralStmt->execute([$referrerId, $newUserId, $referrerReward, $referredReward]);
                        }
                        
                        $pdo->commit();
                        $success = 'Account created successfully! You can now login.' . ($referrerId ? ' Referral bonus has been added to your account!' : '');
                        $activeTab = 'login';
                    } catch (Exception $e) {
                        if ($pdo->inTransaction()) {
                            $pdo->rollback();
                        }
                        $error = 'Failed to create account. Error: ' . $e->getMessage();
                    }
                }
            }
        }
    }
}

// Set active tab based on URL parameter
if (isset($_GET['tab']) && $_GET['tab'] == 'signup') {
    $activeTab = 'signup';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Expo Tournament</title>
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

        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body class="bg-primary text-white select-none min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto bg-secondary rounded-lg shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="text-center py-8 bg-gradient-to-r from-accent to-highlight">
                <!-- Logo -->
                <div class="mb-4">
                    <img src="uploads/logo.png" alt="ColorBuzz Logo" class="w-20 h-20 mx-auto rounded-full shadow-lg bg-white p-2" onerror="this.style.display='none'; document.getElementById('fallback-logo').style.display='block';">
                    <i id="fallback-logo" class="fas fa-trophy text-white text-5xl" style="display: none;"></i>
                </div>
                <h1 class="text-3xl font-bold text-white">Expo Tournament</h1>
                <p class="text-blue-100 mt-2">Ultimate Gaming & Tournament Platform</p>
            </div>

            <!-- Tabs -->
            <div class="flex bg-accent">
                <button onclick="switchTab('login')" id="loginTab" class="flex-1 py-3 px-4 text-center font-semibold transition <?php echo $activeTab == 'login' ? 'bg-highlight text-white' : 'text-gray-300 hover:bg-secondary'; ?>">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </button>
                <button onclick="switchTab('signup')" id="signupTab" class="flex-1 py-3 px-4 text-center font-semibold transition <?php echo $activeTab == 'signup' ? 'bg-highlight text-white' : 'text-gray-300 hover:bg-secondary'; ?>">
                    <i class="fas fa-user-plus mr-2"></i>Sign Up
                </button>
            </div>

            <div class="p-6">
                <!-- Messages -->
                <?php if ($error): ?>
                    <div class="bg-red-600 text-white p-4 rounded-lg mb-6 text-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-600 text-white p-4 rounded-lg mb-6 text-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <div id="loginContent" class="tab-content <?php echo $activeTab == 'login' ? 'active' : ''; ?>">
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">
                                <i class="fas fa-user mr-2"></i>Username
                            </label>
                            <input type="text" name="username" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="Enter your username">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">
                                <i class="fas fa-lock mr-2"></i>Password
                            </label>
                            <input type="password" name="password" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="Enter your password">
                        </div>

                        <button type="submit" name="login" class="w-full bg-highlight hover:bg-red-600 transition text-white font-semibold py-3 px-4 rounded-lg">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </button>
                    </form>

                    <div class="mt-6 text-center text-sm text-gray-400">
                        <p>Test Account: <strong>testuser1</strong> / <strong>password123</strong></p>
                    </div>
                </div>

                <!-- Signup Form -->
                <div id="signupContent" class="tab-content <?php echo $activeTab == 'signup' ? 'active' : ''; ?>">
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">
                                <i class="fas fa-user mr-2"></i>Username
                            </label>
                            <input type="text" name="username" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="Choose a username">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">
                                <i class="fas fa-envelope mr-2"></i>Email
                            </label>
                            <input type="email" name="email" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="Enter your email">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">
                                <i class="fas fa-lock mr-2"></i>Password
                            </label>
                            <input type="password" name="password" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="Create a password (min 6 characters)">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">
                                <i class="fas fa-gift mr-2"></i>Referral Code (Optional)
                            </label>
                            <input type="text" name="referral_code" 
                                   value="<?php echo htmlspecialchars($referralCode); ?>" 
                                   <?php echo $referralReadonly ? 'readonly' : ''; ?>
                                   class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none <?php echo $referralReadonly ? 'opacity-75' : ''; ?>" 
                                   placeholder="Enter referral code (optional)" id="referralCodeInput">
                            
                            <?php if (!$referralReadonly): ?>
                                <div class="mt-2">
                                    <button type="button" onclick="useAdminCode()" 
                                            class="text-xs text-gray-400 hover:text-white transition">
                                        <i class="fas fa-hand-point-right mr-1"></i>I have no refer code
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" name="signup" class="w-full bg-highlight hover:bg-red-600 transition text-white font-semibold py-3 px-4 rounded-lg">
                            <i class="fas fa-user-plus mr-2"></i>Create Account
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="admin/login.php" class="text-gray-400 hover:text-white transition text-sm">
                <i class="fas fa-shield-alt mr-1"></i>Admin Login
            </a>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            // Hide all content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('[id$="Tab"]').forEach(tabBtn => {
                tabBtn.classList.remove('bg-highlight', 'text-white');
                tabBtn.classList.add('text-gray-300', 'hover:bg-secondary');
            });
            
            // Show selected content
            document.getElementById(tab + 'Content').classList.add('active');
            
            // Activate selected tab
            const activeTab = document.getElementById(tab + 'Tab');
            activeTab.classList.remove('text-gray-300', 'hover:bg-secondary');
            activeTab.classList.add('bg-highlight', 'text-white');
        }
        
        function useAdminCode() {
            const adminCode = '<?php echo $adminReferralCode; ?>';
            document.getElementById('referralCodeInput').value = adminCode;
        }

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
