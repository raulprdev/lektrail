const fs = require('fs');
const path = require('path');

const storageCode = fs.readFileSync(
    path.join(__dirname, '../../assets/js/storage.js'),
    'utf8'
);

beforeEach(() => {
    localStorage.clear();
    const script = new Function(storageCode);
    script();
});

describe('Storage: empty state', () => {
    test('new storage has 0 viewed and 0 read', () => {
        expect(CompletionistStorage.getViewedCount()).toBe(0);
        expect(CompletionistStorage.getReadCount()).toBe(0);
        expect(CompletionistStorage.getViewedIds()).toEqual([]);
        expect(CompletionistStorage.getReadIds()).toEqual([]);
    });
});

describe('Storage: add viewed', () => {
    test('adding a viewed post increments viewed count', () => {
        CompletionistStorage.addViewed(123);

        expect(CompletionistStorage.getViewedCount()).toBe(1);
    });

    test('adding same post as viewed twice does not duplicate', () => {
        CompletionistStorage.addViewed(123);
        CompletionistStorage.addViewed(123);

        expect(CompletionistStorage.getViewedCount()).toBe(1);
    });

    test('getViewedIds returns the viewed post id', () => {
        CompletionistStorage.addViewed(123);

        expect(CompletionistStorage.getViewedIds()).toContain(123);
    });
});

describe('Storage: add read', () => {
    test('adding a read post increments read count', () => {
        CompletionistStorage.addRead(123);

        expect(CompletionistStorage.getReadCount()).toBe(1);
    });

    test('adding a read post removes it from viewed (promotion)', () => {
        CompletionistStorage.addViewed(123);
        expect(CompletionistStorage.getViewedCount()).toBe(1);

        CompletionistStorage.addRead(123);

        expect(CompletionistStorage.getViewedCount()).toBe(0);
        expect(CompletionistStorage.getReadCount()).toBe(1);
    });

    test('if post was not viewed, adding as read still works', () => {
        CompletionistStorage.addRead(123);

        expect(CompletionistStorage.getReadCount()).toBe(1);
        expect(CompletionistStorage.hasRead(123)).toBe(true);
    });

    test('getReadIds returns the read post id', () => {
        CompletionistStorage.addRead(123);

        expect(CompletionistStorage.getReadIds()).toContain(123);
    });

    test('addRead ignores duplicates', () => {
        CompletionistStorage.addRead(123);
        CompletionistStorage.addRead(123);

        expect(CompletionistStorage.getReadCount()).toBe(1);
    });
});

describe('Storage: state queries', () => {
    test('hasViewed returns true for viewed post', () => {
        CompletionistStorage.addViewed(123);

        expect(CompletionistStorage.hasViewed(123)).toBe(true);
    });

    test('hasViewed returns false for read post (promoted out)', () => {
        CompletionistStorage.addViewed(123);
        CompletionistStorage.addRead(123);

        expect(CompletionistStorage.hasViewed(123)).toBe(false);
    });

    test('hasViewed returns false for unknown post', () => {
        expect(CompletionistStorage.hasViewed(999)).toBe(false);
    });

    test('hasRead returns true for read post', () => {
        CompletionistStorage.addRead(123);

        expect(CompletionistStorage.hasRead(123)).toBe(true);
    });

    test('hasRead returns false for viewed post', () => {
        CompletionistStorage.addViewed(123);

        expect(CompletionistStorage.hasRead(123)).toBe(false);
    });

    test('hasRead returns false for unknown post', () => {
        expect(CompletionistStorage.hasRead(999)).toBe(false);
    });

    test('isTracked returns true for viewed post', () => {
        CompletionistStorage.addViewed(123);

        expect(CompletionistStorage.isTracked(123)).toBe(true);
    });

    test('isTracked returns true for read post', () => {
        CompletionistStorage.addRead(123);

        expect(CompletionistStorage.isTracked(123)).toBe(true);
    });

    test('isTracked returns false for unknown post', () => {
        expect(CompletionistStorage.isTracked(999)).toBe(false);
    });
});

describe('Storage: counts', () => {
    test('getViewedCount returns count of viewed posts', () => {
        CompletionistStorage.addViewed(1);
        CompletionistStorage.addViewed(2);
        CompletionistStorage.addViewed(3);

        expect(CompletionistStorage.getViewedCount()).toBe(3);
    });

    test('getReadCount returns count of read posts', () => {
        CompletionistStorage.addRead(1);
        CompletionistStorage.addRead(2);

        expect(CompletionistStorage.getReadCount()).toBe(2);
    });
});

describe('Storage: clear', () => {
    test('clear removes all viewed and read', () => {
        CompletionistStorage.addViewed(1);
        CompletionistStorage.addRead(2);
        CompletionistStorage.clear();

        expect(CompletionistStorage.getViewedCount()).toBe(0);
        expect(CompletionistStorage.getReadCount()).toBe(0);
    });
});