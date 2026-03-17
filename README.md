# Completionist

Show visitors what they've read, what they started, and what to read next.

## Why Completionist?

Most websites have no memory. Visitors return and see the same content with no recognition of what they've already explored. Completionist changes that.

**For content sites**: Show readers which articles they started but didn't finish, and suggest new content based on their history.

**For e-commerce**: Display recently viewed products so customers can easily return to items they were considering.

**For courses/documentation**: Track progress through lessons or guides, showing what's completed and what's next.

## How It Works

Completionist tracks when visitors scroll through your content:
- **Viewed**: Posts they started reading
- **Completed**: Posts they scrolled to the end (configurable threshold)
- **Suggestions**: Unread posts based on your configuration

The widget displays three sections: "Continue Reading" (started but not finished), "Completed", and "Suggested Reading".

## Two Tracking Modes

### localStorage Mode (Default)
- Works for all visitors, no login required
- Data stored in browser, completely private
- Optional consent banner
- Data clears if browser storage is cleared

### Server-Side Mode
- Requires logged-in users (WordPress users or membership plugin)
- Reading history persists across devices
- Useful for membership sites, courses, or personalized experiences
- Anonymous visitors see suggestions only

## Installation

1. Upload `completionist` to `/wp-content/plugins/`
2. Activate the plugin
3. Add the widget using either method:
   - **Gutenberg Block**: Search for "Completionist" in the block inserter
   - **Shortcode**: Add `[completionist]` to any page
4. Configure in Settings > Completionist

## Configuration

### Display Options
- Show/hide excerpts and thumbnails
- Limit posts per section (viewed, completed, suggestions)
- Enable/disable sections independently
- Custom labels for all text

### Tracking Options
- Post types to track (posts, pages, products, custom types)
- Read threshold: scroll percentage to mark as completed (10-100%)
- Track logged-in users server-side (optional)

### Suggestions
- Order: Random, recent, or related to reading history
- Category filters: include or exclude specific categories
- Cache duration for performance

### Privacy
- Optional consent requirement before tracking
- Built-in consent banner or integrate with cookie plugins
- localStorage mode keeps all data in the browser

## Requirements

- WordPress 6.0+
- PHP 7.4+
- For server-side tracking: logged-in users (WordPress or membership plugin)

## License

GPL v2 or later