    </main>

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
    </script>
</body>
</html>
