<?php
include 'common/header.php';

$message = '';
$messageType = '';

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $upi_id = trim($_POST['upi_id']);
        
        if (empty($username) || empty($email)) {
            $message = 'Please fill in all required fields.';
            $messageType = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address.';
            $messageType = 'error';
        } else {
            // Check if username or email already exists (excluding current user)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $_SESSION['user_id']]);
            
            if ($stmt->fetch()) {
                $message = 'Username or email already exists.';
                $messageType = 'error';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, upi_id = ? WHERE id = ?");
                if ($stmt->execute([$username, $email, $upi_id, $_SESSION['user_id']])) {
                    $_SESSION['username'] = $username;
                    $message = 'Profile updated successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to update profile. Please try again.';
                    $messageType = 'error';
                }
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $message = 'Please fill in all password fields.';
            $messageType = 'error';
        } elseif (strlen($new_password) < 6) {
            $message = 'New password must be at least 6 characters long.';
            $messageType = 'error';
        } elseif ($new_password !== $confirm_password) {
            $message = 'New passwords do not match.';
            $messageType = 'error';
        } else {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (password_verify($current_password, $user['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                
                if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
                    $message = 'Password changed successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to change password. Please try again.';
                    $messageType = 'error';
                }
            } else {
                $message = 'Current password is incorrect.';
                $messageType = 'error';
            }
        }
    }
}

// Get current user data
$stmt = $pdo->prepare("SELECT username, email, upi_id, created_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get user statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_tournaments,
        SUM(CASE WHEN t.winner_id = ? THEN 1 ELSE 0 END) as wins,
        SUM(CASE WHEN t.status = 'Upcoming' OR t.status = 'Live' THEN 1 ELSE 0 END) as upcoming
    FROM participants p
    JOIN tournaments t ON p.tournament_id = t.id
    WHERE p.user_id = ?
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$stats = $stmt->fetch();
?>

<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="text-center mb-6">
        <div class="w-20 h-20 bg-highlight rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-user text-white text-3xl"></i>
        </div>
        <h2 class="text-2xl font-bold mb-1"><?php echo htmlspecialchars($user['username']); ?></h2>
        <p class="text-gray-400">Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
        <div class="<?php echo $messageType == 'success' ? 'bg-green-600' : 'bg-red-600'; ?> text-white p-4 rounded-lg mb-6 text-center">
            <i class="fas <?php echo $messageType == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-2"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- User Stats -->
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-secondary rounded-lg p-4 text-center">
            <i class="fas fa-gamepad text-highlight text-xl mb-2"></i>
            <div class="text-lg font-bold"><?php echo $stats['total_tournaments']; ?></div>
            <div class="text-xs text-gray-400">Tournaments</div>
        </div>
        <div class="bg-secondary rounded-lg p-4 text-center">
            <i class="fas fa-trophy text-highlight text-xl mb-2"></i>
            <div class="text-lg font-bold"><?php echo $stats['wins']; ?></div>
            <div class="text-xs text-gray-400">Wins</div>
        </div>
        <div class="bg-secondary rounded-lg p-4 text-center">
            <i class="fas fa-clock text-highlight text-xl mb-2"></i>
            <div class="text-lg font-bold"><?php echo $stats['upcoming']; ?></div>
            <div class="text-xs text-gray-400">Upcoming</div>
        </div>
    </div>

    <!-- Profile Information -->
    <div class="bg-secondary rounded-lg p-4 mb-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-user-edit text-highlight mr-2"></i>
            Profile Information
        </h3>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">
                    <i class="fas fa-user mr-2"></i>Username
                </label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">
                    <i class="fas fa-envelope mr-2"></i>Email
                </label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">
                    <i class="fas fa-mobile-alt mr-2"></i>UPI ID <span class="text-gray-500">(Optional)</span>
                </label>
                <input type="text" name="upi_id" value="<?php echo htmlspecialchars($user['upi_id'] ?? ''); ?>" class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="example@paytm">
                <p class="text-xs text-gray-400 mt-1">For withdrawals - Enter your UPI ID (PhonePe, GPay, Paytm, etc.)</p>
            </div>

            <button type="submit" name="update_profile" class="w-full bg-highlight hover:bg-red-600 transition text-white font-semibold py-3 px-4 rounded-lg">
                <i class="fas fa-save mr-2"></i>Update Profile
            </button>
        </form>
    </div>

    <!-- Change Password -->
    <div class="bg-secondary rounded-lg p-4 mb-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-lock text-highlight mr-2"></i>
            Change Password
        </h3>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">
                    <i class="fas fa-key mr-2"></i>Current Password
                </label>
                <input type="password" name="current_password" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="Enter current password">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">
                    <i class="fas fa-lock mr-2"></i>New Password
                </label>
                <input type="password" name="new_password" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="Enter new password (min 6 characters)">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">
                    <i class="fas fa-lock mr-2"></i>Confirm New Password
                </label>
                <input type="password" name="confirm_password" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="Confirm new password">
            </div>

            <button type="submit" name="change_password" class="w-full bg-blue-600 hover:bg-blue-700 transition text-white font-semibold py-3 px-4 rounded-lg">
                <i class="fas fa-key mr-2"></i>Change Password
            </button>
        </form>
    </div>

    <!-- Account Actions -->
    <div class="space-y-4">
        <a href="wallet.php" class="block w-full bg-green-600 hover:bg-green-700 transition text-white font-semibold py-3 px-4 rounded-lg text-center">
            <i class="fas fa-wallet mr-2"></i>Manage Wallet
        </a>

        <a href="my_tournaments.php" class="block w-full bg-blue-600 hover:bg-blue-700 transition text-white font-semibold py-3 px-4 rounded-lg text-center">
            <i class="fas fa-gamepad mr-2"></i>My Tournaments
        </a>

        <button onclick="confirmLogout()" class="w-full bg-red-600 hover:bg-red-700 transition text-white font-semibold py-3 px-4 rounded-lg">
            <i class="fas fa-sign-out-alt mr-2"></i>Logout
        </button>
    </div>

    <!-- Legal Section -->
    <div class="bg-secondary rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4 flex items-center">
            <i class="fas fa-gavel mr-2 text-highlight"></i>
            Legal & Policies
        </h2>
        
        <?php
        // Get active legal pages
        $stmt = $pdo->query("SELECT page_key, title FROM legal_pages WHERE is_active = 1 ORDER BY 
            CASE page_key 
                WHEN 'terms_of_service' THEN 1
                WHEN 'privacy_policy' THEN 2
                WHEN 'refund_policy' THEN 3
                WHEN 'responsible_gaming' THEN 4
                WHEN 'fair_play_policy' THEN 5
                WHEN 'community_guidelines' THEN 6
                WHEN 'contact_us' THEN 7
                ELSE 8
            END");
        $legalPages = $stmt->fetchAll();
        ?>
        
        <div class="grid grid-cols-1 gap-3">
            <?php foreach ($legalPages as $page): ?>
                <a href="legal.php?page=<?php echo $page['page_key']; ?>" 
                   class="flex items-center justify-between bg-accent hover:bg-highlight transition p-4 rounded-lg group">
                    <div class="flex items-center">
                        <i class="fas <?php 
                            echo match($page['page_key']) {
                                'terms_of_service' => 'fa-file-contract',
                                'privacy_policy' => 'fa-shield-alt',
                                'refund_policy' => 'fa-money-bill-wave',
                                'responsible_gaming' => 'fa-heart',
                                'fair_play_policy' => 'fa-balance-scale',
                                'community_guidelines' => 'fa-users',
                                'contact_us' => 'fa-envelope',
                                default => 'fa-file-alt'
                            };
                        ?> mr-3 text-blue-400 group-hover:text-white"></i>
                        <span class="font-medium"><?php echo htmlspecialchars($page['title']); ?></span>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-white"></i>
                </a>
            <?php endforeach; ?>
            
            <?php if (empty($legalPages)): ?>
                <div class="text-center py-6 text-gray-400">
                    <i class="fas fa-file-contract text-3xl mb-2"></i>
                    <p>Legal pages are being updated</p>
                    <p class="text-sm">Please check back later</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mt-4 p-4 bg-accent rounded-lg">
            <p class="text-sm text-gray-300">
                <i class="fas fa-info-circle mr-2 text-blue-400"></i>
                By using our platform, you agree to our terms and policies. 
                Please review them carefully to understand your rights and responsibilities.
            </p>
        </div>
    </div>

    <!-- App Download Section -->
    <div class="bg-secondary rounded-lg p-6" id="appDownloadSection">
        <h2 class="text-xl font-bold mb-4 flex items-center">
            <i class="fas fa-mobile-alt mr-2 text-highlight"></i>
            Download as App
        </h2>
        
        <div class="bg-gradient-to-r from-highlight to-red-600 rounded-lg p-4 mb-4">
            <div class="flex items-center">
                <div class="bg-white p-3 rounded-lg mr-4">
                    <i class="fas fa-download text-highlight text-2xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-white mb-1">Get the App Experience</h3>
                    <p class="text-red-100 text-sm">Install Expo Tournament for faster access, offline support, and app-like experience!</p>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <div class="flex items-center">
                <i class="fas fa-check text-green-400 mr-3"></i>
                <span class="text-sm">Works offline - Access tournaments anytime</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-check text-green-400 mr-3"></i>
                <span class="text-sm">Faster loading - No browser delays</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-check text-green-400 mr-3"></i>
                <span class="text-sm">Home screen access - One tap to open</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-check text-green-400 mr-3"></i>
                <span class="text-sm">Push notifications - Never miss tournaments</span>
            </div>
        </div>

        <div class="mt-6 space-y-3">
            <button onclick="installPWAFromProfile()" id="installAppBtn" class="w-full bg-highlight hover:bg-red-600 transition text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-center">
                <i class="fas fa-download mr-2"></i>
                Install App
            </button>
            
            <div id="installInstructions" class="hidden">
                <div class="bg-accent rounded-lg p-4">
                    <h4 class="font-semibold mb-2 flex items-center">
                        <i class="fas fa-lightbulb text-yellow-400 mr-2"></i>
                        Manual Installation
                    </h4>
                    <div class="text-sm text-gray-300 space-y-2">
                        <p><strong>Chrome/Edge:</strong> Menu → Install Expo Tournament</p>
                        <p><strong>Safari:</strong> Share → Add to Home Screen</p>
                        <p><strong>Firefox:</strong> Menu → Install</p>
                    </div>
                </div>
            </div>
            
            <button onclick="toggleInstallInstructions()" class="w-full bg-accent hover:bg-gray-600 transition text-gray-300 py-2 px-4 rounded-lg text-sm">
                <i class="fas fa-question-circle mr-2"></i>
                Need Help Installing?
            </button>
        </div>
    </div>
</div>

<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-secondary rounded-lg p-6 w-full max-w-sm">
        <div class="text-center mb-4">
            <i class="fas fa-sign-out-alt text-red-500 text-3xl mb-2"></i>
            <h3 class="text-lg font-semibold">Confirm Logout</h3>
            <p class="text-gray-400 text-sm mt-2">Are you sure you want to logout?</p>
        </div>
        
        <div class="flex space-x-2">
            <button onclick="hideLogoutModal()" class="flex-1 bg-gray-600 hover:bg-gray-700 transition text-white py-2 px-4 rounded-lg">Cancel</button>
            <a href="login.php?logout=1" class="flex-1 bg-red-600 hover:bg-red-700 transition text-white py-2 px-4 rounded-lg text-center">Logout</a>
        </div>
    </div>
</div>

<script>
    function confirmLogout() {
        document.getElementById('logoutModal').classList.remove('hidden');
    }

    function hideLogoutModal() {
        document.getElementById('logoutModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('logoutModal').addEventListener('click', function(e) {
        if (e.target === this) hideLogoutModal();
    });

    // PWA Install from Profile
    function installPWAFromProfile() {
        // Try to trigger install if available
        if (window.deferredPrompt) {
            window.deferredPrompt.prompt();
            window.deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted the install prompt from profile');
                    document.getElementById('installAppBtn').innerHTML = '<i class="fas fa-check mr-2"></i>App Installed!';
                    document.getElementById('installAppBtn').classList.add('bg-green-600');
                    document.getElementById('installAppBtn').classList.remove('bg-highlight');
                } else {
                    console.log('User dismissed the install prompt from profile');
                }
                window.deferredPrompt = null;
            });
        } else {
            // Show manual instructions if PWA not available
            toggleInstallInstructions();
        }
    }

    function toggleInstallInstructions() {
        const instructions = document.getElementById('installInstructions');
        if (instructions.classList.contains('hidden')) {
            instructions.classList.remove('hidden');
        } else {
            instructions.classList.add('hidden');
        }
    }

    // Check if app is already installed
    window.addEventListener('appinstalled', (evt) => {
        document.getElementById('installAppBtn').innerHTML = '<i class="fas fa-check mr-2"></i>App Installed!';
        document.getElementById('installAppBtn').classList.add('bg-green-600');
        document.getElementById('installAppBtn').classList.remove('bg-highlight');
        document.getElementById('installAppBtn').disabled = true;
    });

    // Hide install button if already installed (standalone mode)
    if (window.matchMedia('(display-mode: standalone)').matches) {
        document.getElementById('appDownloadSection').style.display = 'none';
    }
</script>

<?php include 'common/bottom.php'; ?>
