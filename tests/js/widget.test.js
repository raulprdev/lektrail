const fs = require('fs');
const path = require('path');
const {
    mockStorage,
    mockConsentManager,
    setupWidgetTest,
    triggerXhrResponses
} = require('./helpers/widget-helpers');

function post(id, title, options = {}) {
    const p = { id, title, url: `/${id}` };
    if (options.excerpt) p.excerpt = options.excerpt;
    if (options.thumbnail) p.thumbnail = options.thumbnail;
    return p;
}

const widgetCode = fs.readFileSync(
    path.join(__dirname, '../../assets/js/widget.js'),
    'utf8'
);

afterEach(() => {
    delete window.CompletionistStorage;
    delete window.CompletionistConfig;
});

describe('Widget: loading and empty states', () => {
    test('shows loading state while fetching', () => {
        const { container } = setupWidgetTest();
        window.CompletionistStorage = mockStorage();

        eval(widgetCode);

        expect(container.innerHTML).toContain('Loading');
    });

    test('shows empty state message when no posts to display', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage();

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('Start reading');
    });
});

describe('Widget: section counts in titles', () => {
    test('shows viewed count in section title', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({
            viewedPosts: [post(1, 'Post 1'), post(2, 'Post 2'), post(3, 'Post 3')]
        });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('Continue reading (3)');
    });

    test('shows read count in section title', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({
            readPosts: [1, 2, 3, 4, 5].map(i => post(i, `Post ${i}`))
        });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('Completed (5)');
    });

    test('shows total count even when display is limited', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({
            readPosts: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map(i => post(i, `Post ${i}`))
        });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('Completed (10)');
    });

    test('suggestions section does not show count', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage();

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            suggestions: [{ id: 1, title: 'Post 1' }, { id: 2, title: 'Post 2' }]
        });

        expect(container.innerHTML).toContain('Suggested reading');
        expect(container.innerHTML).not.toContain('Suggested reading (');
    });
});

describe('Widget: continue reading section', () => {
    test('displays viewed posts from storage', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({
            viewedPosts: [post(1, 'Viewed Post')]
        });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('Continue reading');
        expect(container.innerHTML).toContain('Viewed Post');
    });

    test('does not show read posts in this section', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({
            viewedPosts: [post(1, 'Viewed Post')],
            readPosts: [post(2, 'Read Post')]
        });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        const continueSection = container.innerHTML.split('Completed')[0];
        expect(continueSection).toContain('Viewed Post');
        expect(continueSection).not.toContain('Read Post');
    });

    test('limits to 3 posts', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({
            viewedPosts: [1, 2, 3, 4, 5].map(i => post(i, `Post ${i}`))
        });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        const section = container.innerHTML.split('Completed')[0];
        expect(section).toContain('Post 1');
        expect(section).toContain('Post 3');
        expect(section).not.toContain('Post 4');
    });

    test('hides section when no viewed posts', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({
            readPosts: [post(1, 'Read Post')]
        });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).not.toContain('Continue reading');
    });
});

describe('Widget: completed section', () => {
    test('displays read posts from storage', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({
            readPosts: [post(1, 'Read Post')]
        });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('Completed');
        expect(container.innerHTML).toContain('Read Post');
    });

    test('limits to 5 posts', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({
            readPosts: [1, 2, 3, 4, 5, 6, 7].map(i => post(i, `Post ${i}`))
        });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        const section = container.innerHTML.split('Completed')[1] || '';
        expect(section).toContain('Post 5');
        expect(section).not.toContain('Post 6');
    });

    test('hides section when no read posts', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({
            viewedPosts: [post(1, 'Viewed Post')]
        });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).not.toContain('Completed');
    });
});

describe('Widget: suggested reading section', () => {
    test('displays untracked posts', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage();

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            suggestions: [{ id: 1, title: 'New Post' }]
        });

        expect(container.innerHTML).toContain('Suggested reading');
        expect(container.innerHTML).toContain('New Post');
    });

    test('excludes viewed posts', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({
            viewedPosts: [post(1, 'Viewed')]
        });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            suggestions: [{ id: 1, title: 'Viewed' }, { id: 2, title: 'New' }]
        });

        const section = container.innerHTML.split('Suggested reading')[1] || '';
        expect(section).toContain('New');
        expect(section).not.toContain('Viewed');
    });

    test('excludes read posts', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({
            readPosts: [post(1, 'Read')]
        });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            suggestions: [{ id: 1, title: 'Read' }, { id: 2, title: 'New' }]
        });

        const section = container.innerHTML.split('Suggested reading')[1] || '';
        expect(section).toContain('New');
        expect(section).not.toContain('Read');
    });

    test('limits to 5 posts', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage();

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            suggestions: [1, 2, 3, 4, 5, 6, 7].map(i => ({ id: i, title: `Post ${i}` }))
        });

        expect(container.innerHTML).toContain('Post 5');
        expect(container.innerHTML).not.toContain('Post 6');
    });

    test('hides section when no suggestions', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({
            viewedPosts: [post(1, 'Viewed')],
            readPosts: [post(2, 'Read')]
        });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            suggestions: [{ id: 1, title: 'Viewed' }, { id: 2, title: 'Read' }]
        });

        expect(container.innerHTML).not.toContain('Suggested reading');
    });
});

describe('Widget: all sections together', () => {
    test('displays all three sections correctly', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({
            viewedPosts: [post(1, 'Viewed Post')],
            readPosts: [post(2, 'Read Post')]
        });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            suggestions: [{ id: 3, title: 'New Post' }]
        });

        expect(container.innerHTML).toContain('Continue reading');
        expect(container.innerHTML).toContain('Viewed Post');
        expect(container.innerHTML).toContain('Completed');
        expect(container.innerHTML).toContain('Read Post');
        expect(container.innerHTML).toContain('Suggested reading');
        expect(container.innerHTML).toContain('New Post');
    });
});

describe('Widget: section enable/disable', () => {
    test('hides viewed section when disabled', () => {
        const { container, xhrInstances } = setupWidgetTest({ viewedEnabled: false });
        window.CompletionistStorage = mockStorage({
            viewedPosts: [post(1, 'Viewed Post')],
            readPosts: [post(2, 'Read Post')]
        });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            suggestions: [{ id: 3, title: 'New Post' }]
        });

        expect(container.innerHTML).not.toContain('Continue reading');
        expect(container.innerHTML).not.toContain('Viewed Post');
        expect(container.innerHTML).toContain('Completed');
        expect(container.innerHTML).toContain('Suggested reading');
    });

    test('hides completed section when disabled', () => {
        const { container, xhrInstances } = setupWidgetTest({ completedEnabled: false });
        window.CompletionistStorage = mockStorage({
            viewedPosts: [post(1, 'Viewed Post')],
            readPosts: [post(2, 'Read Post')]
        });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            suggestions: [{ id: 3, title: 'New Post' }]
        });

        expect(container.innerHTML).toContain('Continue reading');
        expect(container.innerHTML).not.toContain('Completed');
        expect(container.innerHTML).not.toContain('Read Post');
        expect(container.innerHTML).toContain('Suggested reading');
    });

    test('shows only suggestions when both viewed and completed disabled', () => {
        const { container, xhrInstances } = setupWidgetTest({
            viewedEnabled: false,
            completedEnabled: false
        });
        window.CompletionistStorage = mockStorage({
            viewedPosts: [post(1, 'Viewed Post')],
            readPosts: [post(2, 'Read Post')]
        });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            suggestions: [{ id: 3, title: 'New Post' }]
        });

        expect(container.innerHTML).not.toContain('Continue reading');
        expect(container.innerHTML).not.toContain('Completed');
        expect(container.innerHTML).toContain('Suggested reading');
        expect(container.innerHTML).toContain('New Post');
    });
});

describe('Widget: configurable labels', () => {
    test('uses custom labels from config', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({
            viewedPosts: [post(1, 'Post 1')],
            readPosts: [post(2, 'Post 2')]
        });
        window.CompletionistConfig = {
            widgetId: 'completionist-widget',
            maxViewed: 3,
            maxRead: 5,
            maxSuggestions: 5,
            viewedEnabled: true,
            completedEnabled: true,
            labels: {
                continue: 'Seguir leyendo',
                completed: 'Completados',
                suggestions: 'Lecturas sugeridas',
                empty: 'Empieza a leer',
                loading: 'Cargando...'
            }
        };

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            suggestions: [{ id: 3, title: 'Post 3' }]
        });

        expect(container.innerHTML).toContain('Seguir leyendo');
        expect(container.innerHTML).toContain('Completados');
        expect(container.innerHTML).toContain('Lecturas sugeridas');
    });

    test('uses custom loading label from config', () => {
        const { container } = setupWidgetTest();
        window.CompletionistStorage = mockStorage();
        window.CompletionistConfig = {
            widgetId: 'completionist-widget',
            viewedEnabled: true,
            completedEnabled: true,
            labels: { loading: 'Cargando...' }
        };

        eval(widgetCode);

        expect(container.innerHTML).toContain('Cargando...');
    });

    test('uses custom empty state label from config', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage();
        window.CompletionistConfig = {
            widgetId: 'completionist-widget',
            viewedEnabled: true,
            completedEnabled: true,
            labels: { empty: 'Empieza a leer' }
        };

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('Empieza a leer');
    });
});

describe('Widget: consent banner', () => {
    test('shows consent banner when requireConsent is true and no consent', () => {
        const { container } = setupWidgetTest({ requireConsent: true });
        window.CompletionistStorage = mockStorage();
        window.CompletionistConsentManager = {
            create: function() {
                return mockConsentManager({ consent: null, isBuiltIn: true });
            }
        };

        eval(widgetCode);

        expect(container.innerHTML).toContain('completionist-consent');
    });

    test('shows widget normally when requireConsent is true and consent granted', () => {
        const { container, xhrInstances } = setupWidgetTest({ requireConsent: true });
        window.CompletionistStorage = mockStorage();
        window.CompletionistConsentManager = {
            create: function() {
                return mockConsentManager({ consent: true });
            }
        };

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).not.toContain('completionist-consent');
    });

    test('shows widget normally when requireConsent is false', () => {
        const { container, xhrInstances } = setupWidgetTest({ requireConsent: false });
        window.CompletionistStorage = mockStorage();

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).not.toContain('completionist-consent');
    });
});

describe('Widget: caching', () => {
    test('renders viewed posts from cache without API call', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = {
            ...mockStorage(),
            getViewedPosts: () => [{ id: 1, title: 'Cached Post', url: '/cached' }],
            getReadPosts: () => [],
            getSuggestions: () => [],
            isSuggestionsCacheValid: () => false
        };

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('Cached Post');
    });

    test('renders read posts from cache without API call', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = {
            ...mockStorage(),
            getViewedPosts: () => [],
            getReadPosts: () => [{ id: 1, title: 'Read Post', url: '/read' }],
            getSuggestions: () => [],
            isSuggestionsCacheValid: () => false
        };

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('Read Post');
    });

    test('uses cached suggestions when cache is valid', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = {
            ...mockStorage(),
            getViewedPosts: () => [],
            getReadPosts: () => [],
            getSuggestions: () => [{ id: 1, title: 'Cached Suggestion', url: '/suggestion' }],
            isSuggestionsCacheValid: () => true
        };

        eval(widgetCode);

        expect(container.innerHTML).toContain('Cached Suggestion');
        expect(xhrInstances.length).toBe(0);
    });

    test('fetches suggestions when cache is expired', () => {
        const { container, xhrInstances } = setupWidgetTest();
        const setSuggestions = jest.fn();
        window.CompletionistStorage = {
            ...mockStorage(),
            getViewedPosts: () => [],
            getReadPosts: () => [],
            getSuggestions: () => [],
            isSuggestionsCacheValid: () => false,
            setSuggestions: setSuggestions
        };

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, { suggestions: [{ id: 1, title: 'New Suggestion' }] });

        expect(container.innerHTML).toContain('New Suggestion');
        expect(setSuggestions).toHaveBeenCalled();
    });
});

describe('Widget: display options', () => {
    test('renders excerpt when showExcerpt is enabled and data has excerpt', () => {
        const { container, xhrInstances } = setupWidgetTest({ showExcerpt: true });
        window.CompletionistStorage = mockStorage();

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            suggestions: [{ id: 1, title: 'Post Title', excerpt: 'This is the excerpt.' }]
        });

        expect(container.innerHTML).toContain('completionist-excerpt');
        expect(container.innerHTML).toContain('This is the excerpt.');
    });

    test('renders excerpt from storage for viewed posts', () => {
        const { container, xhrInstances } = setupWidgetTest({ showExcerpt: true });
        window.CompletionistStorage = mockStorage({
            viewedPosts: [post(1, 'Post Title', { excerpt: 'Stored excerpt text.' })]
        });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('completionist-excerpt');
        expect(container.innerHTML).toContain('Stored excerpt text.');
    });

    test('renders thumbnail from storage for viewed posts', () => {
        const { container, xhrInstances } = setupWidgetTest({ showThumbnail: true });
        window.CompletionistStorage = mockStorage({
            viewedPosts: [post(1, 'Post Title', { thumbnail: 'http://example.com/image.jpg' })]
        });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('completionist-thumbnail');
        expect(container.innerHTML).toContain('http://example.com/image.jpg');
    });
});