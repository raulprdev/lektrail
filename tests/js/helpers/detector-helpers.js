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

module.exports = {
    mockDom
};