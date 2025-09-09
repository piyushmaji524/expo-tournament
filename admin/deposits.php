<?php
include 'common/header.php';

$message = '';
$messageType = '';

// Handle deposit approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve_deposit'])) {
        $deposit_id = $_POST['deposit_id'];
        $admin_note = trim($_POST['admin_note']);
        
        // Get deposit details
        $stmt = $pdo->prepare("SELECT * FROM deposits WHERE id = ? AND status = 'Pending'");
        $stmt->execute([$deposit_id]);
        $deposit = $stmt->fetch();
        
        if ($deposit) {
            $pdo->beginTransaction();
            
            try {
                // Update deposit status
                $stmt = $pdo->prepare("UPDATE deposits SET status = 'Approved', admin_note = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$admin_note, $deposit_id]);
                
                // Add money to user's wallet
                updateWalletBalance($deposit['user_id'], $deposit['amount'], $pdo);
                
                // Add transaction record
                addTransaction($deposit['user_id'], $deposit['amount'], 'credit', 'UPI Deposit - Transaction ID: ' . $deposit['transaction_id'], $pdo);
                
                $pdo->commit();
                
                $message = 'Deposit approved successfully! Money has been added to user\'s wallet.';
                $messageType = 'success';
            } catch (Exception $e) {
                $pdo->rollback();
                $message = 'Failed to approve deposit. Please try again.';
                $messageType = 'error';
            }
        } else {
            $message = 'Deposit request not found or already processed.';
            $messageType = 'error';
        }
    } elseif (isset($_POST['reject_deposit'])) {
        $deposit_id = $_POST['deposit_id'];
        $admin_note = trim($_POST['admin_note']);
        
        $stmt = $pdo->prepare("UPDATE deposits SET status = 'Rejected', admin_note = ?, updated_at = NOW() WHERE id = ? AND status = 'Pending'");
        if ($stmt->execute([$admin_note, $deposit_id])) {
            $message = 'Deposit request rejected.';
            $messageType = 'success';
        } else {
            $message = 'Failed to reject deposit request.';
            $messageType = 'error';
        }
    }
}

// Get all deposit requests
$stmt = $pdo->query("
    SELECT d.*, u.username, u.email 
    FROM deposits d
    JOIN users u ON d.user_id = u.id
    ORDER BY d.created_at DESC
");
$deposits = $stmt->fetchAll();

// Get counts for different statuses
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM deposits GROUP BY status");
$status_counts = [];
while ($row = $stmt->fetch()) {
    $status_counts[$row['status']] = $row['count'];
}
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold">Deposit Requests</h2>
            <p class="text-gray-400">Manage user UPI deposit requests</p>
        </div>
        <div class="text-sm text-gray-400">
            Total Requests: <?php echo count($deposits); ?>
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
            <div class="text-2xl font-bold"><?php echo $status_counts['Approved'] ?? 0; ?></div>
            <div class="text-sm text-gray-400">Approved</div>
        </div>

        <div class="bg-secondary rounded-lg p-4 text-center border-l-4 border-red-500">
            <i class="fas fa-times-circle text-red-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold"><?php echo $status_counts['Rejected'] ?? 0; ?></div>
            <div class="text-sm text-gray-400">Rejected</div>
        </div>
    </div>

    <!-- Deposit Requests List -->
    <div class="bg-secondary rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-list text-highlight mr-2"></i>
            All Deposit Requests
        </h3>

        <?php if (empty($deposits)): ?>
            <div class="text-center py-8">
                <i class="fas fa-inbox text-4xl text-gray-500 mb-4"></i>
                <h4 class="text-lg font-semibold mb-2">No Deposit Requests</h4>
                <p class="text-gray-400">Deposit requests will appear here when users submit them</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($deposits as $deposit): ?>
                    <div class="bg-accent rounded-lg p-4 border-l-4 <?php echo $deposit['status'] == 'Pending' ? 'border-yellow-500' : ($deposit['status'] == 'Approved' ? 'border-green-500' : 'border-red-500'); ?>">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <div class="w-10 h-10 bg-highlight rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold"><?php echo htmlspecialchars($deposit['username']); ?></h4>
                                        <p class="text-sm text-gray-400"><?php echo htmlspecialchars($deposit['email']); ?></p>
                                    </div>
                                </div>
                            </div>
                            <span class="bg-<?php echo $deposit['status'] == 'Pending' ? 'yellow' : ($deposit['status'] == 'Approved' ? 'green' : 'red'); ?>-600 text-white px-3 py-1 rounded text-sm">
                                <?php echo $deposit['status']; ?>
                            </span>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-3 text-sm">
                            <div>
                                <i class="fas fa-money-bill text-green-500 mr-1"></i>
                                <strong>Amount:</strong> <?php echo formatCurrency($deposit['amount']); ?>
                            </div>
                            <div>
                                <i class="fas fa-receipt text-blue-500 mr-1"></i>
                                <strong>Transaction ID:</strong> 
                                <span class="font-mono"><?php echo htmlspecialchars($deposit['transaction_id']); ?></span>
                            </div>
                            <div>
                                <i class="fas fa-clock text-gray-500 mr-1"></i>
                                <strong>Requested:</strong> <?php echo date('M j, Y g:i A', strtotime($deposit['created_at'])); ?>
                            </div>
                            <?php if ($deposit['status'] != 'Pending'): ?>
                                <div>
                                    <i class="fas fa-check text-purple-500 mr-1"></i>
                                    <strong>Updated:</strong> <?php echo date('M j, Y g:i A', strtotime($deposit['updated_at'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($deposit['admin_note'])): ?>
                            <div class="bg-secondary rounded-lg p-3 mb-3">
                                <h5 class="font-semibold text-sm mb-1 text-blue-400">Admin Note:</h5>
                                <p class="text-sm"><?php echo htmlspecialchars($deposit['admin_note']); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($deposit['status'] == 'Pending'): ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <!-- Approve Form -->
                                <form method="POST" class="bg-green-600 bg-opacity-20 border border-green-600 rounded-lg p-3">
                                    <input type="hidden" name="deposit_id" value="<?php echo $deposit['id']; ?>">
                                    <label class="block text-sm font-medium mb-2 text-green-400">Approve with Note:</label>
                                    <textarea name="admin_note" rows="2" class="w-full bg-accent border border-gray-600 rounded px-3 py-2 text-white placeholder-gray-400 text-sm mb-2" placeholder="Optional approval note..."></textarea>
                                    <button type="submit" name="approve_deposit" onclick="return confirm('Are you sure you want to APPROVE this deposit? Money will be added to user\'s wallet.')" class="w-full bg-green-600 hover:bg-green-700 transition text-white font-semibold py-2 px-3 rounded text-sm">
                                        <i class="fas fa-check mr-1"></i>Approve Deposit
                                    </button>
                                </form>

                                <!-- Reject Form -->
                                <form method="POST" class="bg-red-600 bg-opacity-20 border border-red-600 rounded-lg p-3">
                                    <input type="hidden" name="deposit_id" value="<?php echo $deposit['id']; ?>">
                                    <label class="block text-sm font-medium mb-2 text-red-400">Reject with Reason:</label>
                                    <textarea name="admin_note" rows="2" class="w-full bg-accent border border-gray-600 rounded px-3 py-2 text-white placeholder-gray-400 text-sm mb-2" placeholder="Reason for rejection..."></textarea>
                                    <button type="submit" name="reject_deposit" onclick="return confirm('Are you sure you want to REJECT this deposit request?')" class="w-full bg-red-600 hover:bg-red-700 transition text-white font-semibold py-2 px-3 rounded text-sm">
                                        <i class="fas fa-times mr-1"></i>Reject Deposit
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
