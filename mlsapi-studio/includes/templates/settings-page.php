<?php
/**
 * Settings & Billing Page Template
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap mlsapi-settings-wrap">
    <h1>
        <span class="dashicons dashicons-camera" style="font-size:32px; width:32px; height:32px; vertical-align:middle; color:#2271b1;"></span>
        <?php esc_html_e( 'MLS API Studio – Configuration & Usage', 'mlsapi-studio' ); ?>
    </h1>

    <nav class="nav-tab-wrapper wp-clearfix" aria-label="<?php esc_attr_e( 'Settings Navigation', 'mlsapi-studio' ); ?>">
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=mlsapi-settings&tab=settings' ) ); ?>" class="nav-tab <?php echo 'settings' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-admin-generic" style="margin-top:2px;"></span> <?php esc_html_e( 'API & General Settings', 'mlsapi-studio' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=mlsapi-settings&tab=billing' ) ); ?>" class="nav-tab <?php echo 'billing' === $active_tab ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-chart-area" style="margin-top:2px;"></span> <?php esc_html_e( 'Account & Credit Usage', 'mlsapi-studio' ); ?>
        </a>
    </nav>

    <?php if ( 'settings' === $active_tab ) : ?>
        <!-- Tab 1: Settings Form -->
        <div class="mlsapi-settings-content">
            <form method="post" action="options.php" class="mlsapi-settings-form">
                <?php
                settings_fields( 'mlsapi_settings_group' );
                do_settings_sections( 'mlsapi_settings_group' );
                $current_key = get_option( 'mlsapi_api_key', '' );
                $masked_key  = ! empty( $current_key ) ? substr( $current_key, 0, 8 ) . '••••••••' . substr( $current_key, -4 ) : '';
                ?>

                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="mlsapi_api_key"><?php esc_html_e( 'API Key', 'mlsapi-studio' ); ?></label>
                            </th>
                            <td>
                                <input name="mlsapi_api_key" type="password" id="mlsapi_api_key" value="<?php echo esc_attr( $current_key ); ?>" class="regular-text" placeholder="sk_live_..." autocomplete="off" />
                                <button type="button" class="button button-secondary" id="mlsapi-toggle-key-visibility">
                                    <span class="dashicons dashicons-visibility" style="margin-top:4px;"></span>
                                </button>
                                <button type="button" class="button button-secondary" id="mlsapi-test-conn-btn" style="margin-left: 8px;">
                                    <?php esc_html_e( 'Test Connection', 'mlsapi-studio' ); ?>
                                </button>
                                <span id="mlsapi-conn-status" class="mlsapi-conn-status" style="margin-left:10px;"></span>
                                <p class="description">
                                    <?php
                                    printf(
                                        /* translators: %s: URL to mlsapi.dev keys */
                                        __( 'Obtain your API Key from your <a href="%s" target="_blank" rel="noopener noreferrer">mlsapi.dev Dashboard</a>.', 'mlsapi-studio' ),
                                        'https://mlsapi.dev'
                                    );
                                    ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="mlsapi_key_env"><?php esc_html_e( 'Environment', 'mlsapi-studio' ); ?></label>
                            </th>
                            <td>
                                <select name="mlsapi_key_env" id="mlsapi_key_env">
                                    <option value="live" <?php selected( get_option( 'mlsapi_key_env', 'live' ), 'live' ); ?>><?php esc_html_e( 'Production (Live)', 'mlsapi-studio' ); ?></option>
                                    <option value="test" <?php selected( get_option( 'mlsapi_key_env', 'live' ), 'test' ); ?>><?php esc_html_e( 'Test / Playground Sandbox', 'mlsapi-studio' ); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e( 'Switch between live production billing or test sandbox environments.', 'mlsapi-studio' ); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="mlsapi_default_format"><?php esc_html_e( 'Default Export Format', 'mlsapi-studio' ); ?></label>
                            </th>
                            <td>
                                <select name="mlsapi_default_format" id="mlsapi_default_format">
                                    <option value="webp" <?php selected( get_option( 'mlsapi_default_format', 'webp' ), 'webp' ); ?>><?php esc_html_e( 'WebP (Recommended: high compression, fast load)', 'mlsapi-studio' ); ?></option>
                                    <option value="png" <?php selected( get_option( 'mlsapi_default_format', 'webp' ), 'png' ); ?>><?php esc_html_e( 'PNG (Lossless quality)', 'mlsapi-studio' ); ?></option>
                                    <option value="jpg" <?php selected( get_option( 'mlsapi_default_format', 'webp' ), 'jpg' ); ?>><?php esc_html_e( 'JPEG (Universal compatibility)', 'mlsapi-studio' ); ?></option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="mlsapi_api_base_url"><?php esc_html_e( 'API Base URL', 'mlsapi-studio' ); ?></label>
                            </th>
                            <td>
                                <input name="mlsapi_api_base_url" type="url" id="mlsapi_api_base_url" value="<?php echo esc_attr( get_option( 'mlsapi_api_base_url', MLSAPI_DEFAULT_API_URL ) ); ?>" class="regular-text" />
                                <p class="description"><?php esc_html_e( 'Default: https://api.mlsapi.dev. Modify only for custom enterprise proxies or local development.', 'mlsapi-studio' ); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><?php esc_html_e( 'Integrations & Buttons', 'mlsapi-studio' ); ?></th>
                            <td>
                                <fieldset>
                                    <label for="mlsapi_enable_media_button">
                                        <input name="mlsapi_enable_media_button" type="checkbox" id="mlsapi_enable_media_button" value="1" <?php checked( get_option( 'mlsapi_enable_media_button', true ) ); ?> />
                                        <?php esc_html_e( 'Display "MLS Studio AI" button on Media Library screens', 'mlsapi-studio' ); ?>
                                    </label>
                                    <br>
                                    <label for="mlsapi_enable_admin_bar">
                                        <input name="mlsapi_enable_admin_bar" type="checkbox" id="mlsapi_enable_admin_bar" value="1" <?php checked( get_option( 'mlsapi_enable_admin_bar', true ) ); ?> />
                                        <?php esc_html_e( 'Display "Edit with MLS Studio" button on Admin Bar toolbar', 'mlsapi-studio' ); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button( __( 'Save Settings', 'mlsapi-studio' ) ); ?>
            </form>
        </div>

    <?php else : ?>
        <!-- Tab 2: Billing & Usage Dashboard -->
        <div class="mlsapi-settings-content">
            <?php include MLSAPI_PLUGIN_DIR . 'includes/templates/billing-dashboard.php'; ?>
        </div>
    <?php endif; ?>
</div>
