    </main>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-secondary border-t border-accent z-50">
        <div class="flex justify-around py-2">
            <a href="index.php" class="flex flex-col items-center py-2 px-4 text-center <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-highlight' : 'text-gray-400'; ?>">
                <i class="fas fa-home text-lg mb-1"></i>
                <span class="text-xs">Home</span>
            </a>
            <a href="my_tournaments.php" class="flex flex-col items-center py-2 px-4 text-center <?php echo basename($_SERVER['PHP_SELF']) == 'my_tournaments.php' ? 'text-highlight' : 'text-gray-400'; ?>">
                <i class="fas fa-gamepad text-lg mb-1"></i>
                <span class="text-xs">My Tournaments</span>
            </a>
            <a href="wallet.php" class="flex flex-col items-center py-2 px-4 text-center <?php echo basename($_SERVER['PHP_SELF']) == 'wallet.php' ? 'text-highlight' : 'text-gray-400'; ?>">
                <i class="fas fa-wallet text-lg mb-1"></i>
                <span class="text-xs">Wallet</span>
            </a>
            <a href="referral.php" class="flex flex-col items-center py-2 px-4 text-center <?php echo basename($_SERVER['PHP_SELF']) == 'referral.php' ? 'text-highlight' : 'text-gray-400'; ?>">
                <i class="fas fa-users text-lg mb-1"></i>
                <span class="text-xs">Referral</span>
            </a>
            <a href="profile.php" class="flex flex-col items-center py-2 px-4 text-center <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'text-highlight' : 'text-gray-400'; ?>">
                <i class="fas fa-user text-lg mb-1"></i>
                <span class="text-xs">Profile</span>
            </a>
        </div>
    </nav>

    <script>
        // Disable right-click
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });

        // Disable text selection
        document.addEventListener('selectstart', function(e) {
            e.preventDefault();
        });

        // Disable zoom
        document.addEventListener('wheel', function(e) {
            if (e.ctrlKey) {
                e.preventDefault();
            }
        });

        // Disable keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Disable Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+Z, F12, Ctrl+Shift+I, Ctrl+U
            if (e.ctrlKey && (e.keyCode === 65 || e.keyCode === 67 || e.keyCode === 86 || e.keyCode === 88 || e.keyCode === 90)) {
                e.preventDefault();
            }
            if (e.keyCode === 123 || (e.ctrlKey && e.shiftKey && e.keyCode === 73) || (e.ctrlKey && e.keyCode === 85)) {
                e.preventDefault();
            }
        });

        // Disable drag
        document.addEventListener('dragstart', function(e) {
            e.preventDefault();
        });

        // Disable touch events for zoom
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(event) {
            let now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);

        // Disable pinch zoom
        document.addEventListener('gesturestart', function(e) {
            e.preventDefault();
        });

        // PWA Install Functionality
        let deferredPrompt;
        let installButton = null;

        // Create install button
        function createInstallButton() {
            if (installButton) return;
            
            installButton = document.createElement('div');
            installButton.id = 'pwa-install-banner';
            installButton.className = 'fixed top-4 left-4 right-4 bg-highlight text-white p-3 rounded-lg shadow-lg z-50 flex items-center justify-between transform -translate-y-full opacity-0 transition-all duration-300';
            installButton.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-download mr-3 text-lg"></i>
                    <div>
                        <div class="font-semibold text-sm">Install Expo Tournament</div>
                        <div class="text-xs opacity-90">Get the app experience!</div>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button onclick="installPWA()" class="bg-white text-highlight px-3 py-1 rounded text-sm font-semibold hover:bg-gray-100 transition">
                        Install
                    </button>
                    <button onclick="dismissInstallBanner()" class="text-white hover:text-gray-200 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            document.body.appendChild(installButton);
            
            // Show banner
            setTimeout(() => {
                installButton.style.transform = 'translateY(0)';
                installButton.style.opacity = '1';
            }, 1000);
        }

        // Handle PWA install prompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            // Check if user has dismissed banner recently
            const dismissed = localStorage.getItem('pwa-install-dismissed');
            const dismissedTime = localStorage.getItem('pwa-install-dismissed-time');
            const now = Date.now();
            
            // Show banner if not dismissed or dismissed more than 7 days ago
            if (!dismissed || (dismissedTime && (now - parseInt(dismissedTime)) > 7 * 24 * 60 * 60 * 1000)) {
                createInstallButton();
            }
        });

        // Install PWA function
        function installPWA() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                        dismissInstallBanner();
                    } else {
                        console.log('User dismissed the install prompt');
                    }
                    deferredPrompt = null;
                });
            }
        }

        // Dismiss install banner
        function dismissInstallBanner() {
            if (installButton) {
                installButton.style.transform = 'translateY(-100%)';
                installButton.style.opacity = '0';
                setTimeout(() => {
                    if (installButton && installButton.parentNode) {
                        installButton.parentNode.removeChild(installButton);
                    }
                    installButton = null;
                }, 300);
            }
            
            // Remember dismissal
            localStorage.setItem('pwa-install-dismissed', 'true');
            localStorage.setItem('pwa-install-dismissed-time', Date.now().toString());
        }

        // Register service worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('SW registered: ', registration);
                    })
                    .catch((registrationError) => {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }

        // Handle app installed event
        window.addEventListener('appinstalled', (evt) => {
            console.log('App was installed.');
            dismissInstallBanner();
        });
    </script>
</body>
</html>
