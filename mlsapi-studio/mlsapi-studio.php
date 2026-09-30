<?php
/**
 * Plugin Name: MLS API Studio – AI Real Estate Image Staging & Enhancement
 * Plugin URI:  https://mlsapi.dev
 * Description: AI-powered virtual staging, dusk/twilight conversion, decluttering, restyling, floor plan 3D renders, and photo enhancement for real estate listings.
 * Version:     1.0.0
 * Author:      mlsapi.dev
 * Author URI:  https://mlsapi.dev
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mlsapi-studio
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define Plugin Constants
define( 'MLSAPI_VERSION', '1.0.0' );
define( 'MLSAPI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MLSAPI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MLSAPI_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'MLSAPI_DEFAULT_API_URL', 'https://api.mlsapi.dev' );

// Load Core Includes
require_once MLSAPI_PLUGIN_DIR . 'includes/class-mlsapi-api-client.php';
require_once MLSAPI_PLUGIN_DIR . 'includes/class-mlsapi-admin.php';
require_once MLSAPI_PLUGIN_DIR . 'includes/class-mlsapi-ajax.php';
require_once MLSAPI_PLUGIN_DIR . 'includes/class-mlsapi-settings.php';
require_once MLSAPI_PLUGIN_DIR . 'includes/class-mlsapi-media.php';

/**
 * Main Plugin Bootstrap Class
 */
class MLSAPI_Studio_Plugin {

    /**
     * Singleton instance
     *
     * @var MLSAPI_Studio_Plugin|null
     */
    private static $instance = null;

    /**
     * API Client instance
     *
     * @var MLSAPI_Api_Client
     */
    public $api_client;

    /**
     * Admin Handler instance
     *
     * @var MLSAPI_Admin
     */
    public $admin;

    /**
     * AJAX Handler instance
     *
     * @var MLSAPI_Ajax
     */
    public $ajax;

    /**
     * Settings Handler instance
     *
     * @var MLSAPI_Settings
     */
    public $settings;

    /**
     * Media Library Handler instance
     *
     * @var MLSAPI_Media
     */
    public $media;

    /**
     * Get singleton instance
     *
     * @return MLSAPI_Studio_Plugin
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_components();
        $this->init_hooks();
    }

    /**
     * Instantiate modular components
     */
    private function init_components() {
        $this->api_client = new MLSAPI_Api_Client();
        $this->settings   = new MLSAPI_Settings( $this->api_client );
        $this->ajax       = new MLSAPI_Ajax( $this->api_client );
        $this->admin      = new MLSAPI_Admin( $this->api_client );
        $this->media      = new MLSAPI_Media();
    }

    /**
     * Setup lifecycle hooks
     */
    private function init_hooks() {
        register_activation_hook( __FILE__, array( $this, 'on_activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'on_deactivate' ) );
        add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
    }

    /**
     * Runs on plugin activation
     */
    public function on_activate() {
        if ( ! get_option( 'mlsapi_default_format' ) ) {
            add_option( 'mlsapi_default_format', 'webp' );
        }
        if ( ! get_option( 'mlsapi_api_base_url' ) ) {
            add_option( 'mlsapi_api_base_url', MLSAPI_DEFAULT_API_URL );
        }
        if ( ! get_option( 'mlsapi_key_env' ) ) {
            add_option( 'mlsapi_key_env', 'live' );
        }
    }

    /**
     * Runs on plugin deactivation
     */
    public function on_deactivate() {
        delete_transient( 'mlsapi_billing_overview' );
    }

    /**
     * Runs on plugins_loaded
     */
    public function on_plugins_loaded() {
        load_plugin_textdomain( 'mlsapi-studio', false, dirname( MLSAPI_PLUGIN_BASENAME ) . '/languages' );
    }
}

// Initialize the plugin
function mlsapi_studio() {
    return MLSAPI_Studio_Plugin::get_instance();
}

mlsapi_studio();
