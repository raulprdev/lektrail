const fs = require('fs');
const path = require('path');
const { mockStorage } = require('./test-helpers');

const renderItemCode = fs.readFileSync(
    path.join(__dirname, '../../../assets/js/render-item.js'),
    'utf8'
);

function loadRenderItem() {
    eval(renderItemCode);
}

function mockXhr() {
    return {
        open: jest.fn(),
        send: jest.fn(),
        onload: null,
        status: 200,
        responseText: '{"success":true,"data":[]}'
    };
}

function createWidgetContainer(options = {}) {
    return {
        innerHTML: '',
        dataset: {
            endpoint: options.endpoint || '/api',
            postsEndpoint: options.postsEndpoint || '/wp-json/wp/v2/posts'
        }
    };
}

function mockConfig(options = {}) {
    const defaultLabels = {
        continue: 'Continue reading',
        completed: 'Completed',
        suggestions: 'Suggested reading',
        empty: 'Start reading to track your progress!',
        loading: 'Loading suggestions...'
    };
    return {
        maxViewed: options.maxViewed || 3,
        maxRead: options.maxRead || 5,
        maxSuggestions: options.maxSuggestions || 5,
        viewedEnabled: options.viewedEnabled !== false,
        completedEnabled: options.completedEnabled !== false,
        labels: Object.assign({}, defaultLabels, options.labels || {})
    };
}

function setupWidgetTest(configOptions = {}) {
    const container = createWidgetContainer();
    const xhrInstances = [];

    loadRenderItem();
    window.CompletionistConfig = mockConfig(configOptions);

    document.getElementById = jest.fn(id => {
        if (id === 'completionist-widget') return container;
        return null;
    });
    document.readyState = 'complete';
    window.XMLHttpRequest = jest.fn(() => {
        const xhr = mockXhr();
        xhrInstances.push(xhr);
        return xhr;
    });

    return { container, xhrInstances };
}

function wpPost(id, title) {
    return { id, title: { rendered: title }, link: `/${id}` };
}

function suggestionsResponse(posts) {
    return JSON.stringify({
        success: true,
        data: posts.map(p => ({ id: p.id, title: p.title, url: p.url || `/${p.id}` }))
    });
}

function wpPostsResponse(posts) {
    return JSON.stringify(posts);
}

function triggerXhrResponses(xhrInstances, config) {
    xhrInstances.forEach(xhr => {
        const url = xhr.open.mock.calls[0]?.[1] || '';
        let response = JSON.stringify([]);

        if (config.viewed && url.includes(`include=${config.viewed.ids.join(',')}`)) {
            response = wpPostsResponse(config.viewed.posts);
        } else if (config.read && url.includes(`include=${config.read.ids.join(',')}`)) {
            response = wpPostsResponse(config.read.posts);
        } else if (url.includes('/api')) {
            response = suggestionsResponse(config.suggestions || []);
        }

        xhr.responseText = response;
        if (xhr.onload) xhr.onload();
    });
}

module.exports = {
    mockStorage,
    mockXhr,
    mockConfig,
    createWidgetContainer,
    setupWidgetTest,
    wpPost,
    suggestionsResponse,
    wpPostsResponse,
    triggerXhrResponses
};