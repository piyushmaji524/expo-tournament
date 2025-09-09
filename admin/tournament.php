<?php
include 'common/header.php';

$message = '';
$messageType = '';

// Handle tournament creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_tournament'])) {
    $title = trim($_POST['title']);
    $game_name = trim($_POST['game_name']);
    $entry_fee = floatval($_POST['entry_fee']);
    $prize_pool = floatval($_POST['prize_pool']);
    $commission_percentage = floatval($_POST['commission_percentage']);
    $match_time = $_POST['match_time'];
    
    if (empty($title) || empty($game_name) || $entry_fee <= 0 || $prize_pool <= 0 || empty($match_time)) {
        $message = 'Please fill in all fields with valid values.';
        $messageType = 'error';
    } elseif (strtotime($match_time) <= time()) {
        $message = 'Match time must be in the future.';
        $messageType = 'error';
    } else {
        $stmt = $pdo->prepare("INSERT INTO tournaments (title, game_name, entry_fee, prize_pool, commission_percentage, match_time) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$title, $game_name, $entry_fee, $prize_pool, $commission_percentage, $match_time])) {
            $message = 'Tournament created successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to create tournament. Please try again.';
            $messageType = 'error';
        }
    }
}

// Handle tournament deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $tournament_id = $_GET['delete'];
    
    // Check if tournament has participants
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM participants WHERE tournament_id = ?");
    $stmt->execute([$tournament_id]);
    $participant_count = $stmt->fetch()['count'];
    
    if ($participant_count > 0) {
        $message = 'Cannot delete tournament with participants. Please remove all participants first.';
        $messageType = 'error';
    } else {
        $stmt = $pdo->prepare("DELETE FROM tournaments WHERE id = ?");
        if ($stmt->execute([$tournament_id])) {
            $message = 'Tournament deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete tournament.';
            $messageType = 'error';
        }
    }
}

// Get all tournaments
$stmt = $pdo->query("
    SELECT t.*, COUNT(p.id) as participant_count
    FROM tournaments t 
    LEFT JOIN participants p ON t.id = p.tournament_id
    GROUP BY t.id
    ORDER BY t.created_at DESC
");
$tournaments = $stmt->fetchAll();
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold">Tournament Management</h2>
            <p class="text-gray-400">Create and manage tournaments</p>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
        <div class="<?php echo $messageType == 'success' ? 'bg-green-600' : 'bg-red-600'; ?> text-white p-4 rounded-lg text-center">
            <i class="fas <?php echo $messageType == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-2"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Create Tournament Form -->
    <div class="bg-secondary rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-plus text-highlight mr-2"></i>
            Create New Tournament
        </h3>

        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Tournament Title</label>
                <input type="text" name="title" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="e.g., PUBG Mobile Championship">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Game Name</label>
                <select name="game_name" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-highlight focus:outline-none">
                    <option value="">Select Game</option>
                    <option value="PUBG Mobile">PUBG Mobile</option>
                    <option value="Free Fire">Free Fire</option>
                    <option value="Call of Duty Mobile">Call of Duty Mobile</option>
                    <option value="Valorant">Valorant</option>
                    <option value="CS:GO">CS:GO</option>
                    <option value="Fortnite">Fortnite</option>
                    <option value="Apex Legends">Apex Legends</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Entry Fee (₹)</label>
                <input type="number" name="entry_fee" step="0.01" min="0.01" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="100.00">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Prize Pool (₹)</label>
                <input type="number" name="prize_pool" step="0.01" min="0.01" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none" placeholder="800.00">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Commission (%)</label>
                <input type="number" name="commission_percentage" step="0.01" min="0" max="100" value="20" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:border-highlight focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Match Time</label>
                <input type="datetime-local" name="match_time" required class="w-full bg-accent border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-highlight focus:outline-none">
            </div>

            <div class="md:col-span-2">
                <button type="submit" name="create_tournament" class="w-full bg-highlight hover:bg-red-600 transition text-white font-semibold py-3 px-4 rounded-lg">
                    <i class="fas fa-plus mr-2"></i>Create Tournament
                </button>
            </div>
        </form>
    </div>

    <!-- Tournaments List -->
    <div class="bg-secondary rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">
                <i class="fas fa-trophy text-highlight mr-2"></i>
                All Tournaments
            </h3>
            <div class="text-sm text-gray-400">
                Total: <?php echo count($tournaments); ?>
            </div>
        </div>

        <?php if (empty($tournaments)): ?>
            <div class="text-center py-8">
                <i class="fas fa-trophy text-4xl text-gray-500 mb-4"></i>
                <h4 class="text-lg font-semibold mb-2">No Tournaments</h4>
                <p class="text-gray-400">Create your first tournament using the form above</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-600">
                            <th class="text-left py-3 px-2">Tournament</th>
                            <th class="text-left py-3 px-2">Game</th>
                            <th class="text-left py-3 px-2">Entry Fee</th>
                            <th class="text-left py-3 px-2">Prize Pool</th>
                            <th class="text-left py-3 px-2">Participants</th>
                            <th class="text-left py-3 px-2">Status</th>
                            <th class="text-left py-3 px-2">Match Time</th>
                            <th class="text-left py-3 px-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tournaments as $tournament): ?>
                            <tr class="border-b border-gray-700 hover:bg-accent transition">
                                <td class="py-3 px-2">
                                    <div class="font-semibold"><?php echo htmlspecialchars($tournament['title']); ?></div>
                                </td>
                                <td class="py-3 px-2 text-sm"><?php echo htmlspecialchars($tournament['game_name']); ?></td>
                                <td class="py-3 px-2 text-sm"><?php echo formatCurrency($tournament['entry_fee']); ?></td>
                                <td class="py-3 px-2 text-sm font-semibold text-yellow-400"><?php echo formatCurrency($tournament['prize_pool']); ?></td>
                                <td class="py-3 px-2 text-sm">
                                    <span class="bg-blue-600 text-white px-2 py-1 rounded text-xs">
                                        <?php echo $tournament['participant_count']; ?> players
                                    </span>
                                </td>
                                <td class="py-3 px-2">
                                    <span class="bg-<?php echo $tournament['status'] == 'Upcoming' ? 'blue' : ($tournament['status'] == 'Live' ? 'green' : 'gray'); ?>-600 text-white px-2 py-1 rounded text-xs">
                                        <?php echo $tournament['status']; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-2 text-sm"><?php echo date('M j, Y g:i A', strtotime($tournament['match_time'])); ?></td>
                                <td class="py-3 px-2">
                                    <div class="flex space-x-2">
                                        <a href="manage_tournament.php?id=<?php echo $tournament['id']; ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs transition">
                                            <i class="fas fa-cog mr-1"></i>Manage
                                        </a>
                                        <?php if ($tournament['participant_count'] == 0): ?>
                                            <a href="?delete=<?php echo $tournament['id']; ?>" onclick="return confirm('Are you sure you want to delete this tournament?')" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs transition">
                                                <i class="fas fa-trash mr-1"></i>Delete
                                            </a>
                                        <?php endif; ?>
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

<?php include 'common/bottom.php'; ?>
