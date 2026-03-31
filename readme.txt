=== LekTrail Reading Tracker ===
Contributors: raulprdev
Tags: reading progress, tracking, engagement, recently viewed, suggestions
Requires at least: 6.3
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show visitors what they've read, what they started, and what to read next.

== Description ==

Most websites have no memory. Visitors return and see the same content with no recognition of what they've already explored. LekTrail changes that.

**For content sites**: Show readers which articles they started but didn't finish, and suggest new content based on their history.

**For e-commerce**: Display recently viewed products so customers can easily return to items they were considering.

**For courses and documentation**: Track progress through lessons or guides, showing what's completed and what's next.

= How It Works =

LekTrail tracks when visitors scroll through your content:

* **Viewed**: Posts they started reading
* **Completed**: Posts they scrolled to the end (configurable threshold)
* **Suggestions**: Unread posts based on your configuration

The widget displays three sections: "Continue Reading", "Completed", and "Suggested Reading".

= Two Tracking Modes =

**localStorage Mode (Default)**

* Works for all visitors, no login required
* Data stored in browser, completely private
* Optional consent banner
* Data clears if browser storage is cleared

**Server-Side Mode**

* Requires logged-in users (WordPress users or membership plugin)
* Reading history persists across devices
* Useful for membership sites, courses, or personalized experiences
* Anonymous visitors see suggestions only

= Features =

* Gutenberg block and shortcode support
* Works with any post type (posts, pages, products, custom types)
* Configurable scroll threshold to mark content as "completed"
* Show/hide excerpts and thumbnails
* Suggestion order: random, recent, or related
* Category filters for suggestions
* Customizable labels (multilingual ready)
* Optional consent requirement with built-in banner
* Performance caching for suggestions

= Development =

Source code and build tools are available on [GitHub](https://github.com/raulprdev/lektrail).

== Installation ==

1. Upload `lektrail` to `/wp-content/plugins/`
2. Activate the plugin
3. Add the widget:
   * **Gutenberg**: Search for "LekTrail" in the block inserter
   * **Shortcode**: Add `[lektrail]` to any page
4. Configure in Settings > LekTrail

== Frequently Asked Questions ==

= Does this work without user registration? =

Yes. By default, LekTrail uses localStorage which works for all visitors without requiring login. Data is stored privately in the visitor's browser.

= Can I track reading progress for logged-in users? =

Yes. Enable "Track Logged-in Users" in settings. This stores reading history on the server, allowing it to persist across devices. Requires users to be logged in (WordPress users or via a membership plugin).

= What post types can I track? =

Any post type: posts, pages, WooCommerce products, custom post types. Configure which types to track in the settings.

= How do I customize the widget appearance? =

The widget uses minimal styling. Add your own CSS targeting `.lektrail-widget`, `.lektrail-continue`, `.lektrail-completed`, and `.lektrail-suggestions`.

== Changelog ==

= 1.0.0 =
* Initial release
* localStorage and server-side tracking modes
* Gutenberg block and shortcode
* Configurable suggestions (random, recent, related)
* Category filters
* Consent management
* Custom labels