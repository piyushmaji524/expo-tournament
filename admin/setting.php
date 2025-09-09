<?php
include 'common/header.php';

$message = '';
$messageType = '';

// Handle UPI settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_upi'])) {
    $upi_id = trim($_POST['upi_id']);
    $qr_code_filename = '';
    
    // Handle QR code upload
    if (isset($_FILES['qr_code']) && $_FILES['qr_code']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $file_type = $_FILES['qr_code']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = '../uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['qr_code']['name'], PATHINFO_EXTENSION);
            $qr_code_filename = 'admin_qr_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $qr_code_filename;
            
            if (move_uploaded_file($_FILES['qr_code']['tmp_name'], $upload_path)) {
                // Update QR code setting
                $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'admin_qr_code'");
                $stmt->execute([$qr_code_filename]);
            } else {
                $message = 'Failed to upload QR code image.';
                $messageType = 'error';
            }
        } else {
            $message = 'Please upload a valid image file (JPG, PNG).';
            $messageType = 'error';
        }
    }
    
    if (empty($message)) {
        // Update UPI ID
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'admin_upi_id'");
        if ($stmt->execute([$upi_id])) {
            $message = 'UPI settings updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to update UPI settings.';
            $messageType = 'error';
        }
    }
}

// Handle admin info update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_info'])) {
    $username = trim($_POST['username']);
    
    if (empty($username)) {
        $message = 'Username cannot be empty.';
        $messageType = 'error';
    } else {
        $stmt = $pdo->prepare("UPDATE admin SET username = ? WHERE id = ?");
        if ($stmt->execute([$username, $_SESSION['admin_id']])) {
            $_SESSION['admin_username'] = $username;
            $message = 'Admin information updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to update admin information.';
            $messageType = 'error';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
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
        $stmt = $pdo->prepare("SELECT password FROM admin WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();
        
        if (password_verify($current_password, $admin['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE id = ?");
            
            if ($stmt->execute([$hashed_password, $_SESSION['admin_id']])) {
                $message = 'Password changed successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to change password.';
                $messageType = 'error';
            }
        } else {
            $message = 'Current password is incorrect.';
            $messageType = 'error';
        }
    }
}

// Get current admin data
$stmt = $pdo->prepare("SELECT username FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

// Get UPI settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('admin_upi_id', 'admin_qr_code')");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$admin_upi_id = $settings['admin_upi_id'] ?? '';
$admin_qr_code = $settings['admin_qr_code'] ?? '';

// Get system statistics
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$total_users = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM tournaments");
$total_tournaments = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM participants");
$total_participations = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT SUM(amount) as total FROM transactions WHERE type = 'credit'");
$total_credits = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("SELECT SUM(amount) as total FROM transactions WHERE type = 'debit'");
$total_debits = $stmt->fetch()['total'] ?? 0;
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div>
        <h2 class="text-2xl font-bold">Admin Settings</h2>
        <p class="text-gray-400">Manage admin account and system settings</p>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
        <div class="<?php echo $messageType == 'success' ? 'bg-green-600' : 'bg-red-600'; ?> text-white p-4 rounded-lg text-center">
            <i class="fas <?php echo $messageType == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-2"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- UPI Settings -->
        <div class="bg-secondary rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">
                <i class="fas fa-mobile-alt text-highlight mr-2"></i>
                UPI Payment Settings
            </h3>

            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">
                        <i class="fas fa-credit-card mr-2"></i>Admin UPI ID
                    </label>
                    <input type="text" name="upi_id" value="<?php echo htmlspecialchars($admin_upi_id); ?>" class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="example@paytm">
                    <p class="text-xs text-gray-400 mt-1">Users will send money to this UPI ID</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">
                        <i class="fas fa-qrcode mr-2"></i>QR Code Image
                    </label>
                    <?php if (!empty($admin_qr_code) && file_exists('../uploads/' . $admin_qr_code)): ?>
                        <div class="mb-3">
                            <img src="../uploads/<?php echo htmlspecialchars($admin_qr_code); ?>" alt="Current QR Code" class="w-32 h-32 object-cover rounded-lg border border-gray-600">
                            <p class="text-xs text-gray-400 mt-1">Current QR Code</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="qr_code" accept="image/*" class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-highlight file:text-white hover:file:bg-red-600">
                    <p class="text-xs text-gray-400 mt-1">Upload your UPI QR code (JPG, PNG)</p>
                </div>

                <button type="submit" name="update_upi" class="w-full bg-green-600 hover:bg-green-700 transition text-white font-semibold py-3 px-4 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Update UPI Settings
                </button>
            </form>
        </div>

        <!-- Admin Information -->
        <div class="bg-secondary rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">
                <i class="fas fa-user-shield text-highlight mr-2"></i>
                Admin Information
            </h3>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">
                        <i class="fas fa-user mr-2"></i>Admin Username
                    </label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none">
                </div>

                <button type="submit" name="update_info" class="w-full bg-blue-600 hover:bg-blue-700 transition text-white font-semibold py-3 px-4 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Update Information
                </button>
            </form>
        </div>
    </div>

    <!-- Change Password Section -->
    <div class="bg-secondary rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-lock text-highlight mr-2"></i>
            Change Password
        </h3>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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

                <button type="submit" name="change_password" class="w-full bg-highlight hover:bg-red-600 transition text-white font-semibold py-3 px-4 rounded-lg">
                    <i class="fas fa-key mr-2"></i>Change Password
                </button>
            </form>
        </div>
    </div>

    <!-- System Statistics -->
    <div class="bg-secondary rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-chart-bar text-highlight mr-2"></i>
            System Statistics
        </h3>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-accent rounded-lg p-4 text-center">
                <i class="fas fa-users text-blue-500 text-2xl mb-2"></i>
                <div class="text-xl font-bold"><?php echo number_format($total_users); ?></div>
                <div class="text-xs text-gray-400">Total Users</div>
            </div>

            <div class="bg-accent rounded-lg p-4 text-center">
                <i class="fas fa-trophy text-green-500 text-2xl mb-2"></i>
                <div class="text-xl font-bold"><?php echo number_format($total_tournaments); ?></div>
                <div class="text-xs text-gray-400">Tournaments</div>
            </div>

            <div class="bg-accent rounded-lg p-4 text-center">
                <i class="fas fa-gamepad text-purple-500 text-2xl mb-2"></i>
                <div class="text-xl font-bold"><?php echo number_format($total_participations); ?></div>
                <div class="text-xs text-gray-400">Participations</div>
            </div>

            <div class="bg-accent rounded-lg p-4 text-center">
                <i class="fas fa-arrow-up text-green-500 text-2xl mb-2"></i>
                <div class="text-xl font-bold"><?php echo formatCurrency($total_credits); ?></div>
                <div class="text-xs text-gray-400">Total Credits</div>
            </div>

            <div class="bg-accent rounded-lg p-4 text-center">
                <i class="fas fa-arrow-down text-red-500 text-2xl mb-2"></i>
                <div class="text-xl font-bold"><?php echo formatCurrency($total_debits); ?></div>
                <div class="text-xs text-gray-400">Total Debits</div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="bg-secondary rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-server text-highlight mr-2"></i>
            System Information
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-3">
                <h4 class="font-semibold text-blue-400">Server Information</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>PHP Version:</span>
                        <span class="font-mono"><?php echo PHP_VERSION; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Server Software:</span>
                        <span class="font-mono"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Server Time:</span>
                        <span class="font-mono"><?php echo date('Y-m-d H:i:s'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Memory Limit:</span>
                        <span class="font-mono"><?php echo ini_get('memory_limit'); ?></span>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <h4 class="font-semibold text-green-400">Database Information</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>Database:</span>
                        <span class="font-mono">tournament_app</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Host:</span>
                        <span class="font-mono">127.0.0.1</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Connection:</span>
                        <span class="text-green-400">
                            <i class="fas fa-check-circle mr-1"></i>Active
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span>Last Backup:</span>
                        <span class="text-yellow-400">Manual Only</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Settings -->
    <div class="bg-secondary rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-cogs text-highlight mr-2"></i>
            Application Settings
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <h4 class="font-semibold text-yellow-400">General Settings</h4>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm">App Name:</span>
                        <span class="font-mono text-sm">Adept Play</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm">Version:</span>
                        <span class="font-mono text-sm">1.0.0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm">Currency:</span>
                        <span class="font-mono text-sm">INR (₹)</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm">Default Commission:</span>
                        <span class="font-mono text-sm">20%</span>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <h4 class="font-semibold text-purple-400">Security Settings</h4>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm">Session Timeout:</span>
                        <span class="font-mono text-sm">24 hours</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm">Password Encryption:</span>
                        <span class="text-green-400 text-sm">
                            <i class="fas fa-shield-alt mr-1"></i>Enabled
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm">SQL Injection Protection:</span>
                        <span class="text-green-400 text-sm">
                            <i class="fas fa-shield-alt mr-1"></i>Active
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm">Input Validation:</span>
                        <span class="text-green-400 text-sm">
                            <i class="fas fa-shield-alt mr-1"></i>Enabled
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="index.php" class="bg-blue-600 hover:bg-blue-700 transition rounded-lg p-4 text-center">
            <i class="fas fa-dashboard text-2xl mb-2"></i>
            <div class="font-semibold">Dashboard</div>
            <div class="text-sm opacity-80">Go to dashboard</div>
        </a>

        <a href="tournament.php" class="bg-green-600 hover:bg-green-700 transition rounded-lg p-4 text-center">
            <i class="fas fa-trophy text-2xl mb-2"></i>
            <div class="font-semibold">Tournaments</div>
            <div class="text-sm opacity-80">Manage tournaments</div>
        </a>

        <a href="user.php" class="bg-purple-600 hover:bg-purple-700 transition rounded-lg p-4 text-center">
            <i class="fas fa-users text-2xl mb-2"></i>
            <div class="font-semibold">Users</div>
            <div class="text-sm opacity-80">Manage users</div>
        </a>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
