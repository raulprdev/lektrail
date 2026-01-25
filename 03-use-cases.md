# Completionist - Use Cases & Test Scenarios
## Granular User Stories for Development & Testing

### Anonymous Visitor - First Time

- [ ] Visitor lands on post without any tracking cookie
- [ ] System generates anonymous session ID
- [ ] Reading progress starts tracking at 0%
- [ ] Progress updates every 5 seconds while scrolling
- [ ] Progress reaches 100% when hitting post footer
- [ ] Session data saves to localStorage
- [ ] Visitor navigates to second post
- [ ] Previous post shows as "read" in localStorage
- [ ] New post starts tracking from 0%
- [ ] Visitor closes browser
- [ ] Visitor returns to site next day
- [ ] localStorage data persists and loads
- [ ] Previously read posts show checkmark icon
- [ ] Visitor sees "Track your reading" prompt after 3 posts
- [ ] Visitor dismisses prompt
- [ ] Prompt doesn't show again for 30 days

### Anonymous Visitor - Return Visit

- [ ] Visitor with existing localStorage returns to site
- [ ] System loads previous reading history
- [ ] Read posts display "Read on [date]" tooltip
- [ ] Partially read post shows 65% progress bar
- [ ] Visitor clicks "Continue reading" on partial post
- [ ] Page scrolls to last read position
- [ ] Visitor completes previously partial post
- [ ] Progress updates from 65% to 100%
- [ ] Visitor views reading stats widget
- [ ] Widget shows "12 of 47 posts read"
- [ ] Visitor filters by "Unread posts only"
- [ ] Archive page hides read posts
- [ ] Visitor clears browser data
- [ ] All anonymous tracking data is lost
- [ ] System treats as new visitor

### Registration Flow

- [ ] Anonymous visitor with 20 read posts clicks "Register"
- [ ] Registration form loads with reading history notice
- [ ] User completes WordPress registration
- [ ] System prompts "Merge reading history?"
- [ ] User clicks "Yes, merge my history"
- [ ] Server receives anonymous session data
- [ ] Server validates session ownership
- [ ] Anonymous reads convert to user reads
- [ ] localStorage clears after successful merge
- [ ] User profile shows merged history
- [ ] Duplicate reads (if any) resolve by earliest date
- [ ] User can undo merge within 24 hours
- [ ] Merge conflicts show resolution options
- [ ] Failed merge preserves anonymous data
- [ ] User can retry merge later

### Registered User - Basic Reading

- [ ] Logged-in user visits unread post
- [ ] System checks user hasn't read this post
- [ ] Reading tracking starts automatically
- [ ] Progress saves to database every 10 seconds
- [ ] User leaves at 45% completion
- [ ] Database stores partial read state
- [ ] User returns on different device
- [ ] Same post shows 45% progress
- [ ] User completes reading
- [ ] System marks post as completed
- [ ] Read timestamp updates to completion time
- [ ] Total reading time accumulates
- [ ] User stats update in real-time
- [ ] Category progress recalculates
- [ ] User reads same post again
- [ ] System updates "last read" date only

### Reading Dashboard Widget

- [ ] User accesses reading dashboard
- [ ] Dashboard loads within 2 seconds
- [ ] Total posts count displays correctly
- [ ] Read posts count is accurate
- [ ] Completion percentage calculates properly
- [ ] Category breakdown shows all categories
- [ ] Each category shows post count
- [ ] Each category shows completion percent
- [ ] User clicks category name
- [ ] Filter applies showing only that category
- [ ] "Recently read" section shows last 10
- [ ] Each item shows title and date
- [ ] "Continue reading" section appears if applicable
- [ ] Partial reads show progress percentage
- [ ] User can hide completed categories

### Updated Content Detection

- [ ] Admin updates published post
- [ ] System detects content change
- [ ] Change detection runs on save
- [ ] Content hash comparison executes
- [ ] System categorizes change as minor/major
- [ ] Minor changes don't notify users
- [ ] Major changes flag post as updated
- [ ] User who read post sees "Updated" badge
- [ ] Badge shows "Updated since you read it"
- [ ] User clicks updated post
- [ ] System highlights changed sections
- [ ] User completes reading updated version
- [ ] "Updated" badge clears
- [ ] New version hash saves to user record
- [ ] Update history preserves old read status

### Filtering & Discovery

- [ ] User selects "Show unread posts"
- [ ] Archive page filters to unread only
- [ ] User selects "Show read posts"
- [ ] Archive page shows only completed reads
- [ ] User selects "Updated since read"
- [ ] Only posts with updates appear
- [ ] User chooses date range filter
- [ ] Posts filter by publish date
- [ ] User combines multiple filters
- [ ] Filters stack correctly (AND logic)
- [ ] User clears all filters
- [ ] Full post list returns
- [ ] Filter state persists in session
- [ ] User bookmarks filtered URL
- [ ] Filtered state loads from URL parameters

### Privacy & Data Control

- [ ] User accesses privacy settings
- [ ] "Disable tracking" option visible
- [ ] User disables tracking
- [ ] All tracking JavaScript unloads
- [ ] No new reads record
- [ ] Previous history remains accessible
- [ ] User re-enables tracking
- [ ] Tracking resumes from current point
- [ ] User requests data export
- [ ] System generates JSON file
- [ ] Export includes all reading history
- [ ] Export includes preferences
- [ ] User requests data deletion
- [ ] Confirmation prompt appears
- [ ] User confirms deletion
- [ ] All user reading data purges

### Shortcode Display

- [ ] Admin adds [completionist_progress] shortcode
- [ ] Shortcode renders on frontend
- [ ] Progress shows for logged-in users
- [ ] Anonymous users see session progress
- [ ] Admin adds [completionist_stats] shortcode
- [ ] Stats widget renders correctly
- [ ] Admin adds category="tech" parameter
- [ ] Shortcode filters to tech category only
- [ ] Admin adds layout="compact" parameter
- [ ] Display switches to compact mode
- [ ] Shortcode works in widgets
- [ ] Shortcode works in posts
- [ ] Shortcode works in pages
- [ ] Invalid parameters show graceful error
- [ ] Multiple shortcodes work on same page

### Gutenberg Block

- [ ] Admin adds Completionist block
- [ ] Block appears in block library
- [ ] Block preview renders in editor
- [ ] Admin configures block settings
- [ ] Show/hide options work correctly
- [ ] Color settings apply properly
- [ ] Size settings adjust layout
- [ ] Block saves without errors
- [ ] Frontend renders match editor preview
- [ ] Block responds to theme changes
- [ ] Multiple blocks work on same page
- [ ] Block transforms from shortcode
- [ ] Block works in reusable blocks
- [ ] Block works in widget areas
- [ ] Block accessibility passes WCAG 2.1

### Admin Configuration

- [ ] Admin accesses plugin settings
- [ ] All settings load with defaults
- [ ] Admin enables email notifications
- [ ] Save confirms without page reload
- [ ] Admin sets minimum read threshold to 80%
- [ ] Threshold applies to completion logic
- [ ] Admin excludes specific categories
- [ ] Excluded categories stop tracking
- [ ] Admin enables anonymous tracking
- [ ] Anonymous tracking activates globally
- [ ] Admin sets data retention to 365 days
- [ ] Old data cleanup schedules correctly
- [ ] Admin exports configuration
- [ ] Settings export as JSON
- [ ] Admin imports configuration
- [ ] Settings restore from JSON

### Performance & Optimization

- [ ] Page load time increases <50ms
- [ ] Tracking script loads asynchronously
- [ ] Database queries stay under 5 per page
- [ ] API responses return <200ms
- [ ] 1000 concurrent users handle smoothly
- [ ] localStorage stays under 5MB limit
- [ ] Caching reduces database hits 80%
- [ ] CDN serves static assets
- [ ] Gzip reduces payload 70%
- [ ] Images lazy load appropriately
- [ ] No memory leaks in JavaScript
- [ ] No N+1 query problems
- [ ] Batch operations prevent timeouts
- [ ] Cleanup cron runs without errors
- [ ] Plugin deactivation cleans up properly

### Error Handling

- [ ] Database connection failure shows notice
- [ ] API errors return meaningful messages
- [ ] JavaScript errors don't break page
- [ ] Invalid user input sanitizes properly
- [ ] Missing posts handle gracefully
- [ ] Deleted user data cleanup works
- [ ] Malformed localStorage recovers safely
- [ ] Rate limiting prevents abuse
- [ ] Nonce verification blocks CSRF
- [ ] SQL injection attempts fail
- [ ] XSS attempts sanitize correctly
- [ ] Large datasets paginate properly
- [ ] Timeout errors retry automatically
- [ ] Network offline works degraded
- [ ] Plugin conflicts detect and warn

### Migration & Updates

- [ ] Plugin activates without errors
- [ ] Database tables create correctly
- [ ] Upgrade from 1.0 to 1.1 works
- [ ] Data migration preserves all records
- [ ] Rollback mechanism works if needed
- [ ] No data loss during updates
- [ ] Settings preserve through updates
- [ ] Custom code hooks remain compatible
- [ ] Database schema updates safely
- [ ] Large dataset migrations batch properly
- [ ] Progress indicator shows during migration
- [ ] Migration can resume if interrupted
- [ ] Backup recommendation displays
- [ ] Success/failure notifications work
- [ ] Version number updates correctly

### WordPress Multisite

- [ ] Plugin network activates correctly
- [ ] Per-site activation works
- [ ] Network settings apply globally
- [ ] Site settings override network
- [ ] User data isolates per site
- [ ] Super admin sees all stats
- [ ] Site switching preserves context
- [ ] Database tables prefix correctly
- [ ] Uninstall cleans all sites
- [ ] Export works per site
- [ ] Import respects site boundaries
- [ ] Performance scales with sites
- [ ] Memory usage stays reasonable
- [ ] Cron jobs don't conflict
- [ ] Updates apply network-wide

### Accessibility (WCAG 2.1 AA)

- [ ] All interactive elements keyboard accessible
- [ ] Tab order follows logical sequence
- [ ] Focus indicators clearly visible
- [ ] Screen reader announces progress
- [ ] ARIA labels describe functionality
- [ ] Color contrast meets 4.5:1 ratio
- [ ] Text resizable to 200%
- [ ] No keyboard traps exist
- [ ] Error messages associate with fields
- [ ] Time limits have extension options
- [ ] Animation can be paused
- [ ] No seizure-triggering elements
- [ ] Alternative text for icons
- [ ] Semantic HTML structure correct
- [ ] Language attributes present

### Mobile Experience

- [ ] Touch targets minimum 44x44px
- [ ] Dashboard responsive below 768px
- [ ] Progress bar visible on mobile
- [ ] Swipe gestures work naturally
- [ ] Text remains readable when zoomed
- [ ] No horizontal scrolling needed
- [ ] Modals fit mobile viewport
- [ ] Forms usable on touch devices
- [ ] Loading states clear on slow connections
- [ ] Offline message displays appropriately
- [ ] Data usage remains reasonable
- [ ] Battery impact minimal
- [ ] Works in mobile browsers
- [ ] PWA features enhance experience
- [ ] App-like navigation works

---

*Next Document: [04-phase-1-mvp.md - Core Implementation Plan]*