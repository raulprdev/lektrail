(function() {
    'use strict';

    var MAX_CONTINUE_READING = 3;
    var MAX_COMPLETED = 5;
    var MAX_SUGGESTIONS = 5;

    function fetchSuggestions(endpoint, count, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', endpoint + '&count=' + count);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                callback(response.success ? response.data : []);
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

    function filterByIds(posts, ids) {
        return posts.filter(function(post) {
            return ids.indexOf(post.id) !== -1;
        });
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

    function renderWidget(container, posts) {
        var storage = getStorage();
        var viewedIds = storage.getViewedIds();
        var readIds = storage.getReadIds();

        var viewedPosts = filterByIds(posts, viewedIds).slice(0, MAX_CONTINUE_READING);
        var readPosts = filterByIds(posts, readIds).slice(0, MAX_COMPLETED);
        var suggestions = filterUntracked(posts).slice(0, MAX_SUGGESTIONS);

        var html = renderStats();
        html += renderSection('Continue reading', viewedPosts, 'completionist-continue');
        html += renderSection('Completed', readPosts, 'completionist-completed');
        html += renderSection('Suggested reading', suggestions, 'completionist-suggestions');

        container.innerHTML = html;
    }

    function init() {
        var container = document.getElementById('completionist-widget');
        if (!container) return;

        var count = parseInt(container.dataset.count, 10) || 10;
        var endpoint = container.dataset.endpoint;

        renderLoading(container);

        fetchSuggestions(endpoint, count, function(posts) {
            renderWidget(container, posts);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();