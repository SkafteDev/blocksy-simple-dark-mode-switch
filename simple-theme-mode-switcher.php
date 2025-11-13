<?php
/**
 * Plugin Name: Simple Theme Mode Switcher
 * Description: Adds a light/dark mode switcher via shortcode or floating button, plus a dark-mode Blocksy palette (8 colors) with a settings page.
 * Version: 1.2.0
 * Author: Your Name
 * License: GPL2+
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Simple_Theme_Mode_Switcher {

    /**
     * Option name for storing dark palette colors.
     *
     * @var string
     */
    private $option_name = 'stms_dark_palette';

    /**
     * Menu slug for the settings page.
     *
     * @var string
     */
    private $settings_page_slug = 'stms-theme-mode-switcher';

    public function __construct() {
        // Frontend.
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'wp_head', [ $this, 'output_initial_theme_script' ], 5 );
        add_action( 'wp_head', [ $this, 'output_palette_css' ], 20 );
        add_shortcode( 'stms_toggle', [ $this, 'render_toggle_shortcode' ] );

        // Admin settings page.
        add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
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
            '1.2.0'
        );

        wp_enqueue_script(
            'stms-script',
            $plugin_url . 'assets/js/simple-theme-toggle.js',
            [],
            '1.2.0',
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

    /**
     * Output CSS for the dark-mode palette based on saved settings.
     *
     * Maps to:
     *   --theme-palette-color-1 ... --theme-palette-color-8
     * in dark mode only.
     */
    public function output_palette_css() {
        $options = get_option( $this->option_name, [] );

        // If nothing is set, do nothing.
        if ( empty( $options ) || ! is_array( $options ) ) {
            return;
        }

        // Collect defined colors.
        $css_lines = [];
        for ( $i = 1; $i <= 8; $i++ ) {
            $key   = 'palette' . $i;
            $value = isset( $options[ $key ] ) ? trim( $options[ $key ] ) : '';

            if ( ! empty( $value ) ) {
                // Ensure it's a valid hex color.
                $color = sanitize_hex_color( $value );
                if ( $color ) {
                    $css_lines[] = sprintf( '--theme-palette-color-%d: %s;', $i, $color );
                }
            }
        }

        if ( empty( $css_lines ) ) {
            return;
        }

        echo "<style id=\"stms-dark-palette\">\n";
        echo "html[data-stms-theme=\"dark\"] {\n    " . implode( "\n    ", $css_lines ) . "\n}\n";
        echo "</style>\n";
    }

    /**
     * Shortcode to render a header toggle button.
     *
     * Usage: [stms_toggle]
     */
    public function render_toggle_shortcode() {
        // Important: id="stms-toggle" so JS recognises it and doesn't create the floating one.
        $button = '<button id="stms-toggle"'
            . ' class="stms-toggle stms-toggle-header"'
            . ' type="button"'
            . ' aria-label="Toggle dark/light mode"'
            . ' aria-pressed="false"'
            . ' data-stms-theme="light">'
            . '☀'
            . '</button>';

        return $button;
    }

    /* ---------------------------------------------------------------------
     * Admin settings page
     * ------------------------------------------------------------------ */

    /**
     * Add the settings page under Settings.
     */
    public function add_settings_page() {
        add_options_page(
            __( 'Theme Mode Switcher', 'stms' ),
            __( 'Theme Mode Switcher', 'stms' ),
            'manage_options',
            $this->settings_page_slug,
            [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Register settings, sections and fields.
     */
    public function register_settings() {
        register_setting(
            'stms_options_group',
            $this->option_name,
            [
                'type'              => 'array',
                'sanitize_callback' => [ $this, 'sanitize_palette' ],
                'default'           => [],
            ]
        );

        add_settings_section(
            'stms_palette_section',
            __( 'Dark Mode Blocksy Palette', 'stms' ),
            [ $this, 'render_palette_section_intro' ],
            $this->settings_page_slug
        );

        // 8 fields for --theme-palette-color-1 ... 8
        for ( $i = 1; $i <= 8; $i++ ) {
            add_settings_field(
                'stms_palette_' . $i,
                sprintf(
                    __( 'Dark Palette Color %d ( --theme-palette-color-%d )', 'stms' ),
                    $i,
                    $i
                ),
                [ $this, 'render_palette_field' ],
                $this->settings_page_slug,
                'stms_palette_section',
                [
                    'index' => $i,
                ]
            );
        }
    }

    /**
     * Sanitize palette values.
     *
     * @param array $input Raw option input.
     * @return array
     */
    public function sanitize_palette( $input ) {
        $output = [];

        if ( ! is_array( $input ) ) {
            return $output;
        }

        for ( $i = 1; $i <= 8; $i++ ) {
            $key   = 'palette' . $i;
            $value = isset( $input[ $key ] ) ? $input[ $key ] : '';

            if ( ! empty( $value ) ) {
                $color = sanitize_hex_color( $value );
                if ( $color ) {
                    $output[ $key ] = $color;
                }
            }
        }

        return $output;
    }

    /**
     * Section intro text.
     */
    public function render_palette_section_intro() {
        echo '<p>';
        esc_html_e(
            'These colors will apply only when dark mode is active and will override Blocksy’s --theme-palette-color-1 to --theme-palette-color-8 variables.',
            'stms'
        );
        echo '</p>';
    }

    /**
     * Render a single color field.
     *
     * @param array $args Field args (index).
     */
    public function render_palette_field( $args ) {
        $index = isset( $args['index'] ) ? (int) $args['index'] : 1;
        $options = get_option( $this->option_name, [] );
        $key     = 'palette' . $index;
        $value   = isset( $options[ $key ] ) ? esc_attr( $options[ $key ] ) : '';

        printf(
            '<input type="text" class="stms-color-field" id="%1$s" name="%2$s[%3$s]" value="%4$s" data-default-color="#000000" />',
            esc_attr( 'stms_palette_' . $index ),
            esc_attr( $this->option_name ),
            esc_attr( $key ),
            $value
        );

        echo '<p class="description">';
        printf(
            esc_html__( 'Leave empty to keep the theme default for --theme-palette-color-%d.', 'stms' ),
            $index
        );
        echo '</p>';
    }

    /**
     * Render the settings page HTML.
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Simple Theme Mode Switcher', 'stms' ); ?></h1>
            <p><?php esc_html_e( 'Configure the dark mode palette for Blocksy (or any theme that uses these variables).', 'stms' ); ?></p>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'stms_options_group' );
                do_settings_sections( $this->settings_page_slug );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Enqueue admin assets (color picker) only on our settings page.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_admin_assets( $hook ) {
        // Our settings page hook is "settings_page_{menu_slug}".
        if ( 'settings_page_' . $this->settings_page_slug !== $hook ) {
            return;
        }

        $plugin_url = plugin_dir_url( __FILE__ );

        // WP built-in color picker.
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );

        // Our tiny initializer.
        wp_enqueue_script(
            'stms-admin-script',
            $plugin_url . 'assets/js/stms-admin.js',
            [ 'wp-color-picker', 'jquery' ],
            '1.2.0',
            true
        );
    }
}

new Simple_Theme_Mode_Switcher();

