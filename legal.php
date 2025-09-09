<?php
require_once 'common/config.php';

// Get the requested page
$pageKey = $_GET['page'] ?? 'terms_of_service';

// Fetch the legal page content
$stmt = $pdo->prepare("SELECT * FROM legal_pages WHERE page_key = ? AND is_active = 1");
$stmt->execute([$pageKey]);
$legalPage = $stmt->fetch();

// If page not found, redirect to terms of service or show 404
if (!$legalPage) {
    if ($pageKey !== 'terms_of_service') {
        header('Location: legal.php?page=terms_of_service');
        exit();
    } else {
        // Fallback content if no legal pages exist
        $legalPage = [
            'title' => 'Legal Page Not Found',
            'content' => '<h2>Page Not Available</h2><p>The requested legal page is not available at this time. Please contact support for assistance.</p>'
        ];
    }
}

// Get all available legal pages for navigation
$stmt = $pdo->query("SELECT page_key, title FROM legal_pages WHERE is_active = 1 ORDER BY 
    CASE page_key 
        WHEN 'terms_of_service' THEN 1
        WHEN 'privacy_policy' THEN 2
        WHEN 'refund_policy' THEN 3
        WHEN 'responsible_gaming' THEN 4
        WHEN 'fair_play_policy' THEN 5
        WHEN 'community_guidelines' THEN 6
        WHEN 'contact_us' THEN 7
        ELSE 8
    END");
$allPages = $stmt->fetchAll();

include 'common/header.php';
?>

<div class="max-w-6xl mx-auto space-y-6 pb-20">
    <!-- Header -->
    <div class="bg-secondary rounded-lg p-6">
        <h1 class="text-2xl font-bold flex items-center mb-4">
            <i class="fas fa-gavel mr-3 text-highlight"></i>
            <?php echo htmlspecialchars($legalPage['title']); ?>
        </h1>
        
        <!-- Navigation -->
        <div class="flex flex-wrap gap-2">
            <?php foreach ($allPages as $page): ?>
                <a href="legal.php?page=<?php echo $page['page_key']; ?>" 
                   class="px-3 py-2 rounded-lg text-sm transition <?php echo $pageKey === $page['page_key'] ? 'bg-highlight text-white' : 'bg-accent text-gray-300 hover:bg-highlight'; ?>">
                    <?php echo htmlspecialchars($page['title']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Content -->
    <div class="bg-secondary rounded-lg p-6">
        <div class="prose prose-invert max-w-none">
            <div class="legal-content">
                <?php echo $legalPage['content']; ?>
            </div>
        </div>
        
        <!-- Back to Top -->
        <div class="mt-8 text-center">
            <button onclick="scrollToTop()" class="bg-accent hover:bg-highlight transition px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-up mr-2"></i>Back to Top
            </button>
        </div>
    </div>

    <!-- Footer Links -->
    <div class="bg-secondary rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Quick Actions -->
            <div>
                <h3 class="text-lg font-semibold mb-3 flex items-center">
                    <i class="fas fa-link mr-2 text-highlight"></i>
                    Quick Actions
                </h3>
                <div class="space-y-2">
                    <a href="index.php" class="block bg-accent hover:bg-highlight transition p-3 rounded-lg">
                        <i class="fas fa-home mr-2"></i>Back to Dashboard
                    </a>
                    <a href="profile.php" class="block bg-accent hover:bg-highlight transition p-3 rounded-lg">
                        <i class="fas fa-user mr-2"></i>My Profile
                    </a>
                    <a href="wallet.php" class="block bg-accent hover:bg-highlight transition p-3 rounded-lg">
                        <i class="fas fa-wallet mr-2"></i>My Wallet
                    </a>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div>
                <h3 class="text-lg font-semibold mb-3 flex items-center">
                    <i class="fas fa-envelope mr-2 text-highlight"></i>
                    Need Help?
                </h3>
                <div class="space-y-2 text-sm text-gray-300">
                    <p><i class="fas fa-envelope mr-2 text-blue-400"></i>support@adeptplay.com</p>
                    <p><i class="fas fa-clock mr-2 text-green-400"></i>Support Hours: 9 AM - 8 PM IST</p>
                    <a href="legal.php?page=contact_us" class="block bg-blue-600 hover:bg-blue-700 transition p-3 rounded-lg mt-3">
                        <i class="fas fa-phone mr-2"></i>Full Contact Information
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.legal-content {
    line-height: 1.7;
}

.legal-content h2 {
    color: #e94560;
    font-size: 1.5rem;
    font-weight: bold;
    margin: 2rem 0 1rem 0;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #0f3460;
}

.legal-content h3 {
    color: #ffffff;
    font-size: 1.25rem;
    font-weight: 600;
    margin: 1.5rem 0 0.75rem 0;
}

.legal-content h4 {
    color: #d1d5db;
    font-size: 1.1rem;
    font-weight: 600;
    margin: 1rem 0 0.5rem 0;
}

.legal-content p {
    color: #d1d5db;
    margin: 0.75rem 0;
}

.legal-content ul, .legal-content ol {
    color: #d1d5db;
    margin: 1rem 0;
    padding-left: 1.5rem;
}

.legal-content li {
    margin: 0.5rem 0;
}

.legal-content strong {
    color: #ffffff;
    font-weight: 600;
}

.legal-content a {
    color: #60a5fa;
    text-decoration: underline;
}

.legal-content a:hover {
    color: #93c5fd;
}

@media (max-width: 768px) {
    .legal-content h2 {
        font-size: 1.25rem;
    }
    
    .legal-content h3 {
        font-size: 1.1rem;
    }
    
    .legal-content {
        font-size: 0.9rem;
    }
}
</style>

<script>
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Add smooth scrolling for anchor links
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
});
</script>

<?php include 'common/bottom.php'; ?>
