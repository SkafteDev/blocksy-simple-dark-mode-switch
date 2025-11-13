(function () {
    function setTheme(theme) {
        var root = document.documentElement;

        root.setAttribute('data-stms-theme', theme);

        if (theme === 'dark') {
            root.classList.add('stms-dark-mode');
        } else {
            root.classList.remove('stms-dark-mode');
        }

        try {
            localStorage.setItem('stms-theme', theme);
        } catch (e) {
            // Ignore storage errors
        }

        // Update all toggle buttons on the page (header, future clones, etc.)
        var buttons = document.querySelectorAll('[data-stms-toggle="button"], #stms-toggle');
        buttons.forEach(function (btn) {
            btn.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
            btn.setAttribute('data-stms-theme', theme);
            btn.innerHTML = theme === 'dark' ? '☾' : '☀';
        });
    }

    function toggleTheme() {
        var current = document.documentElement.getAttribute('data-stms-theme') || 'light';
        var next = current === 'dark' ? 'light' : 'dark';
        setTheme(next);
    }

    function enhanceButton(btn) {
        if (!btn) return;

        // Mark as toggle button for query selector
        btn.setAttribute('data-stms-toggle', 'button');

        // Avoid double-binding
        if (btn.dataset.stmsBound === 'true') {
            return;
        }

        btn.addEventListener('click', function () {
            toggleTheme();
        });

        btn.dataset.stmsBound = 'true';
    }

    document.addEventListener('DOMContentLoaded', function () {
        // 1. Check if a header shortcode button exists
        var btn = document.getElementById('stms-toggle');

        // 2. If not, create the floating one in the bottom-right corner
        if (!btn) {
            btn = document.createElement('button');
            btn.id = 'stms-toggle';
            btn.type = 'button';
            btn.className = 'stms-toggle'; // floating, uses main CSS
            btn.setAttribute('aria-label', 'Toggle dark/light mode');
            btn.setAttribute('aria-pressed', 'false');
            btn.innerHTML = '☀';

            document.body.appendChild(btn);
        }

        // 3. Attach behaviour to this button
        enhanceButton(btn);

        // 4. Sync button UI with current theme attribute
        var current = document.documentElement.getAttribute('data-stms-theme') || 'light';
        setTheme(current);
    });
})();

