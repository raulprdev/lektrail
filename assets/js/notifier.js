(function () {
	'use strict';

	function createLocalStorageNotifier(storage) {
		return {
			trackViewed: function (postId, postData) {
				storage.addViewed(postId, postData);
			},
			trackRead: function (postId) {
				storage.addRead(postId);
			}
		};
	}

	function createServerSideNotifier(endpoint) {
		return {
			trackViewed: function () {
				// Server-side viewed tracking is done on page load by PHP
			},
			trackRead: function (postId) {
				var xhr = new XMLHttpRequest();
				xhr.open('POST', endpoint);
				xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				xhr.send('post_id=' + postId);
			}
		};
	}

	window.CompletionistNotifier = {
		create: function (options) {
			if (options.endpoint) {
				return createServerSideNotifier(options.endpoint);
			}
			return createLocalStorageNotifier(options.storage);
		}
	};
})();