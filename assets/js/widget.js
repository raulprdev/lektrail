(function() {
    'use strict';

    function getConfig() {
        return window.CompletionistConfig || {
            maxViewed: 3,
            maxRead: 5,
            maxSuggestions: 5
        };
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

    function renderStats() {
        var storage = getStorage();
        var viewedCount = storage.getViewedCount();
        var readCount = storage.getReadCount();

        return '<div class="completionist-stats">' +
            '<span class="completionist-count">' + viewedCount + '</span> viewed, ' +
            '<span class="completionist-count">' + readCount + '</span> read' +
            '</div>';
    }

    function renderLoading(container) {
        container.innerHTML = renderStats() +
            '<div class="completionist-loading">Loading suggestions...</div>';
    }

    function renderSection(title, posts, className) {
        if (posts.length === 0) return '';

        var html = '<div class="' + className + '">';
        html += '<h3>' + title + '</h3>';
        html += '<ul>';
        posts.forEach(function(post) {
            html += '<li><a href="' + post.url + '">' + post.title + '</a></li>';
        });
        html += '</ul>';
        html += '</div>';
        return html;
    }

    function renderEmptyState() {
        return '<div class="completionist-empty">Start reading to track your progress!</div>';
    }

    function renderWidget(container, viewedPosts, readPosts, suggestions) {
        var config = getConfig();
        var viewedSlice = viewedPosts.slice(0, config.maxViewed);
        var readSlice = readPosts.slice(0, config.maxRead);
        var suggestionsSlice = filterUntracked(suggestions).slice(0, config.maxSuggestions);

        var html = renderStats();
        html += renderSection('Continue reading', viewedSlice, 'completionist-continue');
        html += renderSection('Completed', readSlice, 'completionist-completed');
        html += renderSection('Suggested reading', suggestionsSlice, 'completionist-suggestions');

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