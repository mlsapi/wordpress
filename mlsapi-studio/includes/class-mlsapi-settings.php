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
            'sanitize_callback' => array( $this, 'sanitize_api_key' ),
            'default'           => '',
        ) );

        register_setting( 'mlsapi_settings_group', 'mlsapi_key_env', array(
            'type'              => 'string',
            'sanitize_callback' => array( $this, 'sanitize_env' ),
            'default'           => 'live',
        ) );

        register_setting( 'mlsapi_settings_group', 'mlsapi_api_base_url', array(
            'type'              => 'string',
            'sanitize_callback' => array( $this, 'sanitize_base_url' ),
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

    /**
     * Sanitize API key and prevent masked bullet keys from corrupting existing secrets
     *
     * @param string $val
     * @return string
     */
    public function sanitize_api_key( $val ) {
        $val      = trim( sanitize_text_field( $val ) );
        $existing = (string) get_option( 'mlsapi_api_key', '' );

        // If the submitted value contains mask characters (bullets or asterisks)
        if ( false !== strpos( $val, '•' ) || false !== strpos( $val, '***' ) ) {
            if ( ! empty( $existing ) && false === strpos( $existing, '•' ) ) {
                // Preserve the previously saved full secret key
                return $existing;
            }
            add_settings_error(
                'mlsapi_api_key',
                'mlsapi_masked_key_error',
                __( 'The API key you entered contains mask characters (••••). Please copy your full unmasked secret key from your mlsapi.dev dashboard.', 'mlsapi-studio' ),
                'error'
            );
            return '';
        }

        // When key is changed, reset connection status so it gets freshly verified
        if ( $val !== $existing ) {
            delete_option( 'mlsapi_connection_status' );
        }

        return $val;
    }

    /**
     * Sanitize and normalize API Base URL
     *
     * @param string $val
     * @return string
     */
    public function sanitize_base_url( $val ) {
        $url = esc_url_raw( trim( (string) $val ) );
        if ( empty( $url ) || 'https://api.mlsapi.dev' === untrailingslashit( $url ) || 'http://api.mlsapi.dev' === untrailingslashit( $url ) ) {
            return MLSAPI_DEFAULT_API_URL;
        }
        return untrailingslashit( $url );
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
        if ( ! current_user_can( 'upload_files' ) ) {
            return;
        }

        $api_client = $this->api_client;
        include MLSAPI_PLUGIN_DIR . 'includes/templates/workspace-launcher.php';
    }

    /**
     * Render Settings and Account/Billing dashboard page
     */
    public function render_settings_and_billing_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only UI tab navigation parameter.
        $active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'settings';
        $api_client = $this->api_client;

        include MLSAPI_PLUGIN_DIR . 'includes/templates/settings-page.php';
    }
}
