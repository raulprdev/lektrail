# Completionist - Phase 1 MVP
## Core Reading Tracker for Registered Users

### MVP Scope Definition

The first phase delivers a functional reading tracker for logged-in WordPress users with basic progress visualization. This proves the core concept while avoiding the complexity of anonymous users and advanced features.

### Success Criteria

- [ ] Plugin activates without errors on WordPress 6.0+
- [ ] Tracks reading for logged-in users only
- [ ] Displays reading progress via shortcode
- [ ] Performs well on shared hosting
- [ ] Passes WordPress Plugin Review basics

### Core Features Only

#### Included in Phase 1
- Automatic reading detection for logged-in users
- Database storage of reading history
- Basic progress dashboard via shortcode
- Simple admin settings page
- Per-user reading statistics
- Basic completion tracking (0% or 100%)

#### Explicitly Excluded (Phase 2+)
- Anonymous user tracking
- Partial reading progress (45%, 60%, etc.)
- Content update detection
- Gutenberg blocks
- REST API endpoints
- Email notifications
- Data export/import
- Category filtering
- Mobile app features

### Technical Implementation

#### Database Schema (Simplified)

**Table: wp_completionist_reads**
```sql
CREATE TABLE wp_completionist_reads (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint(20) unsigned NOT NULL,
    post_id bigint(20) unsigned NOT NULL,
    completed_at datetime NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY user_post (user_id, post_id),
    KEY post_id (post_id),
    KEY completed_at (completed_at)
)
```

#### File Structure (Minimal)

```
completionist-wp/
├── completionist.php           # Main plugin file
├── uninstall.php               # Cleanup on uninstall
├── readme.txt                  # WordPress.org readme
│
├── includes/
│   ├── class-activator.php    # Install tables
│   ├── class-tracker.php      # Core tracking logic
│   ├── class-database.php     # Database operations
│   ├── class-display.php      # Shortcode output
│   └── class-admin.php        # Settings page
│
├── assets/
│   ├── js/
│   │   └── tracker.js        # Frontend tracking
│   └── css/
│       └── frontend.css      # Basic styles
│
└── languages/
    └── completionist.pot      # Translation template
```

### Development Checklist

#### Week 1: Foundation

**Day 1-2: Setup & Structure**
- [ ] Initialize Git repository
- [ ] Create basic plugin structure
- [ ] Set up local development with wp-env
- [ ] Configure PHPUnit testing
- [ ] Create main plugin class
- [ ] Implement activation hook
- [ ] Create database table on activation
- [ ] Add uninstall cleanup

**Day 3-4: Core Tracking**
- [ ] Build tracking JavaScript
- [ ] Detect when user reaches end of post
- [ ] Send AJAX request to record completion
- [ ] Verify nonce and user authentication
- [ ] Save completion to database
- [ ] Handle duplicate read attempts
- [ ] Add basic error handling
- [ ] Create unit tests for tracking

**Day 5-7: Data Management**
- [ ] Create database abstraction class
- [ ] Implement CRUD operations
- [ ] Add get_user_reads() method
- [ ] Add get_user_stats() method
- [ ] Create data validation functions
- [ ] Add prepare statements for security
- [ ] Optimize queries with proper indexes
- [ ] Test with 1000+ records

#### Week 2: Display & Polish

**Day 8-9: Frontend Display**
- [ ] Create [completionist_progress] shortcode
- [ ] Build HTML output structure
- [ ] Add basic CSS styling
- [ ] Calculate completion percentage
- [ ] Display read/total counts
- [ ] Show recent reads list (last 5)
- [ ] Make responsive for mobile
- [ ] Test in top 5 themes

**Day 10-11: Admin Interface**
- [ ] Create settings page under Settings menu
- [ ] Add enable/disable tracking option
- [ ] Add "delete all data" tool
- [ ] Show total plugin statistics
- [ ] Implement settings save/update
- [ ] Add admin notices for actions
- [ ] Create help documentation tab
- [ ] Add WordPress.org review prompt

**Day 12-14: Testing & Documentation**
- [ ] Write PHPUnit tests (aim for 70% coverage)
- [ ] Test on PHP 7.4, 8.0, 8.1, 8.2
- [ ] Test on WordPress 6.0 to 6.4
- [ ] Create readme.txt for WordPress.org
- [ ] Add inline code documentation
- [ ] Test with Query Monitor for performance
- [ ] Fix any WordPress Coding Standards issues
- [ ] Create basic user documentation

### Code Specifications

#### Main Plugin File Structure

```php
/**
 * Plugin Name: Completionist
 * Description: Track reading progress for your visitors
 * Version: 1.0.0
 * Author: [Your Name]
 * Text Domain: completionist
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Constants
define('COMPLETIONIST_VERSION', '1.0.0');
define('COMPLETIONIST_URL', plugin_dir_url(__FILE__));
define('COMPLETIONIST_PATH', plugin_dir_path(__FILE__));

// Autoloader
require_once COMPLETIONIST_PATH . 'includes/class-completionist.php';

// Initialize
function completionist_init() {
    $plugin = new Completionist();
    $plugin->run();
}
add_action('plugins_loaded', 'completionist_init');

// Activation
register_activation_hook(__FILE__, ['Completionist_Activator', 'activate']);
```

#### JavaScript Tracking Logic

```javascript
// Basic tracking implementation
document.addEventListener('DOMContentLoaded', function() {
    // Only track single posts
    if (!completionist.is_single) return;
    
    // Check if user scrolled to bottom
    let hasCompleted = false;
    
    window.addEventListener('scroll', function() {
        if (hasCompleted) return;
        
        const scrollPercent = (window.scrollY + window.innerHeight) / document.documentElement.scrollHeight;
        
        if (scrollPercent > 0.9) {
            hasCompleted = true;
            trackCompletion();
        }
    });
    
    function trackCompletion() {
        fetch(completionist.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'completionist_track',
                post_id: completionist.post_id,
                nonce: completionist.nonce
            })
        });
    }
});
```

#### Shortcode Output HTML

```html
<div class="completionist-progress">
    <div class="completionist-stats">
        <h3>Your Reading Progress</h3>
        <div class="progress-bar">
            <div class="progress-fill" style="width: 31%"></div>
        </div>
        <p>You've read 47 of 152 posts (31%)</p>
    </div>
    
    <div class="completionist-recent">
        <h4>Recently Read</h4>
        <ul>
            <li><a href="#">Getting Started with WordPress</a> - 2 days ago</li>
            <li><a href="#">Understanding Themes</a> - 3 days ago</li>
            <li><a href="#">Plugin Development Basics</a> - 1 week ago</li>
        </ul>
    </div>
</div>
```

### Testing Strategy

#### Unit Tests (PHPUnit)
- Test database table creation
- Test read recording logic
- Test duplicate prevention
- Test stats calculation
- Test shortcode output

#### Manual Testing Checklist
- [ ] Install on fresh WordPress
- [ ] Activate without errors
- [ ] Read post as logged-in user
- [ ] Verify database record created
- [ ] Check shortcode displays correctly
- [ ] Test with 20+ posts read
- [ ] Deactivate and reactivate
- [ ] Uninstall removes all data

### Performance Benchmarks

**Target Metrics:**
- Page load impact: <30ms
- Database queries: ≤2 per page
- JavaScript file size: <5KB
- CSS file size: <2KB
- Memory usage: <2MB
- Works with 10,000+ posts

### Security Checklist

- [ ] All database queries use $wpdb->prepare()
- [ ] Nonces verified on all AJAX calls
- [ ] Capability checks for admin features
- [ ] Data escaped on output
- [ ] No direct file access
- [ ] No eval() or create_function()
- [ ] Input validation on all forms
- [ ] XSS prevention in place

### WordPress.org Submission Prep

#### Required Files
- [ ] readme.txt with proper headers
- [ ] LICENSE file (GPLv2)
- [ ] Screenshots (up to 12)
- [ ] Banner image (772x250px)
- [ ] Icon image (256x256px)

#### Pre-submission Checklist
- [ ] No console.log() statements
- [ ] No commented code blocks
- [ ] Proper text domain usage
- [ ] Translatable strings marked
- [ ] No external service dependencies
- [ ] No "powered by" links
- [ ] Follows WordPress Coding Standards
- [ ] Security scan passed

### MVP Deliverables

1. **Functional plugin** tracking reads for logged-in users
2. **Basic shortcode** displaying progress
3. **Admin settings page** with core options
4. **Documentation** for users and developers
5. **Test suite** with 70%+ coverage
6. **WordPress.org ready** package

### Success Metrics

- Successfully tracks reading for 100+ users
- No critical bugs in 7 days of testing
- Performance impact under 50ms
- Works on top 10 WordPress themes
- Positive feedback from 5 beta testers

### Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Theme compatibility issues | High | Test with popular themes early |
| Performance on large sites | Medium | Add caching in Week 2 |
| JavaScript errors | Medium | Graceful degradation |
| Database scaling | Low | Proper indexes from start |
| Security vulnerabilities | High | Follow WordPress best practices |

### Phase 1 Completion Criteria

The MVP is complete when:
- [ ] All Week 1 & 2 tasks checked off
- [ ] Plugin passes WordPress standards check
- [ ] Successfully tested on 3 different hosting providers
- [ ] Documentation complete
- [ ] Ready for WordPress.org submission

### Transition to Phase 2

After MVP completion, validate core concept works before adding:
- Anonymous user tracking (main Phase 2 feature)
- Partial progress tracking
- Advanced statistics
- REST API
- Gutenberg blocks

---

*Next Document: [05-phase-2-anonymous.md - Anonymous User Innovation]*