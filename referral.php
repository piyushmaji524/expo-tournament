<?php
require_once 'common/config.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Get user info and referral stats
$stmt = $pdo->prepare("SELECT username, referral_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$referralStats = getUserReferralStats($user_id, $pdo);
$referredUsers = getUserReferredUsers($user_id, $pdo);
$referralSettings = getReferralSettings($pdo);

// Generate referral link
$referralLink = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/login.php?ref=' . $user['referral_id'];

include 'common/header.php';
?>

<div class="max-w-md mx-auto space-y-6 pb-20">
    <!-- Page Header -->
    <div class="bg-secondary rounded-lg p-6 text-center">
        <div class="mb-4">
            <i class="fas fa-users text-4xl text-highlight"></i>
        </div>
        <h1 class="text-2xl font-bold mb-2">Referral Program</h1>
        <p class="text-gray-400 text-sm">Invite friends and earn rewards together!</p>
    </div>

    <!-- Referral Ad -->
    <div class="bg-gradient-to-r from-highlight to-red-600 rounded-lg p-6 text-center">
        <h2 class="text-xl font-bold mb-3">🎉 Refer Your Friends! 🎉</h2>
        <p class="text-lg mb-2">
            Refer your friend and get <span class="font-bold">₹<?php echo $referralSettings['referral_reward_referrer'] ?? '50'; ?></span>
        </p>
        <p class="text-lg">
            Your friend gets <span class="font-bold">₹<?php echo $referralSettings['referral_reward_referred'] ?? '25'; ?></span>
        </p>
        <div class="mt-4 text-sm opacity-90">
            <p>Win-win for everyone! 🚀</p>
        </div>
    </div>

    <!-- Referral Stats -->
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-secondary rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-highlight"><?php echo $referralStats['total_referrals'] ?? 0; ?></div>
            <div class="text-sm text-gray-400">Total Referrals</div>
        </div>
        <div class="bg-secondary rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-green-400"><?php echo formatCurrency($referralStats['referral_earnings'] ?? 0); ?></div>
            <div class="text-sm text-gray-400">Earnings</div>
        </div>
    </div>

    <!-- Referral ID & Link -->
    <div class="bg-secondary rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4 flex items-center">
            <i class="fas fa-id-card mr-2 text-highlight"></i>
            Your Referral Details
        </h3>
        
        <!-- Referral ID -->
        <div class="mb-4">
            <label class="block text-sm text-gray-400 mb-2">Your Referral ID</label>
            <div class="flex items-center space-x-2">
                <input type="text" value="<?php echo htmlspecialchars($user['referral_id']); ?>" readonly 
                       class="flex-1 bg-accent border border-gray-600 rounded-lg px-3 py-2 text-white" id="referralId">
                <button onclick="copyToClipboard('referralId')" 
                        class="bg-highlight hover:bg-red-600 transition px-3 py-2 rounded-lg">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        </div>

        <!-- Referral Link -->
        <div class="mb-4">
            <label class="block text-sm text-gray-400 mb-2">Your Referral Link</label>
            <div class="flex items-center space-x-2">
                <input type="text" value="<?php echo $referralLink; ?>" readonly 
                       class="flex-1 bg-accent border border-gray-600 rounded-lg px-3 py-2 text-white text-xs" id="referralLink">
                <button onclick="copyToClipboard('referralLink')" 
                        class="bg-highlight hover:bg-red-600 transition px-3 py-2 rounded-lg">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-2 gap-3">
            <button onclick="copyToClipboard('referralLink')" 
                    class="bg-blue-600 hover:bg-blue-700 transition py-2 px-4 rounded-lg text-sm">
                <i class="fas fa-copy mr-1"></i>Copy Link
            </button>
            <button onclick="shareReferralLink()" 
                    class="bg-green-600 hover:bg-green-700 transition py-2 px-4 rounded-lg text-sm">
                <i class="fas fa-share mr-1"></i>Share Link
            </button>
        </div>
    </div>

    <!-- Referred Users List -->
    <div class="bg-secondary rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4 flex items-center">
            <i class="fas fa-list mr-2 text-highlight"></i>
            Your Referred Users
        </h3>
        
        <?php if (!empty($referredUsers)): ?>
            <div class="space-y-3">
                <?php foreach ($referredUsers as $referred): ?>
                    <div class="bg-accent rounded-lg p-3 flex items-center justify-between">
                        <div>
                            <div class="font-medium"><?php echo htmlspecialchars($referred['username']); ?></div>
                            <div class="text-xs text-gray-400">
                                Joined: <?php echo date('M j, Y', strtotime($referred['created_at'])); ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-green-400 font-medium">+<?php echo formatCurrency($referred['referrer_reward']); ?></div>
                            <div class="text-xs text-gray-400">Earned</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-8">
                <i class="fas fa-user-friends text-4xl text-gray-600 mb-3"></i>
                <p class="text-gray-400">No referrals yet</p>
                <p class="text-sm text-gray-500 mt-1">Share your referral link to start earning!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- How it Works -->
    <div class="bg-secondary rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4 flex items-center">
            <i class="fas fa-question-circle mr-2 text-highlight"></i>
            How it Works
        </h3>
        <div class="space-y-3 text-sm">
            <div class="flex items-start space-x-3">
                <div class="bg-highlight rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold">1</div>
                <div>
                    <div class="font-medium">Share your referral link</div>
                    <div class="text-gray-400">Send your unique link to friends</div>
                </div>
            </div>
            <div class="flex items-start space-x-3">
                <div class="bg-highlight rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold">2</div>
                <div>
                    <div class="font-medium">Friend signs up</div>
                    <div class="text-gray-400">They create an account using your link</div>
                </div>
            </div>
            <div class="flex items-start space-x-3">
                <div class="bg-highlight rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold">3</div>
                <div>
                    <div class="font-medium">Both get rewarded</div>
                    <div class="text-gray-400">Instant bonus added to both wallets</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        showNotification('Copied to clipboard!', 'success');
    } catch (err) {
        showNotification('Failed to copy', 'error');
    }
}

function shareReferralLink() {
    const referralLink = document.getElementById('referralLink').value;
    const shareData = {
        title: 'Join Adept Play Tournament Platform',
        text: 'Join me on Adept Play and get instant bonus! Use my referral link:',
        url: referralLink
    };
    
    if (navigator.share) {
        navigator.share(shareData);
    } else {
        // Fallback for browsers that don't support Web Share API
        copyToClipboard('referralLink');
    }
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-4 py-2 rounded-lg text-white z-50 ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>

<?php include 'common/bottom.php'; ?>
