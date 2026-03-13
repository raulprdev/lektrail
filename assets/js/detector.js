(function (global) {
	'use strict';

	function getPostId(dom) {
		const el = dom.querySelector('[data-completionist-post]');
		return el ? parseInt(el.dataset.completionistPost, 10) : null;
	}

	function findArticle(dom) {
		return dom.querySelector('article') || dom.querySelector('main');
	}

	function shouldTrack(postId, storage, alreadyTracked) {
		if (alreadyTracked) {
			return false;
		}
		if (!postId) {
			return false;
		}
		if (!storage) {
			return false;
		}
		if (storage.isTracked(postId)) {
			return false;
		}
		return true;
	}

	function shouldMarkRead(postId, storage, alreadyMarkedRead) {
		if (alreadyMarkedRead) {
			return false;
		}
		if (!postId) {
			return false;
		}
		if (!storage) {
			return false;
		}
		if (storage.hasRead(postId)) {
			return false;
		}
		return true;
	}

	function getNotifier(deps, storage) {
		if (deps.notifier) {
			return deps.notifier;
		}
		if (global.CompletionistNotifier) {
			return global.CompletionistNotifier.create({ storage: storage });
		}
		return { trackViewed: function () {}, trackRead: function () {} };
	}

	function trackViewed(postId, notifier, dispatch, postData) {
		notifier.trackViewed(postId, postData);
		if (dispatch) {
			dispatch('completionist:viewed', { postId });
		}
	}

	function trackRead(postId, notifier, dispatch) {
		notifier.trackRead(postId);
		if (dispatch) {
			dispatch('completionist:read', { postId });
		}
	}

	function getReadThreshold() {
		const config = global.CompletionistConfig;
		if (config && typeof config.readThreshold === 'number') {
			return config.readThreshold;
		}
		return 90;
	}

	function createSentinel(dom, article, threshold) {
		const sentinel = dom.createElement('div');
		const bottom = 100 - threshold;
		sentinel.style.cssText =
			'position:absolute;bottom:' +
			bottom +
			'%;width:1px;height:1px;opacity:0;pointer-events:none;';

		const style = global.getComputedStyle
			? global.getComputedStyle(article)
			: { position: 'static' };
		if (style.position === 'static') {
			article.style.position = 'relative';
		}

		article.appendChild(sentinel);
		return sentinel;
	}

	function createDetector(deps) {
		deps = deps || {};
		const dom = deps.dom || document;
		const storage = deps.storage || global.CompletionistStorage;
		const notifier = getNotifier(deps, storage);
		const dispatch =
			deps.dispatch ||
			function (name, detail) {
				dom.dispatchEvent(new CustomEvent(name, { detail }));
			};
		const Observer = deps.Observer || global.IntersectionObserver;
		const consentManager = deps.consentManager || null;

		let markedRead = false;

		function hasConsent() {
			if (!consentManager) {
				return true;
			}
			return consentManager.hasConsent() === true;
		}

		function handleIntersection() {
			if (!hasConsent()) {
				return false;
			}
			const postId = getPostId(dom);
			if (shouldMarkRead(postId, storage, markedRead)) {
				trackRead(postId, notifier, dispatch);
				markedRead = true;
				return true;
			}
			return false;
		}

		function init() {
			const article = findArticle(dom);
			const postId = getPostId(dom);
			const threshold = getReadThreshold();

			if (!article || !postId) {
				return {
					success: false,
					reason: !article ? 'no-article' : 'no-postid',
				};
			}

			if (hasConsent() && shouldTrack(postId, storage, false)) {
				if (!global.CompletionistPostData) {
					// eslint-disable-next-line no-console
					console.warn(
						'Completionist: PostData not found, skipping tracking'
					);
					return { success: false, reason: 'no-postdata' };
				}
				trackViewed(
					postId,
					notifier,
					dispatch,
					global.CompletionistPostData
				);
			}

			if (!Observer) {
				global.addEventListener(
					'scroll',
					function () {
						const totalHeight = global.scrollY + global.innerHeight;
						const percent = (totalHeight / dom.body.scrollHeight) * 100;
						if (percent >= threshold) {
							handleIntersection();
						}
					},
					{ passive: true }
				);
				return { success: true, method: 'scroll' };
			}

			const sentinel = createSentinel(dom, article, threshold);
			const observer = new Observer(function (entries) {
				if (entries[0].isIntersecting) {
					handleIntersection();
					observer.disconnect();
				}
			});
			observer.observe(sentinel);

			return { success: true, method: 'observer', sentinel };
		}

		return {
			init,
			handleIntersection,
			isTracked() {
				return markedRead;
			},
		};
	}

	global.CompletionistDetector = {
		getPostId,
		findArticle,
		shouldTrack,
		trackRead,
		createSentinel,
		createDetector,
		getReadThreshold,
	};

	if (typeof document !== 'undefined') {
		var consentManager = null;
		if (global.CompletionistConsentManager) {
			consentManager = global.CompletionistConsentManager.create();
		}

		var notifier = null;
		var config = global.CompletionistConfig;
		if (config && config.trackingEndpoint && global.CompletionistNotifier) {
			notifier = global.CompletionistNotifier.create({ endpoint: config.trackingEndpoint });
		}

		var detector = createDetector({ consentManager: consentManager, notifier: notifier });
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', function () {
				detector.init();
			});
		} else {
			detector.init();
		}
	}
})(typeof window !== 'undefined' ? window : this);
