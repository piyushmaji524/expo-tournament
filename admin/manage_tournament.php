<?php
include 'common/header.php';

$message = '';
$messageType = '';

// Get tournament ID from URL
$tournament_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$tournament_id) {
    header('Location: tournament.php');
    exit();
}

// Get tournament details
$stmt = $pdo->prepare("SELECT * FROM tournaments WHERE id = ?");
$stmt->execute([$tournament_id]);
$tournament = $stmt->fetch();

if (!$tournament) {
    header('Location: tournament.php');
    exit();
}

// Handle room details update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_room'])) {
    $room_id = trim($_POST['room_id']);
    $room_password = trim($_POST['room_password']);
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE tournaments SET room_id = ?, room_password = ?, status = ? WHERE id = ?");
    if ($stmt->execute([$room_id, $room_password, $status, $tournament_id])) {
        $message = 'Room details updated successfully!';
        $messageType = 'success';
        
        // Refresh tournament data
        $stmt = $pdo->prepare("SELECT * FROM tournaments WHERE id = ?");
        $stmt->execute([$tournament_id]);
        $tournament = $stmt->fetch();
    } else {
        $message = 'Failed to update room details.';
        $messageType = 'error';
    }
}

// Handle winner declaration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['declare_winner'])) {
    $winner_id = $_POST['winner_id'];
    
    if (empty($winner_id)) {
        $message = 'Please select a winner.';
        $messageType = 'error';
    } else {
        $pdo->beginTransaction();
        
        try {
            // Update tournament with winner
            $stmt = $pdo->prepare("UPDATE tournaments SET winner_id = ?, status = 'Completed' WHERE id = ?");
            $stmt->execute([$winner_id, $tournament_id]);
            
            // Add prize money to winner's wallet
            updateWalletBalance($winner_id, $tournament['prize_pool'], $pdo);
            
            // Add transaction record
            addTransaction($winner_id, $tournament['prize_pool'], 'credit', 'Tournament prize for: ' . $tournament['title'], $pdo);
            
            $pdo->commit();
            
            $message = 'Winner declared successfully! Prize money has been added to winner\'s wallet.';
            $messageType = 'success';
            
            // Refresh tournament data
            $stmt = $pdo->prepare("SELECT * FROM tournaments WHERE id = ?");
            $stmt->execute([$tournament_id]);
            $tournament = $stmt->fetch();
        } catch (Exception $e) {
            $pdo->rollback();
            $message = 'Failed to declare winner. Please try again.';
            $messageType = 'error';
        }
    }
}

// Get participants
$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.email, u.wallet_balance, p.joined_at
    FROM participants p
    JOIN users u ON p.user_id = u.id
    WHERE p.tournament_id = ?
    ORDER BY p.joined_at ASC
");
$stmt->execute([$tournament_id]);
$participants = $stmt->fetchAll();

// Get winner details if exists
$winner = null;
if ($tournament['winner_id']) {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$tournament['winner_id']]);
    $winner = $stmt->fetch();
}
?>

<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="tournament.php" class="text-highlight hover:text-red-400 transition">
            <i class="fas fa-arrow-left mr-2"></i>Back to Tournaments
        </a>
    </div>

    <!-- Tournament Header -->
    <div class="bg-gradient-to-r from-accent to-highlight rounded-lg p-6">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($tournament['title']); ?></h2>
                <p class="text-blue-100 mb-2">
                    <i class="fas fa-gamepad mr-2"></i><?php echo htmlspecialchars($tournament['game_name']); ?>
                </p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <i class="fas fa-money-bill mr-1"></i>
                        Entry: <?php echo formatCurrency($tournament['entry_fee']); ?>
                    </div>
                    <div>
                        <i class="fas fa-trophy mr-1"></i>
                        Prize: <?php echo formatCurrency($tournament['prize_pool']); ?>
                    </div>
                    <div>
                        <i class="fas fa-users mr-1"></i>
                        Players: <?php echo count($participants); ?>
                    </div>
                    <div>
                        <i class="fas fa-clock mr-1"></i>
                        <?php echo date('M j, Y g:i A', strtotime($tournament['match_time'])); ?>
                    </div>
                </div>
            </div>
            <span class="bg-<?php echo $tournament['status'] == 'Upcoming' ? 'blue' : ($tournament['status'] == 'Live' ? 'green' : 'gray'); ?>-600 text-white px-3 py-1 rounded">
                <?php echo $tournament['status']; ?>
            </span>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
        <div class="<?php echo $messageType == 'success' ? 'bg-green-600' : 'bg-red-600'; ?> text-white p-4 rounded-lg text-center">
            <i class="fas <?php echo $messageType == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-2"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Room Details -->
        <div class="bg-secondary rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">
                <i class="fas fa-door-open text-highlight mr-2"></i>
                Room Details & Status
            </h3>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Room ID</label>
                    <input type="text" name="room_id" value="<?php echo htmlspecialchars($tournament['room_id']); ?>" class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="Enter room ID">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Room Password</label>
                    <input type="text" name="room_password" value="<?php echo htmlspecialchars($tournament['room_password']); ?>" class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="Enter room password">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Tournament Status</label>
                    <select name="status" class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-highlight focus:outline-none">
                        <option value="Upcoming" <?php echo $tournament['status'] == 'Upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                        <option value="Live" <?php echo $tournament['status'] == 'Live' ? 'selected' : ''; ?>>Live</option>
                        <option value="Completed" <?php echo $tournament['status'] == 'Completed' ? 'selected' : ''; ?> disabled>Completed</option>
                    </select>
                </div>

                <?php if ($tournament['status'] != 'Completed'): ?>
                    <button type="submit" name="update_room" class="w-full bg-blue-600 hover:bg-blue-700 transition text-white font-semibold py-3 px-4 rounded-lg">
                        <i class="fas fa-save mr-2"></i>Update Room Details
                    </button>
                <?php endif; ?>
            </form>
        </div>

        <!-- Winner Declaration -->
        <div class="bg-secondary rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">
                <i class="fas fa-crown text-highlight mr-2"></i>
                Winner Declaration
            </h3>

            <?php if ($tournament['status'] == 'Completed' && $winner): ?>
                <div class="bg-yellow-600 bg-opacity-20 border border-yellow-600 rounded-lg p-4 text-center">
                    <i class="fas fa-trophy text-yellow-400 text-3xl mb-2"></i>
                    <h4 class="text-lg font-semibold text-yellow-400">Tournament Completed</h4>
                    <p class="text-yellow-300 mt-1">Winner: <strong><?php echo htmlspecialchars($winner['username']); ?></strong></p>
                    <p class="text-yellow-300 text-sm mt-1">Prize: <?php echo formatCurrency($tournament['prize_pool']); ?></p>
                </div>
            <?php elseif (count($participants) > 0): ?>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Select Winner</label>
                        <select name="winner_id" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-highlight focus:outline-none">
                            <option value="">Choose a participant</option>
                            <?php foreach ($participants as $participant): ?>
                                <option value="<?php echo $participant['id']; ?>">
                                    <?php echo htmlspecialchars($participant['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="bg-accent rounded-lg p-4">
                        <h5 class="font-semibold mb-2 text-yellow-400">Prize Distribution</h5>
                        <div class="text-sm space-y-1">
                            <div class="flex justify-between">
                                <span>Total Prize Pool:</span>
                                <span class="font-bold"><?php echo formatCurrency($tournament['prize_pool']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Commission (<?php echo $tournament['commission_percentage']; ?>%):</span>
                                <span class="text-red-400">-<?php echo formatCurrency($tournament['prize_pool'] * $tournament['commission_percentage'] / 100); ?></span>
                            </div>
                            <div class="flex justify-between border-t border-gray-600 pt-2">
                                <span>Winner Gets:</span>
                                <span class="font-bold text-green-400"><?php echo formatCurrency($tournament['prize_pool']); ?></span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="declare_winner" onclick="return confirm('Are you sure? This action cannot be undone and will distribute the prize money.')" class="w-full bg-green-600 hover:bg-green-700 transition text-white font-semibold py-3 px-4 rounded-lg">
                        <i class="fas fa-crown mr-2"></i>Declare Winner & Distribute Prize
                    </button>
                </form>
            <?php else: ?>
                <div class="text-center py-4 text-gray-400">
                    <i class="fas fa-users text-2xl mb-2"></i>
                    <p>No participants to declare as winner</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Participants List -->
    <div class="bg-secondary rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-users text-highlight mr-2"></i>
            Participants (<?php echo count($participants); ?>)
        </h3>

        <?php if (empty($participants)): ?>
            <div class="text-center py-8">
                <i class="fas fa-users text-4xl text-gray-500 mb-4"></i>
                <h4 class="text-lg font-semibold mb-2">No Participants Yet</h4>
                <p class="text-gray-400">Participants will appear here when they join the tournament</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-600">
                            <th class="text-left py-3 px-2">#</th>
                            <th class="text-left py-3 px-2">Username</th>
                            <th class="text-left py-3 px-2">Email</th>
                            <th class="text-left py-3 px-2">Wallet Balance</th>
                            <th class="text-left py-3 px-2">Joined At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participants as $index => $participant): ?>
                            <tr class="border-b border-gray-700 hover:bg-accent transition">
                                <td class="py-3 px-2 font-bold"><?php echo $index + 1; ?></td>
                                <td class="py-3 px-2">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-highlight rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-white text-sm"></i>
                                        </div>
                                        <span class="font-semibold"><?php echo htmlspecialchars($participant['username']); ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-2 text-sm text-gray-400"><?php echo htmlspecialchars($participant['email']); ?></td>
                                <td class="py-3 px-2 text-sm font-semibold text-green-400"><?php echo formatCurrency($participant['wallet_balance']); ?></td>
                                <td class="py-3 px-2 text-sm"><?php echo date('M j, Y g:i A', strtotime($participant['joined_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
