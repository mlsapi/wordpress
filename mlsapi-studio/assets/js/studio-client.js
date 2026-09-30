/**
 * MLS API Studio Client
 * Dispatches async AI operations and polls job status until completion.
 */
(function($) {
    'use strict';

    window.MLSAPIStudioClient = {
        
        /**
         * Dispatch an async AI studio job
         *
         * @param {string} operation stage, twilight, declutter, etc.
         * @param {object} params Payload fields
         * @param {number|null} sourceAttachmentId WP attachment ID if originating from library
         * @returns {Promise}
         */
        dispatchJob: function(operation, params, sourceAttachmentId) {
            var data = {
                action: 'mlsapi_dispatch_studio_job',
                nonce: mlsapi_vars.nonce,
                operation: operation,
                params: JSON.stringify(params)
            };

            if (sourceAttachmentId) {
                data.source_attachment_id = sourceAttachmentId;
            }

            return $.ajax({
                url: mlsapi_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: data
            }).then(function(response) {
                if (response && response.success) {
                    return response.data;
                } else {
                    var errorMsg = (response && response.data && response.data.message) ? response.data.message : mlsapi_vars.strings.error_generic;
                    return $.Deferred().reject(errorMsg).promise();
                }
            });
        },

        /**
         * Poll job status until completed or failed
         *
         * @param {string} jobId
         * @param {function} onProgress Callback receiving { progress, step, status }
         * @returns {Promise} Resolves with final result object
         */
        pollJobUntilComplete: function(jobId, onProgress) {
            var deferred = $.Deferred();
            var startTime = Date.now();
            var maxWaitMs = 120000; // 2 minutes max
            var pollIntervalMs = 2500;

            function check() {
                if (Date.now() - startTime > maxWaitMs) {
                    deferred.reject('Job timed out after 2 minutes. Please check your MLS API dashboard.');
                    return;
                }

                $.ajax({
                    url: mlsapi_vars.ajax_url,
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        action: 'mlsapi_poll_studio_job',
                        nonce: mlsapi_vars.nonce,
                        job_id: jobId
                    }
                }).done(function(response) {
                    if (!response || !response.success) {
                        var err = (response && response.data && response.data.message) ? response.data.message : 'Error polling job.';
                        deferred.reject(err);
                        return;
                    }

                    var job = response.data;

                    if (typeof onProgress === 'function') {
                        onProgress({
                            status: job.status,
                            progress: job.progress_percentage || 15,
                            step: job.current_step || 'Processing...',
                            job: job
                        });
                    }

                    if (job.status === 'completed') {
                        deferred.resolve(job.result);
                    } else if (job.status === 'failed') {
                        deferred.reject(job.error || 'Job failed on the rendering engine.');
                    } else {
                        // Still processing, schedule next poll
                        setTimeout(check, pollIntervalMs);
                    }
                }).fail(function(xhr) {
                    deferred.reject('Network error communicating with WordPress backend.');
                });
            }

            // Initial immediate check after short delay
            setTimeout(check, 1500);

            return deferred.promise();
        },

        /**
         * Save generated image to Media Library or replace existing
         *
         * @param {object} options { imageUrl, replaceAttachmentId, format, title, jobId, operation }
         * @returns {Promise}
         */
        saveImage: function(options) {
            var data = {
                action: 'mlsapi_save_image',
                nonce: mlsapi_vars.nonce,
                image_url: options.imageUrl,
                format: options.format || mlsapi_vars.default_format,
                title: options.title || '',
                job_id: options.jobId || '',
                operation: options.operation || ''
            };

            if (options.replaceAttachmentId) {
                data.replace_attachment_id = options.replaceAttachmentId;
            }

            return $.ajax({
                url: mlsapi_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: data
            }).then(function(response) {
                if (response && response.success) {
                    return response.data;
                } else {
                    var errorMsg = (response && response.data && response.data.message) ? response.data.message : 'Failed to save image.';
                    return $.Deferred().reject(errorMsg).promise();
                }
            });
        }
    };

})(jQuery);
