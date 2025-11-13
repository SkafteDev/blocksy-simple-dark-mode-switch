<?php
/**
 * Plugin Name: Simple Theme Mode Switcher
 * Description: Adds a floating light/dark mode switcher that works with any theme (including Blocksy) without paywalled color options.
 * Version: 1.0.0
 * Author: Christian Skafte Beck Clausen
 * License: GPL2+
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Simple_Theme_Mode_Switcher {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'wp_head', [ $this, 'output_initial_theme_script' ], 5 );
    }

    /**
     * Enqueue front-end CSS & JS.
     */
    public function enqueue_assets() {
        $plugin_url = plugin_dir_url( __FILE__ );

        wp_enqueue_style(
            'stms-style',
            $plugin_url . 'assets/css/simple-theme-toggle.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'stms-script',
            $plugin_url . 'assets/js/simple-theme-toggle.js',
            [],
            '1.0.0',
            true
        );
    }

    /**
     * Inline script in <head> to set initial theme before the page paints.
     */
    public function output_initial_theme_script() {
        ?>
        <script>
        (function () {
            try {
                var storedTheme = localStorage.getItem('stms-theme');
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var theme = storedTheme || (prefersDark ? 'dark' : 'light');

                document.documentElement.setAttribute('data-stms-theme', theme);

                if (theme === 'dark') {
                    document.documentElement.classList.add('stms-dark-mode');
                } else {
                    document.documentElement.classList.remove('stms-dark-mode');
                }
            } catch (e) {
                // Fail silently if localStorage is not available
            }
        })();
        </script>
        <?php
    }
}

new Simple_Theme_Mode_Switcher();
