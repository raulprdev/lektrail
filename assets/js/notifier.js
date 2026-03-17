(function () {
	'use strict';

	function createLocalStorageNotifier(storage) {
		return {
			trackViewed(postId, postData) {
				storage.addViewed(postId, postData);
			},
			trackRead(postId) {
				storage.addRead(postId);
			},
		};
	}

	function createServerSideNotifier(endpoint, nonce) {
		return {
			trackViewed() {
				// Server-side viewed tracking is done on page load by PHP
			},
			trackRead(postId) {
				const xhr = new XMLHttpRequest();
				xhr.open('POST', endpoint);
				xhr.setRequestHeader(
					'Content-Type',
					'application/x-www-form-urlencoded'
				);
				xhr.send('post_id=' + postId + '&nonce=' + nonce);
			},
		};
	}

	window.CompletionistNotifier = {
		create(options) {
			if (options.endpoint) {
				return createServerSideNotifier(options.endpoint, options.nonce);
			}
			return createLocalStorageNotifier(options.storage);
		},
	};
})();
