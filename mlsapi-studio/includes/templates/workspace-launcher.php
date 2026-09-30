<?php
/**
 * Studio Workspace Launcher Template
 * Matches the dark studio design system with unified top navigation tabs.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$mlsapi_is_configured = $api_client->is_configured();
$mlsapi_billing_data  = $mlsapi_is_configured ? $api_client->get_billing_overview() : null;

$mlsapi_plan_tier = ! empty( $mlsapi_billing_data['plan']['name'] ) 
    ? ucfirst( $mlsapi_billing_data['plan']['name'] ) 
    : ( ! empty( $mlsapi_billing_data['plan_tier'] ) ? ucfirst( $mlsapi_billing_data['plan_tier'] ) : 'Pro' );

$mlsapi_credits_total = isset( $mlsapi_billing_data['plan']['totalCreditsAvailable'] ) 
    ? (int) $mlsapi_billing_data['plan']['totalCreditsAvailable'] 
    : ( isset( $mlsapi_billing_data['total_credits'] ) ? (int) $mlsapi_billing_data['total_credits'] : 15460 );
?>
<div class="wrap mlsapi-dark-wrap">

    <!-- Top Tabs / Navigation -->
    <nav class="mlsapi-top-nav">
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=mlsapi-studio' ) ); ?>" class="mlsapi-nav-link active">
            <span class="dashicons dashicons-art"></span> <?php esc_html_e( 'Studio Workspace', 'mlsapi-studio' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=mlsapi-settings&tab=settings' ) ); ?>" class="mlsapi-nav-link">
            <span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'Settings', 'mlsapi-studio' ); ?>
        </a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=mlsapi-settings&tab=billing' ) ); ?>" class="mlsapi-nav-link">
            <span class="dashicons dashicons-chart-bar"></span> <?php esc_html_e( 'Dashboard & Billing', 'mlsapi-studio' ); ?>
        </a>
    </nav>

    <!-- Header Card -->
    <div class="mlsapi-card-container">
        <header class="mlsapi-card-header">
            <div class="mlsapi-header-left">
                <h1 class="mlsapi-card-title"><?php esc_html_e( 'Studio Workspace', 'mlsapi-studio' ); ?></h1>
                <div class="mlsapi-breadcrumb">
                    <span>MLS API</span>
                    <span class="mlsapi-bc-sep">›</span>
                    <span>Studio Workspace</span>
                </div>
            </div>
            <div class="mlsapi-header-badges">
                <?php if ( $mlsapi_is_configured ) : ?>
                    <span class="mlsapi-badge-dark mlsapi-badge-active">
                        <span class="mlsapi-live-dot"></span>
                        <?php
                        /* translators: %s: Subscription plan tier (e.g. Free, Starter, Pro). */
                        echo esc_html( sprintf( __( 'Connected · %s Plan', 'mlsapi-studio' ), $mlsapi_plan_tier ) );
                        ?>
                    </span>
                <?php else : ?>
                    <span class="mlsapi-badge-dark" style="color: #ff9800; border-color: rgba(255, 152, 0, 0.4);">
                        <?php esc_html_e( 'API Key Required', 'mlsapi-studio' ); ?>
                    </span>
                <?php endif; ?>
            </div>
        </header>

        <div class="mlsapi-workspace-body">
            
            <!-- Hero Launcher Section -->
            <div class="mlsapi-hero-banner">
                <div class="mlsapi-hero-content">
                    <span class="mlsapi-pill-label"><?php esc_html_e( 'AI Real Estate Media Studio', 'mlsapi-studio' ); ?></span>
                    <h2 class="mlsapi-hero-title"><?php esc_html_e( 'MLS API Studio AI Workspace', 'mlsapi-studio' ); ?></h2>
                    <p class="mlsapi-hero-desc">
                        <?php esc_html_e( 'Generate AI virtual staging, dusk twilight conversions, decluttering, 3D floor plans, and photo enhancements directly in WordPress.', 'mlsapi-studio' ); ?>
                    </p>
                    <div class="mlsapi-hero-actions">
                        <button type="button" class="mlsapi-studio-launch-btn" onclick="if(window.MLSAPIModal){window.MLSAPIModal.open();}">
                            <span class="mlsapi-sparkle">✦</span>
                            <span><?php esc_html_e( 'Open Studio Editor', 'mlsapi-studio' ); ?></span>
                        </button>
                        <a href="https://studio.mlsapi.dev" target="_blank" rel="noopener noreferrer" class="mlsapi-studio-secondary-btn">
                            <span class="dashicons dashicons-external"></span> <?php esc_html_e( 'Launch Web Studio', 'mlsapi-studio' ); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Warning Notice if not configured -->
            <?php if ( ! $mlsapi_is_configured ) : ?>
                <div class="mlsapi-notice-box">
                    <div class="mlsapi-notice-icon">⚠️</div>
                    <div class="mlsapi-notice-content">
                        <strong><?php esc_html_e( 'API Key Required:', 'mlsapi-studio' ); ?></strong>
                        <?php esc_html_e( 'Please add your mlsapi.dev secret API key to enable generative AI features.', 'mlsapi-studio' ); ?>
                    </div>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=mlsapi-settings&tab=settings' ) ); ?>" class="mlsapi-notice-btn">
                        <?php esc_html_e( 'Configure Key', 'mlsapi-studio' ); ?>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Tool Quick Launch Cards -->
            <div class="mlsapi-tools-grid-section">
                <h3 class="mlsapi-grid-heading"><?php esc_html_e( 'Available AI Operations', 'mlsapi-studio' ); ?></h3>
                <div class="mlsapi-tools-grid">
                    
                    <div class="mlsapi-tool-card" onclick="if(window.MLSAPIModal){window.MLSAPIModal.open();window.MLSAPIModal.switchTool('stage');}">
                        <div class="mlsapi-tool-card-icon">🛋️</div>
                        <h4><?php esc_html_e( 'Virtual Staging', 'mlsapi-studio' ); ?></h4>
                        <p><?php esc_html_e( 'Furnish vacant rooms with modern, luxury, or scandinavian designer furniture.', 'mlsapi-studio' ); ?></p>
                        <span class="mlsapi-tool-card-cta"><?php esc_html_e( 'Launch Staging →', 'mlsapi-studio' ); ?></span>
                    </div>

                    <div class="mlsapi-tool-card" onclick="if(window.MLSAPIModal){window.MLSAPIModal.open();window.MLSAPIModal.switchTool('twilight');}">
                        <div class="mlsapi-tool-card-icon">🌆</div>
                        <h4><?php esc_html_e( 'Dusk & Sky', 'mlsapi-studio' ); ?></h4>
                        <p><?php esc_html_e( 'Transform daytime exterior photos into rich sunset or dusk twilight photography.', 'mlsapi-studio' ); ?></p>
                        <span class="mlsapi-tool-card-cta"><?php esc_html_e( 'Launch Twilight →', 'mlsapi-studio' ); ?></span>
                    </div>

                    <div class="mlsapi-tool-card" onclick="if(window.MLSAPIModal){window.MLSAPIModal.open();window.MLSAPIModal.switchTool('declutter');}">
                        <div class="mlsapi-tool-card-icon">🧹</div>
                        <h4><?php esc_html_e( 'Item Declutter', 'mlsapi-studio' ); ?></h4>
                        <p><?php esc_html_e( 'Erase messy items, tenant personal belongings, cords, and boxes seamlessly.', 'mlsapi-studio' ); ?></p>
                        <span class="mlsapi-tool-card-cta"><?php esc_html_e( 'Launch Declutter →', 'mlsapi-studio' ); ?></span>
                    </div>

                    <div class="mlsapi-tool-card" onclick="if(window.MLSAPIModal){window.MLSAPIModal.open();window.MLSAPIModal.switchTool('floorplan-3d');}">
                        <div class="mlsapi-tool-card-icon">📐</div>
                        <h4><?php esc_html_e( '3D Floorplan', 'mlsapi-studio' ); ?></h4>
                        <p><?php esc_html_e( 'Convert black & white 2D architectural blueprints into furnished 3D models.', 'mlsapi-studio' ); ?></p>
                        <span class="mlsapi-tool-card-cta"><?php esc_html_e( 'Launch 3D Render →', 'mlsapi-studio' ); ?></span>
                    </div>

                    <div class="mlsapi-tool-card" onclick="if(window.MLSAPIModal){window.MLSAPIModal.open();window.MLSAPIModal.switchTool('wall-colors');}">
                        <div class="mlsapi-tool-card-icon">🎨</div>
                        <h4><?php esc_html_e( 'Wall Colors', 'mlsapi-studio' ); ?></h4>
                        <p><?php esc_html_e( 'Test designer paint palettes including crisp white, warm neutral, and greige.', 'mlsapi-studio' ); ?></p>
                        <span class="mlsapi-tool-card-cta"><?php esc_html_e( 'Launch Colors →', 'mlsapi-studio' ); ?></span>
                    </div>

                    <div class="mlsapi-tool-card" onclick="if(window.MLSAPIModal){window.MLSAPIModal.open();window.MLSAPIModal.switchTool('enhance-exterior');}">
                        <div class="mlsapi-tool-card-icon">✨</div>
                        <h4><?php esc_html_e( 'Enhance & 4K', 'mlsapi-studio' ); ?></h4>
                        <p><?php esc_html_e( 'Upscale resolution, polish curb appeal, green grass, and clarify pools.', 'mlsapi-studio' ); ?></p>
                        <span class="mlsapi-tool-card-cta"><?php esc_html_e( 'Launch Enhance →', 'mlsapi-studio' ); ?></span>
                    </div>

                </div>
            </div>

            <!-- Quick Account & Billing Overview Row -->
            <div class="mlsapi-cloud-banner">
                <div class="mlsapi-cloud-left">
                    <span class="dashicons dashicons-cloud"></span>
                    <div class="mlsapi-cloud-text">
                        <strong><?php esc_html_e( 'Web Studio App & Billing:', 'mlsapi-studio' ); ?></strong>
                        <span>
                            <?php 
                            if ( $mlsapi_is_configured ) {
                                /* translators: 1: Subscription plan tier (e.g. Free, Starter, Pro), 2: Number of available credits. */
                                echo esc_html( sprintf( __( '%1$s Tier · %2$s total credits available', 'mlsapi-studio' ), $mlsapi_plan_tier, number_format_i18n( $mlsapi_credits_total ) ) );
                            } else {
                                esc_html_e( 'Connect your API key to view live subscription and credit allowance.', 'mlsapi-studio' );
                            }
                            ?>
                        </span>
                    </div>
                </div>
                <div class="mlsapi-cloud-actions">
                    <a href="https://studio.mlsapi.dev/billing" target="_blank" rel="noopener noreferrer" class="mlsapi-cloud-link">
                        <?php esc_html_e( 'Manage Subscription', 'mlsapi-studio' ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </a>
                </div>
            </div>

        </div>
    </div>

</div>
