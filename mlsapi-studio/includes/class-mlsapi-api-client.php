<?php
/**
 * MLS API Client Wrapper
 * Handles all authenticated HTTP communication with mlsapi.dev
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MLSAPI_Api_Client {

    /**
     * Get configured API key
     *
     * @return string
     */
    public function get_api_key() {
        return trim( (string) get_option( 'mlsapi_api_key', '' ) );
    }

    /**
     * Get configured API base URL
     *
     * @return string
     */
    public function get_base_url() {
        $url = trim( (string) get_option( 'mlsapi_api_base_url', MLSAPI_DEFAULT_API_URL ) );
        return untrailingslashit( empty( $url ) ? MLSAPI_DEFAULT_API_URL : $url );
    }

    /**
     * Get configured environment (live vs test)
     *
     * @return string
     */
    public function get_env() {
        return get_option( 'mlsapi_key_env', 'live' ) === 'test' ? 'test' : 'live';
    }

    /**
     * Check if plugin is configured with an API key
     *
     * @return bool
     */
    public function is_configured() {
        $key = $this->get_api_key();
        return ! empty( $key );
    }

    /**
     * Generate common HTTP request headers
     *
     * @return array
     */
    private function get_headers() {
        $key = $this->get_api_key();
        return array(
            'Authorization' => 'Bearer ' . $key,
            'x-api-key'     => $key,
            'x-key-env'     => $this->get_env(),
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'User-Agent'    => 'WordPress-MLSAPI-Studio/' . MLSAPI_VERSION . '; ' . home_url(),
        );
    }

    /**
     * Perform HTTP request to the API
     *
     * @param string $method GET, POST, etc.
     * @param string $endpoint Path relative to base URL.
     * @param array|null $body JSON payload if POST/PUT.
     * @param int $timeout Request timeout in seconds.
     * @return array|WP_Error Parsed JSON response array or WP_Error.
     */
    public function request( $method, $endpoint, $body = null, $timeout = 30 ) {
        if ( ! $this->is_configured() ) {
            return new WP_Error( 'not_configured', __( 'MLS API key is not configured. Please visit settings.', 'mlsapi-studio' ) );
        }

        $url = $this->get_base_url() . '/' . ltrim( $endpoint, '/' );

        $args = array(
            'method'  => strtoupper( $method ),
            'headers' => $this->get_headers(),
            'timeout' => $timeout,
        );

        if ( null !== $body && in_array( $args['method'], array( 'POST', 'PUT', 'PATCH' ), true ) ) {
            $args['body'] = wp_json_encode( $body );
        }

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $raw_body = wp_remote_retrieve_body( $response );
        $data = json_decode( $raw_body, true );

        if ( $code >= 400 ) {
            $msg = isset( $data['error']['message'] ) ? $data['error']['message'] : ( isset( $data['error'] ) ? $data['error'] : 'HTTP Error ' . $code );
            return new WP_Error( 'api_error_' . $code, $msg, array( 'status' => $code, 'data' => $data ) );
        }

        return is_array( $data ) ? $data : array( 'raw' => $raw_body );
    }

    /**
     * Test connection and validate API key
     *
     * @return array|WP_Error
     */
    public function test_connection() {
        return $this->request( 'GET', '/api/billing/overview' );
    }

    /**
     * Fetch workspace billing & credit usage overview (cached with transient)
     *
     * @param bool $force_refresh Skip cache if true.
     * @return array|WP_Error
     */
    public function get_billing_overview( $force_refresh = false ) {
        $cache_key = 'mlsapi_billing_overview_' . md5( $this->get_api_key() );

        if ( ! $force_refresh ) {
            $cached = get_transient( $cache_key );
            if ( false !== $cached ) {
                return $cached;
            }
        }

        $data = $this->request( 'GET', '/api/billing/overview' );
        if ( ! is_wp_error( $data ) ) {
            set_transient( $cache_key, $data, 5 * MINUTE_IN_SECONDS );
        }
        return $data;
    }

    /**
     * Dispatch an asynchronous Studio AI operation
     *
     * @param string $operation Staging, twilight, declutter, etc.
     * @param array $payload Endpoint parameters.
     * @return array|WP_Error
     */
    public function dispatch_studio_job( $operation, $payload ) {
        $endpoint_map = array(
            'stage'             => '/v1/studio/staging/stage',
            'furnish'           => '/v1/studio/staging/stage',
            'twilight'          => '/v1/studio/staging/twilight',
            'declutter'         => '/v1/studio/staging/declutter',
            'empty'             => '/v1/studio/staging/empty',
            'restyle'           => '/v1/studio/staging/restyle',
            'replace-furniture' => '/v1/studio/staging/replace-furniture',
            'replace-material'  => '/v1/studio/staging/replace-material',
            'wall-colors'       => '/v1/studio/staging/wall-colors',
            'floorplan-3d'      => '/v1/studio/floorplan/render-3d',
            'creatives'         => '/v1/studio/creatives/generate',
            'enhance-exterior'  => '/v1/studio/enhance/exterior',
            'upscale'           => '/v1/studio/enhance/upscale',
            'architectural'     => '/v1/studio/render/architectural',
        );

        if ( ! isset( $endpoint_map[ $operation ] ) ) {
            return new WP_Error( 'invalid_operation', sprintf( __( 'Unknown operation "%s"', 'mlsapi-studio' ), esc_html( $operation ) ) );
        }

        return $this->request( 'POST', $endpoint_map[ $operation ], $payload, 45 );
    }

    /**
     * Poll status of an async Studio job
     *
     * @param string $job_id
     * @return array|WP_Error
     */
    public function get_job_status( $job_id ) {
        $job_id = sanitize_text_field( $job_id );
        return $this->request( 'GET', '/v1/studio/jobs/' . $job_id );
    }

    /**
     * Safely download remote image binary from CDN URL
     *
     * @param string $url
     * @return string|WP_Error Binary data or error
     */
    public function download_remote_image( $url ) {
        if ( ! wp_http_validate_url( $url ) ) {
            return new WP_Error( 'invalid_url', __( 'Invalid image URL provided', 'mlsapi-studio' ) );
        }

        $response = wp_safe_remote_get( $url, array(
            'timeout'    => 60,
            'user-agent' => 'WordPress-MLSAPI-Studio/' . MLSAPI_VERSION,
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== $code ) {
            return new WP_Error( 'download_failed', sprintf( __( 'Remote image download failed with HTTP code %d', 'mlsapi-studio' ), $code ) );
        }

        $binary = wp_remote_retrieve_body( $response );
        if ( empty( $binary ) ) {
            return new WP_Error( 'empty_image', __( 'Downloaded image body is empty', 'mlsapi-studio' ) );
        }

        return $binary;
    }
}
