jQuery(document).ready(function($) {
    
    // Add RSS Feed functionality
    $('#add-feed').on('click', function() {
        var container = $('#rss-feeds-container');
        var newRow    = $('<div class="rss-feed-row">');
        newRow.html(
            '<input type="url" name="aanp_settings[rss_feeds][]" value="" class="regular-text" placeholder="https://example.com/feed.xml" /> ' +
            '<button type="button" class="button test-feed">Test</button> ' +
            '<button type="button" class="button remove-feed">Remove</button> ' +
            '<span class="feed-test-result"></span>'
        );
        container.append(newRow);
    });

    // Test RSS feed URL
    $(document).on('click', '.test-feed', function() {
        var row      = $(this).closest('.rss-feed-row');
        var feedUrl  = row.find('input[type="url"]').val().trim();
        var result   = row.find('.feed-test-result');

        if (!feedUrl) {
            result.html('<span class="aanp-status-error">Enter a URL first.</span>');
            return;
        }

        var btn = $(this).prop('disabled', true).text('Testing…');

        $.ajax({
            url: aanp_ajax.ajax_url,
            type: 'POST',
            data: { action: 'aanp_test_feed', nonce: aanp_ajax.nonce, feed_url: feedUrl },
            success: function(response) {
                if (response.success) {
                    result.html('<span class="aanp-status-success">&#x2713; ' + escapeHtml(response.data.message) + '</span>');
                } else {
                    result.html('<span class="aanp-status-error">&#x2717; ' + escapeHtml(response.data || 'Error') + '</span>');
                }
            },
            error: function() {
                result.html('<span class="aanp-status-error">&#x2717; Request failed.</span>');
            },
            complete: function() {
                btn.prop('disabled', false).text('Test');
            }
        });
    });
    
    // Remove RSS Feed functionality
    $(document).on('click', '.remove-feed', function() {
        $(this).closest('.rss-feed-row').remove();
    });
    
    // Generate Posts — two-phase: fetch article list, then generate one at a time
    $('#aanp-generate-posts').on('click', function() {
        var button      = $(this);
        var statusDiv   = $('#aanp-generation-status');
        var statusText  = $('#aanp-status-text');
        var progressBar = $('.aanp-progress-bar');
        var resultsDiv  = $('#aanp-generation-results');
        var resultsList = $('#aanp-results-list');

        button.prop('disabled', true);
        button.find('.dashicons').addClass('spin');
        statusDiv.show();
        resultsDiv.hide();
        resultsList.empty();
        progressBar.css('width', '0%');
        statusText.text(aanp_ajax.generating_text);

        // Phase 1: fetch article list
        $.ajax({
            url: aanp_ajax.ajax_url,
            type: 'POST',
            data: { action: 'aanp_fetch_articles', nonce: aanp_ajax.nonce },
            success: function(response) {
                if (!response.success) {
                    var errMsg = (response.data && response.data.message) ? response.data.message : (response.data || aanp_ajax.error_text);
                    statusText.html('<span class="aanp-status-error">&#x2717; ' + escapeHtml(errMsg) + '</span>');
                    showAdminNotice(errMsg, 'error');
                    finishGeneration(button, statusDiv);
                    return;
                }

                var articles   = response.data.articles;
                var total      = articles.length;
                var completed  = 0;
                var generated  = [];

                if (total === 0) {
                    statusText.html('<span class="aanp-status-error">&#x2717; ' + escapeHtml(aanp_ajax.error_text) + '</span>');
                    finishGeneration(button, statusDiv);
                    return;
                }

                // Phase 2: generate each article sequentially for real progress
                function generateNext(index) {
                    if (index >= total) {
                        // All done
                        progressBar.css('width', '100%');
                        var doneMsg = generated.length + ' ' + aanp_ajax.success_text;

                        if (generated.length > 0) {
                            statusText.html('<span class="aanp-status-success">&#x2713; ' + escapeHtml(doneMsg) + '</span>');
                            var listHtml = '<ul>';
                            $.each(generated, function(i, post) {
                                listHtml += '<li><strong>' + escapeHtml(post.title) + '</strong> <a href="' + post.edit_link + '" class="button button-small" target="_blank">Edit Post</a></li>';
                            });
                            listHtml += '</ul>';
                            resultsList.html(listHtml);
                            resultsDiv.show();
                            showAdminNotice(doneMsg, 'success');
                        } else {
                            statusText.html('<span class="aanp-status-error">&#x2717; ' + escapeHtml(aanp_ajax.error_text) + '</span>');
                            showAdminNotice(aanp_ajax.error_text, 'error');
                        }

                        finishGeneration(button, statusDiv);
                        return;
                    }

                    var article = articles[index];
                    /* translators: %1$d current article number, %2$d total articles */
                    statusText.text('(' + (index + 1) + '/' + total + ') ' + escapeHtml(article.title));
                    progressBar.css('width', Math.round((index / total) * 100) + '%');

                    $.ajax({
                        url: aanp_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'aanp_generate_single',
                            nonce:   aanp_ajax.nonce,
                            article: article
                        },
                        success: function(res) {
                            if (res.success) {
                                generated.push(res.data);
                            }
                            completed++;
                            generateNext(index + 1);
                        },
                        error: function() {
                            completed++;
                            generateNext(index + 1);
                        }
                    });
                }

                generateNext(0);
            },
            error: function(xhr, status, error) {
                statusText.html('<span class="aanp-status-error">&#x2717; AJAX Error: ' + escapeHtml(error) + '</span>');
                showAdminNotice(error, 'error');
                finishGeneration(button, statusDiv);
            }
        });
    });

    // Re-enable the button after a cooldown and hide the status panel
    function finishGeneration(button, statusDiv) {
        button.find('.dashicons').removeClass('spin');

        var cooldown    = aanp_ajax.cooldown_seconds || 60;
        var remaining   = cooldown;
        var originalText = button.text().trim();

        var countdownInterval = setInterval(function() {
            remaining--;
            button.text(aanp_ajax.cooldown_text.replace('%d', remaining));
            if (remaining <= 0) {
                clearInterval(countdownInterval);
                button.prop('disabled', false);
                button.text(originalText);
            }
        }, 1000);

        setTimeout(function() { statusDiv.fadeOut(); }, 4000);
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    // Helper function to show admin notices
    function showAdminNotice(message, type) {
        var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        var notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + escapeHtml(message) + '</p></div>');
        
        // Insert after the page title
        $('.wrap h1').after(notice);
        
        // Make it dismissible
        notice.on('click', '.notice-dismiss', function() {
            notice.fadeOut();
        });
        
        // Auto-hide success notices
        if (type === 'success') {
            setTimeout(function() {
                notice.fadeOut();
            }, 5000);
        }
    }
    
    // API Key visibility toggle
    $('#api_key').after('<button type="button" class="button" id="toggle-api-key" style="margin-left: 10px;">Show</button>');
    
    $('#toggle-api-key').on('click', function() {
        var apiKeyField = $('#api_key');
        var button = $(this);
        
        if (apiKeyField.attr('type') === 'password') {
            apiKeyField.attr('type', 'text');
            button.text('Hide');
        } else {
            apiKeyField.attr('type', 'password');
            button.text('Show');
        }
    });
    
    // Form validation
    $('form').on('submit', function(e) {
        var apiKey = $('#api_key').val().trim();
        var provider = $('#llm_provider').val();
        
        if (!apiKey && provider !== 'custom') {
            e.preventDefault();
            alert('Please enter an API key for the selected LLM provider.');
            $('#api_key').focus();
            return false;
        }
        
        // Validate RSS feeds
        var hasValidFeed = false;
        $('input[name="aanp_settings[rss_feeds][]"]').each(function() {
            var feedUrl = $(this).val().trim();
            if (feedUrl && isValidUrl(feedUrl)) {
                hasValidFeed = true;
                return false; // break loop
            }
        });
        
        if (!hasValidFeed) {
            e.preventDefault();
            alert('Please add at least one valid RSS feed URL.');
            return false;
        }
    });
    
    // URL validation helper
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
    
    // Add spinning animation for dashicons
    $('<style>').text(`
        .dashicons.spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .aanp-progress {
            position: relative;
            overflow: hidden;
        }
        
        .aanp-progress-bar {
            transition: width 0.3s ease;
        }
    `).appendTo('head');
    
});
