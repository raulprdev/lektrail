# Completionist

Track reading progress for website visitors using browser localStorage.

## Features

- **Automatic Tracking**: Detects when users scroll past a configurable threshold (default 90%)
- **Three-Section Widget**: Shows "Continue Reading" (started), "Completed" (finished), and "Suggested" posts
- **Privacy-Friendly**: All data stored in browser localStorage, nothing on server
- **Optional Consent**: Can require user opt-in before tracking
- **Configurable Suggestions**: Filter by category, order by random/recent/related
- **Multilingual**: Supports custom labels, includes Spanish defaults

## Installation

1. Upload the `completionist` folder to `/wp-content/plugins/`
2. Activate the plugin in WordPress
3. Add `[completionist]` shortcode to any page
4. Configure settings in Settings > Completionist

## Usage

Add the shortcode where you want the widget to appear:

```
[completionist]
```

The widget displays:
- **Continue Reading**: Posts the user started but didn't finish
- **Completed**: Posts the user scrolled to the end
- **Suggested Reading**: Unread posts based on your configuration

## Settings

- **Post Types**: Choose which post types to track
- **Read Threshold**: Scroll percentage to mark as completed (10-100%)
- **Display Options**: Show/hide excerpts and thumbnails
- **Section Limits**: Max posts per section
- **Suggestion Order**: Random, recent, or related to reading history
- **Category Filters**: Include or exclude specific categories
- **Labels**: Customize all text labels
- **Consent**: Optionally require user consent before tracking

## Requirements

- WordPress 6.0+
- PHP 7.4+

## License

GPL v2 or later
