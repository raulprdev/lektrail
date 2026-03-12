(function () {
	'use strict';

	function createAsyncProvider(storage, endpoint) {
		return {
			getViewed: function () {
				return storage.getViewedPosts();
			},
			getRead: function () {
				return storage.getReadPosts();
			},
			getSuggestions: function (callback) {
				var excludeIds = storage
					.getViewedIds()
					.concat(storage.getReadIds());

				var url = endpoint;
				if (excludeIds.length > 0) {
					url += '&exclude=' + excludeIds.join(',');
				}

				var xhr = new XMLHttpRequest();
				xhr.open('GET', url);
				xhr.onload = function () {
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
				xhr.onerror = function () {
					callback([]);
				};
				xhr.send();
			}
		};
	}

	function createInlineProvider(inlineData) {
		return {
			getViewed: function () {
				return inlineData.viewed || [];
			},
			getRead: function () {
				return inlineData.read || [];
			},
			getSuggestions: function (callback) {
				callback(inlineData.suggestions || []);
			}
		};
	}

	function getStorage() {
		return (
			window.CompletionistStorage || {
				getViewedPosts: function () {
					return [];
				},
				getReadPosts: function () {
					return [];
				},
				getViewedIds: function () {
					return [];
				},
				getReadIds: function () {
					return [];
				}
			}
		);
	}

	function create(config) {
		if (config.dataMode === 'inline') {
			return createInlineProvider(config.inlineData || {});
		}
		return createAsyncProvider(getStorage(), config.endpoint);
	}

	window.CompletionistDataProvider = {
		create: create
	};
})();