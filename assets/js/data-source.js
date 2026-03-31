(function () {
	'use strict';

	function createServerSideSource(inlineData) {
		const viewed = inlineData.viewed || [];
		const read = inlineData.read || [];
		const suggestions = inlineData.suggestions || [];

		return {
			getViewed() {
				return viewed;
			},
			getRead() {
				return read;
			},
			getViewedCount() {
				return viewed.length;
			},
			getReadCount() {
				return read.length;
			},
			getSuggestions(callback) {
				callback(suggestions);
			},
		};
	}

	function createLocalStorageSource(storage, fetcher, cacheHours) {
		return {
			getViewed() {
				return storage.getViewedPosts();
			},
			getRead() {
				return storage.getReadPosts();
			},
			getViewedCount() {
				return storage.getViewedCount();
			},
			getReadCount() {
				return storage.getReadCount();
			},
			getSuggestions(callback) {
				if (storage.isSuggestionsCacheValid(cacheHours)) {
					callback(storage.getSuggestions());
					return;
				}
				fetcher.fetch(function (suggestions) {
					storage.setSuggestions(suggestions);
					callback(suggestions);
				});
			},
		};
	}

	window.LekTrailDataSource = {
		create(options) {
			if (options.inlineData) {
				return createServerSideSource(options.inlineData);
			}
			return createLocalStorageSource(
				options.storage,
				options.fetcher,
				options.cacheHours || 24
			);
		},
	};
})();
