(function () {
	'use strict';

	function renderItem(post) {
		let html = '<li class="completionist-item">';

		if (post.thumbnail) {
			html += '<div class="completionist-thumbnail">';
			html += '<img src="' + post.thumbnail + '" alt="">';
			html += '</div>';
		}

		html += '<div class="completionist-content">';
		html +=
			'<a class="completionist-title" href="' +
			post.url +
			'">' +
			post.title +
			'</a>';

		if (post.excerpt) {
			html += '<p class="completionist-excerpt">' + post.excerpt + '</p>';
		}

		html += '</div>';
		html += '</li>';

		return html;
	}

	window.CompletionistRenderItem = renderItem;
})();
