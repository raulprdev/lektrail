function mockStorage(options = {}) {
    const viewedIds = options.viewedIds ? [...options.viewedIds] : [];
    const readIds = options.readIds ? [...options.readIds] : [];
    let suggestions = [];
    let suggestionsUpdatedAt = null;

    return {
        getViewedCount: () => viewedIds.length,
        getReadCount: () => readIds.length,
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
        getViewedPosts: () => viewedIds.map(id => ({ id, needsFetch: true })),
        getReadPosts: () => readIds.map(id => ({ id, needsFetch: true })),
        getSuggestions: () => suggestions,
        setSuggestions: jest.fn(posts => {
            suggestions = posts;
            suggestionsUpdatedAt = new Date().toISOString();
        }),
        isSuggestionsCacheValid: () => false,
        clearSuggestionsCache: jest.fn()
    };
}

module.exports = {
    mockStorage
};