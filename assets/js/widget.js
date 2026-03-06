(function() {
    'use strict';

    function getConfig() {
        if (!window.CompletionistConfig) {
            throw new Error('CompletionistConfig not found');
        }
        return window.CompletionistConfig;
    }

    function fetchPostsByIds(endpoint, ids, callback) {
        if (!ids.length) {
            callback([]);
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.open('GET', endpoint + '?include=' + ids.join(','));
        xhr.onload = function() {
            if (xhr.status === 200) {
                var posts = JSON.parse(xhr.responseText);
                var normalized = posts.map(function(post) {
                    return {
                        id: post.id,
                        title: post.title.rendered,
                        url: post.link
                    };
                });
                callback(normalized);
            } else {
                callback([]);
            }
        };
        xhr.send();
    }

    function fetchSuggestions(endpoint, count, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', endpoint + '&count=' + count);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                callback(response.success ? response.data : []);
            } else {
                callback([]);
            }
        };
        xhr.send();
    }

    function getStorage() {
        return window.CompletionistStorage || {
            getViewedCount: function() { return 0; },
            getReadCount: function() { return 0; },
            getViewedIds: function() { return []; },
            getReadIds: function() { return []; },
            isTracked: function() { return false; }
        };
    }

    function filterUntracked(posts) {
        var storage = getStorage();
        return posts.filter(function(post) {
            return !storage.isTracked(post.id);
        });
    }

    function renderLoading(container) {
        var config = getConfig();
        container.innerHTML = '<div class="completionist-loading">' + config.labels.loading + '</div>';
    }

    function renderSection(title, posts, className, count) {
        if (posts.length === 0) return '';

        var titleWithCount = count !== undefined ? title + ' (' + count + ')' : title;
        var html = '<div class="' + className + '">';
        html += '<h3>' + titleWithCount + '</h3>';
        html += '<ul>';
        posts.forEach(function(post) {
            html += '<li><a href="' + post.url + '">' + post.title + '</a></li>';
        });
        html += '</ul>';
        html += '</div>';
        return html;
    }

    function renderEmptyState() {
        var config = getConfig();
        return '<div class="completionist-empty">' + config.labels.empty + '</div>';
    }

    function renderWidget(container, viewedPosts, readPosts, suggestions) {
        var config = getConfig();
        var storage = getStorage();
        var viewedSlice = config.viewedEnabled ? viewedPosts.slice(0, config.maxViewed) : [];
        var readSlice = config.completedEnabled ? readPosts.slice(0, config.maxRead) : [];
        var suggestionsSlice = filterUntracked(suggestions).slice(0, config.maxSuggestions);

        var html = '';
        if (config.viewedEnabled) {
            html += renderSection(config.labels.continue, viewedSlice, 'completionist-continue', storage.getViewedCount());
        }
        if (config.completedEnabled) {
            html += renderSection(config.labels.completed, readSlice, 'completionist-completed', storage.getReadCount());
        }
        html += renderSection(config.labels.suggestions, suggestionsSlice, 'completionist-suggestions');

        var hasContent = viewedSlice.length || readSlice.length || suggestionsSlice.length;
        if (!hasContent) {
            html += renderEmptyState();
        }

        container.innerHTML = html;
    }

    function init() {
        var container = document.getElementById('completionist-widget');
        if (!container) return;

        var storage = getStorage();
        var config = getConfig();
        var suggestionsEndpoint = container.dataset.endpoint;
        var postsEndpoint = container.dataset.postsEndpoint || '/wp-json/wp/v2/posts';

        var viewedIds = storage.getViewedIds();
        var readIds = storage.getReadIds();

        renderLoading(container);

        var viewedPosts = [];
        var readPosts = [];
        var suggestions = [];
        var pending = 3;

        function checkComplete() {
            pending--;
            if (pending === 0) {
                renderWidget(container, viewedPosts, readPosts, suggestions);
            }
        }

        fetchPostsByIds(postsEndpoint, viewedIds, function(posts) {
            viewedPosts = posts;
            checkComplete();
        });

        fetchPostsByIds(postsEndpoint, readIds, function(posts) {
            readPosts = posts;
            checkComplete();
        });

        fetchSuggestions(suggestionsEndpoint, config.maxSuggestions, function(posts) {
            suggestions = posts;
            checkComplete();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();