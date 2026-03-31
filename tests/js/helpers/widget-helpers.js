const fs = require('fs');
const path = require('path');
const { mockStorage } = require('./test-helpers');

const renderItemCode = fs.readFileSync(
    path.join(__dirname, '../../../assets/js/render-item.js'),
    'utf8'
);

const dataProviderCode = fs.readFileSync(
    path.join(__dirname, '../../../assets/js/data-provider.js'),
    'utf8'
);

function loadRenderItem() {
    eval(renderItemCode);
}

function loadDataProvider() {
    eval(dataProviderCode);
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
    var clickHandlers = {};
    var container = {
        innerHTML: '',
        className: '',
        dataset: {
            endpoint: options.endpoint || '/api',
            postsEndpoint: options.postsEndpoint || '/wp-json/wp/v2/posts'
        },
        querySelector: function(selector) {
            if (selector === '#lektrail-consent-checkbox') {
                return {
                    addEventListener: jest.fn()
                };
            }
            if (selector === '.lektrail-clear-btn') {
                if (container.innerHTML.indexOf('lektrail-clear-btn') !== -1) {
                    return {
                        addEventListener: function(event, handler) {
                            clickHandlers[selector] = handler;
                        },
                        click: function() {
                            if (clickHandlers[selector]) {
                                clickHandlers[selector]();
                            }
                        }
                    };
                }
            }
            return null;
        }
    };
    return container;
}

function mockConfig(options = {}) {
    const defaultLabels = {
        continue: 'Continue reading',
        completed: 'Completed',
        suggestions: 'Suggested reading',
        empty: 'Start reading to track your progress!',
        loading: 'Loading suggestions...',
        consentMessage: 'Track your reading progress?',
        consentCheckboxLabel: 'Yes, track my reading',
        clear: 'Clear history'
    };
    return {
        widgetId: options.widgetId || 'lektrail-widget',
        maxViewed: options.maxViewed || 3,
        maxRead: options.maxRead || 5,
        maxSuggestions: options.maxSuggestions || 5,
        viewedEnabled: options.viewedEnabled !== false,
        completedEnabled: options.completedEnabled !== false,
        showClearButton: options.showClearButton !== false,
        showThumbnail: options.showThumbnail || false,
        showExcerpt: options.showExcerpt || false,
        requireConsent: options.requireConsent || false,
        serverSideTracking: options.serverSideTracking || false,
        labels: Object.assign({}, defaultLabels, options.labels || {})
    };
}

function mockConsentManager(options = {}) {
    return {
        hasConsent: jest.fn(function() { return options.consent; }),
        isBuiltInProvider: jest.fn(function() { return options.isBuiltIn !== false; }),
        onConsentChange: jest.fn(),
        grantConsent: jest.fn()
    };
}

function setupWidgetTest(configOptions = {}) {
    const container = createWidgetContainer();
    const xhrInstances = [];

    loadRenderItem();
    loadDataProvider();
    window.LekTrailConfig = mockConfig(configOptions);

    document.getElementById = jest.fn(id => {
        if (id === 'lektrail-widget') return container;
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

function wpPost(id, title, options = {}) {
    const post = { id, title: { rendered: title }, link: `/${id}` };
    if (options.excerpt) {
        post.excerpt = { rendered: `<p>${options.excerpt}</p>` };
    }
    if (options.thumbnail) {
        post._embedded = {
            'wp:featuredmedia': [{
                source_url: options.thumbnail,
                media_details: {
                    sizes: {
                        thumbnail: { source_url: options.thumbnail }
                    }
                }
            }]
        };
    }
    return post;
}

function suggestionsResponse(posts) {
    return JSON.stringify({
        success: true,
        data: posts.map(p => {
            const post = { id: p.id, title: p.title, url: p.url || `/${p.id}` };
            if (p.excerpt) post.excerpt = p.excerpt;
            if (p.thumbnail) post.thumbnail = p.thumbnail;
            return post;
        })
    });
}

function wpPostsResponse(posts) {
    return JSON.stringify(posts);
}

function triggerXhrResponses(xhrInstances, config) {
    xhrInstances.forEach(xhr => {
        xhr.responseText = suggestionsResponse(config.suggestions || []);
        if (xhr.onload) xhr.onload();
    });
}

module.exports = {
    mockStorage,
    mockXhr,
    mockConfig,
    mockConsentManager,
    createWidgetContainer,
    setupWidgetTest,
    wpPost,
    suggestionsResponse,
    wpPostsResponse,
    triggerXhrResponses
};