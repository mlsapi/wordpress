<?php
/**
 * MLS API Settings & Billing Management
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MLSAPI_Settings {

    /**
     * @var MLSAPI_Api_Client
     */
    private $api_client;

    /**
     * Constructor
     */
    public function __construct( MLSAPI_Api_Client $api_client ) {
        $this->api_client = $api_client;

        add_action( 'admin_menu', array( $this, 'register_admin_menus' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_settings_assets' ) );
    }

    /**
     * Register admin menus and submenu pages
     */
    public function register_admin_menus() {
        // Top-level menu
        add_menu_page(
            __( 'MLS API Studio', 'mlsapi-studio' ),
            __( 'MLS Studio', 'mlsapi-studio' ),
            'upload_files',
            'mlsapi-studio',
            array( $this, 'render_studio_launcher_page' ),
            'dashicons-camera',
            11
        );

        // Submenu: Launch Studio
        add_submenu_page(
            'mlsapi-studio',
            __( 'Studio Editor', 'mlsapi-studio' ),
            __( 'Studio Editor', 'mlsapi-studio' ),
            'upload_files',
            'mlsapi-studio',
            array( $this, 'render_studio_launcher_page' )
        );

        // Submenu: Settings & Billing
        add_submenu_page(
            'mlsapi-studio',
            __( 'Settings & Billing', 'mlsapi-studio' ),
            __( 'Settings & Billing', 'mlsapi-studio' ),
            'manage_options',
            'mlsapi-settings',
            array( $this, 'render_settings_and_billing_page' )
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting( 'mlsapi_settings_group', 'mlsapi_api_key', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ) );

        register_setting( 'mlsapi_settings_group', 'mlsapi_key_env', array(
            'type'              => 'string',
            'sanitize_callback' => array( $this, 'sanitize_env' ),
            'default'           => 'live',
        ) );

        register_setting( 'mlsapi_settings_group', 'mlsapi_api_base_url', array(
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default'           => MLSAPI_DEFAULT_API_URL,
        ) );

        register_setting( 'mlsapi_settings_group', 'mlsapi_default_format', array(
            'type'              => 'string',
            'sanitize_callback' => array( $this, 'sanitize_format' ),
            'default'           => 'webp',
        ) );

        register_setting( 'mlsapi_settings_group', 'mlsapi_enable_admin_bar', array(
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => true,
        ) );

        register_setting( 'mlsapi_settings_group', 'mlsapi_enable_media_button', array(
            'type'              => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default'           => true,
        ) );
    }

    public function sanitize_env( $val ) {
        return in_array( $val, array( 'live', 'test' ), true ) ? $val : 'live';
    }

    public function sanitize_format( $val ) {
        return in_array( $val, array( 'webp', 'png', 'jpg' ), true ) ? $val : 'webp';
    }

    /**
     * Enqueue settings page styles and scripts
     */
    public function enqueue_settings_assets( $hook ) {
        if ( 'mls-studio_page_mlsapi-settings' !== $hook && 'toplevel_page_mlsapi-studio' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'mlsapi-settings-css',
            MLSAPI_PLUGIN_URL . 'assets/css/settings.css',
            array(),
            MLSAPI_VERSION
        );

        wp_enqueue_script(
            'mlsapi-settings-js',
            MLSAPI_PLUGIN_URL . 'assets/js/settings-page.js',
            array( 'jquery' ),
            MLSAPI_VERSION,
            true
        );

        wp_localize_script( 'mlsapi-settings-js', 'mlsapi_settings_vars', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'mlsapi_studio_nonce' ),
        ) );
    }

    /**
     * Render Studio Launcher landing page
     */
    public function render_studio_launcher_page() {
        ?>
        <div class="wrap mlsapi-launcher-wrap">
            <h1><?php esc_html_e( 'MLS API Studio AI Workspace', 'mlsapi-studio' ); ?></h1>
            <p class="description">
                <?php esc_html_e( 'Generate AI virtual staging, dusk twilight conversions, decluttering, 3D floor plans, and photo enhancements directly in WordPress.', 'mlsapi-studio' ); ?>
            </p>

            <div class="mlsapi-card mlsapi-hero-card">
                <h2><?php esc_html_e( 'Launch Studio Editor', 'mlsapi-studio' ); ?></h2>
                <p><?php esc_html_e( 'Open the full-screen visual editor to select any media library asset, paste from clipboard, or drag and drop a real estate photo.', 'mlsapi-studio' ); ?></p>
                <button type="button" class="button button-primary button-hero" onclick="if(window.MLSAPIModal){window.MLSAPIModal.open();}">
                    <span class="dashicons dashicons-art" style="margin-top:4px;"></span> <?php esc_html_e( 'Open Studio Editor', 'mlsapi-studio' ); ?>
                </button>
            </div>

            <?php if ( ! $this->api_client->is_configured() ) : ?>
                <div class="notice notice-warning inline" style="margin-top: 20px;">
                    <p>
                        <strong><?php esc_html_e( 'API Key Required:', 'mlsapi-studio' ); ?></strong>
                        <?php esc_html_e( 'Please add your mlsapi.dev API key to enable generative AI features.', 'mlsapi-studio' ); ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=mlsapi-settings' ) ); ?>" class="button button-secondary button-small" style="margin-left: 10px;">
                            <?php esc_html_e( 'Configure API Key', 'mlsapi-studio' ); ?>
                        </a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render Settings and Account/Billing dashboard page
     */
    public function render_settings_and_billing_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'settings';
        $api_client = $this->api_client;

        include MLSAPI_PLUGIN_DIR . 'includes/templates/settings-page.php';
    }
}
