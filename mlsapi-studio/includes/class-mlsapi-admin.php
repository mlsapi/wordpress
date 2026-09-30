<?php
/**
 * MLS API Admin Handler
 * Enqueues assets, registers admin menus, and renders UI templates.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MLSAPI_Admin {

    /**
     * @var MLSAPI_Api_Client
     */
    private $api_client;

    /**
     * Constructor
     */
    public function __construct( MLSAPI_Api_Client $api_client ) {
        $this->api_client = $api_client;

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'admin_footer', array( $this, 'inject_modal_template' ) );
        add_filter( 'plugin_action_links_' . MLSAPI_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );
    }

    /**
     * Enqueue CSS and JS assets across relevant WP Admin screens
     */
    public function enqueue_admin_assets( $hook ) {
        // Enqueue on media pages, post editor screens, and MLS Studio settings screens
        $relevant_hooks = array(
            'upload.php',
            'post.php',
            'post-new.php',
            'toplevel_page_mlsapi-studio',
            'mls-studio_page_mlsapi-settings',
            'media-upload-popup',
        );

        // Also check if Elementor editor is active.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only action parameter check for Elementor screen.
        $is_elementor = did_action( 'elementor/loaded' ) && isset( $_GET['action'] ) && 'elementor' === sanitize_key( wp_unslash( $_GET['action'] ) );

        if ( ! in_array( $hook, $relevant_hooks, true ) && ! $is_elementor && 'post' !== get_post_type() ) {
            // Still allow on any attachment post type
            $screen = get_current_screen();
            if ( ! $screen || 'attachment' !== $screen->post_type ) {
                // If not directly on media screen, check if user can upload files
                if ( ! current_user_can( 'upload_files' ) ) {
                    return;
                }
            }
        }

        // Stylesheets
        wp_enqueue_style(
            'mlsapi-compare-slider',
            MLSAPI_PLUGIN_URL . 'assets/css/compare-slider.css',
            array(),
            MLSAPI_VERSION
        );

        wp_enqueue_style(
            'mlsapi-admin-modal',
            MLSAPI_PLUGIN_URL . 'assets/css/admin-modal.css',
            array( 'dashicons' ),
            MLSAPI_VERSION
        );

        // Scripts
        wp_enqueue_script(
            'mlsapi-compare-slider',
            MLSAPI_PLUGIN_URL . 'assets/js/compare-slider.js',
            array( 'jquery' ),
            MLSAPI_VERSION,
            true
        );

        wp_enqueue_script(
            'mlsapi-studio-client',
            MLSAPI_PLUGIN_URL . 'assets/js/studio-client.js',
            array( 'jquery' ),
            MLSAPI_VERSION,
            true
        );

        wp_enqueue_script(
            'mlsapi-modal-controller',
            MLSAPI_PLUGIN_URL . 'assets/js/mlsapi-modal.js',
            array( 'jquery', 'mlsapi-compare-slider', 'mlsapi-studio-client' ),
            MLSAPI_VERSION,
            true
        );

        // Localized Config
        wp_localize_script( 'mlsapi-modal-controller', 'mlsapi_vars', array(
            'ajax_url'       => admin_url( 'admin-ajax.php' ),
            'nonce'          => wp_create_nonce( 'mlsapi_studio_nonce' ),
            'is_configured'  => $this->api_client->is_configured(),
            'default_format' => get_option( 'mlsapi_default_format', 'webp' ),
            'settings_url'   => admin_url( 'admin.php?page=mlsapi-settings' ),
            'strings'        => array(
                'modal_title'      => __( 'MLS Studio AI Image Editor', 'mlsapi-studio' ),
                'processing'       => __( 'AI processing in progress...', 'mlsapi-studio' ),
                'saving'           => __( 'Saving image to Media Library...', 'mlsapi-studio' ),
                'replacing'        => __( 'Replacing existing image...', 'mlsapi-studio' ),
                'success_saved'    => __( 'Image saved to Media Library successfully!', 'mlsapi-studio' ),
                'success_replaced' => __( 'Existing image replaced successfully!', 'mlsapi-studio' ),
                'error_generic'    => __( 'An error occurred during AI processing.', 'mlsapi-studio' ),
                'not_configured'   => __( 'Please enter your MLS API key in Settings before generating images.', 'mlsapi-studio' ),
            ),
        ) );
    }

    /**
     * Inject the modal HTML container into the admin footer
     */
    public function inject_modal_template() {
        if ( ! current_user_can( 'upload_files' ) ) {
            return;
        }

        $template_path = MLSAPI_PLUGIN_DIR . 'includes/templates/modal-editor.php';
        if ( file_exists( $template_path ) ) {
            include $template_path;
        }
    }

    /**
     * Add settings & launch links to Plugins page
     */
    public function add_action_links( $links ) {
        $custom_links = array(
            '<a href="' . esc_url( admin_url( 'admin.php?page=mlsapi-settings' ) ) . '">' . esc_html__( 'Settings', 'mlsapi-studio' ) . '</a>',
            '<a href="#" onclick="if(window.MLSAPIModal){window.MLSAPIModal.open();};return false;" style="color:#2271b1;font-weight:600;">' . esc_html__( 'Open Studio', 'mlsapi-studio' ) . '</a>',
        );
        return array_merge( $custom_links, $links );
    }
}
