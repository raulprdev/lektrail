(function() {
    'use strict';

    var STORAGE_KEY = 'completionist';

    function getData() {
        try {
            var data = JSON.parse(localStorage.getItem(STORAGE_KEY));
            if (!data) return { viewed: [], read: [] };

            if (data.reads && !data.read) {
                return { viewed: [], read: data.reads };
            }

            return {
                viewed: data.viewed || [],
                read: data.read || []
            };
        } catch (e) {
            return { viewed: [], read: [] };
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

        addViewed: function(postId) {
            var data = getData();

            if (findIndexById(data.viewed, postId) !== -1) return;
            if (findIndexById(data.read, postId) !== -1) return;

            data.viewed.push({ postId: postId, viewedAt: new Date().toISOString() });
            setData(data);
        },

        addRead: function(postId) {
            var data = getData();

            if (findIndexById(data.read, postId) !== -1) return;

            var viewedIndex = findIndexById(data.viewed, postId);
            if (viewedIndex !== -1) {
                data.viewed.splice(viewedIndex, 1);
            }

            data.read.push({ postId: postId, readAt: new Date().toISOString() });
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
        }
    };
})();