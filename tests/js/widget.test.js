const fs = require('fs');
const path = require('path');

const widgetCode = fs.readFileSync(
    path.join(__dirname, '../../assets/js/widget.js'),
    'utf8'
);

function mockStorage(options = {}) {
    const viewedIds = options.viewedIds || [];
    const readIds = options.readIds || [];
    return {
        getViewedCount: () => viewedIds.length,
        getReadCount: () => readIds.length,
        getViewedIds: () => viewedIds,
        getReadIds: () => readIds,
        isTracked: id => viewedIds.includes(id) || readIds.includes(id)
    };
}

function mockXhr() {
    const xhr = {
        open: jest.fn(),
        send: jest.fn(),
        onload: null,
        status: 200,
        responseText: '{"success":true,"data":[]}'
    };
    return xhr;
}

describe('Widget: stats display', () => {
    let container;
    let originalXhr;

    beforeEach(() => {
        container = {
            innerHTML: '',
            dataset: { count: '5', endpoint: '/api' }
        };
        document.getElementById = jest.fn(id => {
            if (id === 'completionist-widget') return container;
            return null;
        });
        document.readyState = 'complete';
        originalXhr = window.XMLHttpRequest;
        window.XMLHttpRequest = jest.fn(() => mockXhr());
    });

    afterEach(() => {
        window.XMLHttpRequest = originalXhr;
        delete window.CompletionistStorage;
    });

    test('shows "X viewed, Y read" immediately from localStorage', () => {
        window.CompletionistStorage = mockStorage({ viewedIds: [1, 2], readIds: [3, 4, 5] });

        eval(widgetCode);

        expect(container.innerHTML).toContain('2');
        expect(container.innerHTML).toContain('viewed');
        expect(container.innerHTML).toContain('3');
        expect(container.innerHTML).toContain('read');
    });

    test('shows "Loading..." while fetching suggestions', () => {
        window.CompletionistStorage = mockStorage();

        eval(widgetCode);

        expect(container.innerHTML).toContain('Loading');
    });
});

describe('Widget: continue reading section', () => {
    let container;
    let xhrInstances;

    beforeEach(() => {
        container = {
            innerHTML: '',
            dataset: { count: '10', endpoint: '/api', postsEndpoint: '/wp-json/wp/v2/posts' }
        };
        document.getElementById = jest.fn(id => {
            if (id === 'completionist-widget') return container;
            return null;
        });
        document.readyState = 'complete';
        xhrInstances = [];
        window.XMLHttpRequest = jest.fn(() => {
            const xhr = mockXhr();
            xhrInstances.push(xhr);
            return xhr;
        });
    });

    afterEach(() => {
        delete window.CompletionistStorage;
    });

    function triggerAllXhr(viewedResponse, readResponse, suggestionsResponse) {
        xhrInstances.forEach(xhr => {
            const url = xhr.open.mock.calls[0]?.[1] || '';
            if (url.includes('include=') && viewedResponse && !url.includes('read')) {
                xhr.responseText = JSON.stringify(viewedResponse);
            } else if (url.includes('include=') && readResponse) {
                xhr.responseText = JSON.stringify(readResponse);
            } else {
                xhr.responseText = JSON.stringify(suggestionsResponse || { success: true, data: [] });
            }
            if (xhr.onload) xhr.onload();
        });
    }

    test('displays viewed posts with title and link', () => {
        window.CompletionistStorage = mockStorage({ viewedIds: [1] });

        eval(widgetCode);

        xhrInstances.forEach(xhr => {
            const url = xhr.open.mock.calls[0]?.[1] || '';
            if (url.includes('include=1')) {
                xhr.responseText = JSON.stringify([
                    { id: 1, title: { rendered: 'Viewed Post' }, link: '/viewed' }
                ]);
            } else {
                xhr.responseText = JSON.stringify({ success: true, data: [] });
            }
            if (xhr.onload) xhr.onload();
        });

        expect(container.innerHTML).toContain('Continue reading');
        expect(container.innerHTML).toContain('Viewed Post');
        expect(container.innerHTML).toContain('/viewed');
    });

    test('does not display read posts in continue reading section', () => {
        window.CompletionistStorage = mockStorage({ viewedIds: [1], readIds: [2] });

        eval(widgetCode);

        xhrInstances.forEach(xhr => {
            const url = xhr.open.mock.calls[0]?.[1] || '';
            if (url.includes('include=1')) {
                xhr.responseText = JSON.stringify([
                    { id: 1, title: { rendered: 'Viewed Post' }, link: '/viewed' }
                ]);
            } else if (url.includes('include=2')) {
                xhr.responseText = JSON.stringify([
                    { id: 2, title: { rendered: 'Read Post' }, link: '/read' }
                ]);
            } else {
                xhr.responseText = JSON.stringify({ success: true, data: [] });
            }
            if (xhr.onload) xhr.onload();
        });

        const continueSection = container.innerHTML.split('Completed')[0];
        expect(continueSection).toContain('Viewed Post');
        expect(continueSection).not.toContain('Read Post');
    });

    test('respects configured limit (max 3)', () => {
        window.CompletionistStorage = mockStorage({ viewedIds: [1, 2, 3, 4, 5] });

        eval(widgetCode);

        xhrInstances.forEach(xhr => {
            const url = xhr.open.mock.calls[0]?.[1] || '';
            if (url.includes('include=')) {
                xhr.responseText = JSON.stringify([
                    { id: 1, title: { rendered: 'Post 1' }, link: '/1' },
                    { id: 2, title: { rendered: 'Post 2' }, link: '/2' },
                    { id: 3, title: { rendered: 'Post 3' }, link: '/3' },
                    { id: 4, title: { rendered: 'Post 4' }, link: '/4' },
                    { id: 5, title: { rendered: 'Post 5' }, link: '/5' }
                ]);
            } else {
                xhr.responseText = JSON.stringify({ success: true, data: [] });
            }
            if (xhr.onload) xhr.onload();
        });

        const continueSection = container.innerHTML.split('Completed')[0];
        expect(continueSection).toContain('Post 1');
        expect(continueSection).toContain('Post 2');
        expect(continueSection).toContain('Post 3');
        expect(continueSection).not.toContain('Post 4');
    });

    test('does not show section if no viewed posts', () => {
        window.CompletionistStorage = mockStorage({ readIds: [1] });

        eval(widgetCode);

        xhrInstances.forEach(xhr => {
            const url = xhr.open.mock.calls[0]?.[1] || '';
            if (url.includes('include=1')) {
                xhr.responseText = JSON.stringify([
                    { id: 1, title: { rendered: 'Read Post' }, link: '/read' }
                ]);
            } else {
                xhr.responseText = JSON.stringify({ success: true, data: [] });
            }
            if (xhr.onload) xhr.onload();
        });

        expect(container.innerHTML).not.toContain('Continue reading');
    });
});

describe('Widget: completed section', () => {
    let container;
    let xhrInstances;

    beforeEach(() => {
        container = {
            innerHTML: '',
            dataset: { count: '10', endpoint: '/api', postsEndpoint: '/wp-json/wp/v2/posts' }
        };
        document.getElementById = jest.fn(id => {
            if (id === 'completionist-widget') return container;
            return null;
        });
        document.readyState = 'complete';
        xhrInstances = [];
        window.XMLHttpRequest = jest.fn(() => {
            const xhr = mockXhr();
            xhrInstances.push(xhr);
            return xhr;
        });
    });

    afterEach(() => {
        delete window.CompletionistStorage;
    });

    test('displays read posts with title and link', () => {
        window.CompletionistStorage = mockStorage({ readIds: [1] });

        eval(widgetCode);

        xhrInstances.forEach(xhr => {
            const url = xhr.open.mock.calls[0]?.[1] || '';
            if (url.includes('include=1')) {
                xhr.responseText = JSON.stringify([
                    { id: 1, title: { rendered: 'Read Post' }, link: '/read' }
                ]);
            } else {
                xhr.responseText = JSON.stringify({ success: true, data: [] });
            }
            if (xhr.onload) xhr.onload();
        });

        expect(container.innerHTML).toContain('Completed');
        expect(container.innerHTML).toContain('Read Post');
        expect(container.innerHTML).toContain('/read');
    });

    test('respects configured limit (max 5)', () => {
        window.CompletionistStorage = mockStorage({ readIds: [1, 2, 3, 4, 5, 6, 7] });

        eval(widgetCode);

        xhrInstances.forEach(xhr => {
            const url = xhr.open.mock.calls[0]?.[1] || '';
            if (url.includes('include=')) {
                xhr.responseText = JSON.stringify([
                    { id: 1, title: { rendered: 'Post 1' }, link: '/1' },
                    { id: 2, title: { rendered: 'Post 2' }, link: '/2' },
                    { id: 3, title: { rendered: 'Post 3' }, link: '/3' },
                    { id: 4, title: { rendered: 'Post 4' }, link: '/4' },
                    { id: 5, title: { rendered: 'Post 5' }, link: '/5' },
                    { id: 6, title: { rendered: 'Post 6' }, link: '/6' },
                    { id: 7, title: { rendered: 'Post 7' }, link: '/7' }
                ]);
            } else {
                xhr.responseText = JSON.stringify({ success: true, data: [] });
            }
            if (xhr.onload) xhr.onload();
        });

        const completedSection = container.innerHTML.split('Completed')[1] || '';
        expect(completedSection).toContain('Post 1');
        expect(completedSection).toContain('Post 5');
        expect(completedSection).not.toContain('Post 6');
    });

    test('does not show section if no read posts', () => {
        window.CompletionistStorage = mockStorage({ viewedIds: [1] });

        eval(widgetCode);

        xhrInstances.forEach(xhr => {
            const url = xhr.open.mock.calls[0]?.[1] || '';
            if (url.includes('include=1')) {
                xhr.responseText = JSON.stringify([
                    { id: 1, title: { rendered: 'Viewed Post' }, link: '/viewed' }
                ]);
            } else {
                xhr.responseText = JSON.stringify({ success: true, data: [] });
            }
            if (xhr.onload) xhr.onload();
        });

        expect(container.innerHTML).not.toContain('Completed');
    });
});

describe('Widget: suggested reading section', () => {
    let container;
    let xhrInstances;

    beforeEach(() => {
        container = {
            innerHTML: '',
            dataset: { count: '10', endpoint: '/api', postsEndpoint: '/wp-json/wp/v2/posts' }
        };
        document.getElementById = jest.fn(id => {
            if (id === 'completionist-widget') return container;
            return null;
        });
        document.readyState = 'complete';
        xhrInstances = [];
        window.XMLHttpRequest = jest.fn(() => {
            const xhr = mockXhr();
            xhrInstances.push(xhr);
            return xhr;
        });
    });

    afterEach(() => {
        delete window.CompletionistStorage;
    });

    test('displays posts that are neither viewed nor read', () => {
        window.CompletionistStorage = mockStorage();

        eval(widgetCode);

        xhrInstances.forEach(xhr => {
            const url = xhr.open.mock.calls[0]?.[1] || '';
            if (url.includes('/api')) {
                xhr.responseText = JSON.stringify({
                    success: true,
                    data: [{ id: 1, title: 'New Post', url: '/new' }]
                });
            } else {
                xhr.responseText = JSON.stringify([]);
            }
            if (xhr.onload) xhr.onload();
        });

        expect(container.innerHTML).toContain('Suggested reading');
        expect(container.innerHTML).toContain('New Post');
    });

    test('does not display viewed posts in suggestions', () => {
        window.CompletionistStorage = mockStorage({ viewedIds: [1] });

        eval(widgetCode);

        xhrInstances.forEach(xhr => {
            const url = xhr.open.mock.calls[0]?.[1] || '';
            if (url.includes('/api')) {
                xhr.responseText = JSON.stringify({
                    success: true,
                    data: [
                        { id: 1, title: 'Viewed Post', url: '/viewed' },
                        { id: 2, title: 'New Post', url: '/new' }
                    ]
                });
            } else if (url.includes('include=1')) {
                xhr.responseText = JSON.stringify([
                    { id: 1, title: { rendered: 'Viewed Post' }, link: '/viewed' }
                ]);
            } else {
                xhr.responseText = JSON.stringify([]);
            }
            if (xhr.onload) xhr.onload();
        });

        const suggestedSection = container.innerHTML.split('Suggested reading')[1] || '';
        expect(suggestedSection).toContain('New Post');
        expect(suggestedSection).not.toContain('Viewed Post');
    });

    test('does not display read posts in suggestions', () => {
        window.CompletionistStorage = mockStorage({ readIds: [1] });

        eval(widgetCode);

        xhrInstances.forEach(xhr => {
            const url = xhr.open.mock.calls[0]?.[1] || '';
            if (url.includes('/api')) {
                xhr.responseText = JSON.stringify({
                    success: true,
                    data: [
                        { id: 1, title: 'Read Post', url: '/read' },
                        { id: 2, title: 'New Post', url: '/new' }
                    ]
                });
            } else if (url.includes('include=1')) {
                xhr.responseText = JSON.stringify([
                    { id: 1, title: { rendered: 'Read Post' }, link: '/read' }
                ]);
            } else {
                xhr.responseText = JSON.stringify([]);
            }
            if (xhr.onload) xhr.onload();
        });

        const suggestedSection = container.innerHTML.split('Suggested reading')[1] || '';
        expect(suggestedSection).toContain('New Post');
        expect(suggestedSection).not.toContain('Read Post');
    });

    test('respects configured limit (max 5)', () => {
        window.CompletionistStorage = mockStorage();

        eval(widgetCode);

        xhrInstances.forEach(xhr => {
            const url = xhr.open.mock.calls[0]?.[1] || '';
            if (url.includes('/api')) {
                xhr.responseText = JSON.stringify({
                    success: true,
                    data: [
                        { id: 1, title: 'Post 1', url: '/1' },
                        { id: 2, title: 'Post 2', url: '/2' },
                        { id: 3, title: 'Post 3', url: '/3' },
                        { id: 4, title: 'Post 4', url: '/4' },
                        { id: 5, title: 'Post 5', url: '/5' },
                        { id: 6, title: 'Post 6', url: '/6' },
                        { id: 7, title: 'Post 7', url: '/7' }
                    ]
                });
            } else {
                xhr.responseText = JSON.stringify([]);
            }
            if (xhr.onload) xhr.onload();
        });

        expect(container.innerHTML).toContain('Post 5');
        expect(container.innerHTML).not.toContain('Post 6');
    });

    test('does not show section if no suggestions', () => {
        window.CompletionistStorage = mockStorage({ viewedIds: [1], readIds: [2] });

        eval(widgetCode);

        xhrInstances.forEach(xhr => {
            const url = xhr.open.mock.calls[0]?.[1] || '';
            if (url.includes('/api')) {
                xhr.responseText = JSON.stringify({
                    success: true,
                    data: [
                        { id: 1, title: 'Viewed Post', url: '/viewed' },
                        { id: 2, title: 'Read Post', url: '/read' }
                    ]
                });
            } else if (url.includes('include=1')) {
                xhr.responseText = JSON.stringify([
                    { id: 1, title: { rendered: 'Viewed Post' }, link: '/viewed' }
                ]);
            } else if (url.includes('include=2')) {
                xhr.responseText = JSON.stringify([
                    { id: 2, title: { rendered: 'Read Post' }, link: '/read' }
                ]);
            } else {
                xhr.responseText = JSON.stringify([]);
            }
            if (xhr.onload) xhr.onload();
        });

        expect(container.innerHTML).not.toContain('Suggested reading');
    });
});

describe('Widget: combined limits', () => {
    let container;
    let xhrInstances;

    beforeEach(() => {
        container = {
            innerHTML: '',
            dataset: { count: '10', endpoint: '/api', postsEndpoint: '/wp-json/wp/v2/posts' }
        };
        document.getElementById = jest.fn(id => {
            if (id === 'completionist-widget') return container;
            return null;
        });
        document.readyState = 'complete';
        xhrInstances = [];
        window.XMLHttpRequest = jest.fn(() => {
            const xhr = mockXhr();
            xhrInstances.push(xhr);
            return xhr;
        });
    });

    afterEach(() => {
        delete window.CompletionistStorage;
    });

    test('shows all sections with appropriate limits', () => {
        window.CompletionistStorage = mockStorage({ viewedIds: [1, 2], readIds: [3, 4] });

        eval(widgetCode);

        xhrInstances.forEach(xhr => {
            const url = xhr.open.mock.calls[0]?.[1] || '';
            if (url.includes('include=1,2')) {
                xhr.responseText = JSON.stringify([
                    { id: 1, title: { rendered: 'Viewed 1' }, link: '/v1' },
                    { id: 2, title: { rendered: 'Viewed 2' }, link: '/v2' }
                ]);
            } else if (url.includes('include=3,4')) {
                xhr.responseText = JSON.stringify([
                    { id: 3, title: { rendered: 'Read 1' }, link: '/r1' },
                    { id: 4, title: { rendered: 'Read 2' }, link: '/r2' }
                ]);
            } else if (url.includes('/api')) {
                xhr.responseText = JSON.stringify({
                    success: true,
                    data: [
                        { id: 5, title: 'New 1', url: '/n1' },
                        { id: 6, title: 'New 2', url: '/n2' }
                    ]
                });
            } else {
                xhr.responseText = JSON.stringify([]);
            }
            if (xhr.onload) xhr.onload();
        });

        expect(container.innerHTML).toContain('Continue reading');
        expect(container.innerHTML).toContain('Completed');
        expect(container.innerHTML).toContain('Suggested reading');
        expect(container.innerHTML).toContain('Viewed 1');
        expect(container.innerHTML).toContain('Read 1');
        expect(container.innerHTML).toContain('New 1');
    });
});

describe('Widget: fetches posts by IDs from WP API', () => {
    let container;
    let xhrInstances;

    beforeEach(() => {
        container = {
            innerHTML: '',
            dataset: {
                count: '5',
                endpoint: '/api/suggestions',
                postsEndpoint: '/wp-json/wp/v2/posts'
            }
        };
        document.getElementById = jest.fn(id => {
            if (id === 'completionist-widget') return container;
            return null;
        });
        document.readyState = 'complete';
        xhrInstances = [];
        window.XMLHttpRequest = jest.fn(() => {
            const xhr = mockXhr();
            xhrInstances.push(xhr);
            return xhr;
        });
    });

    afterEach(() => {
        delete window.CompletionistStorage;
    });

    test('fetches viewed posts by IDs from WP REST API', () => {
        window.CompletionistStorage = mockStorage({ viewedIds: [10, 20] });

        eval(widgetCode);

        const viewedXhr = xhrInstances.find(x =>
            x.open.mock.calls[0]?.[1]?.includes('include=10,20')
        );
        expect(viewedXhr).toBeDefined();
    });

    test('fetches read posts by IDs from WP REST API', () => {
        window.CompletionistStorage = mockStorage({ readIds: [30, 40] });

        eval(widgetCode);

        const readXhr = xhrInstances.find(x =>
            x.open.mock.calls[0]?.[1]?.includes('include=30,40')
        );
        expect(readXhr).toBeDefined();
    });

    test('displays viewed posts fetched by IDs', () => {
        window.CompletionistStorage = mockStorage({ viewedIds: [10] });

        eval(widgetCode);

        xhrInstances.forEach(xhr => {
            const url = xhr.open.mock.calls[0]?.[1] || '';
            if (url.includes('include=10')) {
                xhr.responseText = JSON.stringify([
                    { id: 10, title: { rendered: 'Viewed Post' }, link: '/viewed' }
                ]);
            } else {
                xhr.responseText = JSON.stringify({ success: true, data: [] });
            }
            if (xhr.onload) xhr.onload();
        });

        expect(container.innerHTML).toContain('Continue reading');
        expect(container.innerHTML).toContain('Viewed Post');
    });

    test('displays read posts fetched by IDs', () => {
        window.CompletionistStorage = mockStorage({ readIds: [20] });

        eval(widgetCode);

        xhrInstances.forEach(xhr => {
            const url = xhr.open.mock.calls[0]?.[1] || '';
            if (url.includes('include=20')) {
                xhr.responseText = JSON.stringify([
                    { id: 20, title: { rendered: 'Read Post' }, link: '/read' }
                ]);
            } else {
                xhr.responseText = JSON.stringify({ success: true, data: [] });
            }
            if (xhr.onload) xhr.onload();
        });

        expect(container.innerHTML).toContain('Completed');
        expect(container.innerHTML).toContain('Read Post');
    });
});