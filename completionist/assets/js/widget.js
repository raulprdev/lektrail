(function() {
    'use strict';

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

    function filterUnread(posts) {
        var readIds = window.CompletionistStorage ? CompletionistStorage.getReadIds() : [];
        return posts.filter(function(post) {
            return readIds.indexOf(post.id) === -1;
        });
    }

    function renderWidget(container, posts) {
        var readCount = window.CompletionistStorage ? CompletionistStorage.getReadCount() : 0;
        var unread = filterUnread(posts);

        var html = '<div class="completionist-stats">';
        html += '<span class="completionist-count">' + readCount + '</span> posts read';
        html += '</div>';

        if (unread.length > 0) {
            html += '<div class="completionist-suggestions">';
            html += '<h3>Suggested reading</h3>';
            html += '<ul>';
            unread.forEach(function(post) {
                html += '<li><a href="' + post.url + '">' + post.title + '</a></li>';
            });
            html += '</ul>';
            html += '</div>';
        }

        container.innerHTML = html;
    }

    function init() {
        var container = document.getElementById('completionist-widget');
        if (!container) return;

        var count = parseInt(container.dataset.count, 10) || 5;
        var endpoint = container.dataset.endpoint;

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