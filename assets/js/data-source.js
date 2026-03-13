(function () {
	'use strict';

	function createServerSideSource(inlineData) {
		var viewed = inlineData.viewed || [];
		var read = inlineData.read || [];
		var suggestions = inlineData.suggestions || [];

		return {
			getViewed: function () {
				return viewed;
			},
			getRead: function () {
				return read;
			},
			getViewedCount: function () {
				return viewed.length;
			},
			getReadCount: function () {
				return read.length;
			},
			getSuggestions: function (callback) {
				callback(suggestions);
			}
		};
	}

	function createLocalStorageSource(storage, fetcher, cacheHours) {
		return {
			getViewed: function () {
				return storage.getViewedPosts();
			},
			getRead: function () {
				return storage.getReadPosts();
			},
			getViewedCount: function () {
				return storage.getViewedCount();
			},
			getReadCount: function () {
				return storage.getReadCount();
			},
			getSuggestions: function (callback) {
				if (storage.isSuggestionsCacheValid(cacheHours)) {
					callback(storage.getSuggestions());
					return;
				}
				fetcher.fetch(function (suggestions) {
					storage.setSuggestions(suggestions);
					callback(suggestions);
				});
			}
		};
	}

	window.CompletionistDataSource = {
		create: function (options) {
			if (options.inlineData) {
				return createServerSideSource(options.inlineData);
			}
			return createLocalStorageSource(
				options.storage,
				options.fetcher,
				options.cacheHours || 24
			);
		}
	};
})();