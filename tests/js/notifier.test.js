const fs = require('fs');
const path = require('path');

const notifierCode = fs.readFileSync(
    path.join(__dirname, '../../assets/js/notifier.js'),
    'utf8'
);

beforeEach(() => {
    eval(notifierCode);
});

afterEach(() => {
    delete window.CompletionistNotifier;
});

describe('Notifier: localStorage mode', () => {
    test('trackViewed calls storage.addViewed', () => {
        const addViewed = jest.fn();
        const storage = { addViewed, addRead: jest.fn() };
        const notifier = window.CompletionistNotifier.create({ storage });
        const postData = { id: 123, title: 'Test' };

        notifier.trackViewed(123, postData);

        expect(addViewed).toHaveBeenCalledWith(123, postData);
    });

    test('trackRead calls storage.addRead', () => {
        const addRead = jest.fn();
        const storage = { addViewed: jest.fn(), addRead };
        const notifier = window.CompletionistNotifier.create({ storage });

        notifier.trackRead(123);

        expect(addRead).toHaveBeenCalledWith(123);
    });
});

describe('Notifier: server-side mode', () => {
    let xhrInstances;

    beforeEach(() => {
        xhrInstances = [];
        window.XMLHttpRequest = jest.fn(() => {
            const xhr = {
                open: jest.fn(),
                send: jest.fn(),
                setRequestHeader: jest.fn(),
                onload: null,
                status: 200,
                responseText: '{"success":true}'
            };
            xhrInstances.push(xhr);
            return xhr;
        });
    });

    afterEach(() => {
        delete window.XMLHttpRequest;
    });

    test('trackRead sends POST to endpoint', () => {
        const notifier = window.CompletionistNotifier.create({
            endpoint: '/wp-admin/admin-ajax.php?action=track'
        });

        notifier.trackRead(456);

        expect(xhrInstances.length).toBe(1);
        expect(xhrInstances[0].open).toHaveBeenCalledWith('POST', '/wp-admin/admin-ajax.php?action=track');
        expect(xhrInstances[0].send).toHaveBeenCalledWith('post_id=456');
    });

    test('trackRead sets content-type header', () => {
        const notifier = window.CompletionistNotifier.create({
            endpoint: '/wp-admin/admin-ajax.php?action=track'
        });

        notifier.trackRead(789);

        expect(xhrInstances[0].setRequestHeader).toHaveBeenCalledWith(
            'Content-Type',
            'application/x-www-form-urlencoded'
        );
    });

    test('trackViewed does nothing in server-side mode', () => {
        const notifier = window.CompletionistNotifier.create({
            endpoint: '/wp-admin/admin-ajax.php?action=track'
        });

        notifier.trackViewed(123, { id: 123, title: 'Test' });

        expect(xhrInstances.length).toBe(0);
    });
});