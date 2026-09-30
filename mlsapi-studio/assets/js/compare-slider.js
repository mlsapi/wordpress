/**
 * MLS API Interactive Before / After Split Slider
 */
(function($) {
    'use strict';

    window.MLSAPICompareSlider = {
        init: function() {
            var self = this;
            this.$container = $('#mlsapi-compare-viewer');
            this.$afterWrapper = $('#mlsapi-compare-after-wrapper');
            this.$handle = $('#mlsapi-compare-handle');
            this.$afterImg = $('#mlsapi-img-after');
            this.$beforeImg = $('#mlsapi-img-before');

            if (!this.$container.length) return;

            this.isDragging = false;
            this.bindEvents();
            this.setSplit(50);
        },

        bindEvents: function() {
            var self = this;

            this.$container.on('mousedown touchstart', function(e) {
                self.isDragging = true;
                self.updatePosition(e);
            });

            $(document).on('mousemove touchmove', function(e) {
                if (self.isDragging) {
                    self.updatePosition(e);
                }
            });

            $(document).on('mouseup touchend', function() {
                self.isDragging = false;
            });

            // Adjust after image width to match before image natural/rendered dimensions
            this.$beforeImg.on('load', function() {
                self.syncDimensions();
            });
            $(window).on('resize', function() {
                self.syncDimensions();
            });
        },

        syncDimensions: function() {
            var renderedWidth = this.$beforeImg.width();
            if (renderedWidth > 0) {
                this.$afterImg.css('width', renderedWidth + 'px');
            }
        },

        updatePosition: function(e) {
            var offset = this.$container.offset();
            if (!offset) return;

            var pageX = e.pageX || (e.originalEvent && e.originalEvent.touches && e.originalEvent.touches[0].pageX);
            if (typeof pageX === 'undefined') return;

            var x = pageX - offset.left;
            var width = this.$container.width();
            var percentage = Math.max(0, Math.min(100, (x / width) * 100));

            this.setSplit(percentage);
        },

        setSplit: function(percent) {
            this.$afterWrapper.css('width', percent + '%');
            this.$handle.css('left', percent + '%');
        },

        showAfterOnly: function() {
            this.setSplit(100);
        },

        showBeforeOnly: function() {
            this.setSplit(0);
        },

        reset: function() {
            this.setSplit(50);
            this.syncDimensions();
        }
    };

    $(document).ready(function() {
        window.MLSAPICompareSlider.init();
    });

})(jQuery);
