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

	function trackViewed(postId, storage, dispatch, postData) {
		storage.addViewed(postId, postData);
		if (dispatch) {
			dispatch('completionist:viewed', { postId });
		}
	}

	function trackRead(postId, storage, dispatch) {
		storage.addRead(postId);
		if (dispatch) {
			dispatch('completionist:read', { postId });
		}
	}

	function createSentinel(dom, article) {
		const sentinel = dom.createElement('div');
		sentinel.style.cssText =
			'position:absolute;bottom:10%;width:1px;height:1px;opacity:0;pointer-events:none;';

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
				trackRead(postId, storage, dispatch);
				markedRead = true;
				return true;
			}
			return false;
		}

		function init() {
			const article = findArticle(dom);
			const postId = getPostId(dom);

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
					storage,
					dispatch,
					global.CompletionistPostData
				);
			}

			if (!Observer) {
				global.addEventListener(
					'scroll',
					function () {
						const percent =
							((global.scrollY + global.innerHeight) /
								dom.body.scrollHeight) *
							100;
						if (percent >= 90) {
							handleIntersection();
						}
					},
					{ passive: true }
				);
				return { success: true, method: 'scroll' };
			}

			const sentinel = createSentinel(dom, article);
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
	};

	if (typeof document !== 'undefined') {
		let consentManager = null;
		if (global.CompletionistConsentManager) {
			consentManager = global.CompletionistConsentManager.create();
		}
		const detector = createDetector({ consentManager });
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', function () {
				detector.init();
			});
		} else {
			detector.init();
		}
	}
})(typeof window !== 'undefined' ? window : this);
