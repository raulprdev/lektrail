const fs = require('fs');
const path = require('path');

const widgetCode = fs.readFileSync(
    path.join(__dirname, '../../assets/js/widget.js'),
    'utf8'
);

function mockStorage(readCount = 0, readIds = []) {
    return {
        getReadCount: () => readCount,
        getReadIds: () => readIds
    };
}

function mockXhr() {
    const xhr = {
        open: jest.fn(),
        send: jest.fn(),
        onload: null,
        status: 200,
        responseText: '{"success":true,"data":[]}'
    };
    return xhr;
}

describe('widget immediate rendering', () => {
    let container;
    let originalXhr;

    beforeEach(() => {
        container = {
            innerHTML: '',
            dataset: { count: '5', endpoint: '/api' }
        };
        document.getElementById = jest.fn(id => {
            if (id === 'completionist-widget') return container;
            return null;
        });
        document.readyState = 'complete';
        window.CompletionistStorage = mockStorage(3);

        originalXhr = window.XMLHttpRequest;
        window.XMLHttpRequest = jest.fn(() => mockXhr());
    });

    afterEach(() => {
        window.XMLHttpRequest = originalXhr;
        delete window.CompletionistStorage;
    });

    test('shows loading state immediately before ajax completes', () => {
        eval(widgetCode);

        expect(container.innerHTML).not.toBe('');
        expect(container.innerHTML).toContain('3');
    });
});

describe('widget renders suggestions', () => {
    let container;
    let xhr;

    beforeEach(() => {
        container = {
            innerHTML: '',
            dataset: { count: '5', endpoint: '/api' }
        };
        document.getElementById = jest.fn(id => {
            if (id === 'completionist-widget') return container;
            return null;
        });
        document.readyState = 'complete';
        window.CompletionistStorage = mockStorage(2, [1]);

        xhr = mockXhr();
        window.XMLHttpRequest = jest.fn(() => xhr);
    });

    afterEach(() => {
        delete window.CompletionistStorage;
    });

    test('filters out read posts from suggestions', () => {
        xhr.responseText = JSON.stringify({
            success: true,
            data: [
                { id: 1, title: 'Read Post', url: '/read' },
                { id: 2, title: 'Unread Post', url: '/unread' }
            ]
        });

        eval(widgetCode);
        xhr.onload();

        expect(container.innerHTML).toContain('Unread Post');
        expect(container.innerHTML).not.toContain('Read Post');
    });

    test('displays read count', () => {
        xhr.responseText = JSON.stringify({ success: true, data: [] });

        eval(widgetCode);
        xhr.onload();

        expect(container.innerHTML).toContain('2');
        expect(container.innerHTML).toContain('posts read');
    });
});