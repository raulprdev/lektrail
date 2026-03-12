function mockStorage(options = {}) {
    const viewedPosts = options.viewedPosts || [];
    const readPosts = options.readPosts || [];
    const viewedIds = viewedPosts.map(p => p.id);
    const readIds = readPosts.map(p => p.id);
    let suggestions = [];
    let suggestionsUpdatedAt = null;

    return {
        getViewedCount: () => viewedPosts.length,
        getReadCount: () => readPosts.length,
        getViewedIds: () => viewedIds,
        getReadIds: () => readIds,
        hasViewed: id => viewedIds.includes(id),
        hasRead: id => readIds.includes(id),
        isTracked: id => viewedIds.includes(id) || readIds.includes(id),
        addViewed: jest.fn(id => {
            if (!viewedIds.includes(id)) viewedIds.push(id);
        }),
        addRead: jest.fn(id => {
            const idx = viewedIds.indexOf(id);
            if (idx !== -1) viewedIds.splice(idx, 1);
            if (!readIds.includes(id)) readIds.push(id);
        }),
        getViewedPosts: () => viewedPosts,
        getReadPosts: () => readPosts,
        getSuggestions: () => suggestions,
        setSuggestions: jest.fn(posts => {
            suggestions = posts;
            suggestionsUpdatedAt = new Date().toISOString();
        }),
        isSuggestionsCacheValid: () => false,
        clearSuggestionsCache: jest.fn(),
        clear: jest.fn(),
        clearHistory: jest.fn()
    };
}

module.exports = {
    mockStorage
};