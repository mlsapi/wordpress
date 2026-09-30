<?php
/**
 * MLS API Media Integration
 * Injects launch buttons and links into the WordPress Media Library, Edit screens, and Admin Bar.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MLSAPI_Media {

    /**
     * Constructor
     */
    public function __construct() {
        if ( get_option( 'mlsapi_enable_media_button', true ) ) {
            add_action( 'admin_head-upload.php', array( $this, 'inject_media_page_button' ) );
            add_filter( 'media_row_actions', array( $this, 'add_media_row_action' ), 10, 2 );
            add_action( 'attachment_submitbox_misc_actions', array( $this, 'add_attachment_edit_button' ) );
        }

        if ( get_option( 'mlsapi_enable_admin_bar', true ) ) {
            add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_button' ), 100 );
        }
    }

    /**
     * Add "MLS Studio AI" button to upload.php header
     */
    public function inject_media_page_button() {
        if ( ! current_user_can( 'upload_files' ) ) {
            return;
        }
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var $addNewBtn = $('.page-title-action:first');
                if ($addNewBtn.length && !$('#mlsapi-media-launch-btn').length) {
                    var $mlsBtn = $('<a href="#" id="mlsapi-media-launch-btn" class="page-title-action" style="margin-left: 8px; background: #2271b1; color: #fff; border-color: #2271b1; display: inline-flex; align-items: center; gap: 4px;"><span class="dashicons dashicons-art" style="font-size:16px; width:16px; height:16px; line-height:1;"></span> ' + <?php echo wp_json_encode( __( 'MLS Studio AI', 'mlsapi-studio' ) ); ?> + '</a>');
                    $mlsBtn.on('click', function(e) {
                        e.preventDefault();
                        if (window.MLSAPIModal) {
                            window.MLSAPIModal.open();
                        }
                    });
                    $addNewBtn.after($mlsBtn);
                }
            });
        </script>
        <?php
    }

    /**
     * Add row action link to Media Library list view
     */
    public function add_media_row_action( $actions, $post ) {
        if ( ! current_user_can( 'upload_files' ) ) {
            return $actions;
        }

        if ( 0 !== strpos( get_post_mime_type( $post ), 'image/' ) ) {
            return $actions;
        }

        $image_url = wp_get_attachment_url( $post->ID );
        if ( ! $image_url ) {
            return $actions;
        }

        $actions['mlsapi_edit'] = sprintf(
            '<a href="#" onclick="if(window.MLSAPIModal){window.MLSAPIModal.openWithAttachment(%d, \'%s\');}; return false;" style="color:#2271b1; font-weight:600;">%s</a>',
            $post->ID,
            esc_js( $image_url ),
            esc_html__( 'Edit in MLS Studio', 'mlsapi-studio' )
        );

        return $actions;
    }

    /**
     * Add button in attachment edit submitbox
     */
    public function add_attachment_edit_button() {
        global $post;
        if ( ! $post || 'attachment' !== $post->post_type || 0 !== strpos( get_post_mime_type( $post ), 'image/' ) ) {
            return;
        }

        $image_url = wp_get_attachment_url( $post->ID );
        if ( ! $image_url ) {
            return;
        }
        ?>
        <div class="misc-pub-section misc-pub-mlsapi" style="padding: 10px; background: #f0f6fc; border: 1px solid #cce5ff; border-radius: 4px; margin-top: 10px;">
            <button type="button" class="button button-primary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 6px;" onclick="if(window.MLSAPIModal){window.MLSAPIModal.openWithAttachment(<?php echo (int) $post->ID; ?>, '<?php echo esc_js( $image_url ); ?>');}; return false;">
                <span class="dashicons dashicons-art"></span>
                <?php esc_html_e( 'Edit with MLS Studio', 'mlsapi-studio' ); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Add "Edit with MLS Studio" button to the WP Admin Bar
     */
    public function add_admin_bar_button( $wp_admin_bar ) {
        if ( ! current_user_can( 'upload_files' ) ) {
            return;
        }

        $post = get_post();
        if ( ! $post || 'attachment' !== $post->post_type || 0 !== strpos( get_post_mime_type( $post ), 'image/' ) ) {
            return;
        }

        $image_url = wp_get_attachment_url( $post->ID );
        if ( ! $image_url ) {
            return;
        }

        $wp_admin_bar->add_node( array(
            'id'    => 'mlsapi-studio-bar-edit',
            'title' => '<span class="ab-icon dashicons dashicons-art"></span>' . esc_html__( 'Edit with MLS Studio', 'mlsapi-studio' ),
            'href'  => '#',
            'meta'  => array(
                'onclick' => 'if(window.MLSAPIModal){window.MLSAPIModal.openWithAttachment(' . (int) $post->ID . ', \'' . esc_js( $image_url ) . '\');}; return false;',
            ),
        ) );
    }
}
