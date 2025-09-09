<?php
include 'common/header.php';

$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'upcoming';

// Get user's upcoming/live tournaments
$stmt = $pdo->prepare("
    SELECT t.*, p.joined_at,
           CASE WHEN t.winner_id = ? THEN 'Winner' ELSE 'Participated' END as result
    FROM tournaments t
    JOIN participants p ON t.id = p.tournament_id
    WHERE p.user_id = ? AND (t.status = 'Upcoming' OR t.status = 'Live')
    ORDER BY t.match_time ASC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$upcomingTournaments = $stmt->fetchAll();

// Get user's completed tournaments
$stmt = $pdo->prepare("
    SELECT t.*, p.joined_at,
           CASE WHEN t.winner_id = ? THEN 'Winner' ELSE 'Participated' END as result
    FROM tournaments t
    JOIN participants p ON t.id = p.tournament_id
    WHERE p.user_id = ? AND t.status = 'Completed'
    ORDER BY t.match_time DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$completedTournaments = $stmt->fetchAll();
?>

<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold mb-2">
            <i class="fas fa-gamepad text-highlight mr-2"></i>
            My Tournaments
        </h2>
        <p class="text-gray-400">Track your tournament participation and results</p>
    </div>

    <!-- Tabs -->
    <div class="flex bg-accent rounded-lg mb-6 overflow-hidden">
        <a href="?tab=upcoming" class="flex-1 py-3 px-4 text-center font-semibold transition <?php echo $activeTab == 'upcoming' ? 'bg-highlight text-white' : 'text-gray-300 hover:bg-secondary'; ?>">
            <i class="fas fa-clock mr-2"></i>Upcoming/Live
            <?php if (count($upcomingTournaments) > 0): ?>
                <span class="bg-white text-accent text-xs px-2 py-1 rounded-full ml-2"><?php echo count($upcomingTournaments); ?></span>
            <?php endif; ?>
        </a>
        <a href="?tab=completed" class="flex-1 py-3 px-4 text-center font-semibold transition <?php echo $activeTab == 'completed' ? 'bg-highlight text-white' : 'text-gray-300 hover:bg-secondary'; ?>">
            <i class="fas fa-history mr-2"></i>Completed
            <?php if (count($completedTournaments) > 0): ?>
                <span class="bg-white text-accent text-xs px-2 py-1 rounded-full ml-2"><?php echo count($completedTournaments); ?></span>
            <?php endif; ?>
        </a>
    </div>

    <!-- Upcoming/Live Tournaments Tab -->
    <?php if ($activeTab == 'upcoming'): ?>
        <div class="space-y-4">
            <?php if (empty($upcomingTournaments)): ?>
                <div class="bg-secondary rounded-lg p-8 text-center">
                    <i class="fas fa-calendar-times text-4xl text-gray-500 mb-4"></i>
                    <h3 class="text-lg font-semibold mb-2">No Upcoming Tournaments</h3>
                    <p class="text-gray-400 mb-4">You haven't joined any upcoming tournaments yet.</p>
                    <a href="index.php" class="bg-highlight hover:bg-red-600 transition text-white font-semibold py-2 px-4 rounded-lg inline-block">
                        <i class="fas fa-plus mr-2"></i>Join Tournaments
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($upcomingTournaments as $tournament): ?>
                    <div class="bg-secondary rounded-lg p-4 border-l-4 <?php echo $tournament['status'] == 'Live' ? 'border-green-500' : 'border-highlight'; ?>">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold mb-1"><?php echo htmlspecialchars($tournament['title']); ?></h4>
                                <p class="text-highlight font-medium">
                                    <i class="fas fa-gamepad mr-1"></i>
                                    <?php echo htmlspecialchars($tournament['game_name']); ?>
                                </p>
                            </div>
                            <span class="bg-<?php echo $tournament['status'] == 'Live' ? 'green' : 'blue'; ?>-600 text-white px-2 py-1 rounded text-xs">
                                <i class="fas fa-<?php echo $tournament['status'] == 'Live' ? 'circle' : 'clock'; ?> mr-1"></i>
                                <?php echo $tournament['status']; ?>
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                            <div>
                                <i class="fas fa-clock text-highlight mr-1"></i>
                                <?php echo date('M j, Y g:i A', strtotime($tournament['match_time'])); ?>
                            </div>
                            <div>
                                <i class="fas fa-calendar-check text-highlight mr-1"></i>
                                Joined: <?php echo date('M j, Y', strtotime($tournament['joined_at'])); ?>
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

                        <?php if ($tournament['status'] == 'Live' && (!empty($tournament['room_id']) || !empty($tournament['room_password']))): ?>
                            <div class="bg-accent rounded-lg p-4 mt-4">
                                <h5 class="font-semibold mb-2 text-green-400">
                                    <i class="fas fa-door-open mr-2"></i>Room Details
                                </h5>
                                <div class="grid grid-cols-1 gap-2 text-sm">
                                    <?php if (!empty($tournament['room_id'])): ?>
                                        <div class="flex justify-between items-center">
                                            <span>Room ID:</span>
                                            <span class="bg-secondary px-2 py-1 rounded font-mono"><?php echo htmlspecialchars($tournament['room_id']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($tournament['room_password'])): ?>
                                        <div class="flex justify-between items-center">
                                            <span>Password:</span>
                                            <span class="bg-secondary px-2 py-1 rounded font-mono"><?php echo htmlspecialchars($tournament['room_password']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($tournament['status'] == 'Upcoming'): ?>
                            <div class="mt-4 text-center text-blue-400">
                                <i class="fas fa-info-circle mr-2"></i>
                                Tournament will start at the scheduled time. Room details will be available when live.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Completed Tournaments Tab -->
    <?php if ($activeTab == 'completed'): ?>
        <div class="space-y-4">
            <?php if (empty($completedTournaments)): ?>
                <div class="bg-secondary rounded-lg p-8 text-center">
                    <i class="fas fa-history text-4xl text-gray-500 mb-4"></i>
                    <h3 class="text-lg font-semibold mb-2">No Tournament History</h3>
                    <p class="text-gray-400 mb-4">You haven't completed any tournaments yet.</p>
                    <a href="index.php" class="bg-highlight hover:bg-red-600 transition text-white font-semibold py-2 px-4 rounded-lg inline-block">
                        <i class="fas fa-plus mr-2"></i>Join Your First Tournament
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($completedTournaments as $tournament): ?>
                    <div class="bg-secondary rounded-lg p-4 border-l-4 <?php echo $tournament['result'] == 'Winner' ? 'border-yellow-500' : 'border-gray-500'; ?>">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold mb-1"><?php echo htmlspecialchars($tournament['title']); ?></h4>
                                <p class="text-highlight font-medium">
                                    <i class="fas fa-gamepad mr-1"></i>
                                    <?php echo htmlspecialchars($tournament['game_name']); ?>
                                </p>
                            </div>
                            <span class="bg-<?php echo $tournament['result'] == 'Winner' ? 'yellow' : 'gray'; ?>-600 text-white px-2 py-1 rounded text-xs">
                                <i class="fas fa-<?php echo $tournament['result'] == 'Winner' ? 'crown' : 'medal'; ?> mr-1"></i>
                                <?php echo $tournament['result']; ?>
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                            <div>
                                <i class="fas fa-clock text-highlight mr-1"></i>
                                <?php echo date('M j, Y g:i A', strtotime($tournament['match_time'])); ?>
                            </div>
                            <div>
                                <i class="fas fa-flag-checkered text-highlight mr-1"></i>
                                Completed
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

                        <?php if ($tournament['result'] == 'Winner'): ?>
                            <div class="bg-yellow-600 bg-opacity-20 border border-yellow-600 rounded-lg p-3 mt-4">
                                <div class="text-center text-yellow-400">
                                    <i class="fas fa-trophy text-2xl mb-2"></i>
                                    <div class="font-semibold">Congratulations! You won this tournament!</div>
                                    <div class="text-sm mt-1">Prize Amount: <?php echo formatCurrency($tournament['prize_pool']); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Quick Stats -->
    <div class="mt-8 grid grid-cols-3 gap-4">
        <div class="bg-secondary rounded-lg p-4 text-center">
            <i class="fas fa-gamepad text-highlight text-xl mb-2"></i>
            <div class="text-lg font-bold"><?php echo count($upcomingTournaments) + count($completedTournaments); ?></div>
            <div class="text-xs text-gray-400">Total Joined</div>
        </div>
        <div class="bg-secondary rounded-lg p-4 text-center">
            <i class="fas fa-trophy text-highlight text-xl mb-2"></i>
            <div class="text-lg font-bold"><?php echo count(array_filter($completedTournaments, function($t) { return $t['result'] == 'Winner'; })); ?></div>
            <div class="text-xs text-gray-400">Wins</div>
        </div>
        <div class="bg-secondary rounded-lg p-4 text-center">
            <i class="fas fa-clock text-highlight text-xl mb-2"></i>
            <div class="text-lg font-bold"><?php echo count($upcomingTournaments); ?></div>
            <div class="text-xs text-gray-400">Upcoming</div>
        </div>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
