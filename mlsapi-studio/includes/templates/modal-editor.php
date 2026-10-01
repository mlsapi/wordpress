<?php
/**
 * Studio Editor Modal Template
 * Matches the exact MLS API Studio dark branding and layout.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div id="mlsapi-modal-backdrop" class="mlsapi-modal-backdrop" style="display: none;">
    <div id="mlsapi-modal" class="mlsapi-modal" role="dialog" aria-modal="true" aria-labelledby="mlsapi-modal-title">
        
        <!-- Header -->
        <header class="mlsapi-modal-header">
            <div class="mlsapi-header-left">
                <div class="mlsapi-logo-badge">
                    <span class="mlsapi-logo-icon">⌂</span>
                </div>
                <h1 class="mlsapi-modal-title" id="mlsapi-modal-title"><?php esc_html_e( 'MLS API Studio', 'mlsapi-studio' ); ?></h1>
            </div>
            <div class="mlsapi-header-right">
                <div class="mlsapi-file-meta" id="mlsapi-file-meta">
                    <span id="mlsapi-meta-filename"><?php esc_html_e( 'living-room.jpg', 'mlsapi-studio' ); ?></span>
                    <span class="mlsapi-meta-sep">·</span>
                    <span id="mlsapi-meta-dimensions"><?php esc_html_e( '2048×1536', 'mlsapi-studio' ); ?></span>
                </div>
                <button type="button" class="mlsapi-modal-close" id="mlsapi-modal-close" title="<?php esc_attr_e( 'Close', 'mlsapi-studio' ); ?>">
                    &times;
                </button>
            </div>
        </header>

        <!-- Main Body: Left Sidebar + Center Workspace -->
        <div class="mlsapi-modal-body">
            
            <!-- Left Tool Sidebar -->
            <aside class="mlsapi-tool-sidebar" aria-label="<?php esc_attr_e( 'AI Studio Operations', 'mlsapi-studio' ); ?>">
                <nav class="mlsapi-tool-nav">
                    <button type="button" class="mlsapi-tool-item active" data-tool="stage" data-title="Room Staging">
                        <?php esc_html_e( 'Room Staging', 'mlsapi-studio' ); ?>
                    </button>
                    <button type="button" class="mlsapi-tool-item" data-tool="twilight" data-title="Dusk / Sky">
                        <?php esc_html_e( 'Dusk / Sky', 'mlsapi-studio' ); ?>
                    </button>
                    <button type="button" class="mlsapi-tool-item" data-tool="declutter" data-title="Declutter">
                        <?php esc_html_e( 'Declutter', 'mlsapi-studio' ); ?>
                    </button>
                    <button type="button" class="mlsapi-tool-item" data-tool="empty" data-title="Empty Room">
                        <?php esc_html_e( 'Empty Room', 'mlsapi-studio' ); ?>
                    </button>
                    <button type="button" class="mlsapi-tool-item" data-tool="restyle" data-title="Change Style">
                        <?php esc_html_e( 'Change Style', 'mlsapi-studio' ); ?>
                    </button>
                    <button type="button" class="mlsapi-tool-item" data-tool="replace-furniture" data-title="Replace Furniture">
                        <?php esc_html_e( 'Replace Furniture', 'mlsapi-studio' ); ?>
                    </button>
                    <button type="button" class="mlsapi-tool-item" data-tool="wall-colors" data-title="Paint Color">
                        <?php esc_html_e( 'Paint Color', 'mlsapi-studio' ); ?>
                    </button>
                    <button type="button" class="mlsapi-tool-item" data-tool="replace-material" data-title="Replace Materials">
                        <?php esc_html_e( 'Replace Materials', 'mlsapi-studio' ); ?>
                    </button>
                    <button type="button" class="mlsapi-tool-item" data-tool="floorplan-3d" data-title="Blueprint to 3D">
                        <?php esc_html_e( 'Blueprint to 3D', 'mlsapi-studio' ); ?>
                    </button>
                    <button type="button" class="mlsapi-tool-item" data-tool="creatives" data-title="Ad Creatives">
                        <?php esc_html_e( 'Ad Creatives', 'mlsapi-studio' ); ?>
                    </button>
                    <button type="button" class="mlsapi-tool-item" data-tool="enhance-exterior" data-title="Enhance & 4K">
                        <?php esc_html_e( 'Enhance & 4K', 'mlsapi-studio' ); ?>
                    </button>
                </nav>

                <!-- Parameter Selection Boxes -->
                <div class="mlsapi-sidebar-params">
                    <!-- Design Style Param -->
                    <div class="mlsapi-param-group" id="mlsapi-group-style">
                        <label class="mlsapi-param-label" for="mlsapi-param-style"><?php esc_html_e( 'Design Style', 'mlsapi-studio' ); ?></label>
                        <div class="mlsapi-select-wrap">
                            <select id="mlsapi-param-style" class="mlsapi-dark-select">
                                <option value="modern" selected><?php esc_html_e( 'Modern', 'mlsapi-studio' ); ?></option>
                                <option value="scandinavian"><?php esc_html_e( 'Scandinavian', 'mlsapi-studio' ); ?></option>
                                <option value="luxury"><?php esc_html_e( 'Luxury', 'mlsapi-studio' ); ?></option>
                                <option value="coastal"><?php esc_html_e( 'Coastal', 'mlsapi-studio' ); ?></option>
                                <option value="farmhouse"><?php esc_html_e( 'Farmhouse', 'mlsapi-studio' ); ?></option>
                                <option value="japandi"><?php esc_html_e( 'Japandi', 'mlsapi-studio' ); ?></option>
                                <option value="mid_century_modern"><?php esc_html_e( 'Mid-Century Modern', 'mlsapi-studio' ); ?></option>
                                <option value="minimalist"><?php esc_html_e( 'Minimalist', 'mlsapi-studio' ); ?></option>
                                <option value="industrial"><?php esc_html_e( 'Industrial', 'mlsapi-studio' ); ?></option>
                                <option value="mediterranean"><?php esc_html_e( 'Mediterranean', 'mlsapi-studio' ); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Room Type Param -->
                    <div class="mlsapi-param-group" id="mlsapi-group-room">
                        <label class="mlsapi-param-label" for="mlsapi-param-room"><?php esc_html_e( 'Room Type', 'mlsapi-studio' ); ?></label>
                        <div class="mlsapi-select-wrap">
                            <select id="mlsapi-param-room" class="mlsapi-dark-select">
                                <option value="living_room" selected><?php esc_html_e( 'Living Room', 'mlsapi-studio' ); ?></option>
                                <option value="primary_bedroom"><?php esc_html_e( 'Primary Bedroom', 'mlsapi-studio' ); ?></option>
                                <option value="bedroom"><?php esc_html_e( 'Bedroom', 'mlsapi-studio' ); ?></option>
                                <option value="dining_room"><?php esc_html_e( 'Dining Room', 'mlsapi-studio' ); ?></option>
                                <option value="kitchen"><?php esc_html_e( 'Kitchen', 'mlsapi-studio' ); ?></option>
                                <option value="home_office"><?php esc_html_e( 'Home Office', 'mlsapi-studio' ); ?></option>
                                <option value="bathroom"><?php esc_html_e( 'Bathroom', 'mlsapi-studio' ); ?></option>
                                <option value="patio"><?php esc_html_e( 'Patio / Outdoor', 'mlsapi-studio' ); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Furniture to Swap (Replace Furniture) -->
                    <div class="mlsapi-param-group" id="mlsapi-group-furniture-scope" style="display: none;">
                        <label class="mlsapi-param-label" for="mlsapi-param-furniture-scope"><?php esc_html_e( 'Furnishings to Replace', 'mlsapi-studio' ); ?></label>
                        <div class="mlsapi-select-wrap">
                            <select id="mlsapi-param-furniture-scope" class="mlsapi-dark-select">
                                <option value="all" selected><?php esc_html_e( 'All Main Furnishings', 'mlsapi-studio' ); ?></option>
                                <option value="seating"><?php esc_html_e( 'Sofas & Living Seating', 'mlsapi-studio' ); ?></option>
                                <option value="dining"><?php esc_html_e( 'Dining Table & Chairs', 'mlsapi-studio' ); ?></option>
                                <option value="bedroom"><?php esc_html_e( 'Beds & Nightstands', 'mlsapi-studio' ); ?></option>
                                <option value="accents"><?php esc_html_e( 'Coffee Tables & Accents', 'mlsapi-studio' ); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Twilight Mode Param -->
                    <div class="mlsapi-param-group" id="mlsapi-group-twilight" style="display: none;">
                        <label class="mlsapi-param-label" for="mlsapi-param-twilight-mode"><?php esc_html_e( 'Dusk Conversion Mode', 'mlsapi-studio' ); ?></label>
                        <div class="mlsapi-select-wrap">
                            <select id="mlsapi-param-twilight-mode" class="mlsapi-dark-select">
                                <option value="day_to_dusk" selected><?php esc_html_e( 'Day to Twilight Dusk', 'mlsapi-studio' ); ?></option>
                                <option value="blue_sky_replace"><?php esc_html_e( 'Blue Sky Replacement', 'mlsapi-studio' ); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Target Surface (Replace Materials) -->
                    <div class="mlsapi-param-group" id="mlsapi-group-surface" style="display: none;">
                        <label class="mlsapi-param-label" for="mlsapi-param-surface"><?php esc_html_e( 'Target Surface', 'mlsapi-studio' ); ?></label>
                        <div class="mlsapi-select-wrap">
                            <select id="mlsapi-param-surface" class="mlsapi-dark-select">
                                <option value="flooring" selected><?php esc_html_e( 'Flooring / Hardwood', 'mlsapi-studio' ); ?></option>
                                <option value="countertops"><?php esc_html_e( 'Kitchen Countertops', 'mlsapi-studio' ); ?></option>
                                <option value="accent_wall"><?php esc_html_e( 'Accent Wall / Paneling', 'mlsapi-studio' ); ?></option>
                                <option value="backsplash"><?php esc_html_e( 'Kitchen Backsplash', 'mlsapi-studio' ); ?></option>
                                <option value="tiles"><?php esc_html_e( 'Bathroom Vanity & Tiles', 'mlsapi-studio' ); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Material Param (Replace Materials) -->
                    <div class="mlsapi-param-group" id="mlsapi-group-material" style="display: none;">
                        <label class="mlsapi-param-label" for="mlsapi-param-material"><?php esc_html_e( 'Replacement Material', 'mlsapi-studio' ); ?></label>
                        <div class="mlsapi-select-wrap">
                            <select id="mlsapi-param-material" class="mlsapi-dark-select">
                                <option value="white_oak_herringbone" selected><?php esc_html_e( 'White Oak Herringbone', 'mlsapi-studio' ); ?></option>
                                <option value="carrara_marble"><?php esc_html_e( 'Carrara White Marble', 'mlsapi-studio' ); ?></option>
                                <option value="dark_walnut"><?php esc_html_e( 'Dark Walnut Wood', 'mlsapi-studio' ); ?></option>
                                <option value="polished_concrete"><?php esc_html_e( 'Polished Concrete', 'mlsapi-studio' ); ?></option>
                                <option value="calacatta_quartz"><?php esc_html_e( 'Calacatta Gold Quartz', 'mlsapi-studio' ); ?></option>
                                <option value="slate_tile"><?php esc_html_e( 'Slate Gray Tile', 'mlsapi-studio' ); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Paint Output View (Wall Colors) -->
                    <div class="mlsapi-param-group" id="mlsapi-group-wall-mode" style="display: none;">
                        <label class="mlsapi-param-label" for="mlsapi-param-wall-mode"><?php esc_html_e( 'Output View', 'mlsapi-studio' ); ?></label>
                        <div class="mlsapi-select-wrap">
                            <select id="mlsapi-param-wall-mode" class="mlsapi-dark-select">
                                <option value="single" selected><?php esc_html_e( 'Single Color (Selected Below)', 'mlsapi-studio' ); ?></option>
                                <option value="grid"><?php esc_html_e( '3×3 Comparison Grid (All 9)', 'mlsapi-studio' ); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Wall Colors Param (Wall Colors) -->
                    <div class="mlsapi-param-group" id="mlsapi-group-wall" style="display: none;">
                        <label class="mlsapi-param-label" for="mlsapi-param-wall-color"><?php esc_html_e( 'Paint Color Choice', 'mlsapi-studio' ); ?></label>
                        <div class="mlsapi-select-wrap">
                            <select id="mlsapi-param-wall-color" class="mlsapi-dark-select">
                                <option value="Alabaster White" selected><?php esc_html_e( 'Alabaster White (#F2EFE8)', 'mlsapi-studio' ); ?></option>
                                <option value="Agreeable Gray"><?php esc_html_e( 'Agreeable Gray (#D1CBC1)', 'mlsapi-studio' ); ?></option>
                                <option value="Hale Navy"><?php esc_html_e( 'Hale Navy (#303A45)', 'mlsapi-studio' ); ?></option>
                                <option value="Sage Green"><?php esc_html_e( 'Sage Green (#9BA896)', 'mlsapi-studio' ); ?></option>
                                <option value="Terracotta Blush"><?php esc_html_e( 'Terracotta Blush (#C48A76)', 'mlsapi-studio' ); ?></option>
                                <option value="Charcoal Slate"><?php esc_html_e( 'Charcoal Slate (#40444B)', 'mlsapi-studio' ); ?></option>
                                <option value="Warm Taupe"><?php esc_html_e( 'Warm Taupe (#B5A795)', 'mlsapi-studio' ); ?></option>
                                <option value="Crisp Linen"><?php esc_html_e( 'Crisp Linen (#F7F5EE)', 'mlsapi-studio' ); ?></option>
                                <option value="Moody Forest"><?php esc_html_e( 'Moody Forest (#2B3D34)', 'mlsapi-studio' ); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Exterior Enhancement Param -->
                    <div class="mlsapi-param-group" id="mlsapi-group-exterior" style="display: none;">
                        <label class="mlsapi-param-label" for="mlsapi-param-exterior"><?php esc_html_e( 'Enhancement Focus', 'mlsapi-studio' ); ?></label>
                        <div class="mlsapi-select-wrap">
                            <select id="mlsapi-param-exterior" class="mlsapi-dark-select">
                                <option value="all_enhancements" selected><?php esc_html_e( 'Full Curb Appeal Polish', 'mlsapi-studio' ); ?></option>
                                <option value="green_grass"><?php esc_html_e( 'Lush Green Grass', 'mlsapi-studio' ); ?></option>
                                <option value="blue_sky"><?php esc_html_e( 'Bright Blue Sky', 'mlsapi-studio' ); ?></option>
                                <option value="clean_pool"><?php esc_html_e( 'Crystal Clear Pool', 'mlsapi-studio' ); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Primary Generate Button -->
                    <button type="button" id="mlsapi-generate-btn" class="mlsapi-btn-generate" disabled>
                        <span class="mlsapi-btn-sparkle">✦</span>
                        <span id="mlsapi-generate-btn-text"><?php esc_html_e( 'Generate asset', 'mlsapi-studio' ); ?></span>
                    </button>
                </div>
            </aside>

            <!-- Center Workspace Canvas -->
            <main class="mlsapi-workspace">
                
                <!-- Initial Dropzone State -->
                <div id="mlsapi-dropzone-state" class="mlsapi-dropzone-state">
                    <div class="mlsapi-dropzone-box">
                        <span class="dashicons dashicons-format-image mlsapi-dropzone-icon"></span>
                        <h3><?php esc_html_e( 'Select or Paste a Real Estate Photo', 'mlsapi-studio' ); ?></h3>
                        <p><?php esc_html_e( 'Pick from Media Library, drag & drop, or paste from clipboard (Ctrl+V).', 'mlsapi-studio' ); ?></p>
                        <div class="mlsapi-dropzone-actions">
                            <button type="button" id="mlsapi-select-media-btn" class="mlsapi-btn-accent">
                                <span class="dashicons dashicons-admin-media"></span> <?php esc_html_e( 'Select from Media Library', 'mlsapi-studio' ); ?>
                            </button>
                            <input type="file" id="mlsapi-file-input" accept="image/*" style="display:none;" />
                            <button type="button" id="mlsapi-browse-file-btn" class="mlsapi-btn-outline">
                                <?php esc_html_e( 'Upload File', 'mlsapi-studio' ); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Active Image View (Before / After Comparison Slider) -->
                <div id="mlsapi-canvas-state" class="mlsapi-canvas-state" style="display: none;">
                    <div class="mlsapi-canvas-card">
                        <div class="mlsapi-compare-container" id="mlsapi-compare-viewer">
                            <!-- Bottom Layer: After (Generated) Image -->
                            <div class="mlsapi-compare-layer mlsapi-compare-after-layer" id="mlsapi-layer-after" style="display: none;">
                                <img id="mlsapi-img-after" src="" alt="<?php esc_attr_e( 'AI Generated Photo', 'mlsapi-studio' ); ?>" />
                            </div>
                            
                            <!-- Top Layer: Before (Original) Image (clipped with clip-path) -->
                            <div class="mlsapi-compare-layer mlsapi-compare-before-layer" id="mlsapi-layer-before">
                                <img id="mlsapi-img-before" src="" alt="<?php esc_attr_e( 'Original Photo', 'mlsapi-studio' ); ?>" />
                            </div>

                            <!-- Pill Tags -->
                            <span class="mlsapi-compare-label mlsapi-label-before" id="mlsapi-label-before"><?php esc_html_e( 'Before', 'mlsapi-studio' ); ?></span>
                            <span class="mlsapi-compare-label mlsapi-label-after" id="mlsapi-label-after" style="display: none;"><?php esc_html_e( 'After', 'mlsapi-studio' ); ?></span>

                            <!-- Divider Line & Knob (displayed when comparison is active) -->
                            <div class="mlsapi-compare-line" id="mlsapi-compare-line" style="display: none; left: 50%;">
                                <div class="mlsapi-compare-knob">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 6l-6 6 6 6M15 6l6 6-6 6" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Native Range Slider on top for smooth mouse/touch drag -->
                            <input type="range" class="mlsapi-compare-range" id="mlsapi-compare-range" min="0" max="100" step="0.1" value="50" style="display: none;" aria-label="<?php esc_attr_e( 'Drag to compare before and after', 'mlsapi-studio' ); ?>" />
                        </div>

                        <!-- Processing Overlay Scoped to Canvas Viewport -->
                        <div id="mlsapi-processing-overlay" class="mlsapi-processing-overlay" style="display: none;">
                            <div class="mlsapi-spinner-wrap">
                                <div class="mlsapi-spinner"></div>
                                <h4 id="mlsapi-step-title"><?php esc_html_e( 'Generating asset...', 'mlsapi-studio' ); ?></h4>
                                <p id="mlsapi-step-desc"><?php esc_html_e( 'Synthesizing architectural staging on mlsapi.dev', 'mlsapi-studio' ); ?></p>
                                
                                <!-- Progress Bar in Overlay Dialog -->
                                <div class="mlsapi-dialog-progress">
                                    <div class="mlsapi-dialog-progress-track">
                                        <div class="mlsapi-dialog-progress-fill" id="mlsapi-overlay-progress-fill" style="width: 0%;"></div>
                                    </div>
                                    <div class="mlsapi-dialog-progress-meta">
                                        <span id="mlsapi-overlay-step-label"><?php esc_html_e( 'Initializing...', 'mlsapi-studio' ); ?></span>
                                        <span id="mlsapi-overlay-percent">0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Multiple Paint Swatches / Render Views Strip -->
                    <div class="mlsapi-swatches-strip" id="mlsapi-swatches-strip" style="display: none;">
                        <span class="mlsapi-swatches-label" id="mlsapi-swatches-label"><?php esc_html_e( 'Select Variation:', 'mlsapi-studio' ); ?></span>
                        <div class="mlsapi-swatches-list" id="mlsapi-swatches-list"></div>
                    </div>

                    <!-- Progress Bar Row -->
                    <div class="mlsapi-progress-row">
                        <span class="mlsapi-status-label" id="mlsapi-step-label"><?php esc_html_e( 'ready', 'mlsapi-studio' ); ?></span>
                        <div class="mlsapi-progress-track">
                            <div class="mlsapi-progress-fill" id="mlsapi-progress-fill" style="width: 0%;"></div>
                        </div>
                        <span class="mlsapi-status-pct" id="mlsapi-progress-percent">0%</span>
                    </div>

                    <!-- Footer Action Toolbar -->
                    <div class="mlsapi-workspace-footer">
                        <input type="hidden" id="mlsapi-output-format" value="webp" />

                        <!-- Action Buttons -->
                        <div class="mlsapi-action-buttons">
                            <button type="button" id="mlsapi-save-btn" class="mlsapi-btn-save">
                                <?php esc_html_e( 'Save as new', 'mlsapi-studio' ); ?>
                            </button>
                            <button type="button" id="mlsapi-replace-btn" class="mlsapi-btn-secondary" style="display: none;">
                                <?php esc_html_e( 'Replace original', 'mlsapi-studio' ); ?>
                            </button>
                            <button type="button" id="mlsapi-download-btn" class="mlsapi-btn-secondary">
                                <?php esc_html_e( 'Download', 'mlsapi-studio' ); ?>
                            </button>
                        </div>
                    </div>
                </div>

            </main>
        </div>

    </div>
</div>
