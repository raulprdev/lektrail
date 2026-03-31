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

const dataSourceCode = fs.readFileSync(
    path.join(__dirname, '../../assets/js/data-source.js'),
    'utf8'
);

const widgetCode = fs.readFileSync(
    path.join(__dirname, '../../assets/js/widget.js'),
    'utf8'
);

function loadWidget() {
    eval(dataSourceCode);
    eval(widgetCode);
}

afterEach(() => {
    delete window.LekTrailStorage;
    delete window.LekTrailConfig;
    delete window.LekTrailInlineData;
    delete window.LekTrailDataSource;
});

describe('Widget: loading and empty states', () => {
    test('shows loading state while fetching', () => {
        const { container } = setupWidgetTest();
        window.LekTrailStorage = mockStorage();

        loadWidget();

        expect(container.innerHTML).toContain('Loading');
    });

    test('shows empty state message when no posts to display', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = mockStorage();

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('Start reading');
    });
});

describe('Widget: section counts in titles', () => {
    test('shows viewed count in section title', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = mockStorage({
            viewedPosts: [post(1, 'Post 1'), post(2, 'Post 2'), post(3, 'Post 3')]
        });

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('Continue reading (3)');
    });

    test('shows read count in section title', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = mockStorage({
            readPosts: [1, 2, 3, 4, 5].map(i => post(i, `Post ${i}`))
        });

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('Completed (5)');
    });

    test('shows total count even when display is limited', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = mockStorage({
            readPosts: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map(i => post(i, `Post ${i}`))
        });

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('Completed (10)');
    });

    test('suggestions section does not show count', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = mockStorage();

        loadWidget();
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
        window.LekTrailStorage = mockStorage({
            viewedPosts: [post(1, 'Viewed Post')]
        });

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('Continue reading');
        expect(container.innerHTML).toContain('Viewed Post');
    });

    test('does not show read posts in this section', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = mockStorage({
            viewedPosts: [post(1, 'Viewed Post')],
            readPosts: [post(2, 'Read Post')]
        });

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        const continueSection = container.innerHTML.split('Completed')[0];
        expect(continueSection).toContain('Viewed Post');
        expect(continueSection).not.toContain('Read Post');
    });

    test('limits to 3 posts', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = mockStorage({
            viewedPosts: [1, 2, 3, 4, 5].map(i => post(i, `Post ${i}`))
        });

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        const section = container.innerHTML.split('Completed')[0];
        expect(section).toContain('Post 1');
        expect(section).toContain('Post 3');
        expect(section).not.toContain('Post 4');
    });

    test('hides section when no viewed posts', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = mockStorage({
            readPosts: [post(1, 'Read Post')]
        });

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).not.toContain('Continue reading');
    });
});

describe('Widget: completed section', () => {
    test('displays read posts from storage', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = mockStorage({
            readPosts: [post(1, 'Read Post')]
        });

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('Completed');
        expect(container.innerHTML).toContain('Read Post');
    });

    test('limits to 5 posts', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = mockStorage({
            readPosts: [1, 2, 3, 4, 5, 6, 7].map(i => post(i, `Post ${i}`))
        });

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        const section = container.innerHTML.split('Completed')[1] || '';
        expect(section).toContain('Post 5');
        expect(section).not.toContain('Post 6');
    });

    test('hides section when no read posts', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = mockStorage({
            viewedPosts: [post(1, 'Viewed Post')]
        });

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).not.toContain('Completed');
    });
});

describe('Widget: suggested reading section', () => {
    test('displays untracked posts', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = mockStorage();

        loadWidget();
        triggerXhrResponses(xhrInstances, {
            suggestions: [{ id: 1, title: 'New Post' }]
        });

        expect(container.innerHTML).toContain('Suggested reading');
        expect(container.innerHTML).toContain('New Post');
    });

    test('sends viewed post IDs in exclude param', () => {
        const { xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = mockStorage({
            viewedPosts: [post(1, 'Viewed'), post(2, 'Also Viewed')]
        });

        loadWidget();

        expect(xhrInstances[0].open).toHaveBeenCalledWith(
            'GET',
            expect.stringContaining('exclude=1,2')
        );
    });

    test('sends read post IDs in exclude param', () => {
        const { xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = mockStorage({
            readPosts: [post(3, 'Read'), post(4, 'Also Read')]
        });

        loadWidget();

        expect(xhrInstances[0].open).toHaveBeenCalledWith(
            'GET',
            expect.stringContaining('exclude=3,4')
        );
    });

    test('limits to 5 posts', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = mockStorage();

        loadWidget();
        triggerXhrResponses(xhrInstances, {
            suggestions: [1, 2, 3, 4, 5, 6, 7].map(i => ({ id: i, title: `Post ${i}` }))
        });

        expect(container.innerHTML).toContain('Post 5');
        expect(container.innerHTML).not.toContain('Post 6');
    });

    test('hides section when API returns empty suggestions', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = mockStorage({
            viewedPosts: [post(1, 'Viewed')],
            readPosts: [post(2, 'Read')]
        });

        loadWidget();
        triggerXhrResponses(xhrInstances, {
            suggestions: []
        });

        expect(container.innerHTML).not.toContain('Suggested reading');
    });
});

describe('Widget: all sections together', () => {
    test('displays all three sections correctly', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = mockStorage({
            viewedPosts: [post(1, 'Viewed Post')],
            readPosts: [post(2, 'Read Post')]
        });

        loadWidget();
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
        window.LekTrailStorage = mockStorage({
            viewedPosts: [post(1, 'Viewed Post')],
            readPosts: [post(2, 'Read Post')]
        });

        loadWidget();
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
        window.LekTrailStorage = mockStorage({
            viewedPosts: [post(1, 'Viewed Post')],
            readPosts: [post(2, 'Read Post')]
        });

        loadWidget();
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
        window.LekTrailStorage = mockStorage({
            viewedPosts: [post(1, 'Viewed Post')],
            readPosts: [post(2, 'Read Post')]
        });

        loadWidget();
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
        window.LekTrailStorage = mockStorage({
            viewedPosts: [post(1, 'Post 1')],
            readPosts: [post(2, 'Post 2')]
        });
        window.LekTrailConfig = {
            widgetId: 'lektrail-widget',
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

        loadWidget();
        triggerXhrResponses(xhrInstances, {
            suggestions: [{ id: 3, title: 'Post 3' }]
        });

        expect(container.innerHTML).toContain('Seguir leyendo');
        expect(container.innerHTML).toContain('Completados');
        expect(container.innerHTML).toContain('Lecturas sugeridas');
    });

    test('uses custom loading label from config', () => {
        const { container } = setupWidgetTest();
        window.LekTrailStorage = mockStorage();
        window.LekTrailConfig = {
            widgetId: 'lektrail-widget',
            viewedEnabled: true,
            completedEnabled: true,
            labels: { loading: 'Cargando...' }
        };

        loadWidget();

        expect(container.innerHTML).toContain('Cargando...');
    });

    test('uses custom empty state label from config', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = mockStorage();
        window.LekTrailConfig = {
            widgetId: 'lektrail-widget',
            viewedEnabled: true,
            completedEnabled: true,
            labels: { empty: 'Empieza a leer' }
        };

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('Empieza a leer');
    });
});

describe('Widget: consent banner', () => {
    test('shows consent banner when requireConsent is true and no consent', () => {
        const { container } = setupWidgetTest({ requireConsent: true });
        window.LekTrailStorage = mockStorage();
        window.LekTrailConsentManager = {
            create: function() {
                return mockConsentManager({ consent: null, isBuiltIn: true });
            }
        };

        loadWidget();

        expect(container.innerHTML).toContain('lektrail-consent');
    });

    test('shows widget normally when requireConsent is true and consent granted', () => {
        const { container, xhrInstances } = setupWidgetTest({ requireConsent: true });
        window.LekTrailStorage = mockStorage();
        window.LekTrailConsentManager = {
            create: function() {
                return mockConsentManager({ consent: true });
            }
        };

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).not.toContain('lektrail-consent');
    });

    test('shows widget normally when requireConsent is false', () => {
        const { container, xhrInstances } = setupWidgetTest({ requireConsent: false });
        window.LekTrailStorage = mockStorage();

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).not.toContain('lektrail-consent');
    });

    test('skips consent when serverSideTracking is enabled even if requireConsent is true', () => {
        const { container } = setupWidgetTest({
            requireConsent: true,
            serverSideTracking: true
        });
        window.LekTrailInlineData = {
            viewed: [],
            read: [],
            suggestions: []
        };
        window.LekTrailConsentManager = {
            create: function() {
                return mockConsentManager({ consent: null, isBuiltIn: true });
            }
        };

        loadWidget();

        expect(container.innerHTML).not.toContain('lektrail-consent');
    });
});

describe('Widget: caching', () => {
    test('renders viewed posts from cache without API call', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = {
            ...mockStorage(),
            getViewedPosts: () => [{ id: 1, title: 'Cached Post', url: '/cached' }],
            getReadPosts: () => [],
            getSuggestions: () => [],
            isSuggestionsCacheValid: () => false
        };

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('Cached Post');
    });

    test('renders read posts from cache without API call', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = {
            ...mockStorage(),
            getViewedPosts: () => [],
            getReadPosts: () => [{ id: 1, title: 'Read Post', url: '/read' }],
            getSuggestions: () => [],
            isSuggestionsCacheValid: () => false
        };

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('Read Post');
    });

    test('uses cached suggestions when cache is valid', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = {
            ...mockStorage(),
            getViewedPosts: () => [],
            getReadPosts: () => [],
            getSuggestions: () => [{ id: 1, title: 'Cached Suggestion', url: '/suggestion' }],
            isSuggestionsCacheValid: () => true
        };

        loadWidget();

        expect(container.innerHTML).toContain('Cached Suggestion');
        expect(xhrInstances.length).toBe(0);
    });

    test('fetches suggestions when cache is expired', () => {
        const { container, xhrInstances } = setupWidgetTest();
        const setSuggestions = jest.fn();
        window.LekTrailStorage = {
            ...mockStorage(),
            getViewedPosts: () => [],
            getReadPosts: () => [],
            getSuggestions: () => [],
            isSuggestionsCacheValid: () => false,
            setSuggestions: setSuggestions
        };

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [{ id: 1, title: 'New Suggestion' }] });

        expect(container.innerHTML).toContain('New Suggestion');
        expect(setSuggestions).toHaveBeenCalled();
    });
});

describe('Widget: display options', () => {
    test('renders excerpt when showExcerpt is enabled and data has excerpt', () => {
        const { container, xhrInstances } = setupWidgetTest({ showExcerpt: true });
        window.LekTrailStorage = mockStorage();

        loadWidget();
        triggerXhrResponses(xhrInstances, {
            suggestions: [{ id: 1, title: 'Post Title', excerpt: 'This is the excerpt.' }]
        });

        expect(container.innerHTML).toContain('lektrail-excerpt');
        expect(container.innerHTML).toContain('This is the excerpt.');
    });

    test('renders excerpt from storage for viewed posts', () => {
        const { container, xhrInstances } = setupWidgetTest({ showExcerpt: true });
        window.LekTrailStorage = mockStorage({
            viewedPosts: [post(1, 'Post Title', { excerpt: 'Stored excerpt text.' })]
        });

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('lektrail-excerpt');
        expect(container.innerHTML).toContain('Stored excerpt text.');
    });

    test('renders thumbnail from storage for viewed posts', () => {
        const { container, xhrInstances } = setupWidgetTest({ showThumbnail: true });
        window.LekTrailStorage = mockStorage({
            viewedPosts: [post(1, 'Post Title', { thumbnail: 'http://example.com/image.jpg' })]
        });

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('lektrail-thumbnail');
        expect(container.innerHTML).toContain('http://example.com/image.jpg');
    });
});

describe('Widget: clear data button', () => {
    test('shows clear button when enabled', () => {
        const { container, xhrInstances } = setupWidgetTest({ showClearButton: true });
        window.LekTrailStorage = mockStorage({
            viewedPosts: [post(1, 'Post 1')]
        });

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('lektrail-clear');
    });

    test('hides clear button when disabled', () => {
        const { container, xhrInstances } = setupWidgetTest({ showClearButton: false });
        window.LekTrailStorage = mockStorage({
            viewedPosts: [post(1, 'Post 1')]
        });

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).not.toContain('lektrail-clear');
    });

    test('hides clear button when no data to clear', () => {
        const { container, xhrInstances } = setupWidgetTest({ showClearButton: true });
        window.LekTrailStorage = mockStorage();

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).not.toContain('lektrail-clear');
    });

    test('clicking clear button calls storage.clearHistory()', () => {
        const { container, xhrInstances } = setupWidgetTest({ showClearButton: true });
        const clearHistoryFn = jest.fn();
        window.LekTrailStorage = {
            ...mockStorage({ viewedPosts: [post(1, 'Post 1')] }),
            clearHistory: clearHistoryFn
        };

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        const button = container.querySelector('.lektrail-clear-btn');
        button.click();

        expect(clearHistoryFn).toHaveBeenCalled();
    });

    test('uses custom clear label from config', () => {
        const { container, xhrInstances } = setupWidgetTest({ showClearButton: true });
        window.LekTrailStorage = mockStorage({
            viewedPosts: [post(1, 'Post 1')]
        });
        window.LekTrailConfig.labels = { clear: 'Borrar datos' };

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.innerHTML).toContain('Borrar datos');
    });
});

describe('Widget: class preservation', () => {
    test('adds lektrail-widget class after rendering', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailStorage = mockStorage();

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.className).toContain('lektrail-widget');
    });

    test('preserves existing WordPress block classes after rendering', () => {
        const { container, xhrInstances } = setupWidgetTest();
        container.className = 'wp-block-lektrail-widget has-background has-cyan-background-color';
        window.LekTrailStorage = mockStorage();

        loadWidget();
        triggerXhrResponses(xhrInstances, { suggestions: [] });

        expect(container.className).toContain('wp-block-lektrail-widget');
        expect(container.className).toContain('has-background');
        expect(container.className).toContain('lektrail-widget');
    });
});

describe('Widget: server-side tracking (inline data)', () => {
    test('hides clear button when server-side tracking is enabled', () => {
        const { container } = setupWidgetTest({ showClearButton: true, serverSideTracking: true });
        window.LekTrailInlineData = {
            viewed: [post(1, 'Post 1')],
            read: [],
            suggestions: []
        };

        loadWidget();

        expect(container.innerHTML).not.toContain('lektrail-clear');
    });

    test('uses inline data when LekTrailInlineData is present', () => {
        const { container } = setupWidgetTest();
        window.LekTrailInlineData = {
            viewed: [post(1, 'Viewed Post')],
            read: [post(2, 'Read Post')],
            suggestions: [post(3, 'Suggestion')]
        };

        loadWidget();

        expect(container.innerHTML).toContain('Viewed Post');
        expect(container.innerHTML).toContain('Read Post');
        expect(container.innerHTML).toContain('Suggestion');
    });

    test('does not fetch from API when inline data exists', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.LekTrailInlineData = {
            viewed: [],
            read: [],
            suggestions: [post(1, 'Inline Suggestion')]
        };

        loadWidget();

        expect(xhrInstances.length).toBe(0);
        expect(container.innerHTML).toContain('Inline Suggestion');
    });

    test('shows correct count from inline data for viewed section', () => {
        const { container } = setupWidgetTest();
        window.LekTrailInlineData = {
            viewed: [post(1, 'Post 1'), post(2, 'Post 2')],
            read: [],
            suggestions: []
        };

        loadWidget();

        expect(container.innerHTML).toContain('Continue reading (2)');
    });

    test('shows correct count from inline data for read section', () => {
        const { container } = setupWidgetTest();
        window.LekTrailInlineData = {
            viewed: [],
            read: [post(1, 'Post 1'), post(2, 'Post 2'), post(3, 'Post 3')],
            suggestions: []
        };

        loadWidget();

        expect(container.innerHTML).toContain('Completed (3)');
    });
});

describe('Widget: re-initialization class toggling', () => {
    test('removes show-thumbnail class when toggled off', () => {
        const { container } = setupWidgetTest({ showThumbnail: true });
        window.LekTrailInlineData = {
            viewed: [post(1, 'Post 1', { thumbnail: 'http://example.com/img.jpg' })],
            read: [],
            suggestions: []
        };

        loadWidget();
        expect(container.className).toContain('lektrail-show-thumbnail');

        window.LekTrailConfig.showThumbnail = false;
        window.LekTrailWidget.init(container, window.LekTrailInlineData, window.LekTrailConfig);

        expect(container.className).not.toContain('lektrail-show-thumbnail');
    });

    test('removes show-excerpt class when toggled off', () => {
        const { container } = setupWidgetTest({ showExcerpt: true });
        window.LekTrailInlineData = {
            viewed: [post(1, 'Post 1', { excerpt: 'Some excerpt' })],
            read: [],
            suggestions: []
        };

        loadWidget();
        expect(container.className).toContain('lektrail-show-excerpt');

        window.LekTrailConfig.showExcerpt = false;
        window.LekTrailWidget.init(container, window.LekTrailInlineData, window.LekTrailConfig);

        expect(container.className).not.toContain('lektrail-show-excerpt');
    });
});