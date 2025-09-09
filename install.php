<?php
$host = 'localhost';
$username = 'your_database_username';
$password = 'your_database_password';
$database = 'your_database_name';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Connect to MySQL server (without specifying database)
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $database");
        $pdo->exec("USE $database");

        // Create users table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                wallet_balance DECIMAL(10,2) DEFAULT 0.00,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Create admin table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS admin (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL
            )
        ");

        // Create tournaments table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tournaments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                game_name VARCHAR(100) NOT NULL,
                entry_fee DECIMAL(10,2) NOT NULL,
                prize_pool DECIMAL(10,2) NOT NULL,
                commission_percentage DECIMAL(5,2) DEFAULT 0.00,
                match_time DATETIME NOT NULL,
                room_id VARCHAR(100) DEFAULT '',
                room_password VARCHAR(100) DEFAULT '',
                status ENUM('Upcoming', 'Live', 'Completed') DEFAULT 'Upcoming',
                winner_id INT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (winner_id) REFERENCES users(id)
            )
        ");

        // Create participants table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS participants (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                tournament_id INT NOT NULL,
                joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
                UNIQUE KEY unique_participation (user_id, tournament_id)
            )
        ");

        // Create transactions table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                type ENUM('credit', 'debit') NOT NULL,
                description VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");

        // Insert default admin account
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO admin (username, password) VALUES (?, ?)");
        $stmt->execute(['admin', $admin_password]);

        // Insert sample data for testing
        // Add sample users
        $users = [
            ['testuser1', 'test1@example.com', password_hash('password123', PASSWORD_DEFAULT), 1000.00],
            ['testuser2', 'test2@example.com', password_hash('password123', PASSWORD_DEFAULT), 1500.00],
            ['testuser3', 'test3@example.com', password_hash('password123', PASSWORD_DEFAULT), 2000.00]
        ];

        foreach ($users as $user) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password, wallet_balance) VALUES (?, ?, ?, ?)");
            $stmt->execute($user);
        }

        // Add sample tournaments
        $tournaments = [
            ['PUBG Mobile Championship', 'PUBG Mobile', 100.00, 800.00, 20.00, date('Y-m-d H:i:s', strtotime('+1 day'))],
            ['Free Fire Battle Royale', 'Free Fire', 50.00, 400.00, 20.00, date('Y-m-d H:i:s', strtotime('+2 days'))],
            ['Call of Duty Tournament', 'Call of Duty Mobile', 150.00, 1200.00, 20.00, date('Y-m-d H:i:s', strtotime('+3 days'))],
            ['Valorant Championship', 'Valorant', 200.00, 1600.00, 20.00, date('Y-m-d H:i:s', strtotime('+4 days'))]
        ];

        foreach ($tournaments as $tournament) {
            $stmt = $pdo->prepare("INSERT INTO tournaments (title, game_name, entry_fee, prize_pool, commission_percentage, match_time) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute($tournament);
        }

        $success = true;
    } catch(PDOException $e) {
        $error = "Installation failed: " . $e->getMessage();
    }
}

if ($success) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - Adept Play</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1a1a2e',
                        secondary: '#16213e',
                        accent: '#0f3460',
                        highlight: '#e94560'
                    }
                }
            }
        }
    </script>
    <style>
        body {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
            -webkit-tap-highlight-color: transparent;
        }
        
        * {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
    </style>
</head>
<body class="bg-primary text-white select-none min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto bg-secondary rounded-lg shadow-xl p-8">
            <div class="text-center mb-8">
                <i class="fas fa-trophy text-highlight text-5xl mb-4"></i>
                <h1 class="text-3xl font-bold">Adept Play</h1>
                <p class="text-gray-400 mt-2">Tournament Installation</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-600 text-white p-4 rounded-lg mb-6">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="bg-accent p-4 rounded-lg">
                    <h3 class="font-semibold mb-2">Database Configuration</h3>
                    <div class="text-sm text-gray-300 space-y-1">
                        <p><strong>Host:</strong> 127.0.0.1</p>
                        <p><strong>Username:</strong> root</p>
                        <p><strong>Password:</strong> root</p>
                        <p><strong>Database:</strong> tournament_app</p>
                    </div>
                </div>

                <div class="bg-accent p-4 rounded-lg">
                    <h3 class="font-semibold mb-2">Default Admin Account</h3>
                    <div class="text-sm text-gray-300 space-y-1">
                        <p><strong>Username:</strong> admin</p>
                        <p><strong>Password:</strong> admin123</p>
                    </div>
                </div>

                <div class="bg-accent p-4 rounded-lg">
                    <h3 class="font-semibold mb-2">Sample Test Users</h3>
                    <div class="text-sm text-gray-300 space-y-1">
                        <p><strong>testuser1</strong> / password123 (₹1,000)</p>
                        <p><strong>testuser2</strong> / password123 (₹1,500)</p>
                        <p><strong>testuser3</strong> / password123 (₹2,000)</p>
                    </div>
                </div>

                <button type="submit" class="w-full bg-highlight hover:bg-red-600 transition text-white font-semibold py-3 px-4 rounded-lg">
                    <i class="fas fa-database mr-2"></i>
                    Install Database & Setup App
                </button>
            </form>

            <div class="mt-6 text-center text-sm text-gray-400">
                <p>This will create all necessary tables and insert sample data.</p>
            </div>
        </div>
    </div>

    <script>
        // Disable right-click, text selection, and zoom
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('selectstart', e => e.preventDefault());
        document.addEventListener('wheel', e => { if (e.ctrlKey) e.preventDefault(); });
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && (e.keyCode === 65 || e.keyCode === 67 || e.keyCode === 86 || e.keyCode === 88 || e.keyCode === 90)) {
                e.preventDefault();
            }
            if (e.keyCode === 123 || (e.ctrlKey && e.shiftKey && e.keyCode === 73) || (e.ctrlKey && e.keyCode === 85)) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
