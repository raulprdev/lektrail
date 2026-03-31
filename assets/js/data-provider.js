(function () {
	'use strict';

	function createAsyncProvider(storage, endpoint) {
		return {
			getViewed() {
				return storage.getViewedPosts();
			},
			getRead() {
				return storage.getReadPosts();
			},
			getSuggestions(callback) {
				const excludeIds = storage
					.getViewedIds()
					.concat(storage.getReadIds());

				let url = endpoint;
				if (excludeIds.length > 0) {
					url += '&exclude=' + excludeIds.join(',');
				}

				const xhr = new XMLHttpRequest();
				xhr.open('GET', url);
				xhr.onload = function () {
					if (xhr.status === 200) {
						try {
							const response = JSON.parse(xhr.responseText);
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
			},
		};
	}

	function createInlineProvider(inlineData) {
		return {
			getViewed() {
				return inlineData.viewed || [];
			},
			getRead() {
				return inlineData.read || [];
			},
			getSuggestions(callback) {
				callback(inlineData.suggestions || []);
			},
		};
	}

	function getStorage() {
		return (
			window.LekTrailStorage || {
				getViewedPosts() {
					return [];
				},
				getReadPosts() {
					return [];
				},
				getViewedIds() {
					return [];
				},
				getReadIds() {
					return [];
				},
			}
		);
	}

	function create(config) {
		if (config.dataMode === 'inline') {
			return createInlineProvider(config.inlineData || {});
		}
		return createAsyncProvider(getStorage(), config.endpoint);
	}

	window.LekTrailDataProvider = {
		create,
	};
})();
