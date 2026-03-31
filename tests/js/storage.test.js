const fs = require('fs');
const path = require('path');

const storageCode = fs.readFileSync(
    path.join(__dirname, '../../assets/js/storage.js'),
    'utf8'
);

beforeEach(() => {
    localStorage.clear();
    const script = new Function(storageCode);
    script();
});

describe('Storage: empty state', () => {
    test('new storage has 0 viewed and 0 read', () => {
        expect(LekTrailStorage.getViewedCount()).toBe(0);
        expect(LekTrailStorage.getReadCount()).toBe(0);
        expect(LekTrailStorage.getViewedIds()).toEqual([]);
        expect(LekTrailStorage.getReadIds()).toEqual([]);
    });
});

describe('Storage: add viewed', () => {
    test('adding a viewed post increments viewed count', () => {
        LekTrailStorage.addViewed(123);

        expect(LekTrailStorage.getViewedCount()).toBe(1);
    });

    test('adding same post as viewed twice does not duplicate', () => {
        LekTrailStorage.addViewed(123);
        LekTrailStorage.addViewed(123);

        expect(LekTrailStorage.getViewedCount()).toBe(1);
    });

    test('getViewedIds returns the viewed post id', () => {
        LekTrailStorage.addViewed(123);

        expect(LekTrailStorage.getViewedIds()).toContain(123);
    });
});

describe('Storage: add read', () => {
    test('adding a read post increments read count', () => {
        LekTrailStorage.addRead(123);

        expect(LekTrailStorage.getReadCount()).toBe(1);
    });

    test('adding a read post removes it from viewed (promotion)', () => {
        LekTrailStorage.addViewed(123);
        expect(LekTrailStorage.getViewedCount()).toBe(1);

        LekTrailStorage.addRead(123);

        expect(LekTrailStorage.getViewedCount()).toBe(0);
        expect(LekTrailStorage.getReadCount()).toBe(1);
    });

    test('if post was not viewed, adding as read still works', () => {
        LekTrailStorage.addRead(123);

        expect(LekTrailStorage.getReadCount()).toBe(1);
        expect(LekTrailStorage.hasRead(123)).toBe(true);
    });

    test('getReadIds returns the read post id', () => {
        LekTrailStorage.addRead(123);

        expect(LekTrailStorage.getReadIds()).toContain(123);
    });

    test('addRead ignores duplicates', () => {
        LekTrailStorage.addRead(123);
        LekTrailStorage.addRead(123);

        expect(LekTrailStorage.getReadCount()).toBe(1);
    });
});

describe('Storage: state queries', () => {
    test('hasViewed returns true for viewed post', () => {
        LekTrailStorage.addViewed(123);

        expect(LekTrailStorage.hasViewed(123)).toBe(true);
    });

    test('hasViewed returns false for read post (promoted out)', () => {
        LekTrailStorage.addViewed(123);
        LekTrailStorage.addRead(123);

        expect(LekTrailStorage.hasViewed(123)).toBe(false);
    });

    test('hasViewed returns false for unknown post', () => {
        expect(LekTrailStorage.hasViewed(999)).toBe(false);
    });

    test('hasRead returns true for read post', () => {
        LekTrailStorage.addRead(123);

        expect(LekTrailStorage.hasRead(123)).toBe(true);
    });

    test('hasRead returns false for viewed post', () => {
        LekTrailStorage.addViewed(123);

        expect(LekTrailStorage.hasRead(123)).toBe(false);
    });

    test('hasRead returns false for unknown post', () => {
        expect(LekTrailStorage.hasRead(999)).toBe(false);
    });

    test('isTracked returns true for viewed post', () => {
        LekTrailStorage.addViewed(123);

        expect(LekTrailStorage.isTracked(123)).toBe(true);
    });

    test('isTracked returns true for read post', () => {
        LekTrailStorage.addRead(123);

        expect(LekTrailStorage.isTracked(123)).toBe(true);
    });

    test('isTracked returns false for unknown post', () => {
        expect(LekTrailStorage.isTracked(999)).toBe(false);
    });
});

describe('Storage: counts', () => {
    test('getViewedCount returns count of viewed posts', () => {
        LekTrailStorage.addViewed(1);
        LekTrailStorage.addViewed(2);
        LekTrailStorage.addViewed(3);

        expect(LekTrailStorage.getViewedCount()).toBe(3);
    });

    test('getReadCount returns count of read posts', () => {
        LekTrailStorage.addRead(1);
        LekTrailStorage.addRead(2);

        expect(LekTrailStorage.getReadCount()).toBe(2);
    });
});

describe('Storage: clear', () => {
    test('clear removes all viewed and read', () => {
        LekTrailStorage.addViewed(1);
        LekTrailStorage.addRead(2);
        LekTrailStorage.clear();

        expect(LekTrailStorage.getViewedCount()).toBe(0);
        expect(LekTrailStorage.getReadCount()).toBe(0);
    });
});

describe('Storage: migration from old format', () => {
    test('migrates old reads array to new read array', () => {
        localStorage.setItem('lektrail', JSON.stringify({
            reads: [{ postId: 123, readAt: '2024-01-01' }]
        }));

        const script = new Function(storageCode);
        script();

        expect(LekTrailStorage.getReadCount()).toBe(1);
        expect(LekTrailStorage.hasRead(123)).toBe(true);
        expect(LekTrailStorage.getViewedCount()).toBe(0);
    });
});

describe('Storage: post data caching', () => {
    test('addViewed stores post data when provided', () => {
        LekTrailStorage.addViewed(123, { title: 'Test Post', url: '/test-post' });

        const posts = LekTrailStorage.getViewedPosts();
        expect(posts).toHaveLength(1);
        expect(posts[0].id).toBe(123);
        expect(posts[0].title).toBe('Test Post');
        expect(posts[0].url).toBe('/test-post');
    });

    test('addViewed works without post data (backwards compatible)', () => {
        LekTrailStorage.addViewed(123);

        expect(LekTrailStorage.getViewedCount()).toBe(1);
        expect(LekTrailStorage.getViewedIds()).toContain(123);
    });

    test('getViewedPosts returns full post objects', () => {
        LekTrailStorage.addViewed(1, { title: 'Post 1', url: '/post-1' });
        LekTrailStorage.addViewed(2, { title: 'Post 2', url: '/post-2' });

        const posts = LekTrailStorage.getViewedPosts();
        expect(posts).toHaveLength(2);
        expect(posts[0]).toMatchObject({ id: 2, title: 'Post 2', url: '/post-2' });
        expect(posts[1]).toMatchObject({ id: 1, title: 'Post 1', url: '/post-1' });
    });

    test('addViewed stores excerpt and thumbnail', () => {
        LekTrailStorage.addViewed(123, {
            title: 'Test Post',
            url: '/test-post',
            excerpt: 'This is the excerpt.',
            thumbnail: 'http://example.com/image.jpg'
        });

        const posts = LekTrailStorage.getViewedPosts();
        expect(posts[0].excerpt).toBe('This is the excerpt.');
        expect(posts[0].thumbnail).toBe('http://example.com/image.jpg');
    });

    test('addRead stores post data when provided', () => {
        LekTrailStorage.addRead(123, { title: 'Test Post', url: '/test-post' });

        const posts = LekTrailStorage.getReadPosts();
        expect(posts).toHaveLength(1);
        expect(posts[0].id).toBe(123);
        expect(posts[0].title).toBe('Test Post');
        expect(posts[0].url).toBe('/test-post');
    });

    test('addRead preserves post data from viewed entry', () => {
        LekTrailStorage.addViewed(123, { title: 'Test Post', url: '/test-post' });
        LekTrailStorage.addRead(123);

        const posts = LekTrailStorage.getReadPosts();
        expect(posts[0].title).toBe('Test Post');
        expect(posts[0].url).toBe('/test-post');
    });

    test('getReadPosts returns full post objects', () => {
        LekTrailStorage.addRead(1, { title: 'Post 1', url: '/post-1' });
        LekTrailStorage.addRead(2, { title: 'Post 2', url: '/post-2' });

        const posts = LekTrailStorage.getReadPosts();
        expect(posts).toHaveLength(2);
        expect(posts[0]).toMatchObject({ id: 2, title: 'Post 2', url: '/post-2' });
    });

    test('addRead stores excerpt and thumbnail', () => {
        LekTrailStorage.addRead(123, {
            title: 'Test Post',
            url: '/test-post',
            excerpt: 'This is the excerpt.',
            thumbnail: 'http://example.com/image.jpg'
        });

        const posts = LekTrailStorage.getReadPosts();
        expect(posts[0].excerpt).toBe('This is the excerpt.');
        expect(posts[0].thumbnail).toBe('http://example.com/image.jpg');
    });

    test('addRead preserves excerpt and thumbnail from viewed entry', () => {
        LekTrailStorage.addViewed(123, {
            title: 'Test Post',
            url: '/test-post',
            excerpt: 'Original excerpt.',
            thumbnail: 'http://example.com/original.jpg'
        });
        LekTrailStorage.addRead(123);

        const posts = LekTrailStorage.getReadPosts();
        expect(posts[0].excerpt).toBe('Original excerpt.');
        expect(posts[0].thumbnail).toBe('http://example.com/original.jpg');
    });

    test('getViewedPosts returns most recent first', () => {
        LekTrailStorage.addViewed(1, { title: 'First', url: '/first' });
        LekTrailStorage.addViewed(2, { title: 'Second', url: '/second' });
        LekTrailStorage.addViewed(3, { title: 'Third', url: '/third' });

        const posts = LekTrailStorage.getViewedPosts();
        expect(posts[0].id).toBe(3);
        expect(posts[1].id).toBe(2);
        expect(posts[2].id).toBe(1);
    });

    test('getReadPosts returns most recent first', () => {
        LekTrailStorage.addRead(1, { title: 'First', url: '/first' });
        LekTrailStorage.addRead(2, { title: 'Second', url: '/second' });
        LekTrailStorage.addRead(3, { title: 'Third', url: '/third' });

        const posts = LekTrailStorage.getReadPosts();
        expect(posts[0].id).toBe(3);
        expect(posts[1].id).toBe(2);
        expect(posts[2].id).toBe(1);
    });
});

describe('Storage: suggestions cache', () => {
    test('setSuggestions stores posts with timestamp', () => {
        const suggestions = [
            { id: 1, title: 'Suggested 1', url: '/suggested-1' },
            { id: 2, title: 'Suggested 2', url: '/suggested-2' }
        ];

        LekTrailStorage.setSuggestions(suggestions);

        const cached = LekTrailStorage.getSuggestions();
        expect(cached).toHaveLength(2);
        expect(cached[0]).toMatchObject({ id: 1, title: 'Suggested 1' });
    });

    test('getSuggestions returns empty array when no cache', () => {
        expect(LekTrailStorage.getSuggestions()).toEqual([]);
    });

    test('isSuggestionsCacheValid returns false when empty', () => {
        expect(LekTrailStorage.isSuggestionsCacheValid(24)).toBe(false);
    });

    test('isSuggestionsCacheValid returns false when expired', () => {
        const oldDate = new Date(Date.now() - 25 * 60 * 60 * 1000).toISOString();
        localStorage.setItem('lektrail', JSON.stringify({
            viewed: [],
            read: [],
            suggestions: [{ id: 1, title: 'Test', url: '/test' }],
            suggestionsUpdatedAt: oldDate
        }));

        const script = new Function(storageCode);
        script();

        expect(LekTrailStorage.isSuggestionsCacheValid(24)).toBe(false);
    });

    test('isSuggestionsCacheValid returns true when fresh', () => {
        LekTrailStorage.setSuggestions([{ id: 1, title: 'Test', url: '/test' }]);

        expect(LekTrailStorage.isSuggestionsCacheValid(24)).toBe(true);
    });

    test('clearSuggestionsCache removes suggestions and timestamp', () => {
        LekTrailStorage.setSuggestions([{ id: 1, title: 'Test', url: '/test' }]);
        LekTrailStorage.clearSuggestionsCache();

        expect(LekTrailStorage.getSuggestions()).toEqual([]);
        expect(LekTrailStorage.isSuggestionsCacheValid(24)).toBe(false);
    });
});

describe('Storage: clearHistory', () => {
    test('clearHistory removes viewed and read but keeps suggestions', () => {
        LekTrailStorage.addViewed(1, { title: 'Post 1', url: '/1' });
        LekTrailStorage.addRead(2, { title: 'Post 2', url: '/2' });
        LekTrailStorage.setSuggestions([{ id: 3, title: 'Suggestion', url: '/3' }]);

        LekTrailStorage.clearHistory();

        expect(LekTrailStorage.getViewedCount()).toBe(0);
        expect(LekTrailStorage.getReadCount()).toBe(0);
        expect(LekTrailStorage.getSuggestions()).toHaveLength(1);
        expect(LekTrailStorage.isSuggestionsCacheValid(24)).toBe(true);
    });
});

describe('Storage: cache invalidation on tracking', () => {
    test('addViewed clears cache when post is in suggestions', () => {
        LekTrailStorage.setSuggestions([
            { id: 1, title: 'Suggestion 1', url: '/s1' },
            { id: 2, title: 'Suggestion 2', url: '/s2' }
        ]);

        LekTrailStorage.addViewed(1, { title: 'Suggestion 1', url: '/s1' });

        expect(LekTrailStorage.isSuggestionsCacheValid(24)).toBe(false);
    });

    test('addViewed keeps cache when post is not in suggestions', () => {
        LekTrailStorage.setSuggestions([
            { id: 1, title: 'Suggestion 1', url: '/s1' },
            { id: 2, title: 'Suggestion 2', url: '/s2' }
        ]);

        LekTrailStorage.addViewed(99, { title: 'Other Post', url: '/other' });

        expect(LekTrailStorage.isSuggestionsCacheValid(24)).toBe(true);
    });

    test('addRead clears cache when post is in suggestions', () => {
        LekTrailStorage.setSuggestions([
            { id: 1, title: 'Suggestion 1', url: '/s1' },
            { id: 2, title: 'Suggestion 2', url: '/s2' }
        ]);

        LekTrailStorage.addRead(2, { title: 'Suggestion 2', url: '/s2' });

        expect(LekTrailStorage.isSuggestionsCacheValid(24)).toBe(false);
    });

    test('addRead keeps cache when post is not in suggestions', () => {
        LekTrailStorage.setSuggestions([
            { id: 1, title: 'Suggestion 1', url: '/s1' },
            { id: 2, title: 'Suggestion 2', url: '/s2' }
        ]);

        LekTrailStorage.addRead(99, { title: 'Other Post', url: '/other' });

        expect(LekTrailStorage.isSuggestionsCacheValid(24)).toBe(true);
    });
});