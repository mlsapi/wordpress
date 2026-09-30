/**
 * MLS API Studio Modal Controller
 * Coordinates editor lifecycle, image loading, UI state, pill selectors, and ribbon photos.
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
            isProcessing: false,
            ribbonLoaded: false
        },

        toolMeta: {
            'stage': {
                title: 'Stage room',
                desc: 'Furnish an empty room',
                btnText: 'Stage room',
                showWatermark: true
            },
            'restyle': {
                title: 'Restyle room',
                desc: 'Change interior aesthetic to another design theme',
                btnText: 'Restyle room',
                showWatermark: true
            },
            'empty': {
                title: 'Empty room',
                desc: 'Remove all furniture and decor to reveal bare architectural space',
                btnText: 'Empty room',
                showWatermark: true
            },
            'declutter': {
                title: 'Declutter room',
                desc: 'Remove tenant mess, boxes, and cables while keeping core furniture',
                btnText: 'Declutter room',
                showWatermark: true
            },
            'wall-colors': {
                title: 'Paint room',
                desc: 'Preview designer paint colors on interior walls',
                btnText: 'Paint room',
                showWatermark: false
            },
            'twilight': {
                title: 'Twilight conversion',
                desc: 'Convert daytime exteriors to warm twilight or replace washed-out skies',
                btnText: 'Apply Twilight',
                showWatermark: false
            },
            'enhance-exterior': {
                title: 'Curb appeal',
                desc: 'Enhance lawn, blue sky, pool water, and landscaping',
                btnText: 'Enhance curb appeal',
                showWatermark: false
            },
            'upscale': {
                title: 'Upscale photo',
                desc: 'Sharpen architectural details and enhance resolution to 4K',
                btnText: 'Upscale to 4K',
                showWatermark: false
            },
            'floorplan-3d': {
                title: '3D floor plan',
                desc: 'Convert 2D blueprints or sketches into 3D isometric cutaways',
                btnText: 'Render 3D plan',
                showWatermark: false
            }
        },

        init: function() {
            this.cacheDom();
            this.bindEvents();
        },

        cacheDom: function() {
            this.$backdrop           = $('#mlsapi-modal-backdrop');
            this.$modal              = $('#mlsapi-modal');
            this.$closeBtn           = $('#mlsapi-modal-close');
            this.$toolBtns           = $('.mlsapi-tool-btn');
            this.$dropzoneState      = $('#mlsapi-dropzone-state');
            this.$canvasState        = $('#mlsapi-canvas-state');
            this.$overlay            = $('#mlsapi-processing-overlay');
            this.$progressBar        = $('#mlsapi-progress-bar');
            this.$progressPercent    = $('#mlsapi-progress-percent');
            this.$stepTitle          = $('#mlsapi-step-title');
            this.$stepDesc           = $('#mlsapi-step-desc');
            this.$imgBefore          = $('#mlsapi-img-before');
            this.$imgAfter           = $('#mlsapi-img-after');
            this.$generateBtn        = $('#mlsapi-generate-btn');
            this.$generateBtnText    = $('#mlsapi-generate-btn-text');
            this.$saveBtn            = $('#mlsapi-save-btn');
            this.$replaceBtn         = $('#mlsapi-replace-btn');
            this.$downloadBtn        = $('#mlsapi-download-btn');
            this.$outputActions      = $('#mlsapi-output-actions');
            this.$selectMediaBtn     = $('#mlsapi-select-media-btn');
            this.$browseFileBtn      = $('#mlsapi-browse-file-btn');
            this.$fileInput          = $('#mlsapi-file-input');
            this.$toolTitle          = $('#mlsapi-current-tool-title');
            this.$toolDesc           = $('#mlsapi-current-tool-desc');
            this.$ribbonScroll       = $('#mlsapi-ribbon-scroll');
            this.$ribbonAddBtn       = $('#mlsapi-ribbon-add-btn');
            this.$watermarkBadge     = $('#mlsapi-watermark-badge');
            this.$watermarkNotice    = $('#mlsapi-compliance-notice');
            this.$watermarkCheckbox  = $('#mlsapi-include-watermark');
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
            this.$toolBtns.on('click', function() {
                var tool = $(this).data('tool');
                self.switchTool(tool);
            });

            // Style Pill Chips Selection
            $(document).on('click', '#mlsapi-style-pills .mlsapi-pill', function() {
                var $pill = $(this);
                $('#mlsapi-style-pills .mlsapi-pill').removeClass('active');
                $pill.addClass('active');
                $('#mlsapi-param-style').val($pill.data('style'));
            });

            // Twilight Pill Chips Selection
            $(document).on('click', '#mlsapi-twilight-pills .mlsapi-pill', function() {
                var $pill = $(this);
                $('#mlsapi-twilight-pills .mlsapi-pill').removeClass('active');
                $pill.addClass('active');
                $('#mlsapi-param-twilight-mode').val($pill.data('twilight'));
            });

            // Flooring Pill Chips Selection
            $(document).on('click', '#mlsapi-flooring-pills .mlsapi-pill', function() {
                var $pill = $(this);
                $('#mlsapi-flooring-pills .mlsapi-pill').removeClass('active');
                $pill.addClass('active');
                $('#mlsapi-param-flooring').val($pill.data('flooring'));
            });

            // Watermark toggle
            this.$watermarkCheckbox.on('change', function() {
                if ($(this).is(':checked')) {
                    self.$watermarkBadge.show();
                } else {
                    self.$watermarkBadge.hide();
                }
            });

            // Media pickers
            this.$selectMediaBtn.on('click', function() { self.openWordPressMediaPicker(); });
            this.$browseFileBtn.on('click', function() { self.$fileInput.trigger('click'); });
            this.$fileInput.on('change', function(e) { self.handleLocalFileUpload(e); });
            this.$ribbonAddBtn.on('click', function() { self.openWordPressMediaPicker(); });

            // Ribbon item click
            $(document).on('click', '.mlsapi-ribbon-thumb', function() {
                var $thumb = $(this);
                $('.mlsapi-ribbon-thumb').removeClass('active');
                $thumb.addClass('active');
                self.setImage($thumb.data('full-url'), $thumb.data('id'));
            });

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
        },

        open: function() {
            this.state.isOpen = true;
            this.$backdrop.fadeIn(150);
            $('body').addClass('mlsapi-modal-open');
            this.loadRibbonPhotos();
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

        loadRibbonPhotos: function() {
            var self = this;
            if (this.state.ribbonLoaded) return;

            $.ajax({
                url: mlsapi_vars.ajax_url,
                type: 'GET',
                dataType: 'json',
                data: {
                    action: 'mlsapi_get_recent_media',
                    nonce: mlsapi_vars.nonce
                }
            }).done(function(res) {
                if (res && res.success && res.data && res.data.images) {
                    self.state.ribbonLoaded = true;
                    self.$ribbonScroll.empty();

                    if (!res.data.images.length) {
                        self.$ribbonScroll.html('<div style="font-size:12px;color:#94a3b8;padding:10px;">No images in media library yet.</div>');
                        return;
                    }

                    res.data.images.forEach(function(img) {
                        var isActive = (self.state.sourceAttachmentId && self.state.sourceAttachmentId === img.id) ? ' active' : '';
                        var $thumb = $('<div class="mlsapi-ribbon-thumb' + isActive + '" data-id="' + img.id + '" data-full-url="' + img.full_url + '" title="' + (img.title || '') + '">' +
                            '<img src="' + img.thumb_url + '" alt="Photo" />' +
                        '</div>');
                        self.$ribbonScroll.append($thumb);
                    });
                }
            });
        },

        setImage: function(url, attachmentId) {
            this.state.originalImageUrl = url;
            this.state.sourceAttachmentId = attachmentId || null;
            this.state.generatedImageUrl = null;

            this.$imgBefore.attr('src', url);
            this.$imgAfter.attr('src', url);

            this.$dropzoneState.hide();
            this.$canvasState.show();
            this.$generateBtn.prop('disabled', false);

            if (this.state.sourceAttachmentId) {
                this.$replaceBtn.show();
                $('.mlsapi-ribbon-thumb').removeClass('active');
                $('.mlsapi-ribbon-thumb[data-id="' + this.state.sourceAttachmentId + '"]').addClass('active');
            } else {
                this.$replaceBtn.hide();
            }

            this.$outputActions.hide();
            this.$watermarkBadge.hide();

            if (window.MLSAPICompareSlider) {
                window.MLSAPICompareSlider.showBeforeOnly();
            }
        },

        resetToDropzone: function() {
            this.state.originalImageUrl = null;
            this.state.sourceAttachmentId = null;
            this.state.generatedImageUrl = null;

            this.$imgBefore.attr('src', '');
            this.$imgAfter.attr('src', '');

            this.$canvasState.hide();
            this.$dropzoneState.show();
            this.$outputActions.hide();
            this.$generateBtn.prop('disabled', true);
            $('.mlsapi-ribbon-thumb').removeClass('active');
        },

        switchTool: function(tool) {
            this.state.activeTool = tool;

            this.$toolBtns.removeClass('active');
            $('.mlsapi-tool-btn[data-tool="' + tool + '"]').addClass('active');

            var meta = this.toolMeta[tool] || { title: tool, desc: '', btnText: tool, showWatermark: false };
            this.$toolTitle.text(meta.title);
            this.$toolDesc.text(meta.desc);
            this.$generateBtnText.text(meta.btnText);

            // Watermark notice visibility
            if (meta.showWatermark) {
                this.$watermarkNotice.show();
                if (this.$watermarkCheckbox.is(':checked') && this.state.generatedImageUrl) {
                    this.$watermarkBadge.show();
                }
            } else {
                this.$watermarkNotice.hide();
                this.$watermarkBadge.hide();
            }

            // Show/hide matching parameter field groups
            $('.mlsapi-field-group').each(function() {
                var toolsAttr = $(this).data('tools');
                if (!toolsAttr) return;
                var toolsList = toolsAttr.split(',');
                if (toolsList.indexOf(tool) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
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
                    self.state.ribbonLoaded = false;
                    self.loadRibbonPhotos();
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
                photo_url: this.state.originalImageUrl
            };

            var tool = this.state.activeTool;

            if (tool === 'stage' || tool === 'restyle') {
                params.style = $('#mlsapi-param-style').val();
                params.room_type = $('#mlsapi-param-room').val();
            } else if (tool === 'twilight') {
                params.mode = $('#mlsapi-param-twilight-mode').val();
            } else if (tool === 'declutter') {
                params.room_type = $('#mlsapi-param-room').val();
            } else if (tool === 'empty') {
                params.room_type = $('#mlsapi-param-room').val();
                params.restore_flooring = $('#mlsapi-param-flooring').val();
            } else if (tool === 'enhance-exterior') {
                params.enhance_lawn = $('#mlsapi-curb-lawn').is(':checked');
                params.replace_sky = $('#mlsapi-curb-sky').is(':checked');
                params.clean_pool = $('#mlsapi-curb-pool').is(':checked');
            } else if (tool === 'floorplan-3d') {
                params.floorplan_image_url = params.photo_url;
                params.style = $('#mlsapi-param-style').val();
            }

            var notes = $('#mlsapi-param-notes').val();
            if (notes && notes.trim()) {
                params.custom_instructions = notes.trim();
            }

            if ($('#mlsapi-include-watermark').is(':checked')) {
                params.include_watermark = true;
                params.watermark_text = 'Virtually Staged';
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

            this.state.isProcessing = true;
            this.showProcessing('Initializing AI pipeline on mlsapi.dev...', 10);

            window.MLSAPIStudioClient.dispatchJob(tool, params, this.state.sourceAttachmentId)
                .done(function(jobData) {
                    self.state.currentJobId = jobData.job_id;
                    self.pollJobProgress(jobData.job_id);
                })
                .fail(function(err) {
                    self.hideProcessing();
                    alert('Error: ' + err);
                });
        },

        pollJobProgress: function(jobId) {
            var self = this;

            window.MLSAPIStudioClient.pollJobUntilComplete(jobId, function(progressData) {
                var pct = progressData.progress || 25;
                var step = progressData.step || 'Rendering real estate visual...';
                self.updateProcessing(step, pct);
            }).done(function(result) {
                self.hideProcessing();
                self.displayResult(result);
            }).fail(function(err) {
                self.hideProcessing();
                alert('Job Failed: ' + err);
            });
        },

        displayResult: function(result) {
            var outUrl = result.staged_photo_url ||
                         result.enhanced_photo_url ||
                         result.decluttered_photo_url ||
                         result.empty_photo_url ||
                         result.retyped_photo_url ||
                         result.updated_room_photo_url ||
                         result.render_3d_url ||
                         result.upscaled_image_url ||
                         result.image_url;

            if (!outUrl) {
                alert('AI job succeeded, but no output image URL was returned.');
                return;
            }

            this.state.generatedImageUrl = outUrl;
            this.$imgAfter.attr('src', outUrl);

            // Watermark badge check
            if (this.toolMeta[this.state.activeTool]?.showWatermark && this.$watermarkCheckbox.is(':checked')) {
                this.$watermarkBadge.show();
            } else {
                this.$watermarkBadge.hide();
            }

            // Reveal save output panel
            this.$outputActions.fadeIn(200);

            if (window.MLSAPICompareSlider) {
                window.MLSAPICompareSlider.reset();
            }
        },

        saveResult: function(replaceOriginal) {
            var self = this;
            if (!this.state.generatedImageUrl) return;

            var $btn = replaceOriginal ? this.$replaceBtn : this.$saveBtn;
            var originalText = $btn.text();
            $btn.prop('disabled', true).text(replaceOriginal ? mlsapi_vars.strings.replacing : mlsapi_vars.strings.saving);

            window.MLSAPIStudioClient.saveImage({
                imageUrl: this.state.generatedImageUrl,
                replaceAttachmentId: replaceOriginal ? this.state.sourceAttachmentId : null,
                format: $('#mlsapi-output-format').val(),
                jobId: this.state.currentJobId,
                operation: this.state.activeTool
            }).done(function(res) {
                alert(res.message || (replaceOriginal ? mlsapi_vars.strings.success_replaced : mlsapi_vars.strings.success_saved));
                $btn.prop('disabled', false).text(originalText);
                self.state.ribbonLoaded = false;
                self.loadRibbonPhotos();
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

        showProcessing: function(stepText, percent) {
            this.$stepTitle.text(mlsapi_vars.strings.processing);
            this.$stepDesc.text(stepText || '');
            this.updateProgressBar(percent || 15);
            this.$overlay.fadeIn(150);
        },

        updateProcessing: function(stepText, percent) {
            this.$stepDesc.text(stepText);
            this.updateProgressBar(percent);
        },

        updateProgressBar: function(percent) {
            var pct = Math.max(5, Math.min(100, Math.round(percent)));
            this.$progressBar.css('width', pct + '%');
            this.$progressPercent.text(pct + '%');
        },

        hideProcessing: function() {
            this.state.isProcessing = false;
            this.$overlay.fadeOut(150);
        }
    };

    $(document).ready(function() {
        window.MLSAPIModal.init();
    });

})(jQuery);
