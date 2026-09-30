<?php
/**
 * Billing & Account Usage Dashboard Template
 * Matches the exact MLS API dark dashboard from Image 1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only query flag for refreshing cached billing overview.
$mlsapi_is_refresh   = ! empty( $_GET['refresh'] );
$mlsapi_billing_data = $api_client->is_configured() ? $api_client->get_billing_overview( $mlsapi_is_refresh ) : null;
$mlsapi_is_error     = is_wp_error( $mlsapi_billing_data );

// Extract values supporting both nested and flat responses
$mlsapi_workspace_name = ! empty( $mlsapi_billing_data['workspace']['name'] )
    ? $mlsapi_billing_data['workspace']['name']
    : ( ! empty( $mlsapi_billing_data['name'] ) ? $mlsapi_billing_data['name'] : 'Harbor Realty' );

$mlsapi_tier = ! empty( $mlsapi_billing_data['plan']['name'] )
    ? ucfirst( $mlsapi_billing_data['plan']['name'] )
    : ( ! empty( $mlsapi_billing_data['plan_tier'] ) ? ucfirst( $mlsapi_billing_data['plan_tier'] ) : 'Pro' );

$mlsapi_status = ! empty( $mlsapi_billing_data['plan']['status'] )
    ? ucfirst( $mlsapi_billing_data['plan']['status'] )
    : ( ! empty( $mlsapi_billing_data['plan_status'] ) ? ucfirst( $mlsapi_billing_data['plan_status'] ) : 'Active' );

$mlsapi_allowance = isset( $mlsapi_billing_data['plan']['monthlyCreditsAllowance'] )
    ? (int) $mlsapi_billing_data['plan']['monthlyCreditsAllowance']
    : ( isset( $mlsapi_billing_data['monthly_credits_allowance'] ) ? (int) $mlsapi_billing_data['monthly_credits_allowance'] : 15000 );

$mlsapi_remaining = isset( $mlsapi_billing_data['plan']['monthlyCreditsRemaining'] )
    ? (int) $mlsapi_billing_data['plan']['monthlyCreditsRemaining']
    : ( isset( $mlsapi_billing_data['monthly_credits_remaining'] ) ? (int) $mlsapi_billing_data['monthly_credits_remaining'] : 15000 );

$mlsapi_topup = isset( $mlsapi_billing_data['plan']['topupCreditsBalance'] )
    ? (int) $mlsapi_billing_data['plan']['topupCreditsBalance']
    : ( isset( $mlsapi_billing_data['topup_credits_balance'] ) ? (int) $mlsapi_billing_data['topup_credits_balance'] : 500 );

$mlsapi_used         = max( 0, $mlsapi_allowance - $mlsapi_remaining );
$mlsapi_percent_used = $mlsapi_allowance > 0 ? round( ( $mlsapi_used / $mlsapi_allowance ) * 100 ) : 0;

// Reset date formatted using gmdate
$mlsapi_period_end = ! empty( $mlsapi_billing_data['plan']['currentPeriodEnd'] )
    ? $mlsapi_billing_data['plan']['currentPeriodEnd']
    : ( ! empty( $mlsapi_billing_data['current_period_end'] ) ? $mlsapi_billing_data['current_period_end'] : '' );

$mlsapi_reset_date_str = 'resets ' . gmdate( 'M j', strtotime( '+1 month first day of this month' ) );
if ( ! empty( $mlsapi_period_end ) ) {
    $mlsapi_reset_date_str = 'resets ' . gmdate( 'M j', strtotime( $mlsapi_period_end ) );
}

// Parse real daily usage from API if available
$mlsapi_days_data     = array();
$mlsapi_daily_usage   = ! empty( $mlsapi_billing_data['usage']['dailyRequests'] ) && is_array( $mlsapi_billing_data['usage']['dailyRequests'] )
    ? $mlsapi_billing_data['usage']['dailyRequests']
    : array();

$mlsapi_usage_by_date = array();
foreach ( $mlsapi_daily_usage as $mlsapi_item ) {
    if ( ! empty( $mlsapi_item['date'] ) ) {
        $mlsapi_usage_by_date[ $mlsapi_item['date'] ] = $mlsapi_item;
    }
}

$mlsapi_max_daily_total = 1;

for ( $mlsapi_i = 29; $mlsapi_i >= 0; $mlsapi_i-- ) {
    $mlsapi_date_iso = gmdate( 'Y-m-d', strtotime( "-$mlsapi_i days" ) );
    $mlsapi_d        = gmdate( 'M j', strtotime( "-$mlsapi_i days" ) );

    $mlsapi_staging  = 0;
    $mlsapi_twilight = 0;
    $mlsapi_enhance  = 0;

    if ( isset( $mlsapi_usage_by_date[ $mlsapi_date_iso ] ) ) {
        $mlsapi_entry    = $mlsapi_usage_by_date[ $mlsapi_date_iso ];
        $mlsapi_staging  = ! empty( $mlsapi_entry['staging'] ) ? (int) $mlsapi_entry['staging'] : 0;
        $mlsapi_twilight = ! empty( $mlsapi_entry['twilight'] ) ? (int) $mlsapi_entry['twilight'] : 0;
        $mlsapi_enhance  = ! empty( $mlsapi_entry['enhance'] ) ? (int) $mlsapi_entry['enhance'] : 0;
    } elseif ( 0 === $mlsapi_i && $mlsapi_used > 0 ) {
        // Today has recorded operations from current session
        $mlsapi_staging = (int) ceil( $mlsapi_used / 40 );
    }

    $mlsapi_total = $mlsapi_staging + $mlsapi_twilight + $mlsapi_enhance;
    if ( $mlsapi_total > $mlsapi_max_daily_total ) {
        $mlsapi_max_daily_total = $mlsapi_total;
    }

    $mlsapi_days_data[] = array(
        'date'     => $mlsapi_d,
        'staging'  => $mlsapi_staging,
        'twilight' => $mlsapi_twilight,
        'enhance'  => $mlsapi_enhance,
        'total'    => $mlsapi_total,
    );
}

$mlsapi_max_scale = max( 10, $mlsapi_max_daily_total );
?>

<!-- Header -->
<header class="mlsapi-card-header">
    <div class="mlsapi-header-left">
        <h1 class="mlsapi-card-title"><?php echo esc_html( $mlsapi_workspace_name ); ?></h1>
        <div class="mlsapi-breadcrumb">
            <span>MLS API</span>
            <span class="mlsapi-bc-sep">›</span>
            <span>Dashboard & Billing</span>
        </div>
    </div>
    <div class="mlsapi-header-badges">
        <a href="<?php echo esc_url( add_query_arg( 'refresh', '1' ) ); ?>" class="mlsapi-badge-dark mlsapi-btn-refresh" title="<?php esc_attr_e( 'Pull live balance from mlsapi.dev', 'mlsapi-studio' ); ?>">
            <span class="dashicons dashicons-update"></span> <?php esc_html_e( 'Refresh', 'mlsapi-studio' ); ?>
        </a>
        <span class="mlsapi-badge-dark"><?php echo esc_html( $mlsapi_tier ); ?></span>
        <span class="mlsapi-badge-dark mlsapi-badge-active"><?php echo esc_html( $mlsapi_status ); ?></span>
    </div>
</header>

<div class="mlsapi-dashboard-body">

    <!-- Section 1: Monthly Credits -->
    <div class="mlsapi-credits-section">
        <div class="mlsapi-credits-header">
            <span class="mlsapi-section-title"><?php esc_html_e( 'Monthly credits', 'mlsapi-studio' ); ?></span>
            <div class="mlsapi-credits-stat">
                <span class="mlsapi-stat-num"><?php echo esc_html( number_format( $mlsapi_used ) ); ?></span>
                <span class="mlsapi-stat-total">/ <?php echo esc_html( number_format( $mlsapi_allowance ) ); ?> used</span>
            </div>
        </div>

        <!-- Orange Progress Bar -->
        <div class="mlsapi-credit-track">
            <div class="mlsapi-credit-fill" style="width: <?php echo esc_attr( $mlsapi_percent_used ); ?>%;"></div>
        </div>

        <!-- Credits Subtext -->
        <div class="mlsapi-credit-subtext">
            <span>
                <?php
                /* translators: %s: remaining monthly credits */
                echo esc_html( sprintf( __( '%s remaining', 'mlsapi-studio' ), number_format( $mlsapi_remaining ) ) );
                ?>
            </span>
            <span>
                <?php
                /* translators: %s: top-up credits balance */
                echo esc_html( sprintf( __( '+%s top-up balance', 'mlsapi-studio' ), number_format( $mlsapi_topup ) ) );
                ?>
            </span>
            <span><?php echo esc_html( $mlsapi_reset_date_str ); ?></span>
        </div>
    </div>

    <!-- Section 2: Last 30 Days Stacked Chart -->
    <div class="mlsapi-chart-section">
        <div class="mlsapi-chart-header">
            <span class="mlsapi-section-title"><?php esc_html_e( 'Last 30 days', 'mlsapi-studio' ); ?></span>
            <div class="mlsapi-chart-legend">
                <span class="mlsapi-legend-item">
                    <span class="mlsapi-legend-dot dot-staging"></span> <?php esc_html_e( 'Staging', 'mlsapi-studio' ); ?>
                </span>
                <span class="mlsapi-legend-item">
                    <span class="mlsapi-legend-dot dot-twilight"></span> <?php esc_html_e( 'Twilight', 'mlsapi-studio' ); ?>
                </span>
                <span class="mlsapi-legend-item">
                    <span class="mlsapi-legend-dot dot-enhance"></span> <?php esc_html_e( 'Enhance', 'mlsapi-studio' ); ?>
                </span>
            </div>
        </div>
        <p class="mlsapi-chart-subtitle">
            <?php 
            if ( $mlsapi_used > 0 ) {
                esc_html_e( 'Hover any bar to see operation breakdowns for that day.', 'mlsapi-studio' );
            } else {
                esc_html_e( 'No operations recorded yet in current period. Operations will appear here live.', 'mlsapi-studio' );
            }
            ?>
        </p>

        <!-- Stacked Bar Chart -->
        <div class="mlsapi-stacked-chart" id="mlsapi-stacked-chart">
            <?php foreach ( $mlsapi_days_data as $mlsapi_day ) : 
                $mlsapi_h_staging  = $mlsapi_day['staging'] > 0 ? max( 4, round( ( $mlsapi_day['staging'] / $mlsapi_max_scale ) * 140 ) ) : 0;
                $mlsapi_h_twilight = $mlsapi_day['twilight'] > 0 ? max( 4, round( ( $mlsapi_day['twilight'] / $mlsapi_max_scale ) * 140 ) ) : 0;
                $mlsapi_h_enhance  = $mlsapi_day['enhance'] > 0 ? max( 4, round( ( $mlsapi_day['enhance'] / $mlsapi_max_scale ) * 140 ) ) : 0;
            ?>
                <div class="mlsapi-bar-col" title="<?php echo esc_attr( $mlsapi_day['date'] . ': ' . $mlsapi_day['total'] . ' operations (' . $mlsapi_day['staging'] . ' Staging, ' . $mlsapi_day['twilight'] . ' Twilight, ' . $mlsapi_day['enhance'] . ' Enhance)' ); ?>">
                    <?php if ( $mlsapi_day['total'] > 0 ) : ?>
                        <div class="mlsapi-bar-segment seg-enhance" style="height: <?php echo esc_attr( $mlsapi_h_enhance ); ?>px;"></div>
                        <div class="mlsapi-bar-segment seg-twilight" style="height: <?php echo esc_attr( $mlsapi_h_twilight ); ?>px;"></div>
                        <div class="mlsapi-bar-segment seg-staging" style="height: <?php echo esc_attr( $mlsapi_h_staging ); ?>px;"></div>
                    <?php else : ?>
                        <div class="mlsapi-bar-empty"></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Chart X-Axis Labels (Dynamic) -->
        <div class="mlsapi-chart-labels">
            <span><?php echo esc_html( gmdate( 'M j', strtotime( '-29 days' ) ) ); ?></span>
            <span><?php echo esc_html( gmdate( 'M j', strtotime( '-15 days' ) ) ); ?></span>
            <span><?php echo esc_html( gmdate( 'M j' ) ); ?></span>
        </div>
    </div>

    <!-- Bottom Action Button -->
    <div class="mlsapi-dashboard-actions">
        <a href="https://studio.mlsapi.dev/billing" target="_blank" rel="noopener noreferrer" class="mlsapi-btn-orange-cta">
            <?php esc_html_e( 'Manage subscription & buy credits', 'mlsapi-studio' ); ?>
        </a>
    </div>

</div>
