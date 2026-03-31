const fs = require('fs');
const path = require('path');

const renderItemCode = fs.readFileSync(
    path.join(__dirname, '../../assets/js/render-item.js'),
    'utf8'
);

describe('renderItem', () => {
    let renderItem;

    beforeEach(() => {
        eval(renderItemCode);
        renderItem = window.LekTrailRenderItem;
    });

    afterEach(() => {
        delete window.LekTrailRenderItem;
    });

    test('renders title and link', () => {
        const post = { title: 'My Post', url: '/my-post/' };
        const html = renderItem(post);

        expect(html).toContain('lektrail-item');
        expect(html).toContain('lektrail-title');
        expect(html).toContain('My Post');
        expect(html).toContain('href="/my-post/"');
    });

    test('renders thumbnail when present', () => {
        const post = { title: 'Post', url: '/', thumbnail: 'https://example.com/image.jpg' };
        const html = renderItem(post);

        expect(html).toContain('lektrail-thumbnail');
        expect(html).toContain('src="https://example.com/image.jpg"');
    });

    test('omits thumbnail div when no image', () => {
        const post = { title: 'Post', url: '/' };
        const html = renderItem(post);

        expect(html).not.toContain('lektrail-thumbnail');
    });

    test('renders excerpt when present', () => {
        const post = { title: 'Post', url: '/', excerpt: 'This is the excerpt.' };
        const html = renderItem(post);

        expect(html).toContain('lektrail-excerpt');
        expect(html).toContain('This is the excerpt.');
    });

    test('omits excerpt when empty', () => {
        const post = { title: 'Post', url: '/' };
        const html = renderItem(post);

        expect(html).not.toContain('lektrail-excerpt');
    });

    test('wraps content in lektrail-content div', () => {
        const post = { title: 'Post', url: '/' };
        const html = renderItem(post);

        expect(html).toContain('lektrail-content');
    });
});