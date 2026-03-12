const fs = require('fs');
const path = require('path');
const { mockStorage } = require('./helpers/test-helpers');

const dataProviderCode = fs.readFileSync(
    path.join(__dirname, '../../assets/js/data-provider.js'),
    'utf8'
);

function mockXhr() {
    return {
        open: jest.fn(),
        send: jest.fn(),
        onload: null,
        onerror: null,
        status: 200,
        responseText: '{"success":true,"data":[]}'
    };
}

beforeEach(() => {
    delete window.CompletionistDataProvider;
    delete window.CompletionistStorage;
});

describe('DataProvider async mode: getViewed', () => {
    test('returns storage viewed posts', () => {
        const viewedPosts = [
            { id: 1, title: 'Post 1', url: '/post-1' },
            { id: 2, title: 'Post 2', url: '/post-2' }
        ];
        window.CompletionistStorage = mockStorage({ viewedPosts });

        eval(dataProviderCode);
        const provider = window.CompletionistDataProvider.create({
            dataMode: 'async',
            endpoint: '/api'
        });

        expect(provider.getViewed()).toEqual(viewedPosts);
    });
});

describe('DataProvider async mode: getRead', () => {
    test('returns storage read posts', () => {
        const readPosts = [{ id: 3, title: 'Post 3', url: '/post-3' }];
        window.CompletionistStorage = mockStorage({ readPosts });

        eval(dataProviderCode);
        const provider = window.CompletionistDataProvider.create({
            dataMode: 'async',
            endpoint: '/api'
        });

        expect(provider.getRead()).toEqual(readPosts);
    });
});

describe('DataProvider async mode: getSuggestions', () => {
    test('fetches with exclude param', () => {
        const xhrInstances = [];
        window.XMLHttpRequest = jest.fn(() => {
            const xhr = mockXhr();
            xhrInstances.push(xhr);
            return xhr;
        });
        window.CompletionistStorage = mockStorage({
            viewedPosts: [{ id: 1, title: 'V1', url: '/1' }],
            readPosts: [{ id: 2, title: 'R1', url: '/2' }]
        });

        eval(dataProviderCode);
        const provider = window.CompletionistDataProvider.create({
            dataMode: 'async',
            endpoint: '/api?action=test'
        });
        provider.getSuggestions(() => {});

        expect(xhrInstances[0].open).toHaveBeenCalledWith(
            'GET',
            '/api?action=test&exclude=1,2'
        );
    });

    test('includes all tracked IDs in exclude', () => {
        const xhrInstances = [];
        window.XMLHttpRequest = jest.fn(() => {
            const xhr = mockXhr();
            xhrInstances.push(xhr);
            return xhr;
        });
        window.CompletionistStorage = mockStorage({
            viewedPosts: [
                { id: 10, title: 'V1', url: '/10' },
                { id: 20, title: 'V2', url: '/20' }
            ],
            readPosts: [{ id: 30, title: 'R1', url: '/30' }]
        });

        eval(dataProviderCode);
        const provider = window.CompletionistDataProvider.create({
            dataMode: 'async',
            endpoint: '/api?action=test'
        });
        provider.getSuggestions(() => {});

        expect(xhrInstances[0].open).toHaveBeenCalledWith(
            'GET',
            '/api?action=test&exclude=10,20,30'
        );
    });

    test('omits exclude param when no tracked posts', () => {
        const xhrInstances = [];
        window.XMLHttpRequest = jest.fn(() => {
            const xhr = mockXhr();
            xhrInstances.push(xhr);
            return xhr;
        });
        window.CompletionistStorage = mockStorage();

        eval(dataProviderCode);
        const provider = window.CompletionistDataProvider.create({
            dataMode: 'async',
            endpoint: '/api?action=test'
        });
        provider.getSuggestions(() => {});

        expect(xhrInstances[0].open).toHaveBeenCalledWith(
            'GET',
            '/api?action=test'
        );
    });

    test('calls callback with fetched suggestions', () => {
        const xhrInstances = [];
        window.XMLHttpRequest = jest.fn(() => {
            const xhr = mockXhr();
            xhrInstances.push(xhr);
            return xhr;
        });
        window.CompletionistStorage = mockStorage();

        eval(dataProviderCode);
        const provider = window.CompletionistDataProvider.create({
            dataMode: 'async',
            endpoint: '/api'
        });

        const callback = jest.fn();
        provider.getSuggestions(callback);

        xhrInstances[0].responseText = JSON.stringify({
            success: true,
            data: [{ id: 1, title: 'Suggestion', url: '/s1' }]
        });
        xhrInstances[0].onload();

        expect(callback).toHaveBeenCalledWith([
            { id: 1, title: 'Suggestion', url: '/s1' }
        ]);
    });
});

describe('DataProvider inline mode: getViewed', () => {
    test('returns inline data', () => {
        eval(dataProviderCode);
        const provider = window.CompletionistDataProvider.create({
            dataMode: 'inline',
            inlineData: {
                viewed: [{ id: 1, title: 'Inline V', url: '/v1' }],
                read: [],
                suggestions: []
            }
        });

        expect(provider.getViewed()).toEqual([
            { id: 1, title: 'Inline V', url: '/v1' }
        ]);
    });
});

describe('DataProvider inline mode: getRead', () => {
    test('returns inline data', () => {
        eval(dataProviderCode);
        const provider = window.CompletionistDataProvider.create({
            dataMode: 'inline',
            inlineData: {
                viewed: [],
                read: [{ id: 2, title: 'Inline R', url: '/r1' }],
                suggestions: []
            }
        });

        expect(provider.getRead()).toEqual([
            { id: 2, title: 'Inline R', url: '/r1' }
        ]);
    });
});

describe('DataProvider inline mode: getSuggestions', () => {
    test('calls callback immediately with inline data', () => {
        eval(dataProviderCode);
        const provider = window.CompletionistDataProvider.create({
            dataMode: 'inline',
            inlineData: {
                viewed: [],
                read: [],
                suggestions: [{ id: 3, title: 'Inline S', url: '/s1' }]
            }
        });

        const callback = jest.fn();
        provider.getSuggestions(callback);

        expect(callback).toHaveBeenCalledWith([
            { id: 3, title: 'Inline S', url: '/s1' }
        ]);
    });

    test('handles missing suggestions gracefully', () => {
        eval(dataProviderCode);
        const provider = window.CompletionistDataProvider.create({
            dataMode: 'inline',
            inlineData: {}
        });

        const callback = jest.fn();
        provider.getSuggestions(callback);

        expect(callback).toHaveBeenCalledWith([]);
    });
});

describe('DataProvider: default mode', () => {
    test('uses async mode by default', () => {
        const xhrInstances = [];
        window.XMLHttpRequest = jest.fn(() => {
            const xhr = mockXhr();
            xhrInstances.push(xhr);
            return xhr;
        });
        window.CompletionistStorage = mockStorage();

        eval(dataProviderCode);
        const provider = window.CompletionistDataProvider.create({
            endpoint: '/api?action=test'
        });

        provider.getSuggestions(() => {});
        expect(xhrInstances.length).toBe(1);
    });
});