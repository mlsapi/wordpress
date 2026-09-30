/**
 * MLS API Interactive Before / After Split Slider
 * Uses CSS clip-path for pixel-perfect alignment and buttery smooth range interaction.
 */
(function($) {
    'use strict';

    window.MLSAPICompareSlider = {
        init: function() {
            var self = this;
            this.$container   = $('#mlsapi-compare-viewer');
            this.$layerBefore = $('#mlsapi-layer-before');
            this.$layerAfter  = $('#mlsapi-layer-after');
            this.$imgBefore   = $('#mlsapi-img-before');
            this.$imgAfter    = $('#mlsapi-img-after');
            this.$labelBefore = $('#mlsapi-label-before');
            this.$labelAfter  = $('#mlsapi-label-after');
            this.$line        = $('#mlsapi-compare-line');
            this.$range       = $('#mlsapi-compare-range');

            if (!this.$container.length) return;

            this.bindEvents();
            this.setSplit(50);
        },

        bindEvents: function() {
            var self = this;

            // Native range input listener (instant, 60fps hardware accelerated)
            this.$range.on('input change', function() {
                var val = parseFloat($(this).val());
                self.setSplit(val);
            });

            // Container click/drag fallback
            this.isDragging = false;

            this.$container.on('mousedown touchstart', function(e) {
                if (self.$range.is(':visible') && e.target === self.$range[0]) return;
                self.isDragging = true;
                self.updateFromEvent(e);
            });

            $(document).on('mousemove touchmove', function(e) {
                if (self.isDragging) {
                    self.updateFromEvent(e);
                }
            });

            $(document).on('mouseup touchend', function() {
                self.isDragging = false;
            });
        },

        updateFromEvent: function(e) {
            var offset = this.$container.offset();
            if (!offset) return;

            var pageX = e.pageX || (e.originalEvent && e.originalEvent.touches && e.originalEvent.touches[0].pageX);
            if (typeof pageX === 'undefined') return;

            var x = pageX - offset.left;
            var width = this.$container.width();
            if (width <= 0) return;

            var percentage = Math.max(0, Math.min(100, (x / width) * 100));
            this.setSplit(percentage);
        },

        setSplit: function(percent) {
            var pct = Math.max(0, Math.min(100, percent));
            // Before image (top layer) is clipped from the right so left side shows Before, right side reveals After:
            this.$layerBefore.css({
                'clip-path': 'inset(0 calc(100% - ' + pct + '%) 0 0)',
                '-webkit-clip-path': 'inset(0 calc(100% - ' + pct + '%) 0 0)'
            });
            this.$line.css('left', pct + '%');
            this.$range.val(pct);
        },

        showSingleImage: function(imageUrl, labelText) {
            this.$imgBefore.attr('src', imageUrl);
            this.$imgAfter.attr('src', '');
            this.$layerBefore.css({
                'clip-path': 'none',
                '-webkit-clip-path': 'none'
            });
            this.$layerAfter.hide();
            this.$labelAfter.hide();
            this.$line.hide();
            this.$range.hide();
            this.$labelBefore.text(labelText || 'Before').show();
        },

        enableComparison: function(beforeUrl, afterUrl, afterLabelText) {
            this.$imgBefore.attr('src', beforeUrl);
            this.$imgAfter.attr('src', afterUrl);
            this.$layerAfter.show();
            this.$labelAfter.text(afterLabelText || 'After').show();
            this.$labelBefore.text('Before').show();
            this.$line.show();
            this.$range.show();
            this.setSplit(50);
        },

        reset: function() {
            this.setSplit(50);
        }
    };

    $(document).ready(function() {
        window.MLSAPICompareSlider.init();
    });

})(jQuery);
