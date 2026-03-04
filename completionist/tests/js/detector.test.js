const fs = require('fs');
const path = require('path');

const detectorCode = fs.readFileSync(
    path.join(__dirname, '../../assets/js/detector.js'),
    'utf8'
);

let D;

beforeEach(() => {
    delete window.CompletionistDetector;
    eval(detectorCode);
    D = window.CompletionistDetector;
});

function mockDom(options = {}) {
    const elements = {};

    if (options.postId !== undefined) {
        elements['[data-completionist-post]'] = {
            dataset: { completionistPost: String(options.postId) }
        };
    }

    if (options.article) {
        elements['article'] = { style: {}, appendChild: jest.fn() };
    }

    return {
        querySelector: selector => elements[selector] || null,
        createElement: () => ({ style: {} }),
        dispatchEvent: jest.fn()
    };
}

function mockStorage(readIds = []) {
    const stored = [...readIds];
    return {
        hasRead: id => stored.includes(id),
        addRead: jest.fn(id => stored.push(id))
    };
}

describe('shouldTrack', () => {
    test('true when post is unread', () => {
        expect(D.shouldTrack(123, mockStorage(), false)).toBe(true);
    });

    test('false when post already read', () => {
        expect(D.shouldTrack(123, mockStorage([123]), false)).toBe(false);
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

describe('detector.init', () => {
    test('fails without article element', () => {
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

    test('succeeds with article and postId', () => {
        const detector = D.createDetector({
            dom: mockDom({ postId: 123, article: true }),
            Observer: null
        });
        expect(detector.init().success).toBe(true);
    });
});

describe('auto-init', () => {
    test('creates sentinel when article and postId exist', () => {
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

describe('detector.handleIntersection', () => {
    test('tracks unread post', () => {
        const storage = mockStorage();
        const detector = D.createDetector({
            dom: mockDom({ postId: 42, article: true }),
            storage,
            Observer: null
        });

        detector.handleIntersection();

        expect(storage.addRead).toHaveBeenCalledWith(42);
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

    test('does not track already read post', () => {
        const storage = mockStorage([42]);
        const detector = D.createDetector({
            dom: mockDom({ postId: 42, article: true }),
            storage,
            Observer: null
        });

        detector.handleIntersection();

        expect(storage.addRead).not.toHaveBeenCalled();
    });
});