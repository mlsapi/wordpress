<?php
/**
 * Billing & Account Usage Dashboard Template
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$billing_data = $api_client->is_configured() ? $api_client->get_billing_overview( isset( $_GET['refresh'] ) ) : null;
$is_error     = is_wp_error( $billing_data );
?>
<div class="mlsapi-billing-dashboard">
    
    <?php if ( ! $api_client->is_configured() ) : ?>
        <div class="notice notice-warning inline" style="margin: 20px 0;">
            <p>
                <strong><?php esc_html_e( 'API Key Not Set:', 'mlsapi-studio' ); ?></strong>
                <?php esc_html_e( 'Please enter your mlsapi.dev API Key in the Settings tab to view live credit balance and billing statistics.', 'mlsapi-studio' ); ?>
            </p>
        </div>
    <?php elseif ( $is_error ) : ?>
        <div class="notice notice-error inline" style="margin: 20px 0;">
            <p>
                <strong><?php esc_html_e( 'Unable to load billing data:', 'mlsapi-studio' ); ?></strong>
                <?php echo esc_html( $billing_data->get_error_message() ); ?>
            </p>
            <p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=mlsapi-settings&tab=billing&refresh=1' ) ); ?>" class="button button-secondary">
                    <?php esc_html_e( 'Retry', 'mlsapi-studio' ); ?>
                </a>
            </p>
        </div>
    <?php else :
        $tier       = isset( $billing_data['plan_tier'] ) ? $billing_data['plan_tier'] : 'Starter';
        $allowance  = isset( $billing_data['monthly_credits_allowance'] ) ? (int) $billing_data['monthly_credits_allowance'] : 50;
        $remaining  = isset( $billing_data['monthly_credits_remaining'] ) ? (int) $billing_data['monthly_credits_remaining'] : 0;
        $topup      = isset( $billing_data['topup_credits_balance'] ) ? (int) $billing_data['topup_credits_balance'] : 0;
        $total_avail = $remaining + $topup;
        $percent_used = $allowance > 0 ? max( 0, min( 100, round( ( ( $allowance - $remaining ) / $allowance ) * 100 ) ) ) : 0;
    ?>
        <div class="mlsapi-dashboard-header">
            <h2><?php esc_html_e( 'Workspace Credits & Subscription', 'mlsapi-studio' ); ?></h2>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=mlsapi-settings&tab=billing&refresh=1' ) ); ?>" class="button button-secondary">
                <span class="dashicons dashicons-update" style="margin-top:4px;"></span> <?php esc_html_e( 'Refresh Stats', 'mlsapi-studio' ); ?>
            </a>
        </div>

        <div class="mlsapi-metrics-grid">
            
            <!-- Plan Tier Card -->
            <div class="mlsapi-metric-card">
                <span class="mlsapi-metric-label"><?php esc_html_e( 'Active Plan', 'mlsapi-studio' ); ?></span>
                <div class="mlsapi-metric-value"><?php echo esc_html( ucfirst( $tier ) ); ?></div>
                <div class="mlsapi-metric-sub">
                    <span class="mlsapi-status-badge active"><?php esc_html_e( 'Active', 'mlsapi-studio' ); ?></span>
                </div>
            </div>

            <!-- Total Available Credits Card -->
            <div class="mlsapi-metric-card highlight">
                <span class="mlsapi-metric-label"><?php esc_html_e( 'Total Available Credits', 'mlsapi-studio' ); ?></span>
                <div class="mlsapi-metric-value"><?php echo esc_html( number_format( $total_avail ) ); ?></div>
                <div class="mlsapi-metric-sub">
                    <?php echo esc_html( sprintf( __( '%d monthly + %d top-up credits', 'mlsapi-studio' ), $remaining, $topup ) ); ?>
                </div>
            </div>

            <!-- Allowance Usage Card -->
            <div class="mlsapi-metric-card">
                <span class="mlsapi-metric-label"><?php esc_html_e( 'Monthly Allowance Used', 'mlsapi-studio' ); ?></span>
                <div class="mlsapi-metric-value"><?php echo esc_html( $percent_used ); ?>%</div>
                <div class="mlsapi-progress-bar-container" style="margin-top: 10px;">
                    <div class="mlsapi-progress-bar" style="width: <?php echo esc_attr( $percent_used ); ?>%;"></div>
                </div>
            </div>
        </div>

        <!-- Upgrade & Management Actions -->
        <div class="mlsapi-card" style="margin-top: 25px; padding: 20px; background: #fff; border: 1px solid #c3c4c7; border-radius: 6px;">
            <h3><?php esc_html_e( 'Need More Studio Credits?', 'mlsapi-studio' ); ?></h3>
            <p><?php esc_html_e( 'Each virtual staging, 3D floor plan render, or dusk twilight conversion consumes 1 Studio Credit. Top-up anytime or upgrade your tier directly from your mlsapi.dev portal.', 'mlsapi-studio' ); ?></p>
            <a href="https://mlsapi.dev/dashboard/billing" target="_blank" rel="noopener noreferrer" class="button button-primary">
                <?php esc_html_e( 'Manage Plan & Top Up Credits →', 'mlsapi-studio' ); ?>
            </a>
        </div>
    <?php endif; ?>
</div>
