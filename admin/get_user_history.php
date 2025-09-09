<?php
require_once '../common/config.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get user ID from query parameter
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

try {
    // Get user statistics
    $stmt = $pdo->prepare("
        SELECT 
            u.username,
            u.email,
            u.wallet_balance,
            u.created_at as join_date,
            COUNT(DISTINCT p.tournament_id) as total_tournaments,
            COUNT(DISTINCT CASE WHEN t.winner_id = u.id THEN t.id END) as tournaments_won,
            SUM(CASE WHEN tr.type = 'credit' THEN tr.amount ELSE 0 END) as total_credits,
            SUM(CASE WHEN tr.type = 'debit' THEN tr.amount ELSE 0 END) as total_debits
        FROM users u
        LEFT JOIN participants p ON u.id = p.user_id
        LEFT JOIN tournaments t ON p.tournament_id = t.id
        LEFT JOIN transactions tr ON u.id = tr.user_id
        WHERE u.id = ?
        GROUP BY u.id
    ");
    $stmt->execute([$user_id]);
    $user_stats = $stmt->fetch();

    if (!$user_stats) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }

    // Get tournament history
    $stmt = $pdo->prepare("
        SELECT 
            t.id,
            t.title,
            t.entry_fee,
            t.prize_pool,
            t.status,
            p.joined_at,
            CASE WHEN t.winner_id = ? THEN 1 ELSE 0 END as is_winner
        FROM participants p
        JOIN tournaments t ON p.tournament_id = t.id
        WHERE p.user_id = ?
        ORDER BY p.joined_at DESC
        LIMIT 20
    ");
    $stmt->execute([$user_id, $user_id]);
    $tournaments = $stmt->fetchAll();

    // Get recent transactions
    $stmt = $pdo->prepare("
        SELECT 
            id,
            amount,
            type,
            description,
            created_at
        FROM transactions
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 15
    ");
    $stmt->execute([$user_id]);
    $transactions = $stmt->fetchAll();

    // Return success response
    echo json_encode([
        'success' => true,
        'user_stats' => $user_stats,
        'tournaments' => $tournaments,
        'transactions' => $transactions
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
