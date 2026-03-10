const fs = require('fs');
const path = require('path');
const {
    mockStorage,
    mockConsentManager,
    setupWidgetTest,
    wpPost,
    triggerXhrResponses
} = require('./helpers/widget-helpers');

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
        window.CompletionistStorage = mockStorage({ viewedIds: [1, 2, 3] });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            viewed: { ids: [1, 2, 3], posts: [wpPost(1, 'Post 1'), wpPost(2, 'Post 2'), wpPost(3, 'Post 3')] }
        });

        expect(container.innerHTML).toContain('Continue reading (3)');
    });

    test('shows read count in section title', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({ readIds: [1, 2, 3, 4, 5] });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            read: { ids: [1, 2, 3, 4, 5], posts: [1, 2, 3, 4, 5].map(i => wpPost(i, `Post ${i}`)) }
        });

        expect(container.innerHTML).toContain('Completed (5)');
    });

    test('shows total count even when display is limited', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({ readIds: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10] });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            read: { ids: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], posts: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map(i => wpPost(i, `Post ${i}`)) }
        });

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
    test('displays viewed posts fetched by IDs', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({ viewedIds: [1] });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            viewed: { ids: [1], posts: [wpPost(1, 'Viewed Post')] }
        });

        expect(container.innerHTML).toContain('Continue reading');
        expect(container.innerHTML).toContain('Viewed Post');
    });

    test('does not show read posts in this section', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({ viewedIds: [1], readIds: [2] });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            viewed: { ids: [1], posts: [wpPost(1, 'Viewed Post')] },
            read: { ids: [2], posts: [wpPost(2, 'Read Post')] }
        });

        const continueSection = container.innerHTML.split('Completed')[0];
        expect(continueSection).toContain('Viewed Post');
        expect(continueSection).not.toContain('Read Post');
    });

    test('limits to 3 posts', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({ viewedIds: [1, 2, 3, 4, 5] });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            viewed: {
                ids: [1, 2, 3, 4, 5],
                posts: [1, 2, 3, 4, 5].map(i => wpPost(i, `Post ${i}`))
            }
        });

        const section = container.innerHTML.split('Completed')[0];
        expect(section).toContain('Post 1');
        expect(section).toContain('Post 3');
        expect(section).not.toContain('Post 4');
    });

    test('hides section when no viewed posts', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({ readIds: [1] });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            read: { ids: [1], posts: [wpPost(1, 'Read Post')] }
        });

        expect(container.innerHTML).not.toContain('Continue reading');
    });
});

describe('Widget: completed section', () => {
    test('displays read posts fetched by IDs', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({ readIds: [1] });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            read: { ids: [1], posts: [wpPost(1, 'Read Post')] }
        });

        expect(container.innerHTML).toContain('Completed');
        expect(container.innerHTML).toContain('Read Post');
    });

    test('limits to 5 posts', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({ readIds: [1, 2, 3, 4, 5, 6, 7] });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            read: {
                ids: [1, 2, 3, 4, 5, 6, 7],
                posts: [1, 2, 3, 4, 5, 6, 7].map(i => wpPost(i, `Post ${i}`))
            }
        });

        const section = container.innerHTML.split('Completed')[1] || '';
        expect(section).toContain('Post 5');
        expect(section).not.toContain('Post 6');
    });

    test('hides section when no read posts', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({ viewedIds: [1] });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            viewed: { ids: [1], posts: [wpPost(1, 'Viewed Post')] }
        });

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
        window.CompletionistStorage = mockStorage({ viewedIds: [1] });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            viewed: { ids: [1], posts: [wpPost(1, 'Viewed')] },
            suggestions: [{ id: 1, title: 'Viewed' }, { id: 2, title: 'New' }]
        });

        const section = container.innerHTML.split('Suggested reading')[1] || '';
        expect(section).toContain('New');
        expect(section).not.toContain('Viewed');
    });

    test('excludes read posts', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({ readIds: [1] });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            read: { ids: [1], posts: [wpPost(1, 'Read')] },
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
        window.CompletionistStorage = mockStorage({ viewedIds: [1], readIds: [2] });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            viewed: { ids: [1], posts: [wpPost(1, 'Viewed')] },
            read: { ids: [2], posts: [wpPost(2, 'Read')] },
            suggestions: [{ id: 1, title: 'Viewed' }, { id: 2, title: 'Read' }]
        });

        expect(container.innerHTML).not.toContain('Suggested reading');
    });
});

describe('Widget: all sections together', () => {
    test('displays all three sections correctly', () => {
        const { container, xhrInstances } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({ viewedIds: [1], readIds: [2] });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            viewed: { ids: [1], posts: [wpPost(1, 'Viewed Post')] },
            read: { ids: [2], posts: [wpPost(2, 'Read Post')] },
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
        window.CompletionistStorage = mockStorage({ viewedIds: [1], readIds: [2] });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            viewed: { ids: [1], posts: [wpPost(1, 'Viewed Post')] },
            read: { ids: [2], posts: [wpPost(2, 'Read Post')] },
            suggestions: [{ id: 3, title: 'New Post' }]
        });

        expect(container.innerHTML).not.toContain('Continue reading');
        expect(container.innerHTML).not.toContain('Viewed Post');
        expect(container.innerHTML).toContain('Completed');
        expect(container.innerHTML).toContain('Suggested reading');
    });

    test('hides completed section when disabled', () => {
        const { container, xhrInstances } = setupWidgetTest({ completedEnabled: false });
        window.CompletionistStorage = mockStorage({ viewedIds: [1], readIds: [2] });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            viewed: { ids: [1], posts: [wpPost(1, 'Viewed Post')] },
            read: { ids: [2], posts: [wpPost(2, 'Read Post')] },
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
        window.CompletionistStorage = mockStorage({ viewedIds: [1], readIds: [2] });

        eval(widgetCode);
        triggerXhrResponses(xhrInstances, {
            viewed: { ids: [1], posts: [wpPost(1, 'Viewed Post')] },
            read: { ids: [2], posts: [wpPost(2, 'Read Post')] },
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
        window.CompletionistStorage = mockStorage({ viewedIds: [1], readIds: [2] });
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
            viewed: { ids: [1], posts: [wpPost(1, 'Post 1')] },
            read: { ids: [2], posts: [wpPost(2, 'Post 2')] },
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