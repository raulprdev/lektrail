(function() {
    'use strict';

    var STORAGE_KEY = 'completionist';

    function getData() {
        try {
            var data = JSON.parse(localStorage.getItem(STORAGE_KEY));
            if (!data) return { viewed: [], read: [], suggestions: [], suggestionsUpdatedAt: null };

            if (data.reads && !data.read) {
                return { viewed: [], read: data.reads, suggestions: [], suggestionsUpdatedAt: null };
            }

            return {
                viewed: data.viewed || [],
                read: data.read || [],
                suggestions: data.suggestions || [],
                suggestionsUpdatedAt: data.suggestionsUpdatedAt || null
            };
        } catch (e) {
            return { viewed: [], read: [], suggestions: [], suggestionsUpdatedAt: null };
        }
    }

    function setData(data) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        } catch (e) {
            // Storage full or unavailable
        }
    }

    function findIndexById(array, postId) {
        for (var i = 0; i < array.length; i++) {
            if (array[i].postId === postId) return i;
        }
        return -1;
    }

    window.CompletionistStorage = {
        getViewedIds: function() {
            return getData().viewed.map(function(v) { return v.postId; });
        },

        getReadIds: function() {
            return getData().read.map(function(r) { return r.postId; });
        },

        addViewed: function(postId, postData) {
            var data = getData();

            if (findIndexById(data.viewed, postId) !== -1) return;
            if (findIndexById(data.read, postId) !== -1) return;

            var entry = { postId: postId, viewedAt: new Date().toISOString() };
            if (postData && postData.title) entry.title = postData.title;
            if (postData && postData.url) entry.url = postData.url;

            data.viewed.push(entry);
            setData(data);
        },

        addRead: function(postId, postData) {
            var data = getData();

            if (findIndexById(data.read, postId) !== -1) return;

            var viewedIndex = findIndexById(data.viewed, postId);
            var viewedEntry = viewedIndex !== -1 ? data.viewed[viewedIndex] : null;
            if (viewedIndex !== -1) {
                data.viewed.splice(viewedIndex, 1);
            }

            var entry = { postId: postId, readAt: new Date().toISOString() };
            if (postData && postData.title) {
                entry.title = postData.title;
            } else if (viewedEntry && viewedEntry.title) {
                entry.title = viewedEntry.title;
            }
            if (postData && postData.url) {
                entry.url = postData.url;
            } else if (viewedEntry && viewedEntry.url) {
                entry.url = viewedEntry.url;
            }

            data.read.push(entry);
            setData(data);
        },

        hasViewed: function(postId) {
            return findIndexById(getData().viewed, postId) !== -1;
        },

        hasRead: function(postId) {
            return findIndexById(getData().read, postId) !== -1;
        },

        isTracked: function(postId) {
            var data = getData();
            return findIndexById(data.viewed, postId) !== -1 || findIndexById(data.read, postId) !== -1;
        },

        getViewedCount: function() {
            return getData().viewed.length;
        },

        getReadCount: function() {
            return getData().read.length;
        },

        clear: function() {
            localStorage.removeItem(STORAGE_KEY);
        },

        getViewedPosts: function() {
            return getData().viewed.map(function(entry) {
                var post = { id: entry.postId };
                if (entry.title && entry.url) {
                    post.title = entry.title;
                    post.url = entry.url;
                } else {
                    post.needsFetch = true;
                }
                return post;
            }).reverse();
        },

        getReadPosts: function() {
            return getData().read.map(function(entry) {
                var post = { id: entry.postId };
                if (entry.title && entry.url) {
                    post.title = entry.title;
                    post.url = entry.url;
                } else {
                    post.needsFetch = true;
                }
                return post;
            }).reverse();
        },

        setSuggestions: function(posts) {
            var data = getData();
            data.suggestions = posts;
            data.suggestionsUpdatedAt = new Date().toISOString();
            setData(data);
        },

        getSuggestions: function() {
            return getData().suggestions;
        },

        isSuggestionsCacheValid: function(maxHours) {
            var data = getData();
            if (!data.suggestionsUpdatedAt || data.suggestions.length === 0) {
                return false;
            }
            var updatedAt = new Date(data.suggestionsUpdatedAt).getTime();
            var now = Date.now();
            var maxMs = maxHours * 60 * 60 * 1000;
            return (now - updatedAt) < maxMs;
        },

        clearSuggestionsCache: function() {
            var data = getData();
            data.suggestions = [];
            data.suggestionsUpdatedAt = null;
            setData(data);
        }
    };
})();