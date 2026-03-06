(function(global) {
    'use strict';

    function getPostId(dom) {
        var el = dom.querySelector('[data-completionist-post]');
        return el ? parseInt(el.dataset.completionistPost, 10) : null;
    }

    function findArticle(dom) {
        return dom.querySelector('article') || dom.querySelector('main');
    }

    function shouldTrack(postId, storage, alreadyTracked) {
        if (alreadyTracked) return false;
        if (!postId) return false;
        if (!storage) return false;
        if (storage.isTracked(postId)) return false;
        return true;
    }

    function shouldMarkRead(postId, storage, alreadyMarkedRead) {
        if (alreadyMarkedRead) return false;
        if (!postId) return false;
        if (!storage) return false;
        if (storage.hasRead(postId)) return false;
        return true;
    }

    function trackViewed(postId, storage, dispatch) {
        storage.addViewed(postId);
        if (dispatch) {
            dispatch('completionist:viewed', { postId: postId });
        }
    }

    function trackRead(postId, storage, dispatch) {
        storage.addRead(postId);
        if (dispatch) {
            dispatch('completionist:read', { postId: postId });
        }
    }

    function createSentinel(dom, article) {
        var sentinel = dom.createElement('div');
        sentinel.style.cssText = 'position:absolute;bottom:10%;width:1px;height:1px;opacity:0;pointer-events:none;';

        var style = global.getComputedStyle ? global.getComputedStyle(article) : { position: 'static' };
        if (style.position === 'static') {
            article.style.position = 'relative';
        }

        article.appendChild(sentinel);
        return sentinel;
    }

    function createDetector(deps) {
        deps = deps || {};
        var dom = deps.dom || document;
        var storage = deps.storage || global.CompletionistStorage;
        var dispatch = deps.dispatch || function(name, detail) {
            dom.dispatchEvent(new CustomEvent(name, { detail: detail }));
        };
        var Observer = deps.Observer || global.IntersectionObserver;

        var markedRead = false;

        function handleIntersection() {
            var postId = getPostId(dom);
            if (shouldMarkRead(postId, storage, markedRead)) {
                trackRead(postId, storage, dispatch);
                markedRead = true;
                return true;
            }
            return false;
        }

        function init() {
            var article = findArticle(dom);
            var postId = getPostId(dom);

            if (!article || !postId) {
                return { success: false, reason: !article ? 'no-article' : 'no-postid' };
            }

            if (shouldTrack(postId, storage, false)) {
                trackViewed(postId, storage, dispatch);
            }

            if (!Observer) {
                global.addEventListener('scroll', function() {
                    var percent = (global.scrollY + global.innerHeight) / dom.body.scrollHeight * 100;
                    if (percent >= 90) handleIntersection();
                }, { passive: true });
                return { success: true, method: 'scroll' };
            }

            var sentinel = createSentinel(dom, article);
            var observer = new Observer(function(entries) {
                if (entries[0].isIntersecting) {
                    handleIntersection();
                    observer.disconnect();
                }
            });
            observer.observe(sentinel);

            return { success: true, method: 'observer', sentinel: sentinel };
        }

        return {
            init: init,
            handleIntersection: handleIntersection,
            isTracked: function() { return markedRead; }
        };
    }

    global.CompletionistDetector = {
        getPostId: getPostId,
        findArticle: findArticle,
        shouldTrack: shouldTrack,
        trackRead: trackRead,
        createSentinel: createSentinel,
        createDetector: createDetector
    };

    if (typeof document !== 'undefined') {
        var detector = createDetector();
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() { detector.init(); });
        } else {
            detector.init();
        }
    }

})(typeof window !== 'undefined' ? window : this);