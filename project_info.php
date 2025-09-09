<?php
// Standalone project information page - no authentication required
// Database connection for statistics (optional)
try {
    // For security, these credentials should be stored in environment variables
    // or a separate config file that's not committed to version control
    $host = 'localhost';
    $username = 'your_db_username'; // Replace with your database username
    $password = 'your_db_password'; // Replace with your database password
    $database = 'your_db_name';     // Replace with your database name
    
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // If database connection fails, we'll show static content
    $pdo = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expo Tournament - Gaming Tournament Platform</title>
    <meta name="description" content="Comprehensive web-based gaming tournament platform with PWA capabilities. Join tournaments, manage your wallet, and earn through referrals.">
    <meta name="keywords" content="gaming, tournament, esports, PUBG, Free Fire, gaming platform, PWA">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#1a1a2e',
                        'secondary': '#16213e',
                        'accent': '#0f172a',
                        'highlight': '#e94560'
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        body {
            background-color: #1a1a2e;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #0f172a 0%, #e94560 100%);
        }
        
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(233, 69, 96, 0.2);
        }
    </style>
</head>
<body class="bg-primary text-white min-h-screen">
    <!-- Navigation -->
    <nav class="bg-secondary shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-gamepad text-highlight text-2xl mr-3"></i>
                    <h1 class="text-xl font-bold">Expo Tournament</h1>
                </div>
                <div class="text-sm text-gray-400">
                    Gaming Tournament Platform
                </div>
            </div>
        </div>
    </nav>

    <!-- Copy README Ribbon -->
    <div class="bg-gradient-to-r from-yellow-600 to-orange-600 py-4 mb-6 relative overflow-hidden">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-copy text-white text-2xl mr-3"></i>
                    <div>
                        <h3 class="text-white font-bold text-lg">Get the Complete Project Documentation</h3>
                        <p class="text-yellow-100 text-sm">Copy the full Prompt content with installation guide and GitHub Copilot prompt</p>
                    </div>
                </div>
                <button id="copyReadmeBtn" class="bg-white text-orange-600 font-bold py-3 px-6 rounded-lg hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 shadow-lg">
                    <i class="fas fa-copy mr-2"></i>Copy Prompt
                </button>
            </div>
        </div>
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -translate-y-16 translate-x-16"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-10 rounded-full translate-y-12 -translate-x-12"></div>
    </div>

<div class="container mx-auto px-4 py-6 pb-20">
    <!-- Project Header -->
    <div class="bg-gradient-to-r from-accent to-highlight rounded-lg p-6 mb-6">
        <div class="text-center">
            <h1 class="text-3xl font-bold mb-2">Expo Tournament</h1>
            <p class="text-blue-100 text-lg mb-4">Gaming Tournament Platform</p>
            
            <!-- Badges -->
            <div class="flex flex-wrap justify-center gap-2 mb-4">
                <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-xs">Web | PWA</span>
                <span class="bg-purple-600 text-white px-3 py-1 rounded-full text-xs">PHP 8+</span>
                <span class="bg-orange-600 text-white px-3 py-1 rounded-full text-xs">MySQL/MariaDB</span>
                <span class="bg-cyan-600 text-white px-3 py-1 rounded-full text-xs">TailwindCSS</span>
                <span class="bg-green-600 text-white px-3 py-1 rounded-full text-xs">MIT License</span>
            </div>
            
            <p class="text-gray-200">A comprehensive web-based gaming tournament platform with Progressive Web App (PWA) capabilities.</p>
        </div>
    </div>

    <!-- Features Grid -->
    <div id="features" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- User Features -->
        <div class="bg-secondary rounded-lg p-6 card-hover">
            <h2 class="text-xl font-bold mb-4 text-highlight">
                <i class="fas fa-user mr-2"></i>User Features
            </h2>
            <ul class="space-y-3">
                <li class="flex items-start">
                    <i class="fas fa-gamepad text-highlight mr-3 mt-1"></i>
                    <div>
                        <strong>Tournament Participation</strong>
                        <p class="text-gray-400 text-sm">Join gaming tournaments for popular games</p>
                    </div>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-wallet text-highlight mr-3 mt-1"></i>
                    <div>
                        <strong>Digital Wallet</strong>
                        <p class="text-gray-400 text-sm">Secure wallet system with deposits and withdrawals</p>
                    </div>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-users text-highlight mr-3 mt-1"></i>
                    <div>
                        <strong>Referral System</strong>
                        <p class="text-gray-400 text-sm">Earn rewards by referring friends</p>
                    </div>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-mobile-alt text-highlight mr-3 mt-1"></i>
                    <div>
                        <strong>PWA Support</strong>
                        <p class="text-gray-400 text-sm">Install as mobile app with offline capabilities</p>
                    </div>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-history text-highlight mr-3 mt-1"></i>
                    <div>
                        <strong>Transaction History</strong>
                        <p class="text-gray-400 text-sm">Complete audit trail of all activities</p>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Admin Features -->
        <div class="bg-secondary rounded-lg p-6 card-hover">
            <h2 class="text-xl font-bold mb-4 text-highlight">
                <i class="fas fa-cog mr-2"></i>Admin Features
            </h2>
            <ul class="space-y-3">
                <li class="flex items-start">
                    <i class="fas fa-trophy text-highlight mr-3 mt-1"></i>
                    <div>
                        <strong>Tournament Management</strong>
                        <p class="text-gray-400 text-sm">Create, manage, and declare winners</p>
                    </div>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-user-cog text-highlight mr-3 mt-1"></i>
                    <div>
                        <strong>User Management</strong>
                        <p class="text-gray-400 text-sm">View and manage all registered users</p>
                    </div>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-money-check-alt text-highlight mr-3 mt-1"></i>
                    <div>
                        <strong>Financial Control</strong>
                        <p class="text-gray-400 text-sm">Approve deposits and withdrawals</p>
                    </div>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-image text-highlight mr-3 mt-1"></i>
                    <div>
                        <strong>Banner Management</strong>
                        <p class="text-gray-400 text-sm">Promotional banner system</p>
                    </div>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-chart-bar text-highlight mr-3 mt-1"></i>
                    <div>
                        <strong>Analytics Dashboard</strong>
                        <p class="text-gray-400 text-sm">User and tournament statistics</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <!-- Technology Stack -->
    <div id="technology" class="bg-secondary rounded-lg p-6 mb-6 card-hover">
        <h2 class="text-xl font-bold mb-4 text-highlight">
            <i class="fas fa-code mr-2"></i>Technology Stack
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-accent rounded-lg p-4">
                <h3 class="font-semibold mb-2 text-yellow-400">Backend</h3>
                <ul class="text-sm text-gray-300 space-y-1">
                    <li>• PHP 8+ with PDO</li>
                    <li>• MySQL/MariaDB</li>
                    <li>• Session Management</li>
                    <li>• bcrypt Security</li>
                </ul>
            </div>
            <div class="bg-accent rounded-lg p-4">
                <h3 class="font-semibold mb-2 text-blue-400">Frontend</h3>
                <ul class="text-sm text-gray-300 space-y-1">
                    <li>• TailwindCSS 3.0+</li>
                    <li>• Font Awesome 6.0+</li>
                    <li>• Responsive Design</li>
                    <li>• Mobile-first</li>
                </ul>
            </div>
            <div class="bg-accent rounded-lg p-4">
                <h3 class="font-semibold mb-2 text-green-400">PWA</h3>
                <ul class="text-sm text-gray-300 space-y-1">
                    <li>• Service Worker</li>
                    <li>• Web App Manifest</li>
                    <li>• Offline Functionality</li>
                    <li>• Installable App</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Supported Games -->
    <div id="games" class="bg-secondary rounded-lg p-6 mb-6 card-hover">
        <h2 class="text-xl font-bold mb-4 text-highlight">
            <i class="fas fa-gamepad mr-2"></i>Supported Games
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <?php
            $games = [
                ['name' => 'PUBG Mobile', 'icon' => 'fas fa-crosshairs'],
                ['name' => 'Free Fire', 'icon' => 'fas fa-fire'],
                ['name' => 'Call of Duty Mobile', 'icon' => 'fas fa-bullseye'],
                ['name' => 'Valorant', 'icon' => 'fas fa-skull'],
                ['name' => 'CS:GO', 'icon' => 'fas fa-bomb'],
                ['name' => 'Fortnite', 'icon' => 'fas fa-hammer'],
                ['name' => 'Apex Legends', 'icon' => 'fas fa-mountain'],
                ['name' => 'Custom Games', 'icon' => 'fas fa-plus']
            ];
            
            foreach ($games as $game): ?>
                <div class="bg-accent rounded-lg p-3 text-center">
                    <i class="<?php echo $game['icon']; ?> text-highlight text-xl mb-2"></i>
                    <p class="text-sm font-medium"><?php echo $game['name']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Security Features -->
    <div class="bg-secondary rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold mb-4 text-highlight">
            <i class="fas fa-shield-alt mr-2"></i>Security Features
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-3">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-400 mr-3"></i>
                    <span>Secure Authentication System</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-400 mr-3"></i>
                    <span>SQL Injection Prevention</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-400 mr-3"></i>
                    <span>bcrypt Password Hashing</span>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-400 mr-3"></i>
                    <span>Secure Session Management</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-400 mr-3"></i>
                    <span>Input Validation & Sanitization</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-400 mr-3"></i>
                    <span>Secure File Upload System</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Statistics -->
    <div class="bg-secondary rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold mb-4 text-highlight">
            <i class="fas fa-chart-line mr-2"></i>Project Statistics
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php
            // Get some basic statistics from the database
            if ($pdo) {
                try {
                    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                    $total_tournaments = $pdo->query("SELECT COUNT(*) FROM tournaments")->fetchColumn();
                    $total_participants = $pdo->query("SELECT COUNT(*) FROM participants")->fetchColumn();
                    $active_tournaments = $pdo->query("SELECT COUNT(*) FROM tournaments WHERE status = 'Upcoming'")->fetchColumn();
                } catch (Exception $e) {
                    $total_users = 150;
                    $total_tournaments = 25;
                    $total_participants = 480;
                    $active_tournaments = 5;
                }
            } else {
                // Default demo statistics if no database connection
                $total_users = 150;
                $total_tournaments = 25;
                $total_participants = 480;
                $active_tournaments = 5;
            }
            ?>
            
            <div class="bg-accent rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-blue-400"><?php echo number_format($total_users); ?></div>
                <div class="text-sm text-gray-400">Total Users</div>
            </div>
            <div class="bg-accent rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-green-400"><?php echo number_format($total_tournaments); ?></div>
                <div class="text-sm text-gray-400">Total Tournaments</div>
            </div>
            <div class="bg-accent rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-yellow-400"><?php echo number_format($total_participants); ?></div>
                <div class="text-sm text-gray-400">Total Participants</div>
            </div>
            <div class="bg-accent rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-highlight"><?php echo number_format($active_tournaments); ?></div>
                <div class="text-sm text-gray-400">Active Tournaments</div>
            </div>
        </div>
    </div>

    <!-- Installation Guide -->
    <div id="installation" class="bg-secondary rounded-lg p-6 mb-6 card-hover">
        <h2 class="text-xl font-bold mb-4 text-highlight">
            <i class="fas fa-download mr-2"></i>Quick Installation
        </h2>
        <div class="bg-accent rounded-lg p-4">
            <h3 class="font-semibold mb-3 text-yellow-400">Prerequisites:</h3>
            <ul class="text-sm text-gray-300 space-y-1 mb-4">
                <li>• PHP 8.0 or higher</li>
                <li>• MySQL/MariaDB 10.4+</li>
                <li>• Web server (Apache/Nginx)</li>
                <li>• Modern web browser</li>
            </ul>
            
            <h3 class="font-semibold mb-3 text-blue-400">Installation Steps:</h3>
            <ol class="text-sm text-gray-300 space-y-1">
                <li>1. Clone or download the project files</li>
                <li>2. Upload to your web server directory</li>
                <li>3. Create a MySQL database</li>
                <li>4. Navigate to <code class="bg-primary px-2 py-1 rounded">install.php</code> in your browser</li>
                <li>5. Follow the installation wizard</li>
                <li>6. Access admin panel with default credentials</li>
            </ol>
            
            <div class="mt-4 p-3 bg-primary rounded border-l-4 border-yellow-500">
                <p class="text-yellow-300 text-sm">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Default Admin:</strong> username: <code>admin</code>, password: <code>admin123</code>
                </p>
            </div>
        </div>
    </div>

    <!-- Version Information -->
    <div class="bg-secondary rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold mb-4 text-highlight">
            <i class="fas fa-code-branch mr-2"></i>Version History
        </h2>
        <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-accent rounded">
                <div>
                    <span class="font-semibold text-green-400">v1.2.0</span>
                    <span class="text-gray-400 ml-2">Enhanced admin dashboard and analytics</span>
                </div>
                <span class="text-xs text-gray-500">Current</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-accent rounded">
                <div>
                    <span class="font-semibold text-blue-400">v1.1.0</span>
                    <span class="text-gray-400 ml-2">Added PWA support and referral system</span>
                </div>
            </div>
            <div class="flex items-center justify-between p-3 bg-accent rounded">
                <div>
                    <span class="font-semibold text-gray-400">v1.0.0</span>
                    <span class="text-gray-400 ml-2">Initial release with core tournament functionality</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact & Support -->
    <div class="bg-secondary rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4 text-highlight">
            <i class="fas fa-life-ring mr-2"></i>Support & Contact
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold mb-3 text-blue-400">Get Help</h3>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-center">
                        <i class="fas fa-book text-gray-400 mr-3"></i>
                        <span>Check the documentation</span>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-question-circle text-gray-400 mr-3"></i>
                        <span>Review existing issues</span>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-bug text-gray-400 mr-3"></i>
                        <span>Report bugs or request features</span>
                    </li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold mb-3 text-green-400">Contributing</h3>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-center">
                        <i class="fas fa-code-branch text-gray-400 mr-3"></i>
                        <span>Fork the repository</span>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-edit text-gray-400 mr-3"></i>
                        <span>Make your changes</span>
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-share text-gray-400 mr-3"></i>
                        <span>Submit a pull request</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <p class="text-gray-400 text-sm">
                Built with <i class="fas fa-heart text-red-500"></i> for the gaming community
            </p>
        </div>
    </div>
    </div>

    <!-- Footer -->
    <footer class="bg-secondary mt-12 py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <div class="flex items-center mb-3">
                        <i class="fas fa-gamepad text-highlight text-xl mr-2"></i>
                        <span class="font-bold text-lg">Expo Tournament</span>
                    </div>
                    <p class="text-gray-400 text-sm">
                        The ultimate gaming tournament platform for competitive players worldwide.
                    </p>
                </div>
                <div>
                    <h3 class="font-semibold mb-3">Quick Links</h3>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="#features" class="hover:text-highlight transition">Features</a></li>
                        <li><a href="#technology" class="hover:text-highlight transition">Technology</a></li>
                        <li><a href="#games" class="hover:text-highlight transition">Supported Games</a></li>
                        <li><a href="#installation" class="hover:text-highlight transition">Installation</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold mb-3">Connect</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-highlight transition">
                            <i class="fab fa-github text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-highlight transition">
                            <i class="fab fa-discord text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-highlight transition">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-6 pt-6 text-center">
                <p class="text-gray-400 text-sm">
                    © 2025 Expo Tournament. Built with <i class="fas fa-heart text-red-500"></i> for the gaming community.
                </p>
            </div>
        </div>
    </footer>

    <!-- Smooth scrolling script -->
    <script>
        // README content to copy
        const readmeContent = `# Expo Tournament - Gaming Tournament Platform

![Platform](https://img.shields.io/badge/Platform-Web%20%7C%20PWA-blue)
![PHP](https://img.shields.io/badge/PHP-8%2B-purple)
![MySQL](https://img.shields.io/badge/Database-MySQL%2FMariaDB-orange)
![TailwindCSS](https://img.shields.io/badge/UI-TailwindCSS-cyan)
![License](https://img.shields.io/badge/License-MIT-green)

A comprehensive web-based gaming tournament platform with Progressive Web App (PWA) capabilities. This platform allows users to participate in gaming tournaments, manage their digital wallet, and includes a complete referral system with admin management dashboard.

## 🎮 Features

### User Features
- **Tournament Participation**: Join gaming tournaments for popular games
- **Digital Wallet**: Secure wallet system with deposits and withdrawals
- **Referral System**: Earn rewards by referring friends
- **Real-time Updates**: Live tournament status and room details
- **PWA Support**: Install as mobile app with offline capabilities
- **Transaction History**: Complete audit trail of all activities

### Admin Features
- **Tournament Management**: Create, manage, and declare winners
- **User Management**: View and manage all registered users
- **Financial Control**: Approve deposits and withdrawals
- **Banner Management**: Promotional banner system
- **Analytics Dashboard**: User and tournament statistics
- **Settings Management**: Configure system parameters

## 🚀 Quick Start

### Prerequisites
- PHP 8.0 or higher
- MySQL/MariaDB 10.4+
- Web server (Apache/Nginx)
- Modern web browser

### Installation
1. Clone or download the project files
2. Upload to your web server directory
3. Create a MySQL database
4. Navigate to \`install.php\` in your browser
5. Follow the installation wizard
6. Access admin panel with default credentials (admin/admin123)

### Default Credentials
- **Admin**: username: \`admin\`, password: \`admin123\`

## 🏗️ Architecture

### Technology Stack
- **Backend**: PHP 8+ with PDO
- **Database**: MySQL/MariaDB with utf8mb4
- **Frontend**: TailwindCSS 3.0+
- **Icons**: Font Awesome 6.0+
- **PWA**: Service Worker + Manifest
- **Security**: bcrypt, prepared statements

### Database Schema
\`\`\`sql
-- Core tables include:
- admin (authentication)
- users (user accounts & referrals)
- tournaments (tournament data)
- participants (tournament participants)
- transactions (financial records)
- deposits/withdrawals (payment management)
- banners (promotional content)
- legal_pages (terms, privacy, etc.)
- settings (system configuration)
\`\`\`

## 📱 Progressive Web App

The platform includes full PWA support:
- **Offline Functionality**: Core features work without internet
- **Installable**: Add to home screen on mobile devices
- **Responsive Design**: Optimized for all screen sizes
- **App-like Experience**: Standalone display mode

## 🎯 Supported Games

- PUBG Mobile
- Free Fire
- Call of Duty Mobile
- Valorant
- CS:GO
- Fortnite
- Apex Legends
- Custom games (configurable)

## 💰 Financial System

### Wallet Features
- Real-time balance tracking
- Secure transaction processing
- UPI-based withdrawals
- Admin-approved deposits
- Complete transaction history

### Tournament Economics
- Entry fee system
- Automated prize distribution
- Configurable commission rates
- Winner reward system

## 🔒 Security Features

- **Authentication**: Secure login/registration system
- **Data Protection**: Prepared statements prevent SQL injection
- **Password Security**: bcrypt hashing
- **Session Management**: Secure session handling
- **Input Validation**: All user inputs sanitized
- **File Upload Security**: Validated file types and secure storage

## 📁 Project Structure

\`\`\`
/
├── index.php                    # Main dashboard
├── login.php                    # Authentication
├── profile.php                  # User profile
├── wallet.php                   # Wallet management
├── my_tournaments.php           # User tournaments
├── referral.php                 # Referral system
├── legal.php                    # Legal pages
├── install.php                  # Installation wizard
├── manifest.json                # PWA manifest
├── sw.js                        # Service worker
├── database.sql                 # Database schema
├── common/
│   ├── config.php              # Configuration & functions
│   ├── header.php              # Common header
│   └── bottom.php              # Common footer
├── admin/
│   ├── index.php               # Admin dashboard
│   ├── tournament.php          # Tournament management
│   ├── user.php                # User management
│   ├── deposits.php            # Deposit management
│   ├── withdrawals.php         # Withdrawal management
│   ├── banners.php             # Banner management
│   └── setting.php             # Settings
└── uploads/                     # File uploads
\`\`\`

## 🎨 Design System

### Color Palette
- **Primary**: #1a1a2e (Dark background)
- **Secondary**: #16213e
- **Accent**: #0f172a
- **Highlight**: #e94560 (Red accent)
- **Success**: #10b981 (Green)
- **Warning**: #f59e0b (Yellow)

### UI Components
- Consistent card layouts with rounded corners
- Hover effects and smooth transitions
- Mobile-first responsive design
- Intuitive navigation and user flows

---

# 🤖 GitHub Copilot Project Recreation Prompt

## **Detailed GitHub Copilot Project Prompt: Gaming Tournament Platform**

### **Project Overview**
Create a comprehensive web-based gaming tournament platform called "Expo Tournament" with both user and admin interfaces. This is a PHP-based Progressive Web App (PWA) that allows users to participate in gaming tournaments, manage their wallet, and includes a referral system.

### **Core Technologies & Stack**
- **Backend**: PHP 8+ with PDO for database operations
- **Database**: MySQL/MariaDB with utf8mb4 charset
- **Frontend**: Tailwind CSS 3.0+ for responsive design
- **Icons**: Font Awesome 6.0+ for UI icons
- **PWA**: Service Worker + Web App Manifest for app-like experience
- **Session Management**: PHP sessions for authentication
- **Security**: Password hashing with bcrypt, prepared statements for SQL injection prevention

### **Database Architecture**
Design the following database tables:

\`\`\`sql
-- Core Tables
- admin (id, username, password)
- users (id, username, email, password, phone, upi_id, referral_id, referred_by, wallet_balance, created_at)
- tournaments (id, title, game_name, entry_fee, prize_pool, commission_percentage, match_time, room_id, room_password, status, winner_id, created_at)
- participants (id, user_id, tournament_id, joined_at, result)
- transactions (id, user_id, amount, type, description, created_at)
- deposits (id, user_id, amount, transaction_id, status, admin_note, created_at, updated_at)
- withdrawals (id, user_id, amount, upi_id, status, admin_note, created_at, updated_at)
- banners (id, title, image_path, link_url, display_order, is_active, created_at, updated_at)
- legal_pages (id, page_type, title, content, created_at, updated_at)
- settings (id, setting_key, setting_value)
\`\`\`

### **Key Features to Implement**

#### **1. User Authentication System**
- Registration with phone number, email, username, UPI ID
- Login with username/email and password
- Automatic referral ID generation (6-digit unique code)
- Referral bonus system (configurable amounts for referrer and referred)
- Session management with secure logout

#### **2. Tournament Management**
- **Tournament Creation** (Admin): Title, game selection (PUBG Mobile, Free Fire, Call of Duty Mobile, Valorant, CS:GO, Fortnite, Apex Legends), entry fee, prize pool, commission percentage, match time
- **Tournament Status System**: Upcoming → Live → Completed
- **Room Management**: Room ID and password for live tournaments
- **Participant Management**: Join tournaments, view participants list
- **Winner Declaration**: Admin can declare winners and auto-distribute prize money
- **Entry Fee Deduction**: Automatic wallet deduction when joining tournaments

#### **3. Wallet & Payment System**
- **Wallet Balance**: Real-time balance tracking for each user
- **Deposit System**: Users submit deposit requests with transaction ID, admin approval workflow
- **Withdrawal System**: UPI-based withdrawals with admin approval
- **Transaction History**: Complete audit trail of all financial transactions
- **Admin UPI Settings**: Configurable admin UPI ID and QR code for deposits

#### **4. Admin Dashboard**
- **Tournament Management**: Create, edit, delete tournaments; manage participants; declare winners
- **User Management**: View all users, search functionality, user details with tournament history
- **Financial Management**: Approve/reject deposits and withdrawals
- **Banner Management**: Upload and manage promotional banners with display order
- **Legal Pages**: Manage Terms & Conditions, Privacy Policy, Refund Policy
- **Settings**: Configure referral rewards, admin UPI details
- **Analytics**: User statistics, tournament metrics

#### **5. Progressive Web App (PWA)**
- **Manifest.json**: App name "Expo Tournament", standalone display mode, portrait orientation
- **Service Worker**: Offline functionality, caching strategies
- **App Icons**: Multiple sizes (192x192, 512x512) with maskable support
- **Installable**: Add to home screen functionality
- **Responsive Design**: Mobile-first approach with desktop compatibility

#### **6. User Interface Features**
- **Dashboard**: Banner slider, upcoming tournaments, quick stats
- **My Tournaments**: Separate sections for upcoming and completed tournaments
- **Tournament Details**: Entry fee, prize pool, participant count, match time, room details for live tournaments
- **Profile Management**: Edit personal details, UPI ID, view statistics
- **Referral System**: Referral link generation, referred users list, earnings tracking

### **Design Guidelines**

#### **Color Scheme & Theme**
- **Primary Background**: Dark theme (#1a1a2e)
- **Secondary Background**: #16213e
- **Accent Color**: #0f172a
- **Highlight Color**: #e94560 (red accent)
- **Text Colors**: White primary, gray-400 secondary
- **Success/Money**: Green (#10b981)
- **Warning**: Yellow (#f59e0b)

#### **UI Components**
- **Cards**: Rounded corners (rounded-lg), consistent padding (p-4/p-6)
- **Buttons**: Highlight color background, hover effects, icon + text combinations
- **Forms**: Dark inputs with border focus states
- **Icons**: Font Awesome with consistent sizing and spacing
- **Tables**: Responsive with hover effects
- **Messages**: Toast-style success/error messages

### **Security Requirements**
- **Input Validation**: Sanitize all user inputs
- **SQL Injection Prevention**: Use prepared statements exclusively
- **Password Security**: bcrypt hashing with appropriate cost
- **Session Security**: Regenerate session IDs, secure session configuration
- **File Upload Security**: Validate file types, secure upload directory
- **CSRF Protection**: Implement token-based protection for forms
- **Rate Limiting**: Prevent brute force attacks

### **Specific Functionality Details**

#### **Tournament Join Process**
1. Check user authentication
2. Validate tournament exists and is "Upcoming"
3. Check if user already joined
4. Verify sufficient wallet balance
5. Begin database transaction
6. Deduct entry fee from wallet
7. Add transaction record
8. Add user to participants table
9. Commit transaction or rollback on error

#### **Winner Declaration Process**
1. Admin selects winner from participants list
2. Calculate prize distribution (subtract commission)
3. Update tournament status to "Completed"
4. Add prize money to winner's wallet
5. Create credit transaction record
6. Update tournament with winner_id

#### **Referral System Logic**
1. Generate unique 6-digit referral codes
2. Track referral chain (referred_by field)
3. Award bonuses on successful registration
4. Display referral statistics and earnings
5. Generate shareable referral links

### **File Structure to Create**
\`\`\`
/
├── index.php (Dashboard with tournaments)
├── login.php (Login/Register)
├── profile.php (User profile management)
├── wallet.php (Wallet & transactions)
├── my_tournaments.php (User's tournaments)
├── referral.php (Referral system)
├── legal.php (Legal pages)
├── install.php (Database setup)
├── manifest.json (PWA manifest)
├── sw.js (Service worker)
├── database.sql (Database schema)
├── common/
│   ├── config.php (Database & functions)
│   ├── header.php (Common header)
│   └── bottom.php (Common footer)
├── admin/
│   ├── index.php (Admin dashboard)
│   ├── login.php (Admin login)
│   ├── tournament.php (Tournament management)
│   ├── manage_tournament.php (Tournament details)
│   ├── user.php (User management)
│   ├── deposits.php (Deposit management)
│   ├── withdrawals.php (Withdrawal management)
│   ├── banners.php (Banner management)
│   ├── legal_pages.php (Legal content)
│   └── setting.php (System settings)
└── uploads/ (File upload directory)
\`\`\`

### **Key Functions to Implement**
- \`getUserWalletBalance($user_id, $pdo)\`
- \`updateWalletBalance($user_id, $amount, $pdo)\`
- \`addTransaction($user_id, $amount, $type, $description, $pdo)\`
- \`formatCurrency($amount)\` - Format in Indian Rupees
- \`getUserReferralStats($user_id, $pdo)\`
- \`generateReferralId()\` - Generate unique 6-digit code
- \`requireLogin()\` / \`requireAdminLogin()\` - Authentication checks

### **Implementation Steps**
1. **Database Setup**: Create all required tables with proper relationships
2. **Authentication System**: Implement secure login/registration
3. **Tournament Core**: Build tournament creation and participation logic
4. **Wallet System**: Implement secure financial transactions
5. **Admin Panel**: Create comprehensive management interface
6. **PWA Features**: Add manifest, service worker, and offline capabilities
7. **Referral System**: Implement referral tracking and rewards
8. **Security Hardening**: Add CSRF protection, rate limiting, input validation
9. **UI/UX Polish**: Implement responsive design with Tailwind CSS
10. **Testing**: Comprehensive testing of all features and security measures

This prompt provides a complete blueprint for recreating the gaming tournament platform with all its features, security considerations, and technical specifications.

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:
- Create an issue in the repository
- Check the documentation
- Review existing issues for solutions

## 🔄 Updates

- **v1.0.0**: Initial release with core tournament functionality
- **v1.1.0**: Added PWA support and referral system
- **v1.2.0**: Enhanced admin dashboard and analytics

---

**Built with ❤️ for the gaming community**`;

        // Copy to clipboard function
        async function copyToClipboard() {
            try {
                await navigator.clipboard.writeText(readmeContent);
                
                // Update button appearance
                const btn = document.getElementById('copyReadmeBtn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check mr-2"></i>Copied!';
                btn.classList.add('bg-green-500', 'text-white');
                btn.classList.remove('bg-white', 'text-orange-600');
                
                // Show success notification
                showNotification('README content copied to clipboard!', 'success');
                
                // Reset button after 3 seconds
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('bg-green-500', 'text-white');
                    btn.classList.add('bg-white', 'text-orange-600');
                }, 3000);
                
            } catch (err) {
                console.error('Failed to copy: ', err);
                showNotification('Failed to copy. Please try again.', 'error');
            }
        }

        // Show notification function
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = \`fixed top-4 right-4 z-50 px-6 py-3 rounded-lg text-white font-semibold transition-all duration-300 transform translate-x-full \${
                type === 'success' ? 'bg-green-600' : 'bg-red-600'
            }\`;
            notification.innerHTML = \`
                <div class="flex items-center">
                    <i class="fas fa-\${type === 'success' ? 'check-circle' : 'exclamation-triangle'} mr-2"></i>
                    \${message}
                </div>
            \`;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Remove after 4 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 4000);
        }

        // Add event listener to copy button
        document.getElementById('copyReadmeBtn').addEventListener('click', copyToClipboard);

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all cards
        document.querySelectorAll('.card-hover').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>