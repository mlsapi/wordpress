<?php
/**
 * Studio Editor Modal Template
 * Matches the modern real estate UI with photo ribbon, tool strip, and pill style selectors.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div id="mlsapi-modal-backdrop" class="mlsapi-modal-backdrop" style="display: none;">
    <div id="mlsapi-modal" class="mlsapi-modal" role="dialog" aria-modal="true" aria-labelledby="mlsapi-modal-title">
        
        <!-- Top Photo Carousel Ribbon -->
        <div class="mlsapi-top-ribbon" id="mlsapi-top-ribbon">
            <div class="mlsapi-ribbon-label">
                <span class="dashicons dashicons-format-gallery"></span>
                <span><?php esc_html_e( 'Listing Photos', 'mlsapi-studio' ); ?></span>
            </div>
            <div class="mlsapi-ribbon-scroll" id="mlsapi-ribbon-scroll">
                <!-- Dynamically populated thumbnail items -->
                <div class="mlsapi-ribbon-loading">
                    <span class="mlsapi-mini-spinner"></span>
                    <span><?php esc_html_e( 'Loading library photos...', 'mlsapi-studio' ); ?></span>
                </div>
            </div>
            <button type="button" class="mlsapi-ribbon-upload-btn" id="mlsapi-ribbon-add-btn" title="<?php esc_attr_e( 'Add / Upload Photo', 'mlsapi-studio' ); ?>">
                <span class="dashicons dashicons-plus-alt2"></span>
            </button>
            <button type="button" class="mlsapi-btn-icon mlsapi-modal-close-ribbon" id="mlsapi-modal-close" title="<?php esc_attr_e( 'Close', 'mlsapi-studio' ); ?>">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>

        <!-- Main Body: Tool Strip + Workspace Canvas + Configuration Sidebar -->
        <div class="mlsapi-modal-body">
            
            <!-- Left Tool Strip (Compact vertical icon + label) -->
            <nav class="mlsapi-tool-sidebar" aria-label="<?php esc_attr_e( 'AI Tools', 'mlsapi-studio' ); ?>">
                <button type="button" class="mlsapi-tool-btn active" data-tool="stage" data-label="Stage room">
                    <span class="dashicons dashicons-admin-home"></span>
                    <span class="mlsapi-tool-name"><?php esc_html_e( 'Stage', 'mlsapi-studio' ); ?></span>
                </button>
                <button type="button" class="mlsapi-tool-btn" data-tool="restyle" data-label="Restyle room">
                    <span class="dashicons dashicons-art"></span>
                    <span class="mlsapi-tool-name"><?php esc_html_e( 'Restyle', 'mlsapi-studio' ); ?></span>
                </button>
                <button type="button" class="mlsapi-tool-btn" data-tool="empty" data-label="Empty room">
                    <span class="dashicons dashicons-grid-view"></span>
                    <span class="mlsapi-tool-name"><?php esc_html_e( 'Empty', 'mlsapi-studio' ); ?></span>
                </button>
                <button type="button" class="mlsapi-tool-btn" data-tool="declutter" data-label="Declutter room">
                    <span class="dashicons dashicons-trash"></span>
                    <span class="mlsapi-tool-name"><?php esc_html_e( 'Declutter', 'mlsapi-studio' ); ?></span>
                </button>
                <button type="button" class="mlsapi-tool-btn" data-tool="wall-colors" data-label="Paint room">
                    <span class="dashicons dashicons-color-picker"></span>
                    <span class="mlsapi-tool-name"><?php esc_html_e( 'Paint', 'mlsapi-studio' ); ?></span>
                </button>

                <div class="mlsapi-tool-divider"></div>

                <button type="button" class="mlsapi-tool-btn" data-tool="twilight" data-label="Twilight conversion">
                    <span class="dashicons dashicons-visibility"></span>
                    <span class="mlsapi-tool-name"><?php esc_html_e( 'Twilight', 'mlsapi-studio' ); ?></span>
                </button>
                <button type="button" class="mlsapi-tool-btn" data-tool="enhance-exterior" data-label="Enhance curb appeal">
                    <span class="dashicons dashicons-palmtree"></span>
                    <span class="mlsapi-tool-name"><?php esc_html_e( 'Curb appeal', 'mlsapi-studio' ); ?></span>
                </button>

                <div class="mlsapi-tool-divider"></div>

                <button type="button" class="mlsapi-tool-btn" data-tool="upscale" data-label="Upscale photo">
                    <span class="dashicons dashicons-search"></span>
                    <span class="mlsapi-tool-name"><?php esc_html_e( 'Upscale', 'mlsapi-studio' ); ?></span>
                </button>
                <button type="button" class="mlsapi-tool-btn" data-tool="floorplan-3d" data-label="3D floor plan">
                    <span class="dashicons dashicons-building"></span>
                    <span class="mlsapi-tool-name"><?php esc_html_e( '3D plan', 'mlsapi-studio' ); ?></span>
                </button>
            </nav>

            <!-- Center: Canvas Viewport (Dropzone or Compare Slider) -->
            <main class="mlsapi-workspace">
                
                <!-- Placeholder / Empty Dropzone State -->
                <div id="mlsapi-dropzone-state" class="mlsapi-dropzone-state">
                    <div class="mlsapi-dropzone-box">
                        <span class="dashicons dashicons-format-image mlsapi-dropzone-icon"></span>
                        <h3><?php esc_html_e( 'Select or Paste a Real Estate Photo', 'mlsapi-studio' ); ?></h3>
                        <p><?php esc_html_e( 'Pick from the top listing ribbon, browse Media Library, drag & drop, or paste from clipboard (Ctrl+V).', 'mlsapi-studio' ); ?></p>
                        <div class="mlsapi-dropzone-actions">
                            <button type="button" id="mlsapi-select-media-btn" class="button button-primary button-hero">
                                <span class="dashicons dashicons-admin-media"></span> <?php esc_html_e( 'Select from Media Library', 'mlsapi-studio' ); ?>
                            </button>
                            <input type="file" id="mlsapi-file-input" accept="image/*" style="display:none;" />
                            <button type="button" id="mlsapi-browse-file-btn" class="button button-secondary button-hero">
                                <?php esc_html_e( 'Upload File', 'mlsapi-studio' ); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Active Image View (Before / After Comparison Slider) -->
                <div id="mlsapi-canvas-state" class="mlsapi-canvas-state" style="display: none;">
                    <div class="mlsapi-compare-container" id="mlsapi-compare-viewer">
                        <!-- Before (Original) Image -->
                        <div class="mlsapi-compare-before">
                            <img id="mlsapi-img-before" src="" alt="Original Photo" />
                            <span class="mlsapi-compare-label"><?php esc_html_e( 'Before', 'mlsapi-studio' ); ?></span>
                        </div>
                        <!-- After (Generated) Image -->
                        <div class="mlsapi-compare-after" id="mlsapi-compare-after-wrapper">
                            <img id="mlsapi-img-after" src="" alt="AI Generated Photo" />
                            <span class="mlsapi-compare-label"><?php esc_html_e( 'After', 'mlsapi-studio' ); ?></span>
                            <!-- Optional compliance watermark preview badge -->
                            <div class="mlsapi-watermark-badge" id="mlsapi-watermark-badge" style="display:none;">
                                <?php esc_html_e( 'Virtually Staged', 'mlsapi-studio' ); ?>
                            </div>
                        </div>
                        <!-- Split Slider Divider Handle -->
                        <div class="mlsapi-compare-handle" id="mlsapi-compare-handle">
                            <div class="mlsapi-compare-arrows"></div>
                        </div>
                    </div>
                </div>

                <!-- Processing Overlay -->
                <div id="mlsapi-processing-overlay" class="mlsapi-processing-overlay" style="display: none;">
                    <div class="mlsapi-spinner-wrap">
                        <div class="mlsapi-spinner"></div>
                        <h4 id="mlsapi-step-title"><?php esc_html_e( 'Generating with AI...', 'mlsapi-studio' ); ?></h4>
                        <p id="mlsapi-step-desc"><?php esc_html_e( 'Running neural rendering pipeline on mlsapi.dev', 'mlsapi-studio' ); ?></p>
                        <div class="mlsapi-progress-bar-container">
                            <div class="mlsapi-progress-bar" id="mlsapi-progress-bar" style="width: 15%;"></div>
                        </div>
                        <span id="mlsapi-progress-percent" class="mlsapi-progress-percent">15%</span>
                    </div>
                </div>
            </main>

            <!-- Right Sidebar: Configuration Panel -->
            <aside class="mlsapi-param-sidebar">
                <div class="mlsapi-param-header">
                    <h2 id="mlsapi-current-tool-title"><?php esc_html_e( 'Stage room', 'mlsapi-studio' ); ?></h2>
                    <p id="mlsapi-current-tool-desc" class="mlsapi-param-subtitle">
                        <?php esc_html_e( 'Furnish an empty room', 'mlsapi-studio' ); ?>
                    </p>
                </div>

                <!-- Scrollable Parameter Fields Container -->
                <div class="mlsapi-param-fields" id="mlsapi-param-fields">
                    
                    <!-- Room Dropdown (Stage, Restyle, Empty, Declutter, Paint) -->
                    <div class="mlsapi-field-group" data-tools="stage,restyle,empty,declutter,wall-colors">
                        <label for="mlsapi-param-room"><?php esc_html_e( 'Room', 'mlsapi-studio' ); ?></label>
                        <div class="mlsapi-select-wrapper">
                            <select id="mlsapi-param-room" class="mlsapi-select">
                                <option value="living_room" selected><?php esc_html_e( 'Living Room', 'mlsapi-studio' ); ?></option>
                                <option value="primary_bedroom"><?php esc_html_e( 'Primary Bedroom', 'mlsapi-studio' ); ?></option>
                                <option value="bedroom"><?php esc_html_e( 'Bedroom', 'mlsapi-studio' ); ?></option>
                                <option value="dining_room"><?php esc_html_e( 'Dining Room', 'mlsapi-studio' ); ?></option>
                                <option value="kitchen"><?php esc_html_e( 'Kitchen', 'mlsapi-studio' ); ?></option>
                                <option value="home_office"><?php esc_html_e( 'Home Office', 'mlsapi-studio' ); ?></option>
                                <option value="patio"><?php esc_html_e( 'Outdoor Patio / Deck', 'mlsapi-studio' ); ?></option>
                                <option value="bathroom"><?php esc_html_e( 'Bathroom', 'mlsapi-studio' ); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Style Pill Chips Selector (Stage, Restyle, 3D Plan) -->
                    <div class="mlsapi-field-group" data-tools="stage,restyle,floorplan-3d">
                        <label><?php esc_html_e( 'Style', 'mlsapi-studio' ); ?></label>
                        <input type="hidden" id="mlsapi-param-style" value="scandinavian" />
                        <div class="mlsapi-pill-grid" id="mlsapi-style-pills">
                            <button type="button" class="mlsapi-pill active" data-style="scandinavian"><?php esc_html_e( 'Scandinavian', 'mlsapi-studio' ); ?></button>
                            <button type="button" class="mlsapi-pill" data-style="modern"><?php esc_html_e( 'Modern', 'mlsapi-studio' ); ?></button>
                            <button type="button" class="mlsapi-pill" data-style="luxury"><?php esc_html_e( 'Luxury', 'mlsapi-studio' ); ?></button>
                            <button type="button" class="mlsapi-pill" data-style="coastal"><?php esc_html_e( 'Coastal', 'mlsapi-studio' ); ?></button>
                            <button type="button" class="mlsapi-pill" data-style="farmhouse"><?php esc_html_e( 'Modern Farmhouse', 'mlsapi-studio' ); ?></button>
                            <button type="button" class="mlsapi-pill" data-style="japandi"><?php esc_html_e( 'Japandi', 'mlsapi-studio' ); ?></button>
                            <button type="button" class="mlsapi-pill" data-style="mid_century_modern"><?php esc_html_e( 'Mid-Century', 'mlsapi-studio' ); ?></button>
                            <button type="button" class="mlsapi-pill" data-style="minimalist"><?php esc_html_e( 'Minimalist', 'mlsapi-studio' ); ?></button>
                            <button type="button" class="mlsapi-pill" data-style="industrial"><?php esc_html_e( 'Industrial', 'mlsapi-studio' ); ?></button>
                            <button type="button" class="mlsapi-pill" data-style="mediterranean"><?php esc_html_e( 'Mediterranean', 'mlsapi-studio' ); ?></button>
                        </div>
                    </div>

                    <!-- Twilight Mode Selector -->
                    <div class="mlsapi-field-group" data-tools="twilight" style="display: none;">
                        <label><?php esc_html_e( 'Lighting Mode', 'mlsapi-studio' ); ?></label>
                        <input type="hidden" id="mlsapi-param-twilight-mode" value="day_to_dusk" />
                        <div class="mlsapi-pill-grid" id="mlsapi-twilight-pills">
                            <button type="button" class="mlsapi-pill active" data-twilight="day_to_dusk"><?php esc_html_e( 'Day to Dusk', 'mlsapi-studio' ); ?></button>
                            <button type="button" class="mlsapi-pill" data-twilight="blue_sky_replace"><?php esc_html_e( 'Blue Sky Replace', 'mlsapi-studio' ); ?></button>
                        </div>
                    </div>

                    <!-- Curb Appeal Retouch Features -->
                    <div class="mlsapi-field-group" data-tools="enhance-exterior" style="display: none;">
                        <label><?php esc_html_e( 'Exterior Retouch Items', 'mlsapi-studio' ); ?></label>
                        <div class="mlsapi-checkbox-group">
                            <label class="mlsapi-checkbox-row">
                                <input type="checkbox" id="mlsapi-curb-lawn" checked />
                                <span><?php esc_html_e( 'Green grass & lawn repair', 'mlsapi-studio' ); ?></span>
                            </label>
                            <label class="mlsapi-checkbox-row">
                                <input type="checkbox" id="mlsapi-curb-sky" checked />
                                <span><?php esc_html_e( 'Sunny blue sky replacement', 'mlsapi-studio' ); ?></span>
                            </label>
                            <label class="mlsapi-checkbox-row">
                                <input type="checkbox" id="mlsapi-curb-pool" checked />
                                <span><?php esc_html_e( 'Clean & sparkling pool water', 'mlsapi-studio' ); ?></span>
                            </label>
                        </div>
                    </div>

                    <!-- Empty Room Flooring -->
                    <div class="mlsapi-field-group" data-tools="empty" style="display: none;">
                        <label><?php esc_html_e( 'Restore Flooring', 'mlsapi-studio' ); ?></label>
                        <input type="hidden" id="mlsapi-param-flooring" value="hardwood" />
                        <div class="mlsapi-pill-grid" id="mlsapi-flooring-pills">
                            <button type="button" class="mlsapi-pill active" data-flooring="hardwood"><?php esc_html_e( 'Hardwood', 'mlsapi-studio' ); ?></button>
                            <button type="button" class="mlsapi-pill" data-flooring="tile"><?php esc_html_e( 'Tile', 'mlsapi-studio' ); ?></button>
                            <button type="button" class="mlsapi-pill" data-flooring="carpet"><?php esc_html_e( 'Carpet', 'mlsapi-studio' ); ?></button>
                            <button type="button" class="mlsapi-pill" data-flooring="polished_concrete"><?php esc_html_e( 'Concrete', 'mlsapi-studio' ); ?></button>
                        </div>
                    </div>

                    <!-- Freeform Prompt: "Anything specific? (optional)" -->
                    <div class="mlsapi-field-group">
                        <label for="mlsapi-param-notes"><?php esc_html_e( 'Anything specific? (optional)', 'mlsapi-studio' ); ?></label>
                        <input type="text" id="mlsapi-param-notes" class="mlsapi-input" placeholder="<?php esc_attr_e( 'Light oak dining table, linen sofa, a big...', 'mlsapi-studio' ); ?>" />
                    </div>

                    <!-- MLS Compliance Watermark Notice -->
                    <div class="mlsapi-compliance-notice" id="mlsapi-compliance-notice">
                        <div class="mlsapi-compliance-text">
                            <?php esc_html_e( 'Adds the caption "Virtually staged" when it goes on the listing. Most MLSs require it.', 'mlsapi-studio' ); ?>
                        </div>
                        <label class="mlsapi-compliance-toggle">
                            <input type="checkbox" id="mlsapi-include-watermark" checked />
                            <span><?php esc_html_e( 'Include watermark', 'mlsapi-studio' ); ?></span>
                        </label>
                    </div>

                </div>

                <!-- Bottom Action Box -->
                <div class="mlsapi-sidebar-footer">
                    <button type="button" id="mlsapi-generate-btn" class="mlsapi-btn-primary" disabled>
                        <span class="mlsapi-star-icon">✦</span>
                        <span id="mlsapi-generate-btn-text"><?php esc_html_e( 'Stage room', 'mlsapi-studio' ); ?></span>
                    </button>
                    <p class="mlsapi-disclaimer-subtext">
                        <?php esc_html_e( 'Edits this listing photo. Your original stays as it is.', 'mlsapi-studio' ); ?>
                    </p>

                    <!-- Save / Output Actions (Revealed after generation) -->
                    <div class="mlsapi-output-actions" id="mlsapi-output-actions" style="display: none;">
                        <div class="mlsapi-format-row">
                            <span><?php esc_html_e( 'Export format:', 'mlsapi-studio' ); ?></span>
                            <select id="mlsapi-output-format" class="mlsapi-select-mini">
                                <option value="webp" selected>WebP</option>
                                <option value="png">PNG</option>
                                <option value="jpg">JPG</option>
                            </select>
                        </div>
                        <div class="mlsapi-output-buttons">
                            <button type="button" id="mlsapi-save-btn" class="button button-primary">
                                <?php esc_html_e( 'Save to Media Library', 'mlsapi-studio' ); ?>
                            </button>
                            <button type="button" id="mlsapi-replace-btn" class="button button-secondary" style="display: none;">
                                <?php esc_html_e( 'Replace Original', 'mlsapi-studio' ); ?>
                            </button>
                            <button type="button" id="mlsapi-download-btn" class="button button-secondary">
                                <?php esc_html_e( 'Download', 'mlsapi-studio' ); ?>
                            </button>
                        </div>
                    </div>
                </div>

            </aside>
        </div>

    </div>
</div>
