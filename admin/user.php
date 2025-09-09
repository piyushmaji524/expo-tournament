<?php
include 'common/header.php';

$message = '';
$messageType = '';

// Handle user balance update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_balance'])) {
    $user_id = $_POST['user_id'];
    $amount = floatval($_POST['amount']);
    $action = $_POST['action'];
    $description = trim($_POST['description']);
    
    if (empty($user_id) || $amount <= 0 || empty($description)) {
        $message = 'Please fill in all fields with valid values.';
        $messageType = 'error';
    } else {
        $pdo->beginTransaction();
        
        try {
            if ($action == 'add') {
                updateWalletBalance($user_id, $amount, $pdo);
                addTransaction($user_id, $amount, 'credit', 'Admin Credit: ' . $description, $pdo);
                $message = 'Balance added successfully!';
            } else {
                // Check if user has sufficient balance for deduction
                $current_balance = getUserWalletBalance($user_id, $pdo);
                if ($current_balance >= $amount) {
                    updateWalletBalance($user_id, -$amount, $pdo);
                    addTransaction($user_id, $amount, 'debit', 'Admin Debit: ' . $description, $pdo);
                    $message = 'Balance deducted successfully!';
                } else {
                    throw new Exception('Insufficient balance for deduction.');
                }
            }
            
            $pdo->commit();
            $messageType = 'success';
        } catch (Exception $e) {
            $pdo->rollback();
            $message = $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get all users with their stats
$stmt = $pdo->query("
    SELECT u.*, 
           COUNT(DISTINCT p.tournament_id) as tournaments_joined,
           SUM(CASE WHEN t.winner_id = u.id THEN 1 ELSE 0 END) as tournaments_won
    FROM users u
    LEFT JOIN participants p ON u.id = p.user_id
    LEFT JOIN tournaments t ON p.tournament_id = t.id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold">User Management</h2>
            <p class="text-gray-400">Manage users and their accounts</p>
        </div>
        <div class="text-sm text-gray-400">
            Total Users: <?php echo count($users); ?>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
        <div class="<?php echo $messageType == 'success' ? 'bg-green-600' : 'bg-red-600'; ?> text-white p-4 rounded-lg text-center">
            <i class="fas <?php echo $messageType == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-2"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Users List -->
    <div class="bg-secondary rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-users text-highlight mr-2"></i>
            All Users
        </h3>

        <?php if (empty($users)): ?>
            <div class="text-center py-8">
                <i class="fas fa-users text-4xl text-gray-500 mb-4"></i>
                <h4 class="text-lg font-semibold mb-2">No Users</h4>
                <p class="text-gray-400">No users have registered yet</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-600">
                            <th class="text-left py-3 px-2">User</th>
                            <th class="text-left py-3 px-2">Email</th>
                            <th class="text-left py-3 px-2">Wallet Balance</th>
                            <th class="text-left py-3 px-2">Tournaments</th>
                            <th class="text-left py-3 px-2">Wins</th>
                            <th class="text-left py-3 px-2">Joined</th>
                            <th class="text-left py-3 px-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="border-b border-gray-700 hover:bg-accent transition">
                                <td class="py-3 px-2">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-highlight rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                        <div>
                                            <div class="font-semibold"><?php echo htmlspecialchars($user['username']); ?></div>
                                            <div class="text-xs text-gray-400">ID: <?php echo $user['id']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-2 text-sm"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="py-3 px-2">
                                    <span class="font-bold text-green-400"><?php echo formatCurrency($user['wallet_balance']); ?></span>
                                </td>
                                <td class="py-3 px-2 text-center">
                                    <span class="bg-blue-600 text-white px-2 py-1 rounded text-xs">
                                        <?php echo $user['tournaments_joined']; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-2 text-center">
                                    <span class="bg-yellow-600 text-white px-2 py-1 rounded text-xs">
                                        <?php echo $user['tournaments_won']; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-2 text-sm"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td class="py-3 px-2">
                                    <div class="flex space-x-2">
                                        <button onclick="showUserModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', <?php echo $user['wallet_balance']; ?>)" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs transition">
                                            <i class="fas fa-wallet mr-1"></i>Wallet
                                        </button>
                                        <button onclick="viewUserHistory(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-xs transition">
                                            <i class="fas fa-history mr-1"></i>History
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- User Wallet Modal -->
<div id="userWalletModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-secondary rounded-lg p-6 w-full max-w-md">
        <div class="text-center mb-4">
            <i class="fas fa-wallet text-blue-500 text-3xl mb-2"></i>
            <h3 class="text-lg font-semibold">Manage User Wallet</h3>
            <p class="text-gray-400 text-sm" id="userInfo"></p>
        </div>
        
        <form method="POST" class="space-y-4">
            <input type="hidden" name="user_id" id="modalUserId">
            
            <div>
                <label class="block text-sm font-medium mb-2">Action</label>
                <select name="action" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-highlight focus:outline-none">
                    <option value="add">Add Money</option>
                    <option value="deduct">Deduct Money</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-2">Amount (₹)</label>
                <input type="number" name="amount" step="0.01" min="0.01" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="Enter amount">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-2">Description</label>
                <input type="text" name="description" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="Reason for transaction">
            </div>
            
            <div class="flex space-x-2">
                <button type="button" onclick="hideUserModal()" class="flex-1 bg-gray-600 hover:bg-gray-700 transition text-white py-2 px-4 rounded-lg">Cancel</button>
                <button type="submit" name="update_balance" class="flex-1 bg-blue-600 hover:bg-blue-700 transition text-white py-2 px-4 rounded-lg">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- User History Modal -->
<div id="userHistoryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-secondary rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-hidden">
        <div class="text-center mb-4">
            <i class="fas fa-history text-purple-500 text-3xl mb-2"></i>
            <h3 class="text-xl font-semibold">User Tournament History</h3>
            <p class="text-gray-400 text-sm" id="historyUserInfo"></p>
        </div>
        
        <div id="historyContent" class="overflow-y-auto max-h-[60vh] min-h-[200px]">
            <!-- Content will be loaded here -->
        </div>
        
        <div class="mt-6 text-center border-t border-gray-600 pt-4">
            <button onclick="hideHistoryModal()" class="bg-gray-600 hover:bg-gray-700 transition text-white py-2 px-6 rounded-lg">
                <i class="fas fa-times mr-2"></i>Close
            </button>
        </div>
    </div>
</div>

<script>
    function showUserModal(userId, username, balance) {
        document.getElementById('modalUserId').value = userId;
        document.getElementById('userInfo').textContent = `${username} - Current Balance: ₹${balance}`;
        document.getElementById('userWalletModal').classList.remove('hidden');
    }

    function hideUserModal() {
        document.getElementById('userWalletModal').classList.add('hidden');
        document.querySelector('form').reset();
    }

    function viewUserHistory(userId, username) {
        document.getElementById('historyUserInfo').textContent = username;
        document.getElementById('userHistoryModal').classList.remove('hidden');
        
        // Load user history via AJAX
        const historyContent = document.getElementById('historyContent');
        historyContent.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
        
        // Fetch user tournament history
        fetch('get_user_history.php?user_id=' + userId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayUserHistory(data.user_stats, data.tournaments, data.transactions);
                } else {
                    historyContent.innerHTML = `
                        <div class="text-center py-4 text-red-400">
                            <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                            <p>Error loading history: ${data.message}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                historyContent.innerHTML = `
                    <div class="text-center py-4 text-red-400">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                        <p>Error loading history</p>
                    </div>
                `;
            });
    }
    
    function displayUserHistory(userStats, tournaments, transactions) {
        const historyContent = document.getElementById('historyContent');
        
        let html = '<div class="space-y-6">';
        
        // User Statistics Overview
        html += `
            <div class="bg-accent rounded-lg p-4">
                <h4 class="font-semibold text-highlight mb-4">
                    <i class="fas fa-chart-bar mr-2"></i>User Statistics Overview
                </h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-400">₹${userStats.wallet_balance}</div>
                        <div class="text-xs text-gray-400">Current Balance</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-400">${userStats.total_tournaments}</div>
                        <div class="text-xs text-gray-400">Tournaments Joined</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-400">${userStats.tournaments_won}</div>
                        <div class="text-xs text-gray-400">Tournaments Won</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-400">${userStats.tournaments_won > 0 ? Math.round((userStats.tournaments_won / userStats.total_tournaments) * 100) : 0}%</div>
                        <div class="text-xs text-gray-400">Win Rate</div>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-lg font-bold text-green-400">₹${userStats.total_credits || 0}</div>
                        <div class="text-xs text-gray-400">Total Credits</div>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-red-400">₹${userStats.total_debits || 0}</div>
                        <div class="text-xs text-gray-400">Total Debits</div>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-gray-400">${new Date(userStats.join_date).toLocaleDateString()}</div>
                        <div class="text-xs text-gray-400">Member Since</div>
                    </div>
                </div>
            </div>
        `;
        
        // Tournament History
        if (tournaments.length > 0) {
            html += `
                <div class="bg-accent rounded-lg p-4">
                    <h4 class="font-semibold text-highlight mb-3">
                        <i class="fas fa-trophy mr-2"></i>Tournament History (${tournaments.length})
                    </h4>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
            `;
            
            tournaments.forEach(tournament => {
                const statusColor = tournament.status === 'Completed' ? 'green' : 
                                   tournament.status === 'Active' ? 'blue' : 'yellow';
                const isWinner = tournament.is_winner == 1;
                
                html += `
                    <div class="bg-secondary rounded-lg p-3 flex justify-between items-center">
                        <div class="flex-1">
                            <div class="font-medium">${tournament.title}</div>
                            <div class="text-xs text-gray-400 grid grid-cols-2 gap-4 mt-1">
                                <span>Entry: ₹${tournament.entry_fee}</span>
                                <span>Prize: ₹${tournament.prize_pool}</span>
                            </div>
                            <div class="text-xs text-gray-400">
                                Joined: ${new Date(tournament.joined_at).toLocaleDateString()}
                            </div>
                        </div>
                        <div class="text-right ml-4">
                            <div class="text-xs bg-${statusColor}-600 text-white px-2 py-1 rounded mb-1">
                                ${tournament.status}
                            </div>
                            ${isWinner ? '<div class="text-xs bg-yellow-600 text-white px-2 py-1 rounded"><i class="fas fa-crown mr-1"></i>Winner</div>' : ''}
                        </div>
                    </div>
                `;
            });
            
            html += '</div></div>';
        } else {
            html += `
                <div class="bg-accent rounded-lg p-4 text-center text-gray-400">
                    <i class="fas fa-trophy text-2xl mb-2"></i>
                    <p>No tournament history found</p>
                </div>
            `;
        }
        
        // Recent Transactions
        if (transactions.length > 0) {
            html += `
                <div class="bg-accent rounded-lg p-4">
                    <h4 class="font-semibold text-highlight mb-3">
                        <i class="fas fa-exchange-alt mr-2"></i>Recent Transactions (${transactions.length})
                    </h4>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
            `;
            
            transactions.forEach(transaction => {
                const typeColor = transaction.type === 'credit' ? 'green' : 'red';
                const typeIcon = transaction.type === 'credit' ? 'fa-plus' : 'fa-minus';
                
                html += `
                    <div class="bg-secondary rounded-lg p-3 flex justify-between items-center">
                        <div class="flex-1">
                            <div class="font-medium">${transaction.description}</div>
                            <div class="text-xs text-gray-400">
                                ${new Date(transaction.created_at).toLocaleDateString()} ${new Date(transaction.created_at).toLocaleTimeString()}
                            </div>
                        </div>
                        <div class="text-${typeColor}-400 font-bold ml-4">
                            <i class="fas ${typeIcon} mr-1"></i>₹${transaction.amount}
                        </div>
                    </div>
                `;
            });
            
            html += '</div></div>';
        } else {
            html += `
                <div class="bg-accent rounded-lg p-4 text-center text-gray-400">
                    <i class="fas fa-exchange-alt text-2xl mb-2"></i>
                    <p>No transaction history found</p>
                </div>
            `;
        }
        
        html += '</div>';
        historyContent.innerHTML = html;
    }

    function hideHistoryModal() {
        document.getElementById('userHistoryModal').classList.add('hidden');
    }

    // Close modals when clicking outside
    document.getElementById('userWalletModal').addEventListener('click', function(e) {
        if (e.target === this) hideUserModal();
    });

    document.getElementById('userHistoryModal').addEventListener('click', function(e) {
        if (e.target === this) hideHistoryModal();
    });
</script>

<?php include 'common/bottom.php'; ?>
