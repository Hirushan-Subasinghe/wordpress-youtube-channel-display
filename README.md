# wordpress-youtube-channel-display
A lightweight WordPress plugin that displays YouTube channel videos in a responsive grid layout with load more functionality.

![Alt Text](relative/path/to/image.png)


## 🚀 Features

- Display YouTube channel videos in a responsive 3-column grid
- Load more functionality to fetch additional videos
- Customizable through WordPress admin panel
- Responsive design that adapts to all screen sizes
- Lightweight and optimized for performance
- Simple shortcode implementation
- Supports channel handles (e.g., @channelname)

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- YouTube Data API v3 key
- Valid YouTube channel username/handle

## 💻 Installation

1. Download the plugin ZIP file
2. Go to WordPress admin panel > Plugins > Add New
3. Click "Upload Plugin" and choose the downloaded ZIP file
4. Activate the plugin after installation

## ⚙️ Configuration

1. Get your YouTube API Key:
   - Go to [Google Cloud Console](https://console.cloud.google.com)
   - Create a new project or select existing one
   - Enable YouTube Data API v3
   - Create credentials (API key)
   - Copy your API key

2. Plugin Setup:
   - Go to WordPress admin panel
   - Click on "YT Channel Videos" in the left menu
   - Enter your YouTube API Key
   - Enter your YouTube channel username (e.g., @YoutubeChanel)
   - Save settings

## 🎯 Usage

Add the following shortcode to any page or post where you want to display the videos:

```
[youtube_channel_videos]
```

## 🎨 Customization

The plugin includes responsive breakpoints:
- Desktop: 3 videos per row
- Tablet (< 768px): 2 videos per row
- Mobile (< 480px): 1 video per row

## 📝 Changelog

### 1.0.0
- Initial release
- Grid layout with 3 columns
- Load more functionality
- Responsive design
- Admin panel configuration


## 👨‍💻 Author

Your Name
- GitHub: [@Hirushan-Subasinghe](https://github.com/Hirushan-Subasinghe)

## 🙏 Acknowledgments

- [WordPress Plugin Development](https://developer.wordpress.org/plugins/)
- [YouTube Data API](https://developers.google.com/youtube/v3)
