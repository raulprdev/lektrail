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
            count: options.count || '5',
            endpoint: options.endpoint || '/api',
            postsEndpoint: options.postsEndpoint || '/wp-json/wp/v2/posts'
        }
    };
}

function setupWidgetTest() {
    const container = createWidgetContainer();
    const xhrInstances = [];

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
    createWidgetContainer,
    setupWidgetTest,
    wpPost,
    suggestionsResponse,
    wpPostsResponse,
    triggerXhrResponses
};