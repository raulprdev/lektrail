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
        expect(CompletionistStorage.getViewedCount()).toBe(0);
        expect(CompletionistStorage.getReadCount()).toBe(0);
        expect(CompletionistStorage.getViewedIds()).toEqual([]);
        expect(CompletionistStorage.getReadIds()).toEqual([]);
    });
});

describe('Storage: add viewed', () => {
    test('adding a viewed post increments viewed count', () => {
        CompletionistStorage.addViewed(123);

        expect(CompletionistStorage.getViewedCount()).toBe(1);
    });

    test('adding same post as viewed twice does not duplicate', () => {
        CompletionistStorage.addViewed(123);
        CompletionistStorage.addViewed(123);

        expect(CompletionistStorage.getViewedCount()).toBe(1);
    });

    test('getViewedIds returns the viewed post id', () => {
        CompletionistStorage.addViewed(123);

        expect(CompletionistStorage.getViewedIds()).toContain(123);
    });
});

describe('Storage: add read', () => {
    test('adding a read post increments read count', () => {
        CompletionistStorage.addRead(123);

        expect(CompletionistStorage.getReadCount()).toBe(1);
    });

    test('adding a read post removes it from viewed (promotion)', () => {
        CompletionistStorage.addViewed(123);
        expect(CompletionistStorage.getViewedCount()).toBe(1);

        CompletionistStorage.addRead(123);

        expect(CompletionistStorage.getViewedCount()).toBe(0);
        expect(CompletionistStorage.getReadCount()).toBe(1);
    });

    test('if post was not viewed, adding as read still works', () => {
        CompletionistStorage.addRead(123);

        expect(CompletionistStorage.getReadCount()).toBe(1);
        expect(CompletionistStorage.hasRead(123)).toBe(true);
    });

    test('getReadIds returns the read post id', () => {
        CompletionistStorage.addRead(123);

        expect(CompletionistStorage.getReadIds()).toContain(123);
    });

    test('addRead ignores duplicates', () => {
        CompletionistStorage.addRead(123);
        CompletionistStorage.addRead(123);

        expect(CompletionistStorage.getReadCount()).toBe(1);
    });
});

describe('Storage: state queries', () => {
    test('hasViewed returns true for viewed post', () => {
        CompletionistStorage.addViewed(123);

        expect(CompletionistStorage.hasViewed(123)).toBe(true);
    });

    test('hasViewed returns false for read post (promoted out)', () => {
        CompletionistStorage.addViewed(123);
        CompletionistStorage.addRead(123);

        expect(CompletionistStorage.hasViewed(123)).toBe(false);
    });

    test('hasViewed returns false for unknown post', () => {
        expect(CompletionistStorage.hasViewed(999)).toBe(false);
    });

    test('hasRead returns true for read post', () => {
        CompletionistStorage.addRead(123);

        expect(CompletionistStorage.hasRead(123)).toBe(true);
    });

    test('hasRead returns false for viewed post', () => {
        CompletionistStorage.addViewed(123);

        expect(CompletionistStorage.hasRead(123)).toBe(false);
    });

    test('hasRead returns false for unknown post', () => {
        expect(CompletionistStorage.hasRead(999)).toBe(false);
    });

    test('isTracked returns true for viewed post', () => {
        CompletionistStorage.addViewed(123);

        expect(CompletionistStorage.isTracked(123)).toBe(true);
    });

    test('isTracked returns true for read post', () => {
        CompletionistStorage.addRead(123);

        expect(CompletionistStorage.isTracked(123)).toBe(true);
    });

    test('isTracked returns false for unknown post', () => {
        expect(CompletionistStorage.isTracked(999)).toBe(false);
    });
});

describe('Storage: counts', () => {
    test('getViewedCount returns count of viewed posts', () => {
        CompletionistStorage.addViewed(1);
        CompletionistStorage.addViewed(2);
        CompletionistStorage.addViewed(3);

        expect(CompletionistStorage.getViewedCount()).toBe(3);
    });

    test('getReadCount returns count of read posts', () => {
        CompletionistStorage.addRead(1);
        CompletionistStorage.addRead(2);

        expect(CompletionistStorage.getReadCount()).toBe(2);
    });
});

describe('Storage: clear', () => {
    test('clear removes all viewed and read', () => {
        CompletionistStorage.addViewed(1);
        CompletionistStorage.addRead(2);
        CompletionistStorage.clear();

        expect(CompletionistStorage.getViewedCount()).toBe(0);
        expect(CompletionistStorage.getReadCount()).toBe(0);
    });
});

describe('Storage: migration from old format', () => {
    test('migrates old reads array to new read array', () => {
        localStorage.setItem('completionist', JSON.stringify({
            reads: [{ postId: 123, readAt: '2024-01-01' }]
        }));

        const script = new Function(storageCode);
        script();

        expect(CompletionistStorage.getReadCount()).toBe(1);
        expect(CompletionistStorage.hasRead(123)).toBe(true);
        expect(CompletionistStorage.getViewedCount()).toBe(0);
    });
});

describe('Storage: post data caching', () => {
    test('addViewed stores post data when provided', () => {
        CompletionistStorage.addViewed(123, { title: 'Test Post', url: '/test-post' });

        const posts = CompletionistStorage.getViewedPosts();
        expect(posts).toHaveLength(1);
        expect(posts[0].id).toBe(123);
        expect(posts[0].title).toBe('Test Post');
        expect(posts[0].url).toBe('/test-post');
    });

    test('addViewed works without post data (backwards compatible)', () => {
        CompletionistStorage.addViewed(123);

        expect(CompletionistStorage.getViewedCount()).toBe(1);
        expect(CompletionistStorage.getViewedIds()).toContain(123);
    });

    test('getViewedPosts returns full post objects', () => {
        CompletionistStorage.addViewed(1, { title: 'Post 1', url: '/post-1' });
        CompletionistStorage.addViewed(2, { title: 'Post 2', url: '/post-2' });

        const posts = CompletionistStorage.getViewedPosts();
        expect(posts).toHaveLength(2);
        expect(posts[0]).toMatchObject({ id: 2, title: 'Post 2', url: '/post-2' });
        expect(posts[1]).toMatchObject({ id: 1, title: 'Post 1', url: '/post-1' });
    });

    test('addViewed stores excerpt and thumbnail', () => {
        CompletionistStorage.addViewed(123, {
            title: 'Test Post',
            url: '/test-post',
            excerpt: 'This is the excerpt.',
            thumbnail: 'http://example.com/image.jpg'
        });

        const posts = CompletionistStorage.getViewedPosts();
        expect(posts[0].excerpt).toBe('This is the excerpt.');
        expect(posts[0].thumbnail).toBe('http://example.com/image.jpg');
    });

    test('addRead stores post data when provided', () => {
        CompletionistStorage.addRead(123, { title: 'Test Post', url: '/test-post' });

        const posts = CompletionistStorage.getReadPosts();
        expect(posts).toHaveLength(1);
        expect(posts[0].id).toBe(123);
        expect(posts[0].title).toBe('Test Post');
        expect(posts[0].url).toBe('/test-post');
    });

    test('addRead preserves post data from viewed entry', () => {
        CompletionistStorage.addViewed(123, { title: 'Test Post', url: '/test-post' });
        CompletionistStorage.addRead(123);

        const posts = CompletionistStorage.getReadPosts();
        expect(posts[0].title).toBe('Test Post');
        expect(posts[0].url).toBe('/test-post');
    });

    test('getReadPosts returns full post objects', () => {
        CompletionistStorage.addRead(1, { title: 'Post 1', url: '/post-1' });
        CompletionistStorage.addRead(2, { title: 'Post 2', url: '/post-2' });

        const posts = CompletionistStorage.getReadPosts();
        expect(posts).toHaveLength(2);
        expect(posts[0]).toMatchObject({ id: 2, title: 'Post 2', url: '/post-2' });
    });

    test('addRead stores excerpt and thumbnail', () => {
        CompletionistStorage.addRead(123, {
            title: 'Test Post',
            url: '/test-post',
            excerpt: 'This is the excerpt.',
            thumbnail: 'http://example.com/image.jpg'
        });

        const posts = CompletionistStorage.getReadPosts();
        expect(posts[0].excerpt).toBe('This is the excerpt.');
        expect(posts[0].thumbnail).toBe('http://example.com/image.jpg');
    });

    test('addRead preserves excerpt and thumbnail from viewed entry', () => {
        CompletionistStorage.addViewed(123, {
            title: 'Test Post',
            url: '/test-post',
            excerpt: 'Original excerpt.',
            thumbnail: 'http://example.com/original.jpg'
        });
        CompletionistStorage.addRead(123);

        const posts = CompletionistStorage.getReadPosts();
        expect(posts[0].excerpt).toBe('Original excerpt.');
        expect(posts[0].thumbnail).toBe('http://example.com/original.jpg');
    });

    test('getViewedPosts returns most recent first', () => {
        CompletionistStorage.addViewed(1, { title: 'First', url: '/first' });
        CompletionistStorage.addViewed(2, { title: 'Second', url: '/second' });
        CompletionistStorage.addViewed(3, { title: 'Third', url: '/third' });

        const posts = CompletionistStorage.getViewedPosts();
        expect(posts[0].id).toBe(3);
        expect(posts[1].id).toBe(2);
        expect(posts[2].id).toBe(1);
    });

    test('getReadPosts returns most recent first', () => {
        CompletionistStorage.addRead(1, { title: 'First', url: '/first' });
        CompletionistStorage.addRead(2, { title: 'Second', url: '/second' });
        CompletionistStorage.addRead(3, { title: 'Third', url: '/third' });

        const posts = CompletionistStorage.getReadPosts();
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

        CompletionistStorage.setSuggestions(suggestions);

        const cached = CompletionistStorage.getSuggestions();
        expect(cached).toHaveLength(2);
        expect(cached[0]).toMatchObject({ id: 1, title: 'Suggested 1' });
    });

    test('getSuggestions returns empty array when no cache', () => {
        expect(CompletionistStorage.getSuggestions()).toEqual([]);
    });

    test('isSuggestionsCacheValid returns false when empty', () => {
        expect(CompletionistStorage.isSuggestionsCacheValid(24)).toBe(false);
    });

    test('isSuggestionsCacheValid returns false when expired', () => {
        const oldDate = new Date(Date.now() - 25 * 60 * 60 * 1000).toISOString();
        localStorage.setItem('completionist', JSON.stringify({
            viewed: [],
            read: [],
            suggestions: [{ id: 1, title: 'Test', url: '/test' }],
            suggestionsUpdatedAt: oldDate
        }));

        const script = new Function(storageCode);
        script();

        expect(CompletionistStorage.isSuggestionsCacheValid(24)).toBe(false);
    });

    test('isSuggestionsCacheValid returns true when fresh', () => {
        CompletionistStorage.setSuggestions([{ id: 1, title: 'Test', url: '/test' }]);

        expect(CompletionistStorage.isSuggestionsCacheValid(24)).toBe(true);
    });

    test('clearSuggestionsCache removes suggestions and timestamp', () => {
        CompletionistStorage.setSuggestions([{ id: 1, title: 'Test', url: '/test' }]);
        CompletionistStorage.clearSuggestionsCache();

        expect(CompletionistStorage.getSuggestions()).toEqual([]);
        expect(CompletionistStorage.isSuggestionsCacheValid(24)).toBe(false);
    });
});

describe('Storage: cache invalidation on tracking', () => {
    test('addViewed clears cache when post is in suggestions', () => {
        CompletionistStorage.setSuggestions([
            { id: 1, title: 'Suggestion 1', url: '/s1' },
            { id: 2, title: 'Suggestion 2', url: '/s2' }
        ]);

        CompletionistStorage.addViewed(1, { title: 'Suggestion 1', url: '/s1' });

        expect(CompletionistStorage.isSuggestionsCacheValid(24)).toBe(false);
    });

    test('addViewed keeps cache when post is not in suggestions', () => {
        CompletionistStorage.setSuggestions([
            { id: 1, title: 'Suggestion 1', url: '/s1' },
            { id: 2, title: 'Suggestion 2', url: '/s2' }
        ]);

        CompletionistStorage.addViewed(99, { title: 'Other Post', url: '/other' });

        expect(CompletionistStorage.isSuggestionsCacheValid(24)).toBe(true);
    });

    test('addRead clears cache when post is in suggestions', () => {
        CompletionistStorage.setSuggestions([
            { id: 1, title: 'Suggestion 1', url: '/s1' },
            { id: 2, title: 'Suggestion 2', url: '/s2' }
        ]);

        CompletionistStorage.addRead(2, { title: 'Suggestion 2', url: '/s2' });

        expect(CompletionistStorage.isSuggestionsCacheValid(24)).toBe(false);
    });

    test('addRead keeps cache when post is not in suggestions', () => {
        CompletionistStorage.setSuggestions([
            { id: 1, title: 'Suggestion 1', url: '/s1' },
            { id: 2, title: 'Suggestion 2', url: '/s2' }
        ]);

        CompletionistStorage.addRead(99, { title: 'Other Post', url: '/other' });

        expect(CompletionistStorage.isSuggestionsCacheValid(24)).toBe(true);
    });
});