=== Completionist ===
Contributors: completionist
Tags: reading progress, tracking, analytics, engagement
Requires at least: 6.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track reading progress for all visitors. Shows what they've read and what's left to explore.

== Description ==

Completionist helps your visitors track their reading progress across your site. Whether logged in or browsing anonymously, users can see what content they've completed and discover what's left to read.

**Features:**

* **Automatic Tracking** - Reading completion detected when users scroll to 90% of content
* **Works for Everyone** - Logged-in users get server-side storage, anonymous users use browser localStorage
* **Privacy-First** - Anonymous tracking requires consent, no personal data collected
* **Simple Display** - Use `[completionist_progress]` shortcode to show reading stats
* **Admin Dashboard** - Configure tracked post types and view aggregate statistics

**For Logged-in Users:**
* Reading history stored securely on your server
* Progress persists across devices
* No configuration needed

**For Anonymous Users:**
* Reading history stored in browser localStorage
* Clear consent flow before any tracking begins
* Warning displayed about local storage limitations
* Option to delete their data anytime

== Installation ==

1. Upload the `completionist` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add `[completionist_progress]` shortcode to any page to display the reading widget
4. Optionally configure settings via Settings > Completionist

== Frequently Asked Questions ==

= How does tracking work? =

When a user scrolls to 90% of a post's content, the system records it as "read". For logged-in users, this is stored in your database. For anonymous users, it's stored in their browser's localStorage.

= What about privacy? =

Anonymous users must consent before any tracking begins. No personally identifiable information is collected for anonymous users - only the IDs of posts they've read.

= Can users delete their data? =

Yes. Logged-in users can request deletion through standard WordPress tools. Anonymous users can delete their local data directly from the progress widget.

= Which post types are tracked? =

By default, only standard posts are tracked. Administrators can enable tracking for additional post types in Settings > Completionist.

== Screenshots ==

1. Reading progress widget showing stats and recent reads
2. Consent banner for anonymous users
3. Admin settings page

== Changelog ==

= 1.0.0 =
* Initial release
* Server-side tracking for logged-in users
* localStorage tracking for anonymous users
* Consent flow for anonymous tracking
* Progress display shortcode
* Admin settings page

== Upgrade Notice ==

= 1.0.0 =
Initial release of Completionist reading tracker.
