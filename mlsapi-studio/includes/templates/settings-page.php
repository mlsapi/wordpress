<?php
/**
 * Settings & Billing Page Template
 * Matches the exact MLS API dark branding from screenshot
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only UI tab navigation parameter.
$mlsapi_active_tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'settings';
$mlsapi_current_key = get_option( 'mlsapi_api_key', '' );
$mlsapi_masked_key  = ! empty( $mlsapi_current_key ) ? substr( $mlsapi_current_key, 0, 8 ) . '••••••••' . substr( $mlsapi_current_key, -4 ) : '';
$mlsapi_current_env = get_option( 'mlsapi_key_env', 'live' );
?>
<div class="wrap mlsapi-dark-wrap">

    <!-- Top Tabs / Navigation -->
    <nav class="mlsapi-top-nav">
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=mlsapi-studio' ) ); ?>" class="mlsapi-nav-link">
            <span class="dashicons dashicons-art"></span> <?php esc_html_e( 'Studio Workspace', 'mlsapi-studio' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=mlsapi-settings&tab=settings' ) ); ?>" class="mlsapi-nav-link <?php echo 'settings' === $mlsapi_active_tab ? 'active' : ''; ?>">
            <span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'Settings', 'mlsapi-studio' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=mlsapi-settings&tab=billing' ) ); ?>" class="mlsapi-nav-link <?php echo 'billing' === $mlsapi_active_tab ? 'active' : ''; ?>">
            <span class="dashicons dashicons-chart-bar"></span> <?php esc_html_e( 'Dashboard & Billing', 'mlsapi-studio' ); ?>
        </a>
    </nav>

    <?php if ( 'settings' === $mlsapi_active_tab ) : ?>
        <!-- Card 1: Settings Form -->
        <div class="mlsapi-card-container">
            <header class="mlsapi-card-header">
                <h1 class="mlsapi-card-title"><?php esc_html_e( 'Settings', 'mlsapi-studio' ); ?></h1>
                <div class="mlsapi-breadcrumb">
                    <span>MLS API</span>
                    <span class="mlsapi-bc-sep">›</span>
                    <span>Settings</span>
                </div>
            </header>

            <form method="post" action="options.php" class="mlsapi-form-body">
                <?php
                settings_fields( 'mlsapi_settings_group' );
                do_settings_sections( 'mlsapi_settings_group' );
                ?>

                <!-- Field 1: API Key -->
                <div class="mlsapi-form-row">
                    <label class="mlsapi-label" for="mlsapi_api_key">
                        <?php esc_html_e( 'API key', 'mlsapi-studio' ); ?>
                    </label>
                    <div class="mlsapi-input-wrap">
                        <input name="mlsapi_api_key" type="password" id="mlsapi_api_key" value="<?php echo esc_attr( $mlsapi_current_key ); ?>" class="mlsapi-dark-input" placeholder="sk_live_51H..." autocomplete="off" />
                        <button type="button" class="mlsapi-input-toggle" id="mlsapi-toggle-key-visibility" title="<?php esc_attr_e( 'Toggle visibility', 'mlsapi-studio' ); ?>">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                    </div>
                    <p class="mlsapi-field-hint" style="color: #8a99ad; font-size: 12px; margin-top: 6px;">
                        <?php esc_html_e( 'Enter your full secret key from mlsapi.dev. Ensure you click Reveal before copying so that no mask bullets (••••) are included.', 'mlsapi-studio' ); ?>
                    </p>
                </div>

                <!-- Field 2: Environment -->
                <div class="mlsapi-form-row">
                    <label class="mlsapi-label">
                        <?php esc_html_e( 'Environment', 'mlsapi-studio' ); ?>
                    </label>
                    <input type="hidden" name="mlsapi_key_env" id="mlsapi_key_env" value="<?php echo esc_attr( $mlsapi_current_env ); ?>" />
                    <div class="mlsapi-env-segmented">
                        <button type="button" class="mlsapi-env-btn <?php echo 'live' === $mlsapi_current_env ? 'active' : ''; ?>" data-env="live">
                            <?php esc_html_e( 'Live · production', 'mlsapi-studio' ); ?>
                        </button>
                        <button type="button" class="mlsapi-env-btn <?php echo 'test' === $mlsapi_current_env ? 'active' : ''; ?>" data-env="test">
                            <?php esc_html_e( 'Test · sandbox', 'mlsapi-studio' ); ?>
                        </button>
                    </div>
                </div>

                <!-- Field 3: API Base URL -->
                <div class="mlsapi-form-row">
                    <label class="mlsapi-label" for="mlsapi_api_base_url">
                        <?php esc_html_e( 'API base URL', 'mlsapi-studio' ); ?>
                        <span class="mlsapi-label-sub">· optional</span>
                    </label>
                    <input name="mlsapi_api_base_url" type="url" id="mlsapi_api_base_url" value="<?php echo esc_attr( get_option( 'mlsapi_api_base_url', MLSAPI_DEFAULT_API_URL ) ); ?>" class="mlsapi-dark-input" placeholder="https://mlsapi.dev" />
                    <p class="mlsapi-field-hint" style="color: #8a99ad; font-size: 12px; margin-top: 6px;">
                        <?php esc_html_e( 'Default: https://mlsapi.dev. If developing locally with your Node server, set to http://127.0.0.1:3000 (or http://host.docker.internal:3000 if in Docker).', 'mlsapi-studio' ); ?>
                    </p>
                </div>

                <!-- Hidden Defaults to maintain settings structure -->
                <input type="hidden" name="mlsapi_default_format" value="<?php echo esc_attr( get_option( 'mlsapi_default_format', 'webp' ) ); ?>" />
                <input type="hidden" name="mlsapi_enable_admin_bar" value="1" />
                <input type="hidden" name="mlsapi_enable_media_button" value="1" />

                <!-- Action Button Row -->
                <div class="mlsapi-form-actions">
                    <button type="button" class="mlsapi-btn-dark" id="mlsapi-test-conn-btn">
                        <?php esc_html_e( 'Verify & test connection', 'mlsapi-studio' ); ?>
                    </button>
                    <?php
                    $mlsapi_conn_status = get_option( 'mlsapi_connection_status', '' );
                    if ( 'connected' === $mlsapi_conn_status && ! empty( $mlsapi_current_key ) ) {
                        echo '<span id="mlsapi-conn-status" class="mlsapi-status-text success">✓ ' . esc_html__( 'Connected', 'mlsapi-studio' ) . '</span>';
                    } elseif ( 'failed' === $mlsapi_conn_status ) {
                        echo '<span id="mlsapi-conn-status" class="mlsapi-status-text error">✕ ' . esc_html__( 'Connection failed', 'mlsapi-studio' ) . '</span>';
                    } else {
                        echo '<span id="mlsapi-conn-status" class="mlsapi-status-text">' . ( ! empty( $mlsapi_current_key ) ? esc_html__( 'Not verified yet', 'mlsapi-studio' ) : esc_html__( 'Not configured', 'mlsapi-studio' ) ) . '</span>';
                    }
                    ?>
                    <button type="submit" class="mlsapi-btn-save-settings">
                        <?php esc_html_e( 'Save Changes', 'mlsapi-studio' ); ?>
                    </button>
                </div>
            </form>
        </div>

    <?php else : ?>
        <!-- Card 2: Billing & Usage Dashboard -->
        <div class="mlsapi-card-container">
            <?php include MLSAPI_PLUGIN_DIR . 'includes/templates/billing-dashboard.php'; ?>
        </div>
    <?php endif; ?>

</div>
