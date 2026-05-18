jQuery(document).ready(function ($) {
    var config = nt_llms_txt_builder;
    var i18n   = config.i18n;

    // -----------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------
    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function extractMessage(data, fallback) {
        if (!data) return fallback;
        if (typeof data === 'string') return data;
        if (typeof data === 'object' && data.message) return data.message;
        return fallback;
    }

    // -----------------------------------------------------------
    // Preview
    // -----------------------------------------------------------
    var currentVariant = 'standard';

    function loadPreview(variant) {
        variant = variant || currentVariant;
        var $preview = $('#ntllms-preview');

        $preview.html(
            '<div class="ntllms-preview-empty">' +
            '<span class="spinner ntllms-spinner"></span> ' +
            escapeHtml(i18n.loading) +
            '</div>'
        );

        $.ajax({
            url:      ajaxurl,
            type:     'POST',
            dataType: 'json',
            data: {
                action:  'ntllms_txt_builder_preview',
                nonce:   config.preview_nonce,
                variant: variant
            },
            success: function (res) {
                if (res && res.success && res.data && res.data.content) {
                    $preview.html(highlightPreview(res.data.content));
                } else {
                    $preview.html(
                        '<div class="ntllms-preview-empty">' +
                        '<span class="dashicons dashicons-welcome-view-site"></span>' +
                        escapeHtml(i18n.empty) +
                        '</div>'
                    );
                }
            },
            error: function (xhr, status, err) {
                $preview.html(
                    '<div class="ntllms-preview-empty">' +
                    '<span class="dashicons dashicons-warning"></span>' +
                    escapeHtml(i18n.error) +
                    '</div>'
                );
            }
        });
    }

    function highlightPreview(text) {
        var lines = String(text).split('\n');
        var html  = '';
        for (var i = 0; i < lines.length; i++) {
            var line = escapeHtml(lines[i]);
            if (/^# /.test(lines[i])) {
                html += '<span class="ntllms-pre-h1">' + line + '</span>\n';
            } else if (/^## /.test(lines[i])) {
                html += '<span class="ntllms-pre-h2">' + line + '</span>\n';
            } else if (/^> /.test(lines[i])) {
                html += '<span class="ntllms-pre-blockquote">' + line + '</span>\n';
            } else if (/^- \[/.test(lines[i])) {
                html += '<span class="ntllms-pre-link">' + line + '</span>\n';
            } else {
                html += line + '\n';
            }
        }
        return html;
    }

    // Variant toggle
    $('.ntllms-preview-variant a').on('click', function (e) {
        e.preventDefault();
        var $this = $(this);
        if ($this.hasClass('active')) return;

        $('.ntllms-preview-variant a').removeClass('active');
        $this.addClass('active');
        currentVariant = $this.data('variant');
        loadPreview(currentVariant);
    });

    // Initial load
    loadPreview();

    // -----------------------------------------------------------
    // Generate / Clear Cache
    // -----------------------------------------------------------
    function ajaxAction($btn, action, nonce, busyLabel) {
        var origHtml = $btn.html();
        $btn.prop('disabled', true).html(
            '<span class="spinner ntllms-spinner"></span> ' + escapeHtml(busyLabel)
        );

        $.ajax({
            url:      ajaxurl,
            type:     'POST',
            dataType: 'json',
            data:     { action: action, nonce: nonce },
            success: function (res) {
                var isSuccess = !!(res && res.success);
                var msg       = extractMessage(res ? res.data : null, i18n.error);

                showNotice(msg, isSuccess ? 'success' : 'error');

                // Update cache status bar
                if (isSuccess && res.data && res.data.cache_html) {
                    $('#ntllms-cache-status').html(res.data.cache_html);
                }

                // Refresh preview after generate
                if (isSuccess && action === 'ntllms_txt_builder_generate_file') {
                    loadPreview();
                }
            },
            error: function () {
                showNotice(i18n.error, 'error');
            },
            complete: function () {
                $btn.prop('disabled', false).html(origHtml);
            }
        });
    }

    $('#generate-llms-txt').on('click', function () {
        ajaxAction($(this), 'ntllms_txt_builder_generate_file', config.generate_nonce, i18n.generating);
    });

    $('#clear-llms-txt-cache').on('click', function () {
        ajaxAction($(this), 'ntllms_txt_builder_clear_cache_data', config.clear_cache_nonce, i18n.clearing);
    });

    // -----------------------------------------------------------
    // Notices (auto-dismiss)
    // -----------------------------------------------------------
    function showNotice(msg, type) {
        var safeMsg = escapeHtml(msg);
        var $el = $(
            '<div class="notice notice-' + type + ' is-dismissible" style="opacity:0">' +
            '<p>' + safeMsg + '</p>' +
            '<button type="button" class="notice-dismiss"></button>' +
            '</div>'
        );
        $('#llms-txt-result').html($el);
        $el.animate({ opacity: 1 }, 200);

        if (type === 'success') {
            setTimeout(function () {
                $el.animate({ opacity: 0 }, 300, function () { $el.remove(); });
            }, 4000);
        }

        $el.find('.notice-dismiss').on('click', function () { $el.remove(); });
    }

    // -----------------------------------------------------------
    // Select All / None
    // -----------------------------------------------------------
    $('.ntllms-select-all').on('click', function (e) {
        e.preventDefault();
        $(this).closest('.ntllms-checkbox-group').find('input[type="checkbox"]').prop('checked', true);
    });

    $('.ntllms-select-none').on('click', function (e) {
        e.preventDefault();
        $(this).closest('.ntllms-checkbox-group').find('input[type="checkbox"]').prop('checked', false);
    });

    // -----------------------------------------------------------
    // Copy to clipboard
    // -----------------------------------------------------------
    $('.ntllms-copy-btn').on('click', function () {
        var $btn = $(this);
        var url  = $btn.data('url');

        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(function () {
                flashCopied($btn);
            });
        } else {
            var $temp = $('<input>').val(url).appendTo('body').select();
            document.execCommand('copy');
            $temp.remove();
            flashCopied($btn);
        }
    });

    function flashCopied($btn) {
        var $icon = $btn.find('.dashicons');
        $btn.addClass('copied');
        $icon.removeClass('dashicons-clipboard').addClass('dashicons-yes');
        setTimeout(function () {
            $btn.removeClass('copied');
            $icon.removeClass('dashicons-yes').addClass('dashicons-clipboard');
        }, 1500);
    }
});
