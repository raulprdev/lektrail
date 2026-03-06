const fs = require('fs');
const path = require('path');
const {
    mockStorage,
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

describe('Widget: stats display', () => {
    test('shows viewed and read counts immediately', () => {
        const { container } = setupWidgetTest();
        window.CompletionistStorage = mockStorage({ viewedIds: [1, 2], readIds: [3, 4, 5] });

        eval(widgetCode);

        expect(container.innerHTML).toContain('2');
        expect(container.innerHTML).toContain('viewed');
        expect(container.innerHTML).toContain('3');
        expect(container.innerHTML).toContain('read');
    });

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