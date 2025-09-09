<?php
include 'common/header.php';

$message = '';
$messageType = '';

// Handle withdrawal completion/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['complete_withdrawal'])) {
        $withdrawal_id = $_POST['withdrawal_id'];
        $admin_note = trim($_POST['admin_note']);
        
        // Get withdrawal details
        $stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE id = ? AND status = 'Pending'");
        $stmt->execute([$withdrawal_id]);
        $withdrawal = $stmt->fetch();
        
        if ($withdrawal) {
            $pdo->beginTransaction();
            
            try {
                // Update withdrawal status
                $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'Completed', admin_note = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$admin_note, $withdrawal_id]);
                
                // Deduct money from user's wallet
                updateWalletBalance($withdrawal['user_id'], -$withdrawal['amount'], $pdo);
                
                // Add transaction record
                addTransaction($withdrawal['user_id'], $withdrawal['amount'], 'debit', 'UPI Withdrawal to: ' . $withdrawal['upi_id'], $pdo);
                
                $pdo->commit();
                
                $message = 'Withdrawal completed successfully! Money has been deducted from user\'s wallet.';
                $messageType = 'success';
            } catch (Exception $e) {
                $pdo->rollback();
                $message = 'Failed to complete withdrawal. Please try again.';
                $messageType = 'error';
            }
        } else {
            $message = 'Withdrawal request not found or already processed.';
            $messageType = 'error';
        }
    } elseif (isset($_POST['reject_withdrawal'])) {
        $withdrawal_id = $_POST['withdrawal_id'];
        $admin_note = trim($_POST['admin_note']);
        
        $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'Rejected', admin_note = ?, updated_at = NOW() WHERE id = ? AND status = 'Pending'");
        if ($stmt->execute([$admin_note, $withdrawal_id])) {
            $message = 'Withdrawal request rejected.';
            $messageType = 'success';
        } else {
            $message = 'Failed to reject withdrawal request.';
            $messageType = 'error';
        }
    }
}

// Get all withdrawal requests
$stmt = $pdo->query("
    SELECT w.*, u.username, u.email, u.wallet_balance
    FROM withdrawals w
    JOIN users u ON w.user_id = u.id
    ORDER BY w.created_at DESC
");
$withdrawals = $stmt->fetchAll();

// Get counts for different statuses
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM withdrawals GROUP BY status");
$status_counts = [];
while ($row = $stmt->fetch()) {
    $status_counts[$row['status']] = $row['count'];
}
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold">Withdrawal Requests</h2>
            <p class="text-gray-400">Manage user UPI withdrawal requests</p>
        </div>
        <div class="text-sm text-gray-400">
            Total Requests: <?php echo count($withdrawals); ?>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
        <div class="<?php echo $messageType == 'success' ? 'bg-green-600' : 'bg-red-600'; ?> text-white p-4 rounded-lg text-center">
            <i class="fas <?php echo $messageType == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-2"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Status Summary -->
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-secondary rounded-lg p-4 text-center border-l-4 border-yellow-500">
            <i class="fas fa-clock text-yellow-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold"><?php echo $status_counts['Pending'] ?? 0; ?></div>
            <div class="text-sm text-gray-400">Pending</div>
        </div>

        <div class="bg-secondary rounded-lg p-4 text-center border-l-4 border-green-500">
            <i class="fas fa-check-circle text-green-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold"><?php echo $status_counts['Completed'] ?? 0; ?></div>
            <div class="text-sm text-gray-400">Completed</div>
        </div>

        <div class="bg-secondary rounded-lg p-4 text-center border-l-4 border-red-500">
            <i class="fas fa-times-circle text-red-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold"><?php echo $status_counts['Rejected'] ?? 0; ?></div>
            <div class="text-sm text-gray-400">Rejected</div>
        </div>
    </div>

    <!-- Withdrawal Requests List -->
    <div class="bg-secondary rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-list text-highlight mr-2"></i>
            All Withdrawal Requests
        </h3>

        <?php if (empty($withdrawals)): ?>
            <div class="text-center py-8">
                <i class="fas fa-inbox text-4xl text-gray-500 mb-4"></i>
                <h4 class="text-lg font-semibold mb-2">No Withdrawal Requests</h4>
                <p class="text-gray-400">Withdrawal requests will appear here when users submit them</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($withdrawals as $withdrawal): ?>
                    <div class="bg-accent rounded-lg p-4 border-l-4 <?php echo $withdrawal['status'] == 'Pending' ? 'border-yellow-500' : ($withdrawal['status'] == 'Completed' ? 'border-green-500' : 'border-red-500'); ?>">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <div class="w-10 h-10 bg-highlight rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold"><?php echo htmlspecialchars($withdrawal['username']); ?></h4>
                                        <p class="text-sm text-gray-400"><?php echo htmlspecialchars($withdrawal['email']); ?></p>
                                        <p class="text-sm text-green-400">Current Balance: <?php echo formatCurrency($withdrawal['wallet_balance']); ?></p>
                                    </div>
                                </div>
                            </div>
                            <span class="bg-<?php echo $withdrawal['status'] == 'Pending' ? 'yellow' : ($withdrawal['status'] == 'Completed' ? 'green' : 'red'); ?>-600 text-white px-3 py-1 rounded text-sm">
                                <?php echo $withdrawal['status']; ?>
                            </span>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-3 text-sm">
                            <div>
                                <i class="fas fa-money-bill text-red-500 mr-1"></i>
                                <strong>Amount:</strong> <?php echo formatCurrency($withdrawal['amount']); ?>
                            </div>
                            <div>
                                <i class="fas fa-mobile-alt text-blue-500 mr-1"></i>
                                <strong>UPI ID:</strong> 
                                <span class="font-mono"><?php echo htmlspecialchars($withdrawal['upi_id']); ?></span>
                            </div>
                            <div>
                                <i class="fas fa-clock text-gray-500 mr-1"></i>
                                <strong>Requested:</strong> <?php echo date('M j, Y g:i A', strtotime($withdrawal['created_at'])); ?>
                            </div>
                            <?php if ($withdrawal['status'] != 'Pending'): ?>
                                <div>
                                    <i class="fas fa-check text-purple-500 mr-1"></i>
                                    <strong>Updated:</strong> <?php echo date('M j, Y g:i A', strtotime($withdrawal['updated_at'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($withdrawal['admin_note'])): ?>
                            <div class="bg-secondary rounded-lg p-3 mb-3">
                                <h5 class="font-semibold text-sm mb-1 text-blue-400">Admin Note:</h5>
                                <p class="text-sm"><?php echo htmlspecialchars($withdrawal['admin_note']); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($withdrawal['status'] == 'Pending'): ?>
                            <div class="bg-yellow-600 bg-opacity-20 border border-yellow-600 rounded-lg p-3 mb-3">
                                <h5 class="font-semibold text-sm mb-1 text-yellow-400">
                                    <i class="fas fa-info-circle mr-1"></i>Action Required:
                                </h5>
                                <p class="text-sm text-yellow-200">
                                    Send <?php echo formatCurrency($withdrawal['amount']); ?> to UPI ID: <strong><?php echo htmlspecialchars($withdrawal['upi_id']); ?></strong>
                                    <br>After sending the money, click "Mark as Completed" below.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <!-- Complete Form -->
                                <form method="POST" class="bg-green-600 bg-opacity-20 border border-green-600 rounded-lg p-3">
                                    <input type="hidden" name="withdrawal_id" value="<?php echo $withdrawal['id']; ?>">
                                    <label class="block text-sm font-medium mb-2 text-green-400">Mark as Completed:</label>
                                    <textarea name="admin_note" rows="2" class="w-full bg-accent border border-gray-600 rounded px-3 py-2 text-white placeholder-gray-400 text-sm mb-2" placeholder="Optional completion note..."></textarea>
                                    <button type="submit" name="complete_withdrawal" onclick="return confirm('Have you sent the money to user\'s UPI ID? This will deduct money from user\'s wallet.')" class="w-full bg-green-600 hover:bg-green-700 transition text-white font-semibold py-2 px-3 rounded text-sm">
                                        <i class="fas fa-check mr-1"></i>Mark as Completed
                                    </button>
                                </form>

                                <!-- Reject Form -->
                                <form method="POST" class="bg-red-600 bg-opacity-20 border border-red-600 rounded-lg p-3">
                                    <input type="hidden" name="withdrawal_id" value="<?php echo $withdrawal['id']; ?>">
                                    <label class="block text-sm font-medium mb-2 text-red-400">Reject with Reason:</label>
                                    <textarea name="admin_note" rows="2" class="w-full bg-accent border border-gray-600 rounded px-3 py-2 text-white placeholder-gray-400 text-sm mb-2" placeholder="Reason for rejection..."></textarea>
                                    <button type="submit" name="reject_withdrawal" onclick="return confirm('Are you sure you want to REJECT this withdrawal request?')" class="w-full bg-red-600 hover:bg-red-700 transition text-white font-semibold py-2 px-3 rounded text-sm">
                                        <i class="fas fa-times mr-1"></i>Reject Withdrawal
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
