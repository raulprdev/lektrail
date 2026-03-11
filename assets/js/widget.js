(function() {
    'use strict';

    var CONSENT_CHECKBOX_ID = 'completionist-consent-checkbox';

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
        xhr.open('GET', endpoint + '?include=' + ids.join(',') + '&_embed=wp:featuredmedia');
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var posts = JSON.parse(xhr.responseText);
                    var normalized = posts.map(function(post) {
                        var item = {
                            id: post.id,
                            title: post.title.rendered,
                            url: post.link
                        };
                        if (post.excerpt && post.excerpt.rendered) {
                            item.excerpt = post.excerpt.rendered.replace(/<[^>]*>/g, '').trim();
                        }
                        if (post._embedded && post._embedded['wp:featuredmedia'] && post._embedded['wp:featuredmedia'][0]) {
                            var media = post._embedded['wp:featuredmedia'][0];
                            var sizes = media.media_details && media.media_details.sizes;
                            item.thumbnail = (sizes && sizes.thumbnail) ? sizes.thumbnail.source_url : media.source_url;
                        }
                        return item;
                    });
                    callback(normalized);
                } catch (e) {
                    callback([]);
                }
            } else {
                callback([]);
            }
        };
        xhr.onerror = function() {
            callback([]);
        };
        xhr.send();
    }

    function fetchSuggestions(endpoint, count, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', endpoint + '&count=' + count);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    callback(response.success ? response.data : []);
                } catch (e) {
                    callback([]);
                }
            } else {
                callback([]);
            }
        };
        xhr.onerror = function() {
            callback([]);
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

    function filterValid(posts) {
        return posts.filter(function(post) {
            return post.title && post.url;
        });
    }

    function getRenderItem() {
        return window.CompletionistRenderItem || function(post) {
            return '<li><a href="' + post.url + '">' + post.title + '</a></li>';
        };
    }

    function renderLoading(container) {
        var config = getConfig();
        container.innerHTML = '<div class="completionist-loading">' + config.labels.loading + '</div>';
    }

    function renderSection(title, posts, className, count) {
        if (posts.length === 0) return '';

        var renderItem = getRenderItem();
        var titleWithCount = count !== undefined ? title + ' (' + count + ')' : title;
        var html = '<div class="' + className + '">';
        html += '<h3>' + titleWithCount + '</h3>';
        html += '<ul class="completionist-list">';
        posts.forEach(function(post) {
            html += renderItem(post);
        });
        html += '</ul>';
        html += '</div>';
        return html;
    }

    function renderEmptyState() {
        var config = getConfig();
        return '<div class="completionist-empty">' + config.labels.empty + '</div>';
    }

    function renderConsentBanner(container, config, onConsent) {
        var html = '<div class="completionist-consent">';
        html += '<p>' + config.labels.consentMessage + '</p>';
        html += '<label>';
        html += '<input type="checkbox" id="' + CONSENT_CHECKBOX_ID + '">';
        html += ' ' + config.labels.consentCheckboxLabel;
        html += '</label>';
        html += '</div>';
        container.innerHTML = html;

        var checkbox = container.querySelector('#' + CONSENT_CHECKBOX_ID);
        if (checkbox) {
            checkbox.addEventListener('change', function() {
                if (this.checked && onConsent) {
                    onConsent();
                }
            });
        }
    }

    function renderWidget(container, viewedPosts, readPosts, suggestions) {
        var config = getConfig();
        var storage = getStorage();
        var viewedSlice = config.viewedEnabled ? filterValid(viewedPosts).slice(0, config.maxViewed) : [];
        var readSlice = config.completedEnabled ? filterValid(readPosts).slice(0, config.maxRead) : [];
        var suggestionsSlice = filterUntracked(suggestions).slice(0, config.maxSuggestions);

        var classes = [];
        if (config.showExcerpt) classes.push('completionist-show-excerpt');
        if (config.showThumbnail) classes.push('completionist-show-thumbnail');
        container.className = classes.join(' ');

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

    function getConsentManager() {
        if (window.CompletionistConsentManager) {
            return window.CompletionistConsentManager.create();
        }
        return null;
    }

    function init() {
        var config = getConfig();
        var container = document.getElementById(config.widgetId);
        if (!container) return;

        if (config.requireConsent) {
            var consentManager = getConsentManager();
            if (consentManager && consentManager.hasConsent() !== true) {
                if (consentManager.isBuiltInProvider()) {
                    renderConsentBanner(container, config, function() {
                        consentManager.grantConsent();
                        initWidget(container, config);
                    });
                }
                return;
            }
        }

        initWidget(container, config);
    }

    function initWidget(container, config) {
        var storage = getStorage();
        var suggestionsEndpoint = container.dataset.endpoint;
        var postsEndpoint = container.dataset.postsEndpoint || '/wp-json/wp/v2/posts';
        var cacheHours = config.suggestionsCacheHours || 24;

        var viewedPosts = storage.getViewedPosts ? storage.getViewedPosts() : [];
        var readPosts = storage.getReadPosts ? storage.getReadPosts() : [];
        var viewedToFetch = viewedPosts.filter(function(p) { return p.needsFetch; }).map(function(p) { return p.id; });
        var readToFetch = readPosts.filter(function(p) { return p.needsFetch; }).map(function(p) { return p.id; });

        var suggestionsValid = storage.isSuggestionsCacheValid && storage.isSuggestionsCacheValid(cacheHours);
        var suggestions = suggestionsValid && storage.getSuggestions ? storage.getSuggestions() : [];

        var pending = 0;
        if (viewedToFetch.length > 0) pending++;
        if (readToFetch.length > 0) pending++;
        if (!suggestionsValid) pending++;

        if (pending === 0) {
            renderWidget(container, viewedPosts, readPosts, suggestions);
            return;
        }

        renderLoading(container);

        function checkComplete() {
            pending--;
            if (pending === 0) {
                renderWidget(container, viewedPosts, readPosts, suggestions);
            }
        }

        function updatePostsWithFetched(posts, fetched) {
            return posts.map(function(p) {
                if (!p.needsFetch) return p;
                var found = fetched.find(function(f) { return f.id === p.id; });
                return found || p;
            });
        }

        if (viewedToFetch.length > 0) {
            fetchPostsByIds(postsEndpoint, viewedToFetch, function(fetched) {
                viewedPosts = updatePostsWithFetched(viewedPosts, fetched);
                checkComplete();
            });
        }

        if (readToFetch.length > 0) {
            fetchPostsByIds(postsEndpoint, readToFetch, function(fetched) {
                readPosts = updatePostsWithFetched(readPosts, fetched);
                checkComplete();
            });
        }

        if (!suggestionsValid) {
            fetchSuggestions(suggestionsEndpoint, config.maxSuggestions, function(fetched) {
                suggestions = fetched;
                if (storage.setSuggestions) storage.setSuggestions(fetched);
                checkComplete();
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();