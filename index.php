<?php
include 'common/header.php';

$message = '';
$messageType = '';

// Get active banners for slider
$banner_stmt = $pdo->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY display_order ASC, created_at DESC");
$banners = $banner_stmt->fetchAll();

// Handle tournament joining
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['join_tournament'])) {
    $tournament_id = $_POST['tournament_id'];
    
    // Get tournament details
    $stmt = $pdo->prepare("SELECT * FROM tournaments WHERE id = ? AND status = 'Upcoming'");
    $stmt->execute([$tournament_id]);
    $tournament = $stmt->fetch();
    
    if ($tournament) {
        // Check if user already joined
        $stmt = $pdo->prepare("SELECT id FROM participants WHERE user_id = ? AND tournament_id = ?");
        $stmt->execute([$_SESSION['user_id'], $tournament_id]);
        
        if ($stmt->fetch()) {
            $message = 'You have already joined this tournament!';
            $messageType = 'error';
        } else {
            // Check user balance
            $user_balance = getUserWalletBalance($_SESSION['user_id'], $pdo);
            
            if ($user_balance >= $tournament['entry_fee']) {
                // Deduct entry fee and add participant
                $pdo->beginTransaction();
                
                try {
                    // Deduct from wallet
                    updateWalletBalance($_SESSION['user_id'], -$tournament['entry_fee'], $pdo);
                    
                    // Add transaction record
                    addTransaction($_SESSION['user_id'], $tournament['entry_fee'], 'debit', 'Tournament entry fee for: ' . $tournament['title'], $pdo);
                    
                    // Add participant
                    $stmt = $pdo->prepare("INSERT INTO participants (user_id, tournament_id) VALUES (?, ?)");
                    $stmt->execute([$_SESSION['user_id'], $tournament_id]);
                    
                    $pdo->commit();
                    
                    $message = 'Successfully joined tournament: ' . $tournament['title'];
                    $messageType = 'success';
                } catch (Exception $e) {
                    $pdo->rollback();
                    $message = 'Failed to join tournament. Please try again.';
                    $messageType = 'error';
                }
            } else {
                $message = 'Insufficient balance. You need ' . formatCurrency($tournament['entry_fee']) . ' to join this tournament.';
                $messageType = 'error';
            }
        }
    } else {
        $message = 'Tournament not found or no longer available.';
        $messageType = 'error';
    }
}

// Get upcoming tournaments
$stmt = $pdo->prepare("
    SELECT t.*, 
           COUNT(p.id) as participant_count,
           CASE WHEN up.user_id IS NOT NULL THEN 1 ELSE 0 END as user_joined
    FROM tournaments t 
    LEFT JOIN participants p ON t.id = p.tournament_id
    LEFT JOIN participants up ON t.id = up.tournament_id AND up.user_id = ?
    WHERE t.status = 'Upcoming' AND t.match_time > NOW()
    GROUP BY t.id
    ORDER BY t.match_time ASC
");
$stmt->execute([$_SESSION['user_id']]);
$tournaments = $stmt->fetchAll();
?>

<div class="container mx-auto px-4 py-6">
    <!-- Banner Slider -->
    <?php if (!empty($banners)): ?>
        <div class="relative mb-6 overflow-hidden rounded-lg bg-secondary">
            <div class="banner-slider" id="bannerSlider">
                <?php foreach ($banners as $index => $banner): ?>
                    <div class="banner-slide <?php echo $index === 0 ? 'active' : ''; ?>" data-slide="<?php echo $index; ?>">
                        <?php if ($banner['link_url']): ?>
                            <a href="<?php echo htmlspecialchars($banner['link_url']); ?>" target="_blank">
                        <?php endif; ?>
                        <img src="<?php echo htmlspecialchars($banner['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($banner['title']); ?>" 
                             class="w-full h-48 md:h-64 lg:h-80 object-cover">
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-6">
                            <h3 class="text-xl md:text-2xl font-bold text-white mb-2"><?php echo htmlspecialchars($banner['title']); ?></h3>
                        </div>
                        <?php if ($banner['link_url']): ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Navigation arrows -->
            <?php if (count($banners) > 1): ?>
                <button class="banner-nav banner-nav-prev" onclick="changeBannerSlide(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="banner-nav banner-nav-next" onclick="changeBannerSlide(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                <!-- Dots indicator -->
                <div class="banner-dots">
                    <?php foreach ($banners as $index => $banner): ?>
                        <button class="banner-dot <?php echo $index === 0 ? 'active' : ''; ?>" onclick="goToBannerSlide(<?php echo $index; ?>)"></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Messages -->
    <?php if ($message): ?>
        <div class="<?php echo $messageType == 'success' ? 'bg-green-600' : 'bg-red-600'; ?> text-white p-4 rounded-lg mb-6 text-center">
            <i class="fas <?php echo $messageType == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-2"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-accent to-highlight rounded-lg p-4 mb-4 text-center">
        <h2 class="text-xl font-bold mb-1">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p class="text-blue-100 text-sm">Join exciting tournaments and win amazing prizes</p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-secondary rounded-lg p-4 text-center">
            <i class="fas fa-trophy text-highlight text-2xl mb-2"></i>
            <div class="text-lg font-bold"><?php echo count($tournaments); ?></div>
            <div class="text-sm text-gray-400">Available</div>
        </div>
        <div class="bg-secondary rounded-lg p-4 text-center">
            <i class="fas fa-wallet text-highlight text-2xl mb-2"></i>
            <div class="text-lg font-bold"><?php echo formatCurrency(getUserWalletBalance($_SESSION['user_id'], $pdo)); ?></div>
            <div class="text-sm text-gray-400">Your Balance</div>
        </div>
    </div>

    <!-- Tournaments Section -->
    <div class="mb-6">
        <h3 class="text-xl font-bold mb-4">
            <i class="fas fa-gamepad mr-2 text-highlight"></i>
            Upcoming Tournaments
        </h3>

        <?php if (empty($tournaments)): ?>
            <div class="bg-secondary rounded-lg p-8 text-center">
                <i class="fas fa-calendar-times text-4xl text-gray-500 mb-4"></i>
                <h3 class="text-lg font-semibold mb-2">No Tournaments Available</h3>
                <p class="text-gray-400">Check back later for new tournaments!</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($tournaments as $tournament): ?>
                    <div class="bg-secondary rounded-lg p-4 border-l-4 <?php echo $tournament['user_joined'] ? 'border-green-500' : 'border-highlight'; ?>">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold mb-1"><?php echo htmlspecialchars($tournament['title']); ?></h4>
                                <p class="text-highlight font-medium">
                                    <i class="fas fa-gamepad mr-1"></i>
                                    <?php echo htmlspecialchars($tournament['game_name']); ?>
                                </p>
                            </div>
                            <?php if ($tournament['user_joined']): ?>
                                <span class="bg-green-600 text-white px-2 py-1 rounded text-xs">
                                    <i class="fas fa-check mr-1"></i>Joined
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                            <div>
                                <i class="fas fa-clock text-highlight mr-1"></i>
                                <?php echo date('M j, Y g:i A', strtotime($tournament['match_time'])); ?>
                            </div>
                            <div>
                                <i class="fas fa-users text-highlight mr-1"></i>
                                <?php echo $tournament['participant_count']; ?> participants
                            </div>
                            <div>
                                <i class="fas fa-money-bill text-highlight mr-1"></i>
                                Entry: <?php echo formatCurrency($tournament['entry_fee']); ?>
                            </div>
                            <div>
                                <i class="fas fa-trophy text-highlight mr-1"></i>
                                Prize: <?php echo formatCurrency($tournament['prize_pool']); ?>
                            </div>
                        </div>

                        <?php if (!$tournament['user_joined']): ?>
                            <form method="POST" class="mt-4">
                                <input type="hidden" name="tournament_id" value="<?php echo $tournament['id']; ?>">
                                <button type="submit" name="join_tournament" class="w-full bg-highlight hover:bg-red-600 transition text-white font-semibold py-2 px-4 rounded-lg">
                                    <i class="fas fa-plus mr-2"></i>
                                    Join Tournament
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="mt-4 text-center text-green-400 font-semibold">
                                <i class="fas fa-check-circle mr-2"></i>
                                You have successfully joined this tournament
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 gap-4">
        <a href="my_tournaments.php" class="bg-accent hover:bg-secondary transition rounded-lg p-4 text-center">
            <i class="fas fa-gamepad text-highlight text-2xl mb-2"></i>
            <div class="font-semibold">My Tournaments</div>
            <div class="text-sm text-gray-400">View joined</div>
        </a>
        <a href="wallet.php" class="bg-accent hover:bg-secondary transition rounded-lg p-4 text-center">
            <i class="fas fa-wallet text-highlight text-2xl mb-2"></i>
            <div class="font-semibold">Add Money</div>
            <div class="text-sm text-gray-400">Top up wallet</div>
        </a>
    </div>
</div>

<style>
/* Banner Slider Styles */
.banner-slider {
    position: relative;
    width: 100%;
    height: auto;
}

.banner-slide {
    position: relative;
    display: none;
    width: 100%;
}

.banner-slide.active {
    display: block;
}

.banner-slide img {
    width: 100%;
    height: auto;
    display: block;
}

.banner-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    padding: 15px;
    cursor: pointer;
    z-index: 10;
    transition: all 0.1s ease;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.banner-nav:hover {
    background: rgba(233, 69, 96, 0.8);
}

.banner-nav-prev {
    left: 15px;
}

.banner-nav-next {
    right: 15px;
}

.banner-dots {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 10px;
    z-index: 10;
}

.banner-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
    background: transparent;
    cursor: pointer;
    transition: all 0.1s ease;
}

.banner-dot.active,
.banner-dot:hover {
    background: #e94560;
    border-color: #e94560;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .banner-nav {
        width: 40px;
        height: 40px;
        padding: 10px;
    }
    
    .banner-nav-prev {
        left: 10px;
    }
    
    .banner-nav-next {
        right: 10px;
    }
    
    .banner-dots {
        bottom: 15px;
    }
    
    .banner-dot {
        width: 10px;
        height: 10px;
    }
}
</style>

<script>
let currentBannerSlide = 0;
const bannerSlides = document.querySelectorAll('.banner-slide');
const bannerDots = document.querySelectorAll('.banner-dot');
const totalBannerSlides = bannerSlides.length;

// Auto-slide functionality
let bannerSlideInterval;

function showBannerSlide(index) {
    // Hide all slides
    bannerSlides.forEach(slide => slide.classList.remove('active'));
    bannerDots.forEach(dot => dot.classList.remove('active'));
    
    // Show current slide
    if (bannerSlides[index]) {
        bannerSlides[index].classList.add('active');
    }
    if (bannerDots[index]) {
        bannerDots[index].classList.add('active');
    }
    
    currentBannerSlide = index;
}

function nextBannerSlide() {
    currentBannerSlide = (currentBannerSlide + 1) % totalBannerSlides;
    showBannerSlide(currentBannerSlide);
}

function changeBannerSlide(direction) {
    currentBannerSlide += direction;
    
    if (currentBannerSlide >= totalBannerSlides) {
        currentBannerSlide = 0;
    } else if (currentBannerSlide < 0) {
        currentBannerSlide = totalBannerSlides - 1;
    }
    
    showBannerSlide(currentBannerSlide);
    resetBannerAutoSlide();
}

function goToBannerSlide(index) {
    showBannerSlide(index);
    resetBannerAutoSlide();
}

function startBannerAutoSlide() {
    if (totalBannerSlides > 1) {
        bannerSlideInterval = setInterval(nextBannerSlide, 5000); // Change slide every 5 seconds
    }
}

function resetBannerAutoSlide() {
    clearInterval(bannerSlideInterval);
    startBannerAutoSlide();
}

// Initialize banner slider
document.addEventListener('DOMContentLoaded', function() {
    if (totalBannerSlides > 0) {
        showBannerSlide(0);
        startBannerAutoSlide();
        
        // Pause auto-slide when user hovers over banner
        const bannerSlider = document.getElementById('bannerSlider');
        if (bannerSlider) {
            bannerSlider.addEventListener('mouseenter', () => {
                clearInterval(bannerSlideInterval);
            });
            
            bannerSlider.addEventListener('mouseleave', () => {
                startBannerAutoSlide();
            });
        }
    }
});
</script>

<?php include 'common/bottom.php'; ?>
