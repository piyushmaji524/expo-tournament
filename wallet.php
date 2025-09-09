<?php
include 'common/header.php';

$message = '';
$messageType = '';

// Handle deposit request submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_deposit'])) {
    $amount = floatval($_POST['amount']);
    $transaction_id = trim($_POST['transaction_id']);
    
    if ($amount <= 0 || empty($transaction_id)) {
        $message = 'Please enter valid amount and transaction ID.';
        $messageType = 'error';
    } else {
        // Check if transaction ID already exists
        $stmt = $pdo->prepare("SELECT id FROM deposits WHERE transaction_id = ?");
        $stmt->execute([$transaction_id]);
        
        if ($stmt->fetch()) {
            $message = 'This transaction ID has already been used.';
            $messageType = 'error';
        } else {
            $stmt = $pdo->prepare("INSERT INTO deposits (user_id, amount, transaction_id, status) VALUES (?, ?, ?, 'Pending')");
            if ($stmt->execute([$_SESSION['user_id'], $amount, $transaction_id])) {
                $message = 'Deposit request submitted successfully! It will be reviewed by admin within 24 hours.';
                $messageType = 'success';
            } else {
                $message = 'Failed to submit deposit request. Please try again.';
                $messageType = 'error';
            }
        }
    }
}

// Handle withdrawal request submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_withdrawal'])) {
    $amount = floatval($_POST['amount']);
    
    // Get user's UPI ID
    $stmt = $pdo->prepare("SELECT upi_id FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch();
    $upi_id = $user_data['upi_id'] ?? '';
    
    if (empty($upi_id)) {
        $message = 'Please set your UPI ID in your profile first.';
        $messageType = 'error';
    } elseif ($amount <= 0) {
        $message = 'Please enter a valid amount.';
        $messageType = 'error';
    } else {
        $user_balance = getUserWalletBalance($_SESSION['user_id'], $pdo);
        
        if ($amount > $user_balance) {
            $message = 'Insufficient balance for withdrawal.';
            $messageType = 'error';
        } else {
            $stmt = $pdo->prepare("INSERT INTO withdrawals (user_id, amount, upi_id, status) VALUES (?, ?, ?, 'Pending')");
            if ($stmt->execute([$_SESSION['user_id'], $amount, $upi_id])) {
                $message = 'Withdrawal request submitted successfully! It will be processed within 1-3 business days.';
                $messageType = 'success';
            } else {
                $message = 'Failed to submit withdrawal request. Please try again.';
                $messageType = 'error';
            }
        }
    }
}

// Get admin UPI settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('admin_upi_id', 'admin_qr_code')");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$admin_upi_id = $settings['admin_upi_id'] ?? '';
$admin_qr_code = $settings['admin_qr_code'] ?? '';

// Get user's current balance
$user_balance = getUserWalletBalance($_SESSION['user_id'], $pdo);

// Get user's UPI ID for withdrawals
$stmt = $pdo->prepare("SELECT upi_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_data = $stmt->fetch();
$user_upi_id = $user_data['upi_id'] ?? '';

// Get recent transactions
$stmt = $pdo->prepare("
    SELECT * FROM transactions 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 20
");
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll();

// Calculate total earned and spent
$stmt = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as total_earned,
        SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as total_spent
    FROM transactions 
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();
$total_earned = $stats['total_earned'] ?? 0;
$total_spent = $stats['total_spent'] ?? 0;
?>

<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold mb-2">
            <i class="fas fa-wallet text-highlight mr-2"></i>
            My Wallet
        </h2>
        <p class="text-gray-400">Manage your tournament funds</p>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
        <div class="<?php echo $messageType == 'success' ? 'bg-green-600' : 'bg-red-600'; ?> text-white p-4 rounded-lg mb-6 text-center">
            <i class="fas <?php echo $messageType == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-2"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Balance Card -->
    <div class="bg-gradient-to-r from-accent to-highlight rounded-lg p-6 mb-6 text-center">
        <div class="text-white">
            <i class="fas fa-wallet text-4xl mb-3"></i>
            <div class="text-3xl font-bold mb-2"><?php echo formatCurrency($user_balance); ?></div>
            <div class="text-blue-100">Current Balance</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <button onclick="showAddMoneyModal()" class="bg-green-600 hover:bg-green-700 transition rounded-lg p-4 text-center">
            <i class="fas fa-plus text-2xl mb-2"></i>
            <div class="font-semibold">Add Money</div>
            <div class="text-sm opacity-80">Top up wallet</div>
        </button>
        <button onclick="showWithdrawModal()" class="bg-blue-600 hover:bg-blue-700 transition rounded-lg p-4 text-center">
            <i class="fas fa-arrow-up text-2xl mb-2"></i>
            <div class="font-semibold">Withdraw</div>
            <div class="text-sm opacity-80">Cash out</div>
        </button>
    </div>

    <!-- Wallet Stats -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-secondary rounded-lg p-4 text-center">
            <i class="fas fa-arrow-down text-green-500 text-xl mb-2"></i>
            <div class="text-lg font-bold text-green-400"><?php echo formatCurrency($total_earned); ?></div>
            <div class="text-sm text-gray-400">Total Earned</div>
        </div>
        <div class="bg-secondary rounded-lg p-4 text-center">
            <i class="fas fa-arrow-up text-red-500 text-xl mb-2"></i>
            <div class="text-lg font-bold text-red-400"><?php echo formatCurrency($total_spent); ?></div>
            <div class="text-sm text-gray-400">Total Spent</div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="bg-secondary rounded-lg p-4">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-history text-highlight mr-2"></i>
            Recent Transactions
        </h3>

        <?php if (empty($transactions)): ?>
            <div class="text-center py-8">
                <i class="fas fa-receipt text-4xl text-gray-500 mb-4"></i>
                <h4 class="text-lg font-semibold mb-2">No Transactions Yet</h4>
                <p class="text-gray-400">Your transaction history will appear here</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($transactions as $transaction): ?>
                    <div class="flex items-center justify-between p-3 bg-accent rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center <?php echo $transaction['type'] == 'credit' ? 'bg-green-600' : 'bg-red-600'; ?>">
                                <i class="fas fa-<?php echo $transaction['type'] == 'credit' ? 'arrow-down' : 'arrow-up'; ?> text-white"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-sm"><?php echo htmlspecialchars($transaction['description']); ?></div>
                                <div class="text-xs text-gray-400"><?php echo date('M j, Y g:i A', strtotime($transaction['created_at'])); ?></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold <?php echo $transaction['type'] == 'credit' ? 'text-green-400' : 'text-red-400'; ?>">
                                <?php echo $transaction['type'] == 'credit' ? '+' : '-'; ?><?php echo formatCurrency($transaction['amount']); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($transactions) >= 20): ?>
                <div class="text-center mt-4">
                    <p class="text-sm text-gray-400">Showing recent 20 transactions</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Add Money Modal -->
<div id="addMoneyModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-secondary rounded-lg p-6 w-full max-w-md max-h-96 overflow-y-auto">
        <div class="text-center mb-4">
            <i class="fas fa-plus-circle text-green-500 text-3xl mb-2"></i>
            <h3 class="text-lg font-semibold">Add Money via UPI</h3>
        </div>
        
        <?php if (empty($admin_upi_id)): ?>
            <div class="bg-red-600 text-white p-4 rounded-lg text-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                UPI payment not configured. Please contact admin.
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <!-- QR Code Display -->
                <?php if (!empty($admin_qr_code) && file_exists('uploads/' . $admin_qr_code)): ?>
                    <div class="text-center bg-accent rounded-lg p-4">
                        <p class="text-sm text-gray-300 mb-3">Scan QR Code to Pay:</p>
                        <img src="uploads/<?php echo htmlspecialchars($admin_qr_code); ?>" alt="Payment QR Code" class="mx-auto w-48 h-48 object-cover rounded-lg border">
                    </div>
                <?php endif; ?>
                
                <!-- UPI ID Display -->
                <div class="bg-accent rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-300 mb-2">Or pay to UPI ID:</p>
                    <div class="bg-secondary px-3 py-2 rounded font-mono text-highlight font-bold">
                        <?php echo htmlspecialchars($admin_upi_id); ?>
                    </div>
                </div>
                
                <!-- Instructions -->
                <div class="bg-blue-600 bg-opacity-20 border border-blue-600 rounded-lg p-3">
                    <h5 class="font-semibold text-blue-400 mb-2">Payment Instructions:</h5>
                    <ol class="text-sm text-blue-200 space-y-1">
                        <li>1. Open your UPI app (PhonePe, GPay, Paytm, etc.)</li>
                        <li>2. Scan the QR code or pay to the UPI ID</li>
                        <li>3. Enter the amount and complete payment</li>
                        <li>4. Copy the transaction ID from your app</li>
                        <li>5. Fill the form below with payment details</li>
                    </ol>
                </div>
                
                <!-- Deposit Form -->
                <form method="POST" class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium mb-2">Amount Paid (₹)</label>
                        <input type="number" name="amount" step="0.01" min="1" required class="w-full bg-accent border border-gray-600 rounded-lg px-3 py-2 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="Enter amount you paid">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">UPI Transaction ID</label>
                        <input type="text" name="transaction_id" required class="w-full bg-accent border border-gray-600 rounded-lg px-3 py-2 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="Enter transaction ID from your payment app">
                        <p class="text-xs text-gray-400 mt-1">Find this in your payment app's transaction history</p>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button type="button" onclick="hideAddMoneyModal()" class="flex-1 bg-gray-600 hover:bg-gray-700 transition text-white py-2 px-4 rounded-lg">Cancel</button>
                        <button type="submit" name="submit_deposit" class="flex-1 bg-green-600 hover:bg-green-700 transition text-white py-2 px-4 rounded-lg">Submit Request</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Withdraw Modal -->
<div id="withdrawModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-secondary rounded-lg p-6 w-full max-w-sm">
        <div class="text-center mb-4">
            <i class="fas fa-arrow-up text-blue-500 text-3xl mb-2"></i>
            <h3 class="text-lg font-semibold">Withdraw Money</h3>
            <p class="text-sm text-gray-400">Available: <?php echo formatCurrency($user_balance); ?></p>
        </div>
        
        <?php if (empty($user_upi_id)): ?>
            <div class="bg-yellow-600 bg-opacity-20 border border-yellow-600 rounded-lg p-4 text-center mb-4">
                <i class="fas fa-info-circle text-yellow-400 mb-2"></i>
                <p class="text-yellow-200 text-sm">Please set your UPI ID in your profile first to enable withdrawals.</p>
            </div>
            <div class="flex space-x-2">
                <button onclick="hideWithdrawModal()" class="flex-1 bg-gray-600 hover:bg-gray-700 transition text-white py-2 px-4 rounded-lg">Cancel</button>
                <a href="profile.php" class="flex-1 bg-blue-600 hover:bg-blue-700 transition text-white py-2 px-4 rounded-lg text-center">Go to Profile</a>
            </div>
        <?php else: ?>
            <form method="POST" class="space-y-4">
                <div class="bg-accent rounded-lg p-3">
                    <p class="text-sm text-gray-300">Withdrawal will be sent to:</p>
                    <p class="font-mono text-highlight font-semibold"><?php echo htmlspecialchars($user_upi_id); ?></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Amount to Withdraw (₹)</label>
                    <input type="number" name="amount" step="0.01" min="1" max="<?php echo $user_balance; ?>" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="Enter amount">
                </div>
                
                <div class="bg-blue-600 bg-opacity-20 border border-blue-600 rounded-lg p-3">
                    <p class="text-blue-200 text-sm">
                        <i class="fas fa-info-circle mr-1"></i>
                        Withdrawal requests are processed within 1-3 business days.
                    </p>
                </div>
                
                <div class="flex space-x-2">
                    <button type="button" onclick="hideWithdrawModal()" class="flex-1 bg-gray-600 hover:bg-gray-700 transition text-white py-2 px-4 rounded-lg">Cancel</button>
                    <button type="submit" name="submit_withdrawal" class="flex-1 bg-blue-600 hover:bg-blue-700 transition text-white py-2 px-4 rounded-lg">Submit Request</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    let selectedAmount = 0;

    function showAddMoneyModal() {
        document.getElementById('addMoneyModal').classList.remove('hidden');
    }

    function hideAddMoneyModal() {
        document.getElementById('addMoneyModal').classList.add('hidden');
        selectedAmount = 0;
        document.getElementById('customAmount').value = '';
        document.querySelectorAll('.amount-btn').forEach(btn => {
            btn.classList.remove('bg-highlight');
            btn.classList.add('bg-accent');
        });
    }

    function showWithdrawModal() {
        document.getElementById('withdrawModal').classList.remove('hidden');
    }

    function hideWithdrawModal() {
        document.getElementById('withdrawModal').classList.add('hidden');
        document.getElementById('withdrawAmount').value = '';
    }

    function selectAmount(amount) {
        document.getElementById('customAmount').value = amount;
        // Remove active class from all buttons
        document.querySelectorAll('.amount-btn').forEach(btn => btn.classList.remove('bg-highlight'));
        // Add active class to clicked button
        event.target.classList.add('bg-highlight');
    }

    function addMoney() {
        // Form validation will be handled by the form submission
        hideAddMoneyModal();
    }

    function withdrawMoney() {
        // Form validation will be handled by the form submission
        hideWithdrawModal();
    }

    // Close modals when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.id === 'addMoneyModal') {
            hideAddMoneyModal();
        }
        if (e.target.id === 'withdrawModal') {
            hideWithdrawModal();
        }
    });

    // Auto-hide success/error messages
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert-message');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            }, 5000);
        });
    });
</script>

<?php include 'common/bottom.php'; ?>
