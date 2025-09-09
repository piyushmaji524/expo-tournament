<?php
include 'common/header.php';

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_settings'])) {
        $referrerReward = (float)$_POST['referrer_reward'];
        $referredReward = (float)$_POST['referred_reward'];
        $adminCode = trim($_POST['admin_referral_code']);
        
        try {
            $pdo->beginTransaction();
            
            // Update referral rewards
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'referral_reward_referrer'");
            $stmt->execute([$referrerReward]);
            
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'referral_reward_referred'");
            $stmt->execute([$referredReward]);
            
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'admin_referral_code'");
            $stmt->execute([$adminCode]);
            
            $pdo->commit();
            $message = 'Referral settings updated successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $pdo->rollback();
            $message = 'Failed to update settings: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get current referral settings
$referralSettings = getReferralSettings($pdo);

// Get referral statistics
$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_referrals,
        SUM(referrer_reward) as total_referrer_rewards,
        SUM(referred_reward) as total_referred_rewards
    FROM referrals
");
$referralStats = $stmt->fetch();

// Get top referrers
$stmt = $pdo->query("
    SELECT 
        u.id,
        u.username,
        u.total_referrals,
        u.referral_earnings,
        u.created_at
    FROM users u
    WHERE u.total_referrals > 0
    ORDER BY u.total_referrals DESC, u.referral_earnings DESC
    LIMIT 10
");
$topReferrers = $stmt->fetchAll();

// Get recent referrals
$stmt = $pdo->query("
    SELECT 
        r.*,
        referrer.username as referrer_username,
        referred.username as referred_username
    FROM referrals r
    JOIN users referrer ON r.referrer_id = referrer.id
    JOIN users referred ON r.referred_id = referred.id
    ORDER BY r.created_at DESC
    LIMIT 20
");
$recentReferrals = $stmt->fetchAll();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold flex items-center">
            <i class="fas fa-users mr-3 text-highlight"></i>
            Referral Management
        </h1>
    </div>

    <?php if ($message): ?>
        <div class="bg-<?php echo $messageType == 'success' ? 'green' : 'red'; ?>-900 border border-<?php echo $messageType == 'success' ? 'green' : 'red'; ?>-700 rounded-lg p-4">
            <p class="text-<?php echo $messageType == 'success' ? 'green' : 'red'; ?>-300"><?php echo $message; ?></p>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-secondary rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Referrals</p>
                    <p class="text-2xl font-bold text-white"><?php echo $referralStats['total_referrals'] ?? 0; ?></p>
                </div>
                <i class="fas fa-user-friends text-3xl text-blue-400"></i>
            </div>
        </div>

        <div class="bg-secondary rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Referrer Rewards</p>
                    <p class="text-2xl font-bold text-green-400"><?php echo formatCurrency($referralStats['total_referrer_rewards'] ?? 0); ?></p>
                </div>
                <i class="fas fa-gift text-3xl text-green-400"></i>
            </div>
        </div>

        <div class="bg-secondary rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Referred Rewards</p>
                    <p class="text-2xl font-bold text-yellow-400"><?php echo formatCurrency($referralStats['total_referred_rewards'] ?? 0); ?></p>
                </div>
                <i class="fas fa-star text-3xl text-yellow-400"></i>
            </div>
        </div>

        <div class="bg-secondary rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Rewards Paid</p>
                    <p class="text-2xl font-bold text-highlight"><?php echo formatCurrency(($referralStats['total_referrer_rewards'] ?? 0) + ($referralStats['total_referred_rewards'] ?? 0)); ?></p>
                </div>
                <i class="fas fa-money-bill-wave text-3xl text-highlight"></i>
            </div>
        </div>
    </div>

    <!-- Referral Settings -->
    <div class="bg-secondary rounded-lg">
        <div class="p-6 border-b border-gray-700">
            <h2 class="text-xl font-semibold flex items-center">
                <i class="fas fa-cog mr-2 text-highlight"></i>
                Referral Settings
            </h2>
        </div>
        <div class="p-6">
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">Referrer Reward (₹)</label>
                        <input type="number" name="referrer_reward" step="0.01" 
                               value="<?php echo $referralSettings['referral_reward_referrer'] ?? 50; ?>"
                               class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-highlight focus:outline-none" required>
                        <p class="text-xs text-gray-400 mt-1">Amount given to users who refer others</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Referred User Reward (₹)</label>
                        <input type="number" name="referred_reward" step="0.01" 
                               value="<?php echo $referralSettings['referral_reward_referred'] ?? 25; ?>"
                               class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-highlight focus:outline-none" required>
                        <p class="text-xs text-gray-400 mt-1">Welcome bonus for new referred users</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Admin Referral Code</label>
                        <input type="text" name="admin_referral_code" 
                               value="<?php echo $referralSettings['admin_referral_code'] ?? 'ADM001'; ?>"
                               class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-highlight focus:outline-none" required>
                        <p class="text-xs text-gray-400 mt-1">Default code for users with no referrer</p>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" name="update_settings" 
                            class="bg-highlight hover:bg-red-600 transition px-6 py-2 rounded-lg font-medium">
                        <i class="fas fa-save mr-2"></i>Update Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Top Referrers -->
    <div class="bg-secondary rounded-lg">
        <div class="p-6 border-b border-gray-700">
            <h2 class="text-xl font-semibold flex items-center">
                <i class="fas fa-crown mr-2 text-yellow-400"></i>
                Top Referrers
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-accent">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">User ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Username</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Total Referrals</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Total Earnings</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Joined Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php if (empty($topReferrers)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                <i class="fas fa-users text-3xl mb-2"></i>
                                <p>No referrers found yet</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($topReferrers as $index => $referrer): ?>
                            <tr class="hover:bg-accent transition">
                                <td class="px-6 py-4">
                                    <span class="flex items-center justify-center w-8 h-8 bg-highlight rounded-full text-sm font-bold">
                                        <?php echo $index + 1; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-300">#<?php echo $referrer['id']; ?></td>
                                <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($referrer['username']); ?></td>
                                <td class="px-6 py-4">
                                    <span class="bg-blue-900 text-blue-300 px-2 py-1 rounded text-sm">
                                        <?php echo $referrer['total_referrals']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-green-400 font-medium">
                                    <?php echo formatCurrency($referrer['referral_earnings']); ?>
                                </td>
                                <td class="px-6 py-4 text-gray-400 text-sm">
                                    <?php echo date('M j, Y', strtotime($referrer['created_at'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Referrals -->
    <div class="bg-secondary rounded-lg">
        <div class="p-6 border-b border-gray-700">
            <h2 class="text-xl font-semibold flex items-center">
                <i class="fas fa-clock mr-2 text-highlight"></i>
                Recent Referrals
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-accent">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Referrer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Referred User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Referrer Reward</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Referred Reward</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php if (empty($recentReferrals)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                                <i class="fas fa-handshake text-3xl mb-2"></i>
                                <p>No referrals found yet</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentReferrals as $referral): ?>
                            <tr class="hover:bg-accent transition">
                                <td class="px-6 py-4 text-gray-300">#<?php echo $referral['id']; ?></td>
                                <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($referral['referrer_username']); ?></td>
                                <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($referral['referred_username']); ?></td>
                                <td class="px-6 py-4 text-green-400"><?php echo formatCurrency($referral['referrer_reward']); ?></td>
                                <td class="px-6 py-4 text-yellow-400"><?php echo formatCurrency($referral['referred_reward']); ?></td>
                                <td class="px-6 py-4 text-gray-400 text-sm">
                                    <?php echo date('M j, Y g:i A', strtotime($referral['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="bg-green-900 text-green-300 px-2 py-1 rounded text-xs uppercase">
                                        <?php echo $referral['status']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
