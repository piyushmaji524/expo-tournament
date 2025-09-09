<?php
include 'common/header.php';

// Get dashboard statistics
// Total Users
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$total_users = $stmt->fetch()['count'];

// Total Tournaments
$stmt = $pdo->query("SELECT COUNT(*) as count FROM tournaments");
$total_tournaments = $stmt->fetch()['count'];

// Total Prize Distributed (completed tournaments)
$stmt = $pdo->query("SELECT SUM(prize_pool) as total FROM tournaments WHERE status = 'Completed'");
$total_prize_distributed = $stmt->fetch()['total'] ?? 0;

// Total Revenue (commission from completed tournaments)
$stmt = $pdo->query("SELECT SUM(prize_pool * commission_percentage / 100) as total FROM tournaments WHERE status = 'Completed'");
$total_revenue = $stmt->fetch()['total'] ?? 0;

// Pending deposit requests
$stmt = $pdo->query("SELECT COUNT(*) as count FROM deposits WHERE status = 'pending'");
$pending_deposits = $stmt->fetch()['count'];

// Pending withdrawal requests
$stmt = $pdo->query("SELECT COUNT(*) as count FROM withdrawals WHERE status = 'pending'");
$pending_withdrawals = $stmt->fetch()['count'];

// Total active banners
$stmt = $pdo->query("SELECT COUNT(*) as count FROM banners WHERE is_active = 1");
$active_banners = $stmt->fetch()['count'];

// Recent tournaments
$stmt = $pdo->query("
    SELECT t.*, COUNT(p.id) as participant_count
    FROM tournaments t 
    LEFT JOIN participants p ON t.id = p.tournament_id
    GROUP BY t.id
    ORDER BY t.created_at DESC 
    LIMIT 5
");
$recent_tournaments = $stmt->fetchAll();

// Recent users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$recent_users = $stmt->fetchAll();
?>

<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-accent to-highlight rounded-lg p-6 text-center">
        <h2 class="text-2xl font-bold mb-2">Welcome to Admin Dashboard</h2>
        <p class="text-blue-100">Manage tournaments, users, and monitor platform performance</p>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-7 gap-4">
        <div class="bg-secondary rounded-lg p-4 text-center border-l-4 border-blue-500">
            <i class="fas fa-users text-blue-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold"><?php echo number_format($total_users); ?></div>
            <div class="text-sm text-gray-400">Total Users</div>
        </div>

        <div class="bg-secondary rounded-lg p-4 text-center border-l-4 border-green-500">
            <i class="fas fa-trophy text-green-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold"><?php echo number_format($total_tournaments); ?></div>
            <div class="text-sm text-gray-400">Total Tournaments</div>
        </div>

        <div class="bg-secondary rounded-lg p-4 text-center border-l-4 border-yellow-500">
            <i class="fas fa-money-bill-wave text-yellow-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold"><?php echo formatCurrency($total_prize_distributed); ?></div>
            <div class="text-sm text-gray-400">Prize Distributed</div>
        </div>

        <div class="bg-secondary rounded-lg p-4 text-center border-l-4 border-highlight">
            <i class="fas fa-chart-line text-highlight text-2xl mb-2"></i>
            <div class="text-2xl font-bold"><?php echo formatCurrency($total_revenue); ?></div>
            <div class="text-sm text-gray-400">Total Revenue</div>
        </div>

        <div class="bg-secondary rounded-lg p-4 text-center border-l-4 border-orange-500">
            <i class="fas fa-arrow-down text-orange-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold"><?php echo number_format($pending_deposits); ?></div>
            <div class="text-sm text-gray-400">Pending Deposits</div>
        </div>

        <div class="bg-secondary rounded-lg p-4 text-center border-l-4 border-cyan-500">
            <i class="fas fa-arrow-up text-cyan-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold"><?php echo number_format($pending_withdrawals); ?></div>
            <div class="text-sm text-gray-400">Pending Withdrawals</div>
        </div>

        <div class="bg-secondary rounded-lg p-4 text-center border-l-4 border-purple-500">
            <i class="fas fa-images text-purple-500 text-2xl mb-2"></i>
            <div class="text-2xl font-bold"><?php echo number_format($active_banners); ?></div>
            <div class="text-sm text-gray-400">Active Banners</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <a href="tournament.php" class="bg-green-600 hover:bg-green-700 transition rounded-lg p-6 text-center">
            <i class="fas fa-plus text-3xl mb-3"></i>
            <div class="text-lg font-semibold">Create Tournament</div>
            <div class="text-sm opacity-80">Add new tournament</div>
        </a>

        <a href="user.php" class="bg-blue-600 hover:bg-blue-700 transition rounded-lg p-6 text-center">
            <i class="fas fa-users text-3xl mb-3"></i>
            <div class="text-lg font-semibold">Manage Users</div>
            <div class="text-sm opacity-80">View all users</div>
        </a>

        <a href="banners.php" class="bg-purple-600 hover:bg-purple-700 transition rounded-lg p-6 text-center">
            <i class="fas fa-images text-3xl mb-3"></i>
            <div class="text-lg font-semibold">Manage Banners</div>
            <div class="text-sm opacity-80">Homepage banners</div>
        </a>

        <a href="deposits.php" class="bg-orange-600 hover:bg-orange-700 transition rounded-lg p-6 text-center relative">
            <i class="fas fa-arrow-down text-3xl mb-3"></i>
            <div class="text-lg font-semibold">Deposit Requests</div>
            <div class="text-sm opacity-80">Review pending deposits</div>
            <?php if ($pending_deposits > 0): ?>
                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center"><?php echo $pending_deposits; ?></span>
            <?php endif; ?>
        </a>

        <a href="withdrawals.php" class="bg-cyan-600 hover:bg-cyan-700 transition rounded-lg p-6 text-center relative">
            <i class="fas fa-arrow-up text-3xl mb-3"></i>
            <div class="text-lg font-semibold">Withdrawal Requests</div>
            <div class="text-sm opacity-80">Process withdrawals</div>
            <?php if ($pending_withdrawals > 0): ?>
                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center"><?php echo $pending_withdrawals; ?></span>
            <?php endif; ?>
        </a>

        <a href="tournament.php" class="bg-indigo-600 hover:bg-indigo-700 transition rounded-lg p-6 text-center">
            <i class="fas fa-trophy text-3xl mb-3"></i>
            <div class="text-lg font-semibold">View Tournaments</div>
            <div class="text-sm opacity-80">Manage all tournaments</div>
        </a>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Tournaments -->
        <div class="bg-secondary rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">
                <i class="fas fa-trophy text-highlight mr-2"></i>
                Recent Tournaments
            </h3>

            <?php if (empty($recent_tournaments)): ?>
                <div class="text-center py-4 text-gray-400">
                    <i class="fas fa-trophy text-2xl mb-2"></i>
                    <p>No tournaments created yet</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recent_tournaments as $tournament): ?>
                        <div class="bg-accent rounded-lg p-3">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-sm"><?php echo htmlspecialchars($tournament['title']); ?></h4>
                                    <p class="text-xs text-gray-400"><?php echo htmlspecialchars($tournament['game_name']); ?></p>
                                </div>
                                <span class="bg-<?php echo $tournament['status'] == 'Upcoming' ? 'blue' : ($tournament['status'] == 'Live' ? 'green' : 'gray'); ?>-600 text-white px-2 py-1 rounded text-xs">
                                    <?php echo $tournament['status']; ?>
                                </span>
                            </div>
                            <div class="flex justify-between text-xs text-gray-400">
                                <span><i class="fas fa-users mr-1"></i><?php echo $tournament['participant_count']; ?> players</span>
                                <span><i class="fas fa-trophy mr-1"></i><?php echo formatCurrency($tournament['prize_pool']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4 text-center">
                    <a href="tournament.php" class="text-highlight hover:text-red-400 text-sm">
                        View All Tournaments <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Users -->
        <div class="bg-secondary rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">
                <i class="fas fa-users text-highlight mr-2"></i>
                Recent Users
            </h3>

            <?php if (empty($recent_users)): ?>
                <div class="text-center py-4 text-gray-400">
                    <i class="fas fa-users text-2xl mb-2"></i>
                    <p>No users registered yet</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recent_users as $user): ?>
                        <div class="bg-accent rounded-lg p-3">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="font-semibold text-sm"><?php echo htmlspecialchars($user['username']); ?></h4>
                                    <p class="text-xs text-gray-400"><?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-green-400"><?php echo formatCurrency($user['wallet_balance']); ?></div>
                                    <div class="text-xs text-gray-400"><?php echo date('M j', strtotime($user['created_at'])); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4 text-center">
                    <a href="user.php" class="text-highlight hover:text-red-400 text-sm">
                        View All Users <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- System Status -->
    <div class="bg-secondary rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-server text-highlight mr-2"></i>
            System Status
        </h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="w-3 h-3 bg-green-500 rounded-full mx-auto mb-2"></div>
                <div class="text-sm font-semibold">Database</div>
                <div class="text-xs text-gray-400">Online</div>
            </div>

            <div class="text-center">
                <div class="w-3 h-3 bg-green-500 rounded-full mx-auto mb-2"></div>
                <div class="text-sm font-semibold">Web Server</div>
                <div class="text-xs text-gray-400">Running</div>
            </div>

            <div class="text-center">
                <div class="w-3 h-3 bg-green-500 rounded-full mx-auto mb-2"></div>
                <div class="text-sm font-semibold">PHP</div>
                <div class="text-xs text-gray-400"><?php echo PHP_VERSION; ?></div>
            </div>

            <div class="text-center">
                <div class="w-3 h-3 bg-green-500 rounded-full mx-auto mb-2"></div>
                <div class="text-sm font-semibold">App Status</div>
                <div class="text-xs text-gray-400">Active</div>
            </div>
        </div>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
