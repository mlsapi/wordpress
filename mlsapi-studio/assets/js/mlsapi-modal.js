/**
 * MLS API Studio Modal Controller
 * Coordinates editor lifecycle, image loading, UI state, tool selection, and job progress.
 */
(function($) {
    'use strict';

    window.MLSAPIModal = {
        state: {
            isOpen: false,
            sourceAttachmentId: null,
            originalImageUrl: null,
            generatedImageUrl: null,
            currentJobId: null,
            activeTool: 'stage',
            isProcessing: false
        },

        init: function() {
            this.cacheDom();
            this.bindEvents();
        },

        cacheDom: function() {
            this.$backdrop           = $('#mlsapi-modal-backdrop');
            this.$modal              = $('#mlsapi-modal');
            this.$closeBtn           = $('#mlsapi-modal-close');
            this.$toolItems          = $('.mlsapi-tool-item');
            this.$dropzoneState      = $('#mlsapi-dropzone-state');
            this.$canvasState        = $('#mlsapi-canvas-state');
            this.$overlay            = $('#mlsapi-processing-overlay');
            this.$overlayProgressFill    = $('#mlsapi-overlay-progress-fill');
            this.$overlayProgressPercent = $('#mlsapi-overlay-percent');
            this.$overlayStepLabel       = $('#mlsapi-overlay-step-label');
            this.$progressFill       = $('#mlsapi-progress-fill');
            this.$progressPercent    = $('#mlsapi-progress-percent');
            this.$stepLabel          = $('#mlsapi-step-label');
            this.$imgBefore          = $('#mlsapi-img-before');
            this.$imgAfter           = $('#mlsapi-img-after');
            this.$generateBtn        = $('#mlsapi-generate-btn');
            this.$generateBtnText    = $('#mlsapi-generate-btn-text');
            this.$saveBtn            = $('#mlsapi-save-btn');
            this.$replaceBtn         = $('#mlsapi-replace-btn');
            this.$downloadBtn        = $('#mlsapi-download-btn');
            this.$selectMediaBtn     = $('#mlsapi-select-media-btn');
            this.$browseFileBtn      = $('#mlsapi-browse-file-btn');
            this.$fileInput          = $('#mlsapi-file-input');
            this.$metaFilename       = $('#mlsapi-meta-filename');
            this.$metaDimensions     = $('#mlsapi-meta-dimensions');
            this.$paramBoxes         = $('#mlsapi-param-boxes');
            this.$twilightBox        = $('#mlsapi-twilight-box');
        },

        bindEvents: function() {
            var self = this;

            // Modal open/close
            this.$closeBtn.on('click', function() { self.close(); });
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && self.state.isOpen && !self.state.isProcessing) {
                    self.close();
                }
            });

            // Tool switching
            $(document).on('click', '.mlsapi-tool-item', function() {
                var tool = $(this).data('tool');
                self.switchTool(tool);
            });

            // Format Segmented Buttons
            $(document).on('click', '.mlsapi-seg-btn', function() {
                $('.mlsapi-seg-btn').removeClass('active');
                $(this).addClass('active');
                $('#mlsapi-output-format').val($(this).data('format'));
            });

            // Media pickers
            this.$selectMediaBtn.on('click', function() { self.openWordPressMediaPicker(); });
            this.$browseFileBtn.on('click', function() { self.$fileInput.trigger('click'); });
            this.$fileInput.on('change', function(e) { self.handleLocalFileUpload(e); });

            // Clipboard paste
            $(document).on('paste', function(e) {
                if (self.state.isOpen) {
                    self.handleClipboardPaste(e);
                }
            });

            // Primary Actions
            this.$generateBtn.on('click', function() { self.triggerGenerate(); });
            this.$saveBtn.on('click', function() { self.saveResult(false); });
            this.$replaceBtn.on('click', function() { self.saveResult(true); });
            this.$downloadBtn.on('click', function() { self.downloadResult(); });

            // Wall colors output mode switch
            $(document).on('change', '#mlsapi-param-wall-mode', function() {
                if ($(this).val() === 'grid') {
                    $('#mlsapi-group-wall').hide();
                } else {
                    $('#mlsapi-group-wall').show();
                }
            });
        },

        open: function() {
            this.state.isOpen = true;
            this.$backdrop.fadeIn(150);
            $('body').addClass('mlsapi-modal-open');
        },

        close: function() {
            this.state.isOpen = false;
            this.$backdrop.fadeOut(150);
            $('body').removeClass('mlsapi-modal-open');
        },

        openWithAttachment: function(attachmentId, imageUrl) {
            this.open();
            this.setImage(imageUrl, attachmentId);
        },

        setImage: function(url, attachmentId) {
            var self = this;
            this.state.originalImageUrl = url;
            this.state.sourceAttachmentId = attachmentId || null;
            this.state.generatedImageUrl = null;

            // Extract filename from URL
            var filename = 'photo.jpg';
            if (url) {
                var cleanUrl = url.split('?')[0];
                filename = cleanUrl.substring(cleanUrl.lastIndexOf('/') + 1) || 'photo.jpg';
            }
            this.$metaFilename.text(filename);

            // Read natural image dimensions
            var tempImg = new Image();
            tempImg.onload = function() {
                self.$metaDimensions.text(this.naturalWidth + '×' + this.naturalHeight);
            };
            tempImg.src = url;

            this.$dropzoneState.hide();
            this.$canvasState.show();
            this.$generateBtn.prop('disabled', false);

            if (this.state.sourceAttachmentId) {
                this.$replaceBtn.show();
            } else {
                this.$replaceBtn.hide();
            }

            // Set initial progress
            this.updateProgressBar(0, 'ready');

            if (window.MLSAPICompareSlider) {
                window.MLSAPICompareSlider.showSingleImage(url, 'Before');
            }
        },

        resetToDropzone: function() {
            this.state.originalImageUrl = null;
            this.state.sourceAttachmentId = null;
            this.state.generatedImageUrl = null;

            this.$imgBefore.attr('src', '');
            this.$imgAfter.attr('src', '');
            this.$metaFilename.text('');
            this.$metaDimensions.text('');

            this.$canvasState.hide();
            this.$dropzoneState.show();
            this.$generateBtn.prop('disabled', true);
            $('#mlsapi-swatches-strip').hide();
            $('#mlsapi-swatches-list').empty();
        },

        getOperationInfo: function(tool) {
            var ops = {
                'stage': {
                    title: 'Virtually Staging Room...',
                    desc: 'Furnishing space with AI architectural staging on mlsapi.dev'
                },
                'furnish': {
                    title: 'Furnishing Vacant Room...',
                    desc: 'Synthesizing furniture according to chosen style on mlsapi.dev'
                },
                'twilight': {
                    title: 'Converting to Dusk / Twilight...',
                    desc: 'Transforming daytime photo into dusk twilight on mlsapi.dev'
                },
                'declutter': {
                    title: 'Decluttering Room...',
                    desc: 'Erasing personal items, boxes, and clutter on mlsapi.dev'
                },
                'empty': {
                    title: 'Emptying Room...',
                    desc: 'Removing furniture and restoring clean architectural space on mlsapi.dev'
                },
                'restyle': {
                    title: 'Restyling Interior...',
                    desc: 'Re-imagining interior decor with selected design style on mlsapi.dev'
                },
                'replace-furniture': {
                    title: 'Replacing Furniture...',
                    desc: 'Swapping furnishings with selected style on mlsapi.dev'
                },
                'wall-colors': {
                    title: 'Applying Wall Paint Colors...',
                    desc: 'Synthesizing designer wall paint variations on mlsapi.dev'
                },
                'replace-material': {
                    title: 'Replacing Surface Materials...',
                    desc: 'Rendering realistic architectural textures and materials on mlsapi.dev'
                },
                'floorplan-3d': {
                    title: 'Rendering 3D Floorplan...',
                    desc: 'Synthesizing 3D dollhouse model from blueprint on mlsapi.dev'
                },
                'creatives': {
                    title: 'Generating Ad Creatives...',
                    desc: 'Designing professional social marketing creatives on mlsapi.dev'
                },
                'enhance-exterior': {
                    title: 'Enhancing Photo...',
                    desc: 'Polishing curb appeal, skies, landscaping, and pools on mlsapi.dev'
                }
            };
            return ops[tool] || {
                title: 'Generating asset...',
                desc: 'Processing AI operation on mlsapi.dev'
            };
        },

        switchTool: function(tool) {
            this.state.activeTool = tool;

            $('.mlsapi-tool-item').removeClass('active');
            $('.mlsapi-tool-item[data-tool="' + tool + '"]').addClass('active');

            // Hide all parameter groups
            $('.mlsapi-param-group').hide();
            $('#mlsapi-swatches-strip').hide();

            // Display options tailored to active operation
            if (tool === 'stage' || tool === 'furnish') {
                $('#mlsapi-group-style').show();
                $('#mlsapi-group-room').show();
            } else if (tool === 'restyle') {
                $('#mlsapi-group-style').show();
                $('#mlsapi-group-room').show();
            } else if (tool === 'replace-furniture') {
                $('#mlsapi-group-style').show();
                $('#mlsapi-group-furniture-scope').show();
            } else if (tool === 'declutter' || tool === 'empty') {
                $('#mlsapi-group-room').show();
            } else if (tool === 'twilight') {
                $('#mlsapi-group-twilight').show();
            } else if (tool === 'replace-material') {
                $('#mlsapi-group-surface').show();
                $('#mlsapi-group-material').show();
            } else if (tool === 'wall-colors') {
                $('#mlsapi-group-wall-mode').show();
                if ($('#mlsapi-param-wall-mode').val() !== 'grid') {
                    $('#mlsapi-group-wall').show();
                }
            } else if (tool === 'enhance-exterior') {
                $('#mlsapi-group-exterior').show();
            } else if (tool === 'floorplan-3d') {
                $('#mlsapi-group-style').show();
            }
        },

        openWordPressMediaPicker: function() {
            var self = this;
            if (wp && wp.media) {
                var frame = wp.media({
                    title: 'Select Real Estate Photo',
                    button: { text: 'Use in MLS Studio' },
                    multiple: false,
                    library: { type: 'image' }
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    self.setImage(attachment.url, attachment.id);
                });

                frame.open();
            }
        },

        handleLocalFileUpload: function(e) {
            var file = e.target.files && e.target.files[0];
            if (!file) return;

            var reader = new FileReader();
            var self = this;
            reader.onload = function(evt) {
                self.setImage(evt.target.result, null);
            };
            reader.readAsDataURL(file);
        },

        handleClipboardPaste: function(e) {
            var items = (e.clipboardData || e.originalEvent.clipboardData).items;
            if (!items) return;

            for (var i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    var blob = items[i].getAsFile();
                    var reader = new FileReader();
                    var self = this;
                    reader.onload = function(evt) {
                        self.setImage(evt.target.result, null);
                    };
                    reader.readAsDataURL(blob);
                    break;
                }
            }
        },

        collectParams: function() {
            var params = {
                photo_url: this.state.originalImageUrl,
                format: $('#mlsapi-output-format').val() || 'webp'
            };

            var tool = this.state.activeTool;

            if (tool === 'stage' || tool === 'furnish' || tool === 'restyle') {
                params.style = $('#mlsapi-param-style').val();
                params.room_type = $('#mlsapi-param-room').val();
            } else if (tool === 'replace-furniture') {
                params.style = $('#mlsapi-param-style').val();
                params.furniture_scope = $('#mlsapi-param-furniture-scope').val();
            } else if (tool === 'twilight') {
                params.mode = $('#mlsapi-param-twilight-mode').val();
            } else if (tool === 'declutter' || tool === 'empty') {
                params.room_type = $('#mlsapi-param-room').val();
            } else if (tool === 'replace-material') {
                params.surface = $('#mlsapi-param-surface').val();
                params.material = $('#mlsapi-param-material').val();
            } else if (tool === 'wall-colors') {
                var wallMode = $('#mlsapi-param-wall-mode').val();
                var colorName = $('#mlsapi-param-wall-color').val();
                params.mode = wallMode;
                params.color = colorName;
                if (wallMode === 'single') {
                    params.custom_colors = [{ name: colorName, hex: '' }];
                }
            } else if (tool === 'enhance-exterior') {
                params.preset = $('#mlsapi-param-exterior').val();
            } else if (tool === 'floorplan-3d') {
                params.floorplan_image_url = params.photo_url;
                params.style = $('#mlsapi-param-style').val();
            }

            return params;
        },

        triggerGenerate: function() {
            var self = this;

            if (!mlsapi_vars.is_configured) {
                alert(mlsapi_vars.strings.not_configured);
                window.location.href = mlsapi_vars.settings_url;
                return;
            }

            if (!this.state.originalImageUrl) {
                alert('Please select an image first.');
                return;
            }

            var tool = this.state.activeTool;
            var params = this.collectParams();

            // Set endpoint-specific loading message
            var opInfo = this.getOperationInfo(tool);
            $('#mlsapi-step-title').text(opInfo.title);
            $('#mlsapi-step-desc').text(opInfo.desc);

            this.state.isProcessing = true;
            this.$generateBtn.prop('disabled', true);
            this.updateProgressBar(15, 'initializing');
            this.showOverlay();

            window.MLSAPIStudioClient.dispatchJob(tool, params, this.state.sourceAttachmentId)
                .done(function(jobData) {
                    self.state.currentJobId = jobData.job_id;
                    self.pollJobProgress(jobData.job_id);
                })
                .fail(function(err) {
                    self.hideOverlay();
                    self.state.isProcessing = false;
                    self.$generateBtn.prop('disabled', false);
                    var msg = err;
                    if (err && (typeof err === 'string') && (err.indexOf('API key') !== -1 || err.indexOf('UNAUTHORIZED') !== -1 || err.indexOf('401') !== -1 || err.indexOf('masked') !== -1)) {
                        msg = 'Authentication Error: ' + err + '\n\nPlease open MLS Studio → Settings & Billing and verify your API key. Make sure to click Reveal in mlsapi.dev and copy the full unmasked secret key (starts with sk_live_ or sk_test_).';
                    }
                    alert(msg);
                });
        },

        pollJobProgress: function(jobId) {
            var self = this;

            window.MLSAPIStudioClient.pollJobUntilComplete(jobId, function(progressData) {
                var pct = progressData.progress || 35;
                var step = progressData.step || 'processing';
                self.updateProgressBar(pct, step);

                // Dynamically update overlay step description
                if (progressData.step) {
                    var humanStep = progressData.step.replace(/_/g, ' ');
                    $('#mlsapi-step-desc').text(humanStep.charAt(0).toUpperCase() + humanStep.slice(1) + ' on mlsapi.dev');
                }
            }).done(function(result) {
                self.hideOverlay();
                self.state.isProcessing = false;
                self.$generateBtn.prop('disabled', false);
                self.updateProgressBar(100, 'completed');
                self.displayResult(result);
            }).fail(function(err) {
                self.hideOverlay();
                self.state.isProcessing = false;
                self.$generateBtn.prop('disabled', false);
                self.updateProgressBar(100, 'failed');
                alert('Job Failed: ' + err);
            });
        },

        displayResult: function(result) {
            var outUrl = null;
            var tool = this.state.activeTool;

            if (!result) {
                alert('Job completed, but received empty response payload.');
                return;
            }

            // In case result is a direct URL string
            if (typeof result === 'string' && (result.indexOf('http') === 0 || result.indexOf('data:image') === 0)) {
                outUrl = result;
            } else if (tool === 'wall-colors') {
                var wallMode = $('#mlsapi-param-wall-mode').val() || 'single';
                var chosenColor = $('#mlsapi-param-wall-color').val();

                if (wallMode === 'grid' && result.comparison_grid_3x3_url) {
                    outUrl = result.comparison_grid_3x3_url;
                    $('#mlsapi-swatches-strip').hide();
                } else if (result.swatch_results && result.swatch_results.length > 0) {
                    // Match selected single color swatch
                    var match = null;
                    if (chosenColor) {
                        for (var i = 0; i < result.swatch_results.length; i++) {
                            if (result.swatch_results[i].color_name && 
                                result.swatch_results[i].color_name.toLowerCase().indexOf(chosenColor.toLowerCase()) !== -1) {
                                match = result.swatch_results[i];
                                break;
                            }
                        }
                    }
                    var selectedSwatch = match || result.swatch_results[0];
                    outUrl = selectedSwatch.image_url;

                    // Render interactive swatches switcher strip
                    this.renderSwatchBar(result.swatch_results, outUrl);
                } else {
                    outUrl = result.comparison_grid_3x3_url || result.image_url;
                }
            } else if (tool === 'floorplan-3d') {
                outUrl = result.isometric_3d_dollhouse_url ||
                         result.thumbnail_url ||
                         result.render_3d_url ||
                         (result.room_renders && result.room_renders[0] ? result.room_renders[0].image_url : null) ||
                         result.image_url;

                // Render variation chips if dollhouse and room renders exist
                var rendersList = [];
                if (result.isometric_3d_dollhouse_url) {
                    rendersList.push({
                        label: '3D Dollhouse',
                        image_url: result.isometric_3d_dollhouse_url,
                        icon: 'dashicons-admin-home'
                    });
                }
                if (result.room_renders && result.room_renders.length > 0) {
                    result.room_renders.forEach(function(r) {
                        if (r.image_url) {
                            rendersList.push({
                                label: r.room_name || 'Room Render',
                                image_url: r.image_url
                            });
                        }
                    });
                }

                if (rendersList.length > 1) {
                    this.renderVariationChips(rendersList, outUrl, 'Select 3D View / Room:');
                } else {
                    $('#mlsapi-swatches-strip').hide();
                }
            } else {
                $('#mlsapi-swatches-strip').hide();
                outUrl = result.staged_photo_url ||
                         result.enhanced_photo_url ||
                         result.decluttered_photo_url ||
                         result.empty_photo_url ||
                         result.retyped_photo_url ||
                         result.updated_room_photo_url ||
                         result.isometric_3d_dollhouse_url ||
                         result.render_3d_url ||
                         result.thumbnail_url ||
                         (result.room_renders && result.room_renders[0] ? result.room_renders[0].image_url : null) ||
                         result.upscaled_image_url ||
                         result.comparison_grid_3x3_url ||
                         result.image_url ||
                         result.output_url ||
                         result.url ||
                         result.photo_url;
            }

            if (!outUrl) {
                alert('AI job succeeded, but no output image URL was returned.');
                return;
            }

            this.state.generatedImageUrl = outUrl;

            var toolTitle = $('.mlsapi-tool-item.active').data('title') || 'After';
            if (tool === 'floorplan-3d') {
                toolTitle = '3D Dollhouse';
            }

            if (window.MLSAPICompareSlider) {
                window.MLSAPICompareSlider.enableComparison(this.state.originalImageUrl, outUrl, toolTitle);
            }
        },

        renderVariationChips: function(items, currentUrl, labelText) {
            var self = this;
            var $strip = $('#mlsapi-swatches-strip');
            var $list = $('#mlsapi-swatches-list');
            var $label = $('#mlsapi-swatches-label');

            if ($label.length && labelText) {
                $label.text(labelText);
            }
            $list.empty();

            items.forEach(function(item) {
                if (!item.image_url) return;
                var isActive = (item.image_url === currentUrl);
                var $chip = $('<button type="button" class="mlsapi-swatch-chip' + (isActive ? ' active' : '') + '"></button>');
                if (item.hex) {
                    $chip.append('<span class="mlsapi-swatch-dot" style="background-color: ' + item.hex + ';"></span>');
                } else if (item.icon) {
                    $chip.append('<span class="dashicons ' + item.icon + '" style="font-size: 13px; width: 13px; height: 13px; line-height: 13px; vertical-align: middle;"></span>');
                }
                $chip.append('<span>' + (item.label || 'View') + '</span>');

                $chip.on('click', function() {
                    $('.mlsapi-swatch-chip').removeClass('active');
                    $(this).addClass('active');
                    self.state.generatedImageUrl = item.image_url;
                    var viewTitle = item.label || '3D Render';
                    if (window.MLSAPICompareSlider) {
                        window.MLSAPICompareSlider.enableComparison(self.state.originalImageUrl, item.image_url, viewTitle);
                    }
                });

                $list.append($chip);
            });

            $strip.show();
        },

        renderSwatchBar: function(swatches, currentUrl) {
            var items = swatches.map(function(s) {
                return {
                    label: s.color_name || 'Swatch',
                    image_url: s.image_url,
                    hex: s.hex || null
                };
            });
            this.renderVariationChips(items, currentUrl, 'Select Paint Variation:');
        },

        saveResult: function(replaceOriginal) {
            var self = this;
            if (!this.state.generatedImageUrl) return;

            var $btn = replaceOriginal ? this.$replaceBtn : this.$saveBtn;
            var originalText = $btn.text();
            $btn.prop('disabled', true).text(replaceOriginal ? 'Replacing...' : 'Saving...');

            window.MLSAPIStudioClient.saveImage({
                imageUrl: this.state.generatedImageUrl,
                replaceAttachmentId: replaceOriginal ? this.state.sourceAttachmentId : null,
                format: $('#mlsapi-output-format').val(),
                jobId: this.state.currentJobId,
                operation: this.state.activeTool
            }).done(function(res) {
                alert(res.message || 'Image saved successfully!');
                $btn.prop('disabled', false).text(originalText);
            }).fail(function(err) {
                alert('Error saving image: ' + err);
                $btn.prop('disabled', false).text(originalText);
            });
        },

        downloadResult: function() {
            if (!this.state.generatedImageUrl) return;
            var a = document.createElement('a');
            a.href = this.state.generatedImageUrl;
            a.download = 'mls-studio-' + this.state.activeTool + '-' + Date.now();
            a.target = '_blank';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        },

        updateProgressBar: function(percent, stepText) {
            var pct = Math.max(0, Math.min(100, Math.round(percent)));
            this.$progressFill.css('width', pct + '%');
            this.$progressPercent.text(pct + '%');
            if (this.$overlayProgressFill && this.$overlayProgressFill.length) {
                this.$overlayProgressFill.css('width', pct + '%');
            }
            if (this.$overlayProgressPercent && this.$overlayProgressPercent.length) {
                this.$overlayProgressPercent.text(pct + '%');
            }
            if (stepText) {
                var cleanStep = stepText.replace(/_/g, ' ');
                this.$stepLabel.text(cleanStep);
                if (this.$overlayStepLabel && this.$overlayStepLabel.length) {
                    this.$overlayStepLabel.text(cleanStep.charAt(0).toUpperCase() + cleanStep.slice(1));
                }
            }
        },

        showOverlay: function() {
            this.$overlay.addClass('active').show();
        },

        hideOverlay: function() {
            this.$overlay.removeClass('active').hide();
        }
    };

    $(document).ready(function() {
        window.MLSAPIModal.init();
    });

})(jQuery);
