(function() {
    'use strict';

    const STORAGE_KEY = 'completionist';

    function getData() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY)) || { reads: [] };
        } catch (e) {
            return { reads: [] };
        }
    }

    function setData(data) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        } catch (e) {
            // Storage full or unavailable
        }
    }

    window.CompletionistStorage = {
        getReadIds: function() {
            return getData().reads.map(function(r) { return r.postId; });
        },

        addRead: function(postId) {
            var data = getData();
            var exists = data.reads.some(function(r) { return r.postId === postId; });
            if (exists) return;

            data.reads.push({ postId: postId, readAt: new Date().toISOString() });
            setData(data);
        },

        hasRead: function(postId) {
            return getData().reads.some(function(r) { return r.postId === postId; });
        },

        clear: function() {
            localStorage.removeItem(STORAGE_KEY);
        },

        getReadCount: function() {
            return getData().reads.length;
        }
    };
})();
