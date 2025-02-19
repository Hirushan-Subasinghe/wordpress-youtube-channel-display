
    jQuery(document).ready(function($) {
        $(".ytcv-load-more").on("click", function() {
            var button = $(this);
            var grid = $(".ytcv-video-grid");
            var pageToken = button.data("next-page");
            var channelId = grid.data("channel-id");
            
            $.ajax({
                url: ytcv_vars.ajax_url,
                type: "POST",
                data: {
                    action: "ytcv_load_more_videos",
                    nonce: ytcv_vars.nonce,
                    page_token: pageToken,
                    channel_id: channelId
                },
                success: function(response) {
                    if (response.html) {
                        grid.append(response.html);
                        if (response.nextPageToken) {
                            button.data("next-page", response.nextPageToken);
                        } else {
                            button.parent().remove();
                        }
                    }
                }
            });
        });
    });