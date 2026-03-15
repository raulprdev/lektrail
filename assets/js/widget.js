(function () {
	'use strict';

	const CONSENT_CHECKBOX_ID = 'completionist-consent-checkbox';

	function getConfig(container) {
		if (!window.CompletionistConfig) {
			throw new Error('CompletionistConfig not found');
		}
		let config = window.CompletionistConfig;
		if (container && container.dataset.config) {
			try {
				const instanceConfig = JSON.parse(container.dataset.config);
				config = Object.assign({}, config, instanceConfig);
				if (instanceConfig.labels) {
					config.labels = Object.assign(
						{},
						config.labels,
						instanceConfig.labels
					);
				}
			} catch (e) {
				// Invalid JSON, use global config
			}
		}
		return config;
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

	function getDataProvider(endpoint) {
		if (window.CompletionistDataProvider) {
			return window.CompletionistDataProvider.create({
				dataMode: 'async',
				endpoint,
			});
		}
		return null;
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

	function renderLoading(container, config) {
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

	function renderEmptyState(config) {
		return (
			'<div class="completionist-empty">' + config.labels.empty + '</div>'
		);
	}

	function renderClearButton(config) {
		const label = config.labels.clear || 'Clear my data';
		return (
			'<div class="completionist-clear">' +
			'<button type="button" class="completionist-clear-btn">' +
			label +
			'</button>' +
			'</div>'
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

	function renderWidget(container, dataSource, suggestions, config) {
		const storage = getStorage();
		const viewedPosts = dataSource.getViewed();
		const readPosts = dataSource.getRead();
		const viewedCount = dataSource.getViewedCount();
		const readCount = dataSource.getReadCount();

		const viewedSlice = config.viewedEnabled
			? filterValid(viewedPosts).slice(0, config.maxViewed)
			: [];
		const readSlice = config.completedEnabled
			? filterValid(readPosts).slice(0, config.maxRead)
			: [];
		const suggestionsSlice = filterValid(suggestions).slice(
			0,
			config.maxSuggestions
		);

		const classes = ['completionist-widget'];
		if (config.showExcerpt) {
			classes.push('completionist-show-excerpt');
		}
		if (config.showThumbnail) {
			classes.push('completionist-show-thumbnail');
		}
		container.className = classes.join(' ');

		let html = '';

		const hasUserData = viewedCount > 0 || readCount > 0;
		const canClear = !config.serverSideTracking;
		if (config.showClearButton !== false && hasUserData && canClear) {
			html += renderClearButton(config);
		}

		if (config.viewedEnabled) {
			html += renderSection(
				config.labels.continue,
				viewedSlice,
				'completionist-continue',
				viewedCount
			);
		}
		if (config.completedEnabled) {
			html += renderSection(
				config.labels.completed,
				readSlice,
				'completionist-completed',
				readCount
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
			html += renderEmptyState(config);
		}

		container.innerHTML = html;

		const clearBtn = container.querySelector('.completionist-clear-btn');
		if (clearBtn) {
			clearBtn.addEventListener('click', function () {
				storage.clearHistory();
				const emptySource = window.CompletionistDataSource.create({
					inlineData: {},
				});
				renderWidget(container, emptySource, suggestions, config);
			});
		}
	}

	function getConsentManager() {
		if (window.CompletionistConsentManager) {
			return window.CompletionistConsentManager.create();
		}
		return null;
	}

	function init() {
		const globalConfig = getConfig();
		const container = document.getElementById(globalConfig.widgetId);
		if (!container) {
			return;
		}

		const config = getConfig(container);

		if (config.requireConsent && !config.serverSideTracking) {
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

	function createFetcher(endpoint, storage) {
		return {
			fetch(callback) {
				const viewedIds = storage.getViewedIds
					? storage.getViewedIds()
					: [];
				const readIds = storage.getReadIds ? storage.getReadIds() : [];
				const excludeIds = viewedIds.concat(readIds);
				const url =
					endpoint +
					(endpoint.indexOf('?') >= 0 ? '&' : '?') +
					'exclude=' +
					excludeIds.join(',');

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

	function createDataSource(config, container) {
		if (window.CompletionistInlineData) {
			return window.CompletionistDataSource.create({
				inlineData: window.CompletionistInlineData,
			});
		}

		const storage = getStorage();
		const provider = getDataProvider(container.dataset.endpoint);
		const cacheHours = config.suggestionsCacheHours || 24;

		return window.CompletionistDataSource.create({
			storage: {
				getViewedPosts() {
					return provider ? provider.getViewed() : [];
				},
				getReadPosts() {
					return provider ? provider.getRead() : [];
				},
				getViewedCount() {
					return storage.getViewedCount
						? storage.getViewedCount()
						: 0;
				},
				getReadCount() {
					return storage.getReadCount ? storage.getReadCount() : 0;
				},
				getSuggestions() {
					return storage.getSuggestions
						? storage.getSuggestions()
						: [];
				},
				setSuggestions(suggestions) {
					if (storage.setSuggestions) {
						storage.setSuggestions(suggestions);
					}
				},
				isSuggestionsCacheValid(hours) {
					return storage.isSuggestionsCacheValid
						? storage.isSuggestionsCacheValid(hours)
						: false;
				},
			},
			fetcher: createFetcher(container.dataset.endpoint, storage),
			cacheHours,
		});
	}

	function initWidget(container, config) {
		const dataSource = createDataSource(config, container);

		renderLoading(container, config);

		dataSource.getSuggestions(function (suggestions) {
			renderWidget(container, dataSource, suggestions, config);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	window.CompletionistWidget = {
		init(container, previewData, config) {
			window.CompletionistInlineData = previewData;
			window.CompletionistConfig = config;
			initWidget(container, config);
		},
	};
})();
