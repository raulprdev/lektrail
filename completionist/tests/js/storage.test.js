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

describe('CompletionistStorage', () => {
    test('empty storage has no reads', () => {
        expect(CompletionistStorage.getReadCount()).toBe(0);
        expect(CompletionistStorage.getReadIds()).toEqual([]);
    });

    test('addRead stores postId', () => {
        CompletionistStorage.addRead(123);

        expect(CompletionistStorage.hasRead(123)).toBe(true);
        expect(CompletionistStorage.getReadCount()).toBe(1);
    });

    test('addRead ignores duplicates', () => {
        CompletionistStorage.addRead(123);
        CompletionistStorage.addRead(123);

        expect(CompletionistStorage.getReadCount()).toBe(1);
    });

    test('hasRead returns false for unread', () => {
        expect(CompletionistStorage.hasRead(999)).toBe(false);
    });

    test('getReadIds returns all stored ids', () => {
        CompletionistStorage.addRead(1);
        CompletionistStorage.addRead(2);
        CompletionistStorage.addRead(3);

        expect(CompletionistStorage.getReadIds()).toEqual([1, 2, 3]);
    });

    test('clear removes all reads', () => {
        CompletionistStorage.addRead(1);
        CompletionistStorage.addRead(2);
        CompletionistStorage.clear();

        expect(CompletionistStorage.getReadCount()).toBe(0);
    });
});