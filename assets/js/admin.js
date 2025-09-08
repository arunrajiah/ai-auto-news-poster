jQuery(document).ready(function($) {
    
    // Add RSS Feed functionality
    $('#add-feed').on('click', function() {
        var container = $('#rss-feeds-container');
        var newRow = $('<div class="rss-feed-row">');
        newRow.html('<input type="url" name="aanp_settings[rss_feeds][]" value="" class="regular-text" placeholder="https://example.com/feed.xml" /> <button type="button" class="button remove-feed">Remove</button>');
        container.append(newRow);
    });
    
    // Remove RSS Feed functionality
    $(document).on('click', '.remove-feed', function() {
        $(this).closest('.rss-feed-row').remove();
    });
    
    // Generate Posts functionality
    $('#aanp-generate-posts').on('click', function() {
        var button = $(this);
        var statusDiv = $('#aanp-generation-status');
        var statusText = $('#aanp-status-text');
        var progressBar = $('.aanp-progress-bar');
        var resultsDiv = $('#aanp-generation-results');
        var resultsList = $('#aanp-results-list');
        
        // Disable button and show progress
        button.prop('disabled', true);
        button.find('.dashicons').addClass('spin');
        statusDiv.show();
        resultsDiv.hide();
        resultsList.empty();
        
        // Reset progress
        progressBar.css('width', '0%');
        statusText.text(aanp_ajax.generating_text);
        
        // Simulate progress
        var progress = 0;
        var progressInterval = setInterval(function() {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            progressBar.css('width', progress + '%');
        }, 500);
        
        // Make AJAX request
        $.ajax({
            url: aanp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'aanp_generate_posts',
                nonce: aanp_ajax.nonce
            },
            success: function(response) {
                clearInterval(progressInterval);
                progressBar.css('width', '100%');
                
                if (response.success) {
                    statusText.html('<span style="color: #00a32a;">✓ ' + response.data.message + '</span>');
                    
                    // Display generated posts
                    if (response.data.posts && response.data.posts.length > 0) {
                        var postsHtml = '<ul>';
                        $.each(response.data.posts, function(index, post) {
                            postsHtml += '<li>';
                            postsHtml += '<strong>' + escapeHtml(post.title) + '</strong> ';
                            postsHtml += '<a href="' + post.edit_link + '" class="button button-small" target="_blank">Edit Post</a>';
                            postsHtml += '</li>';
                        });
                        postsHtml += '</ul>';
                        resultsList.html(postsHtml);
                        resultsDiv.show();
                    }
                    
                    // Show admin notice
                    showAdminNotice(response.data.message, 'success');
                    
                } else {
                    var errorMsg = response.data || aanp_ajax.error_text;
                    statusText.html('<span style="color: #d63638;">✗ ' + errorMsg + '</span>');
                    showAdminNotice(errorMsg, 'error');
                }
            },
            error: function(xhr, status, error) {
                clearInterval(progressInterval);
                progressBar.css('width', '100%');
                var errorMsg = 'AJAX Error: ' + error;
                statusText.html('<span style="color: #d63638;">✗ ' + errorMsg + '</span>');
                showAdminNotice(errorMsg, 'error');
            },
            complete: function() {
                // Re-enable button
                button.prop('disabled', false);
                button.find('.dashicons').removeClass('spin');
                
                // Hide progress after delay
                setTimeout(function() {
                    statusDiv.fadeOut();
                }, 3000);
            }
        });
    });
    
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
