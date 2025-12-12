<?php
/**
 * Plugin Name: Kadence Breakpoints Override
 * Plugin URI: https://github.com/your-repo/kadence-breakpoints-override
 * Description: Override frontend breakpoint pixel widths for Kadence without builds. Provides an admin page to set breakpoints and injects the CSS on the frontend. NOTE: This only affects the public site (frontend). The Gutenberg editor preview remains unchanged.
 * Version: 1.1.0
 * Author: Your Name
 * Author URI: https://your-website.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: kadence-breakpoints-override
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main plugin class
 */
class Kadence_Breakpoints_Override {

    /**
     * Plugin version
     */
    const VERSION = '1.1.0';

    /**
     * Option name for storing breakpoints
     */
    const OPTION_NAME = 'kbo_breakpoints';

    /**
     * Default breakpoint values (Kadence defaults)
     */
    private $defaults = array(
        'tablet' => 1024,
        'mobile' => 767,
    );

    /**
     * Constructor
     */
    public function __construct() {
        // Admin hooks
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // Add settings link on plugins page
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_settings_link' ) );

        // ============================================================
        // KADENCE BLOCKS FILTERS - Override breakpoints at source
        // ============================================================
        
        // Kadence Blocks tablet breakpoint
        add_filter( 'kadence_blocks_tablet_breakpoint', array( $this, 'filter_tablet_breakpoint' ), 999 );
        
        // Kadence Blocks mobile breakpoint
        add_filter( 'kadence_blocks_mobile_breakpoint', array( $this, 'filter_mobile_breakpoint' ), 999 );

        // Kadence Blocks default breakpoints array
        add_filter( 'kadence_blocks_default_breakpoints', array( $this, 'filter_default_breakpoints' ), 999 );

        // ============================================================
        // KADENCE THEME FILTERS
        // ============================================================
        
        // Kadence Theme screen sizes
        add_filter( 'kadence_screen_sizes', array( $this, 'filter_screen_sizes' ), 999 );

        // ============================================================
        // FRONTEND CSS - Fallback and additional overrides
        // ============================================================
        
        // Only on frontend
        if ( ! is_admin() ) {
            // Very early to set CSS variables
            add_action( 'wp_head', array( $this, 'inject_early_css' ), 1 );
            // Very late to override any remaining inline styles
            add_action( 'wp_head', array( $this, 'inject_late_css' ), 9999 );
            // Also in footer for styles loaded late
            add_action( 'wp_footer', array( $this, 'inject_footer_css' ), 9999 );
        }
    }

    /**
     * Get current breakpoint values
     */
    public function get_breakpoints() {
        $options = get_option( self::OPTION_NAME, array() );
        return wp_parse_args( $options, $this->defaults );
    }

    /**
     * Filter: Kadence Blocks tablet breakpoint
     */
    public function filter_tablet_breakpoint( $breakpoint ) {
        $breakpoints = $this->get_breakpoints();
        return intval( $breakpoints['tablet'] );
    }

    /**
     * Filter: Kadence Blocks mobile breakpoint
     */
    public function filter_mobile_breakpoint( $breakpoint ) {
        $breakpoints = $this->get_breakpoints();
        return intval( $breakpoints['mobile'] );
    }

    /**
     * Filter: Kadence Blocks default breakpoints array
     */
    public function filter_default_breakpoints( $defaults ) {
        $breakpoints = $this->get_breakpoints();
        
        if ( is_array( $defaults ) ) {
            if ( isset( $defaults['tablet'] ) ) {
                $defaults['tablet'] = intval( $breakpoints['tablet'] );
            }
            if ( isset( $defaults['mobile'] ) ) {
                $defaults['mobile'] = intval( $breakpoints['mobile'] );
            }
        }
        
        return $defaults;
    }

    /**
     * Filter: Kadence Theme screen sizes
     */
    public function filter_screen_sizes( $sizes ) {
        $breakpoints = $this->get_breakpoints();
        
        if ( is_array( $sizes ) ) {
            // Kadence Theme uses 'tablet' and 'mobile' keys
            if ( isset( $sizes['tablet'] ) ) {
                $sizes['tablet'] = intval( $breakpoints['tablet'] );
            }
            if ( isset( $sizes['mobile'] ) ) {
                $sizes['mobile'] = intval( $breakpoints['mobile'] );
            }
        }
        
        return $sizes;
    }

    /**
     * Add admin menu under Settings
     */
    public function add_admin_menu() {
        add_options_page(
            __( 'Kadence Breakpoints', 'kadence-breakpoints-override' ),
            __( 'Kadence Breakpoints', 'kadence-breakpoints-override' ),
            'manage_options',
            'kadence-breakpoints',
            array( $this, 'render_admin_page' )
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting(
            'kbo_settings_group',
            self::OPTION_NAME,
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize_breakpoints' ),
                'default'           => $this->defaults,
            )
        );

        add_settings_section(
            'kbo_main_section',
            __( 'Breakpoint Settings', 'kadence-breakpoints-override' ),
            array( $this, 'render_section_description' ),
            'kadence-breakpoints'
        );

        add_settings_field(
            'kbo_tablet',
            __( 'Tablet Breakpoint (px)', 'kadence-breakpoints-override' ),
            array( $this, 'render_tablet_field' ),
            'kadence-breakpoints',
            'kbo_main_section'
        );

        add_settings_field(
            'kbo_mobile',
            __( 'Mobile Breakpoint (px)', 'kadence-breakpoints-override' ),
            array( $this, 'render_mobile_field' ),
            'kadence-breakpoints',
            'kbo_main_section'
        );
    }

    /**
     * Sanitize breakpoint values
     */
    public function sanitize_breakpoints( $input ) {
        $sanitized = array();

        if ( isset( $input['tablet'] ) ) {
            $sanitized['tablet'] = absint( $input['tablet'] );
            if ( $sanitized['tablet'] < 1 ) {
                $sanitized['tablet'] = $this->defaults['tablet'];
            }
        }

        if ( isset( $input['mobile'] ) ) {
            $sanitized['mobile'] = absint( $input['mobile'] );
            if ( $sanitized['mobile'] < 1 ) {
                $sanitized['mobile'] = $this->defaults['mobile'];
            }
        }

        // Ensure mobile < tablet
        if ( $sanitized['mobile'] >= $sanitized['tablet'] ) {
            $sanitized['mobile'] = $sanitized['tablet'] - 1;
        }

        return $sanitized;
    }

    /**
     * Render section description
     */
    public function render_section_description() {
        echo '<p>' . esc_html__( 'Set custom breakpoint values for Kadence. These values will override the default Kadence breakpoints on the frontend.', 'kadence-breakpoints-override' ) . '</p>';
        echo '<p><strong>' . esc_html__( 'How it works:', 'kadence-breakpoints-override' ) . '</strong> ' . esc_html__( 'This plugin uses Kadence filter hooks to change breakpoints at the source, plus CSS overrides as fallback.', 'kadence-breakpoints-override' ) . '</p>';
        echo '<p><strong>' . esc_html__( 'Note:', 'kadence-breakpoints-override' ) . '</strong> ' . esc_html__( 'The Gutenberg editor preview may still use original breakpoints. Clear any caches after saving.', 'kadence-breakpoints-override' ) . '</p>';
        echo '<p>' . esc_html__( 'Kadence defaults: Tablet = 1024px, Mobile = 767px', 'kadence-breakpoints-override' ) . '</p>';
    }

    /**
     * Render tablet field
     */
    public function render_tablet_field() {
        $breakpoints = $this->get_breakpoints();
        ?>
        <input 
            type="number" 
            name="<?php echo esc_attr( self::OPTION_NAME ); ?>[tablet]" 
            value="<?php echo esc_attr( $breakpoints['tablet'] ); ?>" 
            min="1" 
            max="9999"
            class="small-text"
            style="width: 100px;"
        /> px
        <p class="description">
            <?php esc_html_e( 'Screens at or below this width use tablet styles. Default: 1024px', 'kadence-breakpoints-override' ); ?>
        </p>
        <?php
    }

    /**
     * Render mobile field
     */
    public function render_mobile_field() {
        $breakpoints = $this->get_breakpoints();
        ?>
        <input 
            type="number" 
            name="<?php echo esc_attr( self::OPTION_NAME ); ?>[mobile]" 
            value="<?php echo esc_attr( $breakpoints['mobile'] ); ?>" 
            min="1" 
            max="9999"
            class="small-text"
            style="width: 100px;"
        /> px
        <p class="description">
            <?php esc_html_e( 'Screens at or below this width use mobile styles. Default: 767px', 'kadence-breakpoints-override' ); ?>
        </p>
        <?php
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Check if settings were saved
        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error(
                'kbo_messages',
                'kbo_message',
                __( 'Breakpoints saved successfully. Please clear your cache!', 'kadence-breakpoints-override' ),
                'updated'
            );
        }
        
        $breakpoints = $this->get_breakpoints();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <?php settings_errors( 'kbo_messages' ); ?>
            
            <form action="options.php" method="post">
                <?php
                settings_fields( 'kbo_settings_group' );
                do_settings_sections( 'kadence-breakpoints' );
                submit_button( __( 'Save Breakpoints', 'kadence-breakpoints-override' ) );
                ?>
            </form>

            <hr />
            
            <h2><?php esc_html_e( 'Active Filters', 'kadence-breakpoints-override' ); ?></h2>
            <p><?php esc_html_e( 'The following Kadence filters are being used:', 'kadence-breakpoints-override' ); ?></p>
            <ul style="list-style: disc; margin-left: 20px;">
                <li><code>kadence_blocks_tablet_breakpoint</code> → <?php echo esc_html( $breakpoints['tablet'] ); ?>px</li>
                <li><code>kadence_blocks_mobile_breakpoint</code> → <?php echo esc_html( $breakpoints['mobile'] ); ?>px</li>
                <li><code>kadence_blocks_default_breakpoints</code></li>
                <li><code>kadence_screen_sizes</code> (Kadence Theme)</li>
            </ul>
            
            <hr />
            
            <h2><?php esc_html_e( 'CSS Output Preview', 'kadence-breakpoints-override' ); ?></h2>
            <p><?php esc_html_e( 'This fallback CSS is also injected into the frontend:', 'kadence-breakpoints-override' ); ?></p>
            <pre style="background: #23282d; color: #f1f1f1; padding: 15px; overflow-x: auto; border-radius: 4px; font-size: 13px;"><?php echo esc_html( $this->generate_override_css() ); ?></pre>
            
            <hr />
            
            <h2><?php esc_html_e( 'Troubleshooting', 'kadence-breakpoints-override' ); ?></h2>
            <ol>
                <li><?php esc_html_e( 'Clear all caches (browser, plugin caches, server caches)', 'kadence-breakpoints-override' ); ?></li>
                <li><?php esc_html_e( 'If using a CDN, purge the CDN cache', 'kadence-breakpoints-override' ); ?></li>
                <li><?php esc_html_e( 'Check if another plugin or theme is also modifying breakpoints', 'kadence-breakpoints-override' ); ?></li>
                <li><?php esc_html_e( 'Inspect the page source to verify the CSS is being injected', 'kadence-breakpoints-override' ); ?></li>
            </ol>
        </div>
        <?php
    }

    /**
     * Generate override CSS
     */
    public function generate_override_css() {
        $breakpoints = $this->get_breakpoints();
        $tablet = intval( $breakpoints['tablet'] );
        $mobile = intval( $breakpoints['mobile'] );

        $css = "/* Kadence Breakpoints Override v" . self::VERSION . " */\n";
        $css .= "/* Tablet: {$tablet}px | Mobile: {$mobile}px */\n\n";

        // CSS Custom Properties
        $css .= ":root {\n";
        $css .= "  --global-kb-editor-full-width: {$tablet}px !important;\n";
        $css .= "  --global-kb-editor-tablet-width: {$tablet}px !important;\n";
        $css .= "  --global-kb-tablet-width: {$tablet}px !important;\n";
        $css .= "  --global-kb-mobile-width: {$mobile}px !important;\n";
        $css .= "  --kb-tablet-break: {$tablet}px !important;\n";
        $css .= "  --kb-mobile-break: {$mobile}px !important;\n";
        $css .= "}\n";

        return $css;
    }

    /**
     * Inject early CSS (CSS variables)
     */
    public function inject_early_css() {
        $css = $this->generate_override_css();
        echo "\n<style id=\"kbo-early-css\">\n" . $css . "</style>\n";
    }

    /**
     * Inject late CSS (after all other styles)
     */
    public function inject_late_css() {
        $breakpoints = $this->get_breakpoints();
        $tablet = intval( $breakpoints['tablet'] );
        $mobile = intval( $breakpoints['mobile'] );

        // Re-declare visibility classes at custom breakpoints
        $css = "/* Kadence Breakpoints Override - Late CSS */\n";
        
        // Hide default breakpoint rules and add custom ones
        $css .= "@media (min-width: " . ($tablet + 1) . "px) {\n";
        $css .= "  .kb-hide-desktop, .kt-hide-desktop { display: none !important; }\n";
        $css .= "}\n";
        
        $css .= "@media (max-width: {$tablet}px) and (min-width: " . ($mobile + 1) . "px) {\n";
        $css .= "  .kb-hide-tablet, .kt-hide-tablet { display: none !important; }\n";
        $css .= "  .kb-show-tablet, .kt-show-tablet { display: block !important; }\n";
        $css .= "}\n";
        
        $css .= "@media (max-width: {$mobile}px) {\n";
        $css .= "  .kb-hide-mobile, .kt-hide-mobile { display: none !important; }\n";
        $css .= "  .kb-show-mobile, .kt-show-mobile { display: block !important; }\n";
        $css .= "}\n";

        echo "\n<style id=\"kbo-late-css\">\n" . $css . "</style>\n";
    }

    /**
     * Inject footer CSS (for late-loaded styles)
     */
    public function inject_footer_css() {
        $breakpoints = $this->get_breakpoints();
        $tablet = intval( $breakpoints['tablet'] );
        $mobile = intval( $breakpoints['mobile'] );

        $css = "/* Kadence Breakpoints Override - Footer CSS */\n";
        $css .= ":root {\n";
        $css .= "  --global-kb-tablet-width: {$tablet}px !important;\n";
        $css .= "  --global-kb-mobile-width: {$mobile}px !important;\n";
        $css .= "}\n";

        echo "\n<style id=\"kbo-footer-css\">\n" . $css . "</style>\n";
    }

    /**
     * Add settings link on plugins page
     */
    public function add_settings_link( $links ) {
        $settings_link = '<a href="' . admin_url( 'options-general.php?page=kadence-breakpoints' ) . '">' . __( 'Settings', 'kadence-breakpoints-override' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }
}

// Initialize plugin
function kbo_init() {
    new Kadence_Breakpoints_Override();
}
add_action( 'plugins_loaded', 'kbo_init' );

// Activation hook
register_activation_hook( __FILE__, 'kbo_activate' );
function kbo_activate() {
    if ( false === get_option( Kadence_Breakpoints_Override::OPTION_NAME ) ) {
        add_option( Kadence_Breakpoints_Override::OPTION_NAME, array(
            'tablet' => 1024,
            'mobile' => 767,
        ) );
    }
}

// Uninstall hook
register_uninstall_hook( __FILE__, 'kbo_uninstall' );
function kbo_uninstall() {
    delete_option( Kadence_Breakpoints_Override::OPTION_NAME );
}
