(function () {
	'use strict';

	const CONSENT_CHECKBOX_ID = 'completionist-consent-checkbox';

	function getConfig() {
		if (!window.CompletionistConfig) {
			throw new Error('CompletionistConfig not found');
		}
		return window.CompletionistConfig;
	}

	function fetchSuggestions(endpoint, count, callback) {
		const xhr = new XMLHttpRequest();
		xhr.open('GET', endpoint + '&count=' + count);
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
	}

	function getStorage() {
		return (
			window.CompletionistStorage || {
				getViewedCount() {
					return 0;
				},
				getReadCount() {
					return 0;
				},
				getViewedIds() {
					return [];
				},
				getReadIds() {
					return [];
				},
				isTracked() {
					return false;
				},
			}
		);
	}

	function filterUntracked(posts) {
		const storage = getStorage();
		return posts.filter(function (post) {
			return !storage.isTracked(post.id);
		});
	}

	function filterValid(posts) {
		return posts.filter(function (post) {
			return post.title && post.url;
		});
	}

	function getRenderItem() {
		return (
			window.CompletionistRenderItem ||
			function (post) {
				return (
					'<li><a href="' + post.url + '">' + post.title + '</a></li>'
				);
			}
		);
	}

	function renderLoading(container) {
		const config = getConfig();
		container.innerHTML =
			'<div class="completionist-loading">' +
			config.labels.loading +
			'</div>';
	}

	function renderSection(title, posts, className, count) {
		if (posts.length === 0) {
			return '';
		}

		const renderItem = getRenderItem();
		const titleWithCount =
			count !== undefined ? title + ' (' + count + ')' : title;
		let html = '<div class="' + className + '">';
		html += '<h3>' + titleWithCount + '</h3>';
		html += '<ul class="completionist-list">';
		posts.forEach(function (post) {
			html += renderItem(post);
		});
		html += '</ul>';
		html += '</div>';
		return html;
	}

	function renderEmptyState() {
		const config = getConfig();
		return (
			'<div class="completionist-empty">' + config.labels.empty + '</div>'
		);
	}

	function renderConsentBanner(container, config, onConsent) {
		let html = '<div class="completionist-consent">';
		html += '<p>' + config.labels.consentMessage + '</p>';
		html += '<label>';
		html += '<input type="checkbox" id="' + CONSENT_CHECKBOX_ID + '">';
		html += ' ' + config.labels.consentCheckboxLabel;
		html += '</label>';
		html += '</div>';
		container.innerHTML = html;

		const checkbox = container.querySelector('#' + CONSENT_CHECKBOX_ID);
		if (checkbox) {
			checkbox.addEventListener('change', function () {
				if (this.checked && onConsent) {
					onConsent();
				}
			});
		}
	}

	function renderWidget(container, viewedPosts, readPosts, suggestions) {
		const config = getConfig();
		const storage = getStorage();
		const viewedSlice = config.viewedEnabled
			? filterValid(viewedPosts).slice(0, config.maxViewed)
			: [];
		const readSlice = config.completedEnabled
			? filterValid(readPosts).slice(0, config.maxRead)
			: [];
		const suggestionsSlice = filterUntracked(suggestions).slice(
			0,
			config.maxSuggestions
		);

		const classes = [];
		if (config.showExcerpt) {
			classes.push('completionist-show-excerpt');
		}
		if (config.showThumbnail) {
			classes.push('completionist-show-thumbnail');
		}
		container.className = classes.join(' ');

		let html = '';
		if (config.viewedEnabled) {
			html += renderSection(
				config.labels.continue,
				viewedSlice,
				'completionist-continue',
				storage.getViewedCount()
			);
		}
		if (config.completedEnabled) {
			html += renderSection(
				config.labels.completed,
				readSlice,
				'completionist-completed',
				storage.getReadCount()
			);
		}
		html += renderSection(
			config.labels.suggestions,
			suggestionsSlice,
			'completionist-suggestions'
		);

		const hasContent =
			viewedSlice.length || readSlice.length || suggestionsSlice.length;
		if (!hasContent) {
			html += renderEmptyState();
		}

		container.innerHTML = html;
	}

	function getConsentManager() {
		if (window.CompletionistConsentManager) {
			return window.CompletionistConsentManager.create();
		}
		return null;
	}

	function init() {
		const config = getConfig();
		const container = document.getElementById(config.widgetId);
		if (!container) {
			return;
		}

		if (config.requireConsent) {
			const consentManager = getConsentManager();
			if (consentManager && consentManager.hasConsent() !== true) {
				if (consentManager.isBuiltInProvider()) {
					renderConsentBanner(container, config, function () {
						consentManager.grantConsent();
						initWidget(container, config);
					});
				}
				return;
			}
		}

		initWidget(container, config);
	}

	function initWidget(container, config) {
		const storage = getStorage();
		const suggestionsEndpoint = container.dataset.endpoint;
		const cacheHours = config.suggestionsCacheHours || 24;

		const viewedPosts = storage.getViewedPosts
			? storage.getViewedPosts()
			: [];
		const readPosts = storage.getReadPosts ? storage.getReadPosts() : [];

		const suggestionsValid =
			storage.isSuggestionsCacheValid &&
			storage.isSuggestionsCacheValid(cacheHours);
		let suggestions =
			suggestionsValid && storage.getSuggestions
				? storage.getSuggestions()
				: [];

		if (suggestionsValid) {
			renderWidget(container, viewedPosts, readPosts, suggestions);
			return;
		}

		renderLoading(container);

		fetchSuggestions(
			suggestionsEndpoint,
			config.maxSuggestions,
			function (fetched) {
				suggestions = fetched;
				if (storage.setSuggestions) {
					storage.setSuggestions(fetched);
				}
				renderWidget(container, viewedPosts, readPosts, suggestions);
			}
		);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
