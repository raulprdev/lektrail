const fs = require('fs');
const path = require('path');
const { mockStorage } = require('./helpers/test-helpers');
const { mockDom } = require('./helpers/detector-helpers');

const detectorCode = fs.readFileSync(
    path.join(__dirname, '../../assets/js/detector.js'),
    'utf8'
);

let D;

beforeEach(() => {
    delete window.CompletionistDetector;
    delete window.CompletionistPostData;
    eval(detectorCode);
    D = window.CompletionistDetector;
});

afterEach(() => {
    delete window.CompletionistPostData;
});

function setPostData(postId) {
    window.CompletionistPostData = {
        id: postId,
        title: 'Test Post',
        url: '/test-post',
        excerpt: 'Test excerpt',
        thumbnail: 'http://example.com/image.jpg'
    };
}

describe('shouldTrack', () => {
    test('true when post is untracked', () => {
        expect(D.shouldTrack(123, mockStorage(), false)).toBe(true);
    });

    test('false when post already viewed', () => {
        expect(D.shouldTrack(123, mockStorage({ viewedPosts: [{ id: 123, title: 'Test', url: '/test' }] }), false)).toBe(false);
    });

    test('false when post already read', () => {
        expect(D.shouldTrack(123, mockStorage({ readPosts: [{ id: 123, title: 'Test', url: '/test' }] }), false)).toBe(false);
    });

    test('false when already tracked this session', () => {
        expect(D.shouldTrack(123, mockStorage(), true)).toBe(false);
    });

    test('false when no postId', () => {
        expect(D.shouldTrack(null, mockStorage(), false)).toBe(false);
    });

    test('false when no storage', () => {
        expect(D.shouldTrack(123, null, false)).toBe(false);
    });
});

describe('Detector: on page load', () => {
    test('marks post as viewed on init (not read)', () => {
        setPostData(123);
        const storage = mockStorage();
        const detector = D.createDetector({
            dom: mockDom({ postId: 123, article: true }),
            storage,
            Observer: null
        });

        detector.init();

        expect(storage.addViewed).toHaveBeenCalledWith(123, expect.objectContaining({ title: expect.any(String), url: expect.any(String) }));
        expect(storage.addRead).not.toHaveBeenCalled();
    });

    test('does not mark as viewed if already viewed', () => {
        setPostData(123);
        const storage = mockStorage({ viewedPosts: [{ id: 123, title: 'Test', url: '/test' }] });
        const detector = D.createDetector({
            dom: mockDom({ postId: 123, article: true }),
            storage,
            Observer: null
        });

        detector.init();

        expect(storage.addViewed).not.toHaveBeenCalled();
    });

    test('does not mark as viewed if already read', () => {
        setPostData(123);
        const storage = mockStorage({ readPosts: [{ id: 123, title: 'Test', url: '/test' }] });
        const detector = D.createDetector({
            dom: mockDom({ postId: 123, article: true }),
            storage,
            Observer: null
        });

        detector.init();

        expect(storage.addViewed).not.toHaveBeenCalled();
    });

    test('skips tracking when CompletionistPostData is missing', () => {
        const storage = mockStorage();
        const detector = D.createDetector({
            dom: mockDom({ postId: 123, article: true }),
            storage,
            Observer: null
        });

        const result = detector.init();

        expect(result.success).toBe(false);
        expect(result.reason).toBe('no-postdata');
        expect(storage.addViewed).not.toHaveBeenCalled();
    });
});

describe('Detector: on scroll to 90%', () => {
    test('promotes viewed post to read when scrolling completes', () => {
        const storage = mockStorage({ viewedPosts: [{ id: 42, title: 'Test', url: '/test' }] });
        const detector = D.createDetector({
            dom: mockDom({ postId: 42, article: true }),
            storage,
            Observer: null
        });

        detector.handleIntersection();

        expect(storage.addRead).toHaveBeenCalledWith(42);
    });

    test('marks as read directly if somehow not viewed first', () => {
        const storage = mockStorage();
        const detector = D.createDetector({
            dom: mockDom({ postId: 42, article: true }),
            storage,
            Observer: null
        });

        detector.handleIntersection();

        expect(storage.addRead).toHaveBeenCalledWith(42);
    });

    test('does not re-mark if already read', () => {
        const storage = mockStorage({ readPosts: [{ id: 42, title: 'Test', url: '/test' }] });
        const detector = D.createDetector({
            dom: mockDom({ postId: 42, article: true }),
            storage,
            Observer: null
        });

        detector.handleIntersection();

        expect(storage.addRead).not.toHaveBeenCalled();
    });

    test('tracks only once per session', () => {
        const storage = mockStorage();
        const detector = D.createDetector({
            dom: mockDom({ postId: 42, article: true }),
            storage,
            Observer: null
        });

        detector.handleIntersection();
        detector.handleIntersection();

        expect(storage.addRead).toHaveBeenCalledTimes(1);
    });
});

describe('detector.init', () => {
    test('fails without article element', () => {
        setPostData(123);
        const detector = D.createDetector({
            dom: mockDom({ postId: 123 }),
            Observer: null
        });
        expect(detector.init().success).toBe(false);
    });

    test('fails without postId', () => {
        const detector = D.createDetector({
            dom: mockDom({ article: true }),
            Observer: null
        });
        expect(detector.init().success).toBe(false);
    });

    test('succeeds with article, postId and CompletionistPostData', () => {
        setPostData(123);
        const storage = mockStorage();
        const detector = D.createDetector({
            dom: mockDom({ postId: 123, article: true }),
            storage,
            Observer: null
        });
        expect(detector.init().success).toBe(true);
    });
});

describe('Detector: consent', () => {
    function mockConsentManager(options) {
        options = options || {};
        return {
            hasConsent: jest.fn(function() { return options.consent; }),
            onConsentChange: jest.fn(),
            isBuiltInProvider: jest.fn(function() { return true; }),
            grantConsent: jest.fn()
        };
    }

    test('does not track when consent is false', () => {
        setPostData(123);
        const storage = mockStorage();
        const consentManager = mockConsentManager({ consent: false });
        const detector = D.createDetector({
            dom: mockDom({ postId: 123, article: true }),
            storage,
            consentManager,
            Observer: null
        });

        detector.init();

        expect(storage.addViewed).not.toHaveBeenCalled();
    });

    test('does not track when consent is null (pending)', () => {
        setPostData(123);
        const storage = mockStorage();
        const consentManager = mockConsentManager({ consent: null });
        const detector = D.createDetector({
            dom: mockDom({ postId: 123, article: true }),
            storage,
            consentManager,
            Observer: null
        });

        detector.init();

        expect(storage.addViewed).not.toHaveBeenCalled();
    });

    test('tracks when consent is true', () => {
        setPostData(123);
        const storage = mockStorage();
        const consentManager = mockConsentManager({ consent: true });
        const detector = D.createDetector({
            dom: mockDom({ postId: 123, article: true }),
            storage,
            consentManager,
            Observer: null
        });

        detector.init();

        expect(storage.addViewed).toHaveBeenCalledWith(123, expect.objectContaining({ title: expect.any(String), url: expect.any(String) }));
    });

    test('tracks normally when no consent manager', () => {
        setPostData(123);
        const storage = mockStorage();
        const detector = D.createDetector({
            dom: mockDom({ postId: 123, article: true }),
            storage,
            Observer: null
        });

        detector.init();

        expect(storage.addViewed).toHaveBeenCalledWith(123, expect.objectContaining({ title: expect.any(String), url: expect.any(String) }));
    });

    test('does not mark read when consent is false', () => {
        const storage = mockStorage({ viewedPosts: [{ id: 42, title: 'Test', url: '/test' }] });
        const consentManager = mockConsentManager({ consent: false });
        const detector = D.createDetector({
            dom: mockDom({ postId: 42, article: true }),
            storage,
            consentManager,
            Observer: null
        });

        detector.handleIntersection();

        expect(storage.addRead).not.toHaveBeenCalled();
    });
});

describe('auto-init', () => {
    test('creates sentinel when article, postId and CompletionistPostData exist', () => {
        const mockArticle = {
            style: {},
            appendChild: jest.fn(),
            children: []
        };

        const originalQuerySelector = document.querySelector;
        const originalCreateElement = document.createElement;
        const originalGetComputedStyle = window.getComputedStyle;

        document.querySelector = jest.fn(selector => {
            if (selector === 'article') return mockArticle;
            if (selector === '[data-completionist-post]') {
                return { dataset: { completionistPost: '123' } };
            }
            if (selector === 'main') return null;
            return null;
        });
        document.createElement = jest.fn(() => ({ style: {} }));
        window.getComputedStyle = jest.fn(() => ({ position: 'static' }));
        window.CompletionistStorage = mockStorage();
        window.CompletionistPostData = {
            id: 123,
            title: 'Test Post',
            url: '/test-post'
        };
        window.IntersectionObserver = jest.fn(() => ({
            observe: jest.fn(),
            disconnect: jest.fn()
        }));

        delete window.CompletionistDetector;
        eval(detectorCode);

        document.querySelector = originalQuerySelector;
        document.createElement = originalCreateElement;
        window.getComputedStyle = originalGetComputedStyle;

        expect(mockArticle.appendChild).toHaveBeenCalled();
    });
});

describe('Detector: read threshold', () => {
    test('uses default threshold of 90 when no config', () => {
        delete window.CompletionistConfig;
        expect(D.getReadThreshold()).toBe(90);
    });

    test('uses threshold from CompletionistConfig', () => {
        window.CompletionistConfig = { readThreshold: 50 };
        expect(D.getReadThreshold()).toBe(50);
    });

    test('uses default when config has no readThreshold', () => {
        window.CompletionistConfig = { maxViewed: 5 };
        expect(D.getReadThreshold()).toBe(90);
    });

    test('createSentinel positions at correct percentage', () => {
        const mockDomObj = {
            createElement: jest.fn(() => ({ style: {} }))
        };
        const mockArticle = {
            style: {},
            appendChild: jest.fn()
        };
        window.getComputedStyle = jest.fn(() => ({ position: 'relative' }));

        const sentinel = D.createSentinel(mockDomObj, mockArticle, 70);

        expect(sentinel.style.cssText).toContain('bottom:30%');
    });
});