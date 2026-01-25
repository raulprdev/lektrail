# Completionist - Technical Architecture
## WordPress Plugin Technical Decisions & Implementation Strategy

### Core Architecture Principles

1. **Progressive Enhancement** - Functions without JavaScript, better with it
2. **WordPress Native** - Uses WordPress patterns and APIs wherever possible  
3. **Privacy First** - Anonymous by default, opt-in for tracking
4. **Performance Conscious** - Minimal database queries, efficient caching
5. **Theme Agnostic** - Works with any properly coded WordPress theme

### Technology Stack

#### Backend (PHP)
- **PHP 7.4+** - WordPress minimum, but code ready for PHP 8.x
- **WordPress 6.0+** - Minimum version for modern block editor support
- **Composer** - PSR-4 autoloading (development only, compiled for distribution)
- **Dependency Injection** - Simple container, no external libraries

#### Frontend
- **Vue.js 3** - For reactive dashboard components
- **TypeScript** - Type safety for complex state management
- **@wordpress/scripts** - Official build pipeline
- **CSS Custom Properties** - Theme compatibility
- **Web Components** - For maximum portability

#### Database
- **MySQL 5.7+** - WordPress requirement
- **Custom Tables** - For performance at scale
- **WordPress Transients API** - For caching
- **Option Tables** - For configuration only

### Database Schema

#### Table: wp_completionist_reads
```sql
- id (bigint, auto_increment)
- user_id (bigint, nullable) -- NULL for anonymous
- post_id (bigint, indexed)
- session_hash (varchar 64, indexed) -- For anonymous users
- started_at (datetime)
- completed_at (datetime, nullable)
- progress_percent (tinyint)
- time_spent (int) -- seconds
- post_version_hash (varchar 32) -- For change detection
- created_at (datetime)
- updated_at (datetime)

Indexes:
- PRIMARY KEY (id)
- INDEX idx_user_post (user_id, post_id)
- INDEX idx_session_post (session_hash, post_id)
- INDEX idx_post_date (post_id, completed_at)
```

#### Table: wp_completionist_updates
```sql
- id (bigint, auto_increment)
- post_id (bigint, indexed)
- previous_hash (varchar 32)
- current_hash (varchar 32)
- change_significance (enum: 'minor', 'moderate', 'major')
- changed_sections (text) -- JSON array
- detected_at (datetime)

Indexes:
- PRIMARY KEY (id)
- UNIQUE KEY (post_id, current_hash)
```

#### Table: wp_completionist_user_preferences
```sql
- user_id (bigint, primary key)
- track_reading (boolean, default true)
- email_notifications (boolean, default false)
- dashboard_position (varchar 20)
- excluded_categories (text) -- JSON array
- created_at (datetime)
- updated_at (datetime)
```

### Plugin File Structure

```
completionist-wp/
├── completionist.php              # Main plugin file
├── composer.json                  # Development dependencies
├── package.json                   # Frontend build config
├── webpack.config.js              # Extended wp-scripts config
├── .phpcs.xml                     # WordPress coding standards
├── .wp-env.json                   # Local development environment
├── phpunit.xml.dist               # Test configuration
├── phpstan.neon                   # Static analysis config
│
├── includes/                      # PHP source code
│   ├── Core/
│   │   ├── Plugin.php            # Main plugin class
│   │   ├── Activator.php         # Installation/upgrade logic
│   │   ├── Deactivator.php       # Cleanup logic
│   │   └── Container.php         # DI container
│   │
│   ├── Database/
│   │   ├── Schema.php            # Table creation/updates
│   │   ├── Migrations/           # Version-specific updates
│   │   └── QueryBuilder.php      # Abstraction layer
│   │
│   ├── Tracking/
│   │   ├── ReadingTracker.php    # Core tracking logic
│   │   ├── AnonymousTracker.php  # Browser storage bridge
│   │   ├── ChangeDetector.php    # Content comparison
│   │   └── SessionManager.php    # Anonymous session handling
│   │
│   ├── API/
│   │   ├── RestController.php    # REST API endpoints
│   │   ├── Endpoints/            
│   │   └── Authentication.php    # API security
│   │
│   ├── Admin/
│   │   ├── Settings.php          # Plugin settings page
│   │   ├── Dashboard.php         # Admin widgets
│   │   └── UserColumn.php        # User list additions
│   │
│   ├── Frontend/
│   │   ├── Assets.php            # Script/style enqueueing
│   │   ├── Shortcodes.php        # [completionist_*] codes
│   │   └── Blocks.php            # Gutenberg blocks
│   │
│   └── Privacy/
│       ├── DataExporter.php      # GDPR export
│       ├── DataEraser.php        # GDPR deletion
│       └── ConsentManager.php    # Privacy controls
│
├── src/                           # Frontend source (TypeScript/Vue)
│   ├── components/               
│   │   ├── ReadingDashboard.vue
│   │   ├── ProgressBar.vue
│   │   ├── CategoryProgress.vue
│   │   └── UpdateNotifier.vue
│   │
│   ├── blocks/                   # Gutenberg block sources
│   │   └── dashboard/
│   │
│   ├── services/
│   │   ├── api.ts               # REST API client
│   │   ├── storage.ts           # LocalStorage manager
│   │   └── tracker.ts           # Reading detection
│   │
│   └── types/                   # TypeScript definitions
│
├── assets/                       # Compiled assets (git-ignored)
│   ├── js/
│   ├── css/
│   └── images/
│
├── tests/
│   ├── php/                     # PHPUnit tests
│   │   ├── unit/
│   │   ├── integration/
│   │   └── bootstrap.php
│   │
│   └── js/                      # Jest tests
│       ├── unit/
│       └── e2e/                # Playwright tests
│
├── languages/                   # Internationalization
└── bin/                        # Build/deploy scripts
```

### Key Technical Decisions

#### Anonymous User Tracking Strategy

**Browser Storage Structure:**
```javascript
{
  sessionId: "hash-of-ip-ua-timestamp",
  reads: [
    {
      postId: 123,
      started: "2024-01-15T10:30:00Z",
      progress: 65,
      contentHash: "abc123..."
    }
  ],
  lastSync: "2024-01-15T11:00:00Z"
}
```

**Binding Algorithm on Registration:**
1. Generate unique session hash from browser fingerprint
2. Store reads in IndexedDB (fallback to localStorage)
3. On registration, POST anonymous data to binding endpoint
4. Server merges based on timestamp conflicts resolution
5. Clear browser storage after successful merge

#### Change Detection Algorithm

**Content Hashing Strategy:**
```
1. Strip HTML tags, normalize whitespace
2. Split into paragraphs
3. Generate hash per paragraph using xxHash (fast)
4. Compare hash arrays between versions
5. Calculate change significance:
   - <5% different = minor
   - 5-20% = moderate  
   - >20% = major
```

**Why xxHash:** 
- Faster than MD5/SHA for non-cryptographic use
- Good distribution for change detection
- Small memory footprint

#### Performance Optimizations

**Query Strategy:**
- Single query per page load via JOIN
- Batch inserts for multiple reads
- Transient caching for user statistics
- Lazy load tracking script after DOMContentLoaded

**Caching Layers:**
1. WordPress Transients (12 hours) for user stats
2. Browser cache for static assets (1 week)
3. LocalStorage for temporary read state
4. Optional: Object cache (Redis/Memcached) support

### REST API Design

#### Endpoints

```
GET  /wp-json/completionist/v1/progress
POST /wp-json/completionist/v1/track
GET  /wp-json/completionist/v1/stats
POST /wp-json/completionist/v1/bind-anonymous
GET  /wp-json/completionist/v1/updates
POST /wp-json/completionist/v1/preferences
GET  /wp-json/completionist/v1/export
```

#### Response Format
```json
{
  "success": true,
  "data": {
    "reads": [...],
    "stats": {
      "total_posts": 150,
      "read_posts": 47,
      "completion_percent": 31.3
    }
  },
  "meta": {
    "version": "1.0.0",
    "cached": false
  }
}
```

### Security Considerations

#### Data Protection
- Nonce verification on all AJAX calls
- Capability checks for admin features
- Prepared statements for all queries
- Input sanitization via WordPress functions
- Rate limiting on API endpoints

#### Privacy Implementation
- No tracking until explicit consent
- Anonymized data by default
- User-deletable history
- Export functionality for data portability
- Cookie-less tracking option

### Testing Strategy

#### PHP Testing Stack
- **PHPUnit 9.x** with WP_Mock for unit tests
- **Brain Monkey** for testing hooks/filters
- **Mockery** for complex mocks
- **WordPress Test Suite** for integration tests

#### JavaScript Testing
- **Jest** for Vue components
- **@testing-library/vue** for component testing
- **Playwright** for E2E testing
- **MSW** for API mocking

#### Quality Gates
- Minimum 80% code coverage
- PHPStan level 8 passing
- WPCS standards met
- All Playwright tests passing
- Performance budget: <50ms impact

### Build & Deployment Pipeline

#### Development Workflow
```bash
# Setup
composer install
npm install
wp-env start

# Development
npm run start       # Watches files
composer test       # Runs PHPUnit
npm run test        # Runs Jest
npm run lint        # ESLint + PHPCS

# Production
npm run build       # Optimized assets
composer build      # Removes dev dependencies
```

#### CI/CD Pipeline (GitHub Actions)
1. On Pull Request:
   - PHPUnit tests (multiple WP/PHP versions)
   - Jest tests
   - PHPCS/ESLint
   - PHPStan analysis

2. On Main Branch:
   - Full test suite
   - Build production assets
   - Generate plugin ZIP
   - Deploy to testing server

3. On Tag:
   - Deploy to WordPress.org SVN
   - Create GitHub release
   - Update documentation

### Scalability Considerations

#### Handling Growth
- **1K users**: Default configuration
- **10K users**: Enable object caching
- **100K users**: Database read replicas
- **1M+ users**: Consider external service extraction

#### Database Optimization
- Partition tables by date for large datasets
- Archive old reads after 1 year
- Implement cleanup cron for orphaned records
- Use bulk operations for migrations

### Integration Points

#### Future WordPress.com Integration
- OAuth2 for WordPress.com authentication
- Jetpack connection for sync
- WordPress.com Reader API compatibility
- ActivityPub support for federated reading

#### Third-Party Compatibility
- WooCommerce: Track product views
- LearnDash: Course progress integration
- Yoast SEO: Reading time in metadata
- Popular page builders: Custom blocks

### Monitoring & Analytics

#### Performance Metrics
- Time to First Read Track: <100ms
- API response time: <200ms
- Database query count: <5 per page
- JavaScript bundle size: <50KB gzipped

#### Error Tracking
- WordPress debug logging
- Browser error reporting
- API error responses
- Admin dashboard health check

---

*Next Document: [03-use-cases.md - Detailed User Stories & Test Cases]*