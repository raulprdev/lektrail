const fs = require('fs');
const path = require('path');

function post(id, title) {
    return { id, title, url: `/${id}` };
}

const dataSourceCode = fs.readFileSync(
    path.join(__dirname, '../../assets/js/data-source.js'),
    'utf8'
);

beforeEach(() => {
    eval(dataSourceCode);
});

afterEach(() => {
    delete window.LekTrailDataSource;
});

describe('DataSource: server-side mode', () => {
    test('creates server-side source from inline data', () => {
        const inlineData = {
            viewed: [post(1, 'Post 1')],
            read: [post(2, 'Post 2')],
            suggestions: [post(3, 'Post 3')]
        };

        const source = window.LekTrailDataSource.create({ inlineData });

        expect(source.getViewed()).toEqual(inlineData.viewed);
        expect(source.getRead()).toEqual(inlineData.read);
    });

    test('returns viewed count from array length', () => {
        const inlineData = {
            viewed: [post(1, 'A'), post(2, 'B'), post(3, 'C')],
            read: [],
            suggestions: []
        };

        const source = window.LekTrailDataSource.create({ inlineData });

        expect(source.getViewedCount()).toBe(3);
    });

    test('returns read count from array length', () => {
        const inlineData = {
            viewed: [],
            read: [post(1, 'A'), post(2, 'B')],
            suggestions: []
        };

        const source = window.LekTrailDataSource.create({ inlineData });

        expect(source.getReadCount()).toBe(2);
    });

    test('getSuggestions calls callback with inline suggestions', (done) => {
        const inlineData = {
            viewed: [],
            read: [],
            suggestions: [post(1, 'Suggestion')]
        };

        const source = window.LekTrailDataSource.create({ inlineData });

        source.getSuggestions((suggestions) => {
            expect(suggestions).toEqual(inlineData.suggestions);
            done();
        });
    });

    test('handles empty inline data gracefully', () => {
        const source = window.LekTrailDataSource.create({ inlineData: {} });

        expect(source.getViewed()).toEqual([]);
        expect(source.getRead()).toEqual([]);
        expect(source.getViewedCount()).toBe(0);
        expect(source.getReadCount()).toBe(0);
    });
});

describe('DataSource: localStorage mode', () => {
    function mockStorage(data = {}) {
        return {
            getViewedPosts: () => data.viewedPosts || [],
            getReadPosts: () => data.readPosts || [],
            getViewedCount: () => (data.viewedPosts || []).length,
            getReadCount: () => (data.readPosts || []).length,
            getSuggestions: () => data.suggestions || [],
            setSuggestions: jest.fn(),
            isSuggestionsCacheValid: () => data.cacheValid || false
        };
    }

    function mockFetcher(response) {
        return {
            fetch: (callback) => callback(response)
        };
    }

    test('returns viewed posts from storage', () => {
        const storage = mockStorage({
            viewedPosts: [post(1, 'Viewed')]
        });

        const source = window.LekTrailDataSource.create({ storage });

        expect(source.getViewed()).toEqual([post(1, 'Viewed')]);
    });

    test('returns read posts from storage', () => {
        const storage = mockStorage({
            readPosts: [post(1, 'Read')]
        });

        const source = window.LekTrailDataSource.create({ storage });

        expect(source.getRead()).toEqual([post(1, 'Read')]);
    });

    test('returns viewed count from storage', () => {
        const storage = mockStorage({
            viewedPosts: [post(1, 'A'), post(2, 'B')]
        });

        const source = window.LekTrailDataSource.create({ storage });

        expect(source.getViewedCount()).toBe(2);
    });

    test('returns cached suggestions when cache is valid', (done) => {
        const storage = mockStorage({
            suggestions: [post(1, 'Cached')],
            cacheValid: true
        });
        const fetcher = mockFetcher([post(2, 'Fetched')]);

        const source = window.LekTrailDataSource.create({ storage, fetcher });

        source.getSuggestions((suggestions) => {
            expect(suggestions).toEqual([post(1, 'Cached')]);
            done();
        });
    });

    test('fetches suggestions when cache is invalid', (done) => {
        const storage = mockStorage({ cacheValid: false });
        const fetcher = mockFetcher([post(1, 'Fetched')]);

        const source = window.LekTrailDataSource.create({ storage, fetcher });

        source.getSuggestions((suggestions) => {
            expect(suggestions).toEqual([post(1, 'Fetched')]);
            done();
        });
    });

    test('caches fetched suggestions', (done) => {
        const storage = mockStorage({ cacheValid: false });
        const fetcher = mockFetcher([post(1, 'Fetched')]);

        const source = window.LekTrailDataSource.create({ storage, fetcher });

        source.getSuggestions(() => {
            expect(storage.setSuggestions).toHaveBeenCalledWith([post(1, 'Fetched')]);
            done();
        });
    });
});