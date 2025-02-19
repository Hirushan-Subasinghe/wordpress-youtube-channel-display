<?php
/*
Plugin Name: YouTube Channel Videos
Description: Display videos from a specific YouTube channel using shortcode
Version: 1.0
Author: Your Name
*/

// Prevent direct access to this file
defined('ABSPATH') || exit;

// Add menu item to WordPress admin
function ytcv_add_admin_menu() {
    add_menu_page(
        'YouTube Channel Videos Settings',
        'YT Channel Videos',
        'manage_options',
        'youtube-channel-videos',
        'ytcv_settings_page',
        'dashicons-video-alt3'
    );
}
add_action('admin_menu', 'ytcv_add_admin_menu');

// Create the settings page
function ytcv_settings_page() {
    // Save settings
    if (isset($_POST['ytcv_save_settings'])) {
        update_option('ytcv_api_key', sanitize_text_field($_POST['ytcv_api_key']));
        update_option('ytcv_channel_username', sanitize_text_field($_POST['ytcv_channel_username']));
        echo '<div class="updated"><p>Settings saved!</p></div>';
    }

    $api_key = get_option('ytcv_api_key');
    $channel_username = get_option('ytcv_channel_username');
    ?>
    <div class="wrap">
        <h2>YouTube Channel Videos Settings</h2>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="ytcv_api_key">YouTube API Key</label></th>
                    <td>
                        <input type="text" id="ytcv_api_key" name="ytcv_api_key" 
                               value="<?php echo esc_attr($api_key); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th><label for="ytcv_channel_username">Channel Username</label></th>
                    <td>
                        <input type="text" id="ytcv_channel_username" name="ytcv_channel_username" 
                               value="<?php echo esc_attr($channel_username); ?>" class="regular-text">
                        <p class="description">Enter the channel username (e.g., @Mrdeveloper9332)</p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="ytcv_save_settings" class="button-primary" value="Save Settings">
            </p>
        </form>
    </div>
    <?php
}

// Update the enqueue scripts function to add JavaScript
function ytcv_enqueue_scripts() {
    wp_enqueue_style('ytcv-styles', plugins_url('css/style.css', __FILE__));
    wp_enqueue_script('ytcv-script', plugins_url('js/script.js', __FILE__), array('jquery'), '1.0', true);
    
    // Pass PHP variables to JavaScript
    wp_localize_script('ytcv-script', 'ytcv_vars', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ytcv_load_more')
    ));
}
add_action('wp_enqueue_scripts', 'ytcv_enqueue_scripts');

// Add AJAX handler for load more
function ytcv_load_more_videos() {
    check_ajax_referer('ytcv_load_more', 'nonce');
    
    $page_token = isset($_POST['page_token']) ? sanitize_text_field($_POST['page_token']) : '';
    $api_key = get_option('ytcv_api_key');
    $channel_id = isset($_POST['channel_id']) ? sanitize_text_field($_POST['channel_id']) : '';
    
    $videos_url = "https://www.googleapis.com/youtube/v3/search?part=snippet&channelId=" . $channel_id . 
                 "&maxResults=15&order=date&type=video&key=" . $api_key;
    
    if (!empty($page_token)) {
        $videos_url .= "&pageToken=" . $page_token;
    }
    
    $videos_response = wp_remote_get($videos_url);
    $videos_data = json_decode(wp_remote_retrieve_body($videos_response), true);
    
    $output = '';
    foreach ($videos_data['items'] as $video) {
        $video_id = $video['id']['videoId'];
        $title = $video['snippet']['title'];
        $thumbnail = $video['snippet']['thumbnails']['medium']['url'];
        
        $output .= '
        <div class="ytcv-video-item">
            <a href="https://www.youtube.com/watch?v=' . esc_attr($video_id) . '" target="_blank">
                <img src="' . esc_url($thumbnail) . '" alt="' . esc_attr($title) . '">
                <h3>' . esc_html($title) . '</h3>
            </a>
        </div>';
    }
    
    $response = array(
        'html' => $output,
        'nextPageToken' => $videos_data['nextPageToken'] ?? ''
    );
    
    wp_send_json($response);
}
add_action('wp_ajax_ytcv_load_more_videos', 'ytcv_load_more_videos');
add_action('wp_ajax_nopriv_ytcv_load_more_videos', 'ytcv_load_more_videos');

// Update the shortcode function
function ytcv_display_videos_shortcode($atts) {
    $api_key = get_option('ytcv_api_key');
    $channel_username = get_option('ytcv_channel_username');
    
    if (empty($api_key) || empty($channel_username)) {
        return 'Please configure the YouTube Channel Videos plugin settings.';
    }

    // Get channel ID using handle
    $handle = trim($channel_username, '@');
    $channel_url = "https://www.googleapis.com/youtube/v3/search?part=snippet&type=channel&q=" . urlencode($handle) . "&key=" . $api_key;
    
    $channel_response = wp_remote_get($channel_url);
    
    if (is_wp_error($channel_response)) {
        return 'Error fetching channel information.';
    }

    $channel_data = json_decode(wp_remote_retrieve_body($channel_response), true);
    
    if (empty($channel_data['items'])) {
        return 'Channel not found. Please check the channel username.';
    }

    $channel_id = $channel_data['items'][0]['snippet']['channelId'];

    // Get the channel's videos
    $videos_url = "https://www.googleapis.com/youtube/v3/search?part=snippet&channelId=" . $channel_id . 
                 "&maxResults=15&order=date&type=video&key=" . $api_key;
    
    $videos_response = wp_remote_get($videos_url);
    
    if (is_wp_error($videos_response)) {
        return 'Error fetching videos.';
    }

    $videos_data = json_decode(wp_remote_retrieve_body($videos_response), true);
    
    if (empty($videos_data['items'])) {
        return 'No videos found in this channel.';
    }

    $output = '<div class="ytcv-video-grid" data-channel-id="' . esc_attr($channel_id) . '">';
    
    foreach ($videos_data['items'] as $video) {
        $video_id = $video['id']['videoId'];
        $title = $video['snippet']['title'];
        $thumbnail = $video['snippet']['thumbnails']['medium']['url'];
        
        $output .= '
        <div class="ytcv-video-item">
            <a href="https://www.youtube.com/watch?v=' . esc_attr($video_id) . '" target="_blank">
                <img src="' . esc_url($thumbnail) . '" alt="' . esc_attr($title) . '">
                <h3>' . esc_html($title) . '</h3>
            </a>
        </div>';
    }
    
    $output .= '</div>';
    
    if (!empty($videos_data['nextPageToken'])) {
        $output .= '<div class="ytcv-load-more-container">
            <button class="ytcv-load-more" data-next-page="' . esc_attr($videos_data['nextPageToken']) . '">
                Load More Videos
            </button>
        </div>';
    }
    
    return $output;
}
add_shortcode('youtube_channel_videos', 'ytcv_display_videos_shortcode');

// Create plugin stylesheet
function ytcv_create_stylesheet() {
    $css_dir = plugin_dir_path(__FILE__) . 'css';
    if (!file_exists($css_dir)) {
        mkdir($css_dir, 0755, true);
    }
    
    $css_file = $css_dir . '/style.css';
    $css_content = '
    .ytcv-video-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        padding: 20px;
    }
    
    .ytcv-video-item {
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .ytcv-video-item:hover {
        transform: translateY(-5px);
    }
    
    .ytcv-video-item img {
        width: 100%;
        height: auto;
        display: block;
    }
    
    .ytcv-video-item h3 {
        padding: 10px;
        margin: 0;
        font-size: 16px;
        line-height: 1.4;
        color: #333;
    }
    
    .ytcv-video-item a {
        text-decoration: none;
    }
    
    .ytcv-load-more-container {
        text-align: center;
        padding: 20px;
    }
    
    .ytcv-load-more {
        background: #2196F3;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        transition: background 0.3s ease;
    }
    
    .ytcv-load-more:hover {
        background: #1976D2;
    }
    
    @media (max-width: 768px) {
        .ytcv-video-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 480px) {
        .ytcv-video-grid {
            grid-template-columns: 1fr;
        }
    }';
    
    file_put_contents($css_file, $css_content);
    
    // Create JS directory and file
    $js_dir = plugin_dir_path(__FILE__) . 'js';
    if (!file_exists($js_dir)) {
        mkdir($js_dir, 0755, true);
    }
    
    $js_file = $js_dir . '/script.js';
    $js_content = '
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
    });';
    
    file_put_contents($js_file, $js_content);
}
register_activation_hook(__FILE__, 'ytcv_create_stylesheet');