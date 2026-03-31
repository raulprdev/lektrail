(function () {
	'use strict';

	function renderItem(post) {
		let html = '<li class="lektrail-item">';

		if (post.thumbnail) {
			html += '<div class="lektrail-thumbnail">';
			html += '<img src="' + post.thumbnail + '" alt="">';
			html += '</div>';
		}

		html += '<div class="lektrail-content">';
		html +=
			'<a class="lektrail-title" href="' +
			post.url +
			'">' +
			post.title +
			'</a>';

		if (post.excerpt) {
			html += '<p class="lektrail-excerpt">' + post.excerpt + '</p>';
		}

		html += '</div>';
		html += '</li>';

		return html;
	}

	window.LekTrailRenderItem = renderItem;
})();
