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

        var btn = document.getElementById('stms-toggle');
        if (btn) {
            btn.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
            btn.setAttribute('data-stms-theme', theme);
            btn.innerHTML = theme === 'dark' ? '☾' : '☀';
        }
    }

    function toggleTheme() {
        var current = document.documentElement.getAttribute('data-stms-theme') || 'light';
        var next = current === 'dark' ? 'light' : 'dark';
        setTheme(next);
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Create button only once
        if (document.getElementById('stms-toggle')) {
            return;
        }

        var btn = document.createElement('button');
        btn.id = 'stms-toggle';
        btn.type = 'button';
        btn.className = 'stms-toggle';
        btn.setAttribute('aria-label', 'Toggle dark/light mode');
        btn.setAttribute('aria-pressed', 'false');

        // Initial icon (will be updated by setTheme)
        btn.innerHTML = '☀';

        btn.addEventListener('click', function () {
            toggleTheme();
        });

        document.body.appendChild(btn);

        // Make sure button theme matches current attribute
        var current = document.documentElement.getAttribute('data-stms-theme') || 'light';
        setTheme(current);
    });
})();
