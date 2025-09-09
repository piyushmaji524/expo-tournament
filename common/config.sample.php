<?php
// Sample configuration file - Copy this to config.php and update with your database credentials
// Database configuration
$host = 'localhost';
$username = 'your_database_username';
$password = 'your_database_password';
$database = 'your_database_name';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect if admin not logged in
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Format currency in Indian Rupees
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

// Get user wallet balance
function getUserWalletBalance($user_id, $pdo) {
    $stmt = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    return $user ? $user['wallet_balance'] : 0;
}

// Update user wallet balance
function updateWalletBalance($user_id, $amount, $pdo) {
    $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
    return $stmt->execute([$amount, $user_id]);
}

// Add transaction record
function addTransaction($user_id, $amount, $type, $description, $pdo) {
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$user_id, $amount, $type, $description]);
}

// Generate unique referral ID
function generateReferralId() {
    return str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
}

// Get user referral statistics
function getUserReferralStats($user_id, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_referrals FROM users WHERE referred_by = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    
    return [
        'total_referrals' => $result['total_referrals'],
        'total_earnings' => 0 // Calculate based on your referral reward system
    ];
}

// Get users referred by a specific user
function getUserReferredUsers($user_id, $pdo) {
    $stmt = $pdo->prepare("SELECT username, created_at FROM users WHERE referred_by = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Get referral settings
function getReferralSettings($pdo) {
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('referral_reward_referrer', 'referral_reward_referred')");
    $stmt->execute();
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}
?>
