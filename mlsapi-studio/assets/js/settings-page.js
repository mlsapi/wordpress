/**
 * MLS API Settings Page Scripts
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        var $keyInput = $('#mlsapi_api_key');
        var $toggleBtn = $('#mlsapi-toggle-key-visibility');
        var $testBtn = $('#mlsapi-test-conn-btn');
        var $status = $('#mlsapi-conn-status');

        // Toggle Password visibility
        $toggleBtn.on('click', function(e) {
            e.preventDefault();
            if ($keyInput.attr('type') === 'password') {
                $keyInput.attr('type', 'text');
                $toggleBtn.find('.dashicons').removeClass('dashicons-visibility').addClass('dashicons-hidden');
            } else {
                $keyInput.attr('type', 'password');
                $toggleBtn.find('.dashicons').removeClass('dashicons-hidden').addClass('dashicons-visibility');
            }
        });

        // Environment Segmented Buttons
        $('.mlsapi-env-btn').on('click', function(e) {
            e.preventDefault();
            var env = $(this).data('env');
            $('.mlsapi-env-btn').removeClass('active');
            $(this).addClass('active');
            $('#mlsapi_key_env').val(env);
        });

        // Test API Connection
        $testBtn.on('click', function(e) {
            e.preventDefault();

            var currentKey = ($keyInput.val() || '').trim();
            var currentBaseUrl = ($('#mlsapi_api_base_url').val() || '').trim();
            var currentEnv = $('#mlsapi_key_env').val() || 'live';

            if (!currentKey) {
                $status.removeClass('success').addClass('error').text('✕ Please enter an API key to test.').show();
                return;
            }

            if (currentKey.indexOf('•') !== -1 || currentKey.indexOf('***') !== -1) {
                $status.removeClass('success').addClass('error').text('✕ API key contains mask bullets (••••). In mlsapi.dev, click Reveal before copying.').show();
                return;
            }

            $status.removeClass('success error').text('Testing connection...').show();
            $testBtn.prop('disabled', true);

            $.ajax({
                url: mlsapi_settings_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'mlsapi_test_connection',
                    nonce: mlsapi_settings_vars.nonce,
                    api_key: currentKey,
                    base_url: currentBaseUrl,
                    env: currentEnv
                }
            }).done(function(response) {
                $testBtn.prop('disabled', false);
                if (response && response.success) {
                    $status.addClass('success').text('✓ ' + response.data.message);
                } else {
                    var err = (response && response.data && response.data.message) ? response.data.message : 'Connection failed.';
                    $status.addClass('error').text('✕ ' + err);
                }
            }).fail(function() {
                $testBtn.prop('disabled', false);
                $status.addClass('error').text('✕ Network error contacting WordPress admin-ajax.php');
            });
        });
    });

})(jQuery);
