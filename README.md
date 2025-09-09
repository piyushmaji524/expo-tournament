# Expo Tournament - Gaming Tournament Platform

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
4. Navigate to `install.php` in your browser
5. Follow the installation wizard
6. Access admin panel with default credentials (admin/admin123)

### Default Credentials
- **Admin**: username: `admin`, password: `admin123`

## 🏗️ Architecture

### Technology Stack
- **Backend**: PHP 8+ with PDO
- **Database**: MySQL/MariaDB with utf8mb4
- **Frontend**: TailwindCSS 3.0+
- **Icons**: Font Awesome 6.0+
- **PWA**: Service Worker + Manifest
- **Security**: bcrypt, prepared statements

### Database Schema
```sql
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
```

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

```
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
```

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

```sql
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
```

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
```
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
```

### **Key Functions to Implement**
- `getUserWalletBalance($user_id, $pdo)`
- `updateWalletBalance($user_id, $amount, $pdo)`
- `addTransaction($user_id, $amount, $type, $description, $pdo)`
- `formatCurrency($amount)` - Format in Indian Rupees
- `getUserReferralStats($user_id, $pdo)`
- `generateReferralId()` - Generate unique 6-digit code
- `requireLogin()` / `requireAdminLogin()` - Authentication checks

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

**Built with ❤️ for the gaming community**
