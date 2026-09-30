<?php
/**
 * MLS API AJAX Request Handler
 * Handles job dispatching, status polling, test connection, and Media Library saving.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MLSAPI_Ajax {

    /**
     * @var MLSAPI_Api_Client
     */
    private $api_client;

    /**
     * Constructor
     */
    public function __construct( MLSAPI_Api_Client $api_client ) {
        $this->api_client = $api_client;

        add_action( 'wp_ajax_mlsapi_test_connection', array( $this, 'handle_test_connection' ) );
        add_action( 'wp_ajax_mlsapi_dispatch_studio_job', array( $this, 'handle_dispatch_job' ) );
        add_action( 'wp_ajax_mlsapi_poll_studio_job', array( $this, 'handle_poll_job' ) );
        add_action( 'wp_ajax_mlsapi_save_image', array( $this, 'handle_save_image' ) );
        add_action( 'wp_ajax_mlsapi_get_recent_media', array( $this, 'handle_get_recent_media' ) );
    }

    /**
     * Test connection to mlsapi.dev
     */
    public function handle_test_connection() {
        check_ajax_referer( 'mlsapi_studio_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'mlsapi-studio' ) ), 403 );
        }

        $res = $this->api_client->test_connection();

        if ( is_wp_error( $res ) ) {
            wp_send_json_error( array( 'message' => $res->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Connected successfully to mlsapi.dev!', 'mlsapi-studio' ),
            'data'    => $res,
        ) );
    }

    /**
     * Dispatch AI studio job
     */
    public function handle_dispatch_job() {
        check_ajax_referer( 'mlsapi_studio_nonce', 'nonce' );

        if ( ! current_user_can( 'upload_files' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'mlsapi-studio' ) ), 403 );
        }

        $operation = isset( $_POST['operation'] ) ? sanitize_text_field( wp_unslash( $_POST['operation'] ) ) : '';
        $raw_params = isset( $_POST['params'] ) ? wp_unslash( $_POST['params'] ) : '';
        $params = is_string( $raw_params ) ? json_decode( $raw_params, true ) : $raw_params;

        if ( empty( $operation ) || ! is_array( $params ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid operation or request parameters.', 'mlsapi-studio' ) ) );
        }

        // If source attachment ID is provided but photo_url is missing, resolve the attachment URL
        if ( empty( $params['photo_url'] ) && ! empty( $_POST['source_attachment_id'] ) ) {
            $att_id = intval( $_POST['source_attachment_id'] );
            $att_url = wp_get_attachment_url( $att_id );
            if ( $att_url ) {
                $params['photo_url'] = $att_url;
            }
        }

        $res = $this->api_client->dispatch_studio_job( $operation, $params );

        if ( is_wp_error( $res ) ) {
            wp_send_json_error( array( 'message' => $res->get_error_message() ) );
        }

        wp_send_json_success( $res );
    }

    /**
     * Poll status of an async Studio job
     */
    public function handle_poll_job() {
        check_ajax_referer( 'mlsapi_studio_nonce', 'nonce' );

        if ( ! current_user_can( 'upload_files' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'mlsapi-studio' ) ), 403 );
        }

        $job_id = isset( $_GET['job_id'] ) ? sanitize_text_field( wp_unslash( $_GET['job_id'] ) ) : '';
        if ( empty( $job_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Missing required job_id.', 'mlsapi-studio' ) ) );
        }

        $res = $this->api_client->get_job_status( $job_id );

        if ( is_wp_error( $res ) ) {
            wp_send_json_error( array( 'message' => $res->get_error_message() ) );
        }

        wp_send_json_success( $res );
    }

    /**
     * Ingest remote CDN image into the WordPress Media Library or replace existing
     */
    public function handle_save_image() {
        check_ajax_referer( 'mlsapi_studio_nonce', 'nonce' );

        if ( ! current_user_can( 'upload_files' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'mlsapi-studio' ) ), 403 );
        }

        $image_url = isset( $_POST['image_url'] ) ? esc_url_raw( wp_unslash( $_POST['image_url'] ) ) : '';
        if ( empty( $image_url ) ) {
            wp_send_json_error( array( 'message' => __( 'Image URL is required.', 'mlsapi-studio' ) ) );
        }

        $replace_attachment_id = ! empty( $_POST['replace_attachment_id'] ) ? intval( $_POST['replace_attachment_id'] ) : 0;
        $is_replace = $replace_attachment_id > 0;

        if ( $is_replace && ! current_user_can( 'edit_post', $replace_attachment_id ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to edit this attachment.', 'mlsapi-studio' ) ), 403 );
        }

        // Download remote image
        $image_binary = $this->api_client->download_remote_image( $image_url );
        if ( is_wp_error( $image_binary ) ) {
            wp_send_json_error( array( 'message' => $image_binary->get_error_message() ) );
        }

        // Validate image content
        if ( function_exists( 'getimagesizefromstring' ) ) {
            $image_info = @getimagesizefromstring( $image_binary );
            if ( false === $image_info || empty( $image_info['mime'] ) || 0 !== strpos( $image_info['mime'], 'image/' ) ) {
                wp_send_json_error( array( 'message' => __( 'Downloaded payload is not a valid image.', 'mlsapi-studio' ) ) );
            }
        }

        // Initialize WordPress Filesystem
        global $wp_filesystem;
        if ( empty( $wp_filesystem ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $upload_dir = wp_upload_dir();
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $job_id    = isset( $_POST['job_id'] ) ? sanitize_text_field( wp_unslash( $_POST['job_id'] ) ) : '';
        $operation = isset( $_POST['operation'] ) ? sanitize_text_field( wp_unslash( $_POST['operation'] ) ) : '';

        if ( $is_replace ) {
            $existing_file = get_attached_file( $replace_attachment_id );
            if ( ! $existing_file || ! file_exists( $existing_file ) ) {
                wp_send_json_error( array( 'message' => __( 'Existing attachment file not found.', 'mlsapi-studio' ) ) );
            }

            // Path traversal safety
            $upload_basedir = wp_normalize_path( trailingslashit( $upload_dir['basedir'] ) );
            $normalized_existing = wp_normalize_path( $existing_file );
            if ( 0 !== strpos( $normalized_existing, $upload_basedir ) ) {
                wp_send_json_error( array( 'message' => __( 'Invalid attachment file path.', 'mlsapi-studio' ) ) );
            }

            // Write over the existing file
            if ( ! $wp_filesystem->put_contents( $existing_file, $image_binary, FS_CHMOD_FILE ) ) {
                wp_send_json_error( array( 'message' => __( 'Failed to overwrite existing attachment.', 'mlsapi-studio' ) ) );
            }

            // Regenerate metadata and thumbnails
            $metadata = wp_generate_attachment_metadata( $replace_attachment_id, $existing_file );
            wp_update_attachment_metadata( $replace_attachment_id, $metadata );
            clean_attachment_cache( $replace_attachment_id );

            if ( $job_id ) {
                update_post_meta( $replace_attachment_id, '_mlsapi_last_job_id', $job_id );
            }
            if ( $operation ) {
                update_post_meta( $replace_attachment_id, '_mlsapi_last_operation', $operation );
            }

            /**
             * Fires after an image attachment has been replaced by MLS API Studio
             */
            do_action( 'mlsapi_image_replaced', $replace_attachment_id, $existing_file, $operation );

            wp_send_json_success( array(
                'message'       => __( 'Image replaced successfully.', 'mlsapi-studio' ),
                'attachment_id' => $replace_attachment_id,
                'url'           => wp_get_attachment_url( $replace_attachment_id ),
            ) );

        } else {
            // New upload mode
            $title = ! empty( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : 'mls-studio-' . ( $operation ? $operation . '-' : '' ) . time();
            $file_ext = 'webp';
            if ( ! empty( $_POST['format'] ) ) {
                $file_ext = sanitize_key( wp_unslash( $_POST['format'] ) );
            } elseif ( ! empty( $image_info['mime'] ) ) {
                $mime_ext_map = array(
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp',
                    'image/avif' => 'avif',
                );
                if ( isset( $mime_ext_map[ $image_info['mime'] ] ) ) {
                    $file_ext = $mime_ext_map[ $image_info['mime'] ];
                }
            }

            $sanitized_base = sanitize_file_name( $title );
            $filename = wp_unique_filename( $upload_dir['path'], $sanitized_base . '.' . $file_ext );
            $file_path = $upload_dir['path'] . '/' . $filename;

            if ( ! $wp_filesystem->put_contents( $file_path, $image_binary, FS_CHMOD_FILE ) ) {
                wp_send_json_error( array( 'message' => __( 'Failed to save image to uploads directory.', 'mlsapi-studio' ) ) );
            }

            $wp_filetype = wp_check_filetype( $filename, null );
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'] ? $wp_filetype['type'] : 'image/' . $file_ext,
                'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
                'post_content'   => '',
                'post_status'    => 'inherit',
            );

            $attachment_id = wp_insert_attachment( $attachment, $file_path );
            if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
                wp_send_json_error( array( 'message' => __( 'Failed to create Media Library record.', 'mlsapi-studio' ) ) );
            }

            $metadata = wp_generate_attachment_metadata( $attachment_id, $file_path );
            wp_update_attachment_metadata( $attachment_id, $metadata );

            if ( $job_id ) {
                update_post_meta( $attachment_id, '_mlsapi_source_job_id', $job_id );
            }
            if ( $operation ) {
                update_post_meta( $attachment_id, '_mlsapi_operation_type', $operation );
            }

            /**
             * Fires after a new image attachment has been saved by MLS API Studio
             */
            do_action( 'mlsapi_image_created', $attachment_id, $file_path, $operation );

            wp_send_json_success( array(
                'message'       => __( 'Image saved to Media Library successfully.', 'mlsapi-studio' ),
                'attachment_id' => $attachment_id,
                'url'           => wp_get_attachment_url( $attachment_id ),
            ) );
        }
    }

    /**
     * Get recent media library image attachments for the top ribbon
     */
    public function handle_get_recent_media() {
        check_ajax_referer( 'mlsapi_studio_nonce', 'nonce' );

        if ( ! current_user_can( 'upload_files' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'mlsapi-studio' ) ), 403 );
        }

        $query = new WP_Query( array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => 24,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );

        $images = array();
        foreach ( $query->posts as $post ) {
            $thumb_src = wp_get_attachment_image_src( $post->ID, 'thumbnail' );
            $full_url  = wp_get_attachment_url( $post->ID );
            if ( $full_url ) {
                $images[] = array(
                    'id'        => $post->ID,
                    'title'     => get_the_title( $post->ID ),
                    'thumb_url' => $thumb_src ? $thumb_src[0] : $full_url,
                    'full_url'  => $full_url,
                );
            }
        }

        wp_send_json_success( array( 'images' => $images ) );
    }
}

