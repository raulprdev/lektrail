(function () {
	'use strict';

	const STORAGE_KEY = 'lektrail';

	function getData() {
		try {
			const data = JSON.parse(localStorage.getItem(STORAGE_KEY));
			if (!data) {
				return {
					viewed: [],
					read: [],
					suggestions: [],
					suggestionsUpdatedAt: null,
				};
			}

			if (data.reads && !data.read) {
				return {
					viewed: [],
					read: data.reads,
					suggestions: [],
					suggestionsUpdatedAt: null,
				};
			}

			return {
				viewed: data.viewed || [],
				read: data.read || [],
				suggestions: data.suggestions || [],
				suggestionsUpdatedAt: data.suggestionsUpdatedAt || null,
			};
		} catch (e) {
			return {
				viewed: [],
				read: [],
				suggestions: [],
				suggestionsUpdatedAt: null,
			};
		}
	}

	function setData(data) {
		try {
			localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
		} catch (e) {
			// Storage full or unavailable
		}
	}

	function findIndexById(array, postId) {
		for (let i = 0; i < array.length; i++) {
			if (array[i].postId === postId) {
				return i;
			}
		}
		return -1;
	}

	function isInSuggestions(postId) {
		const data = getData();
		for (let i = 0; i < data.suggestions.length; i++) {
			if (data.suggestions[i].id === postId) {
				return true;
			}
		}
		return false;
	}

	function clearSuggestionsIfNeeded(postId) {
		if (isInSuggestions(postId)) {
			const data = getData();
			data.suggestions = [];
			data.suggestionsUpdatedAt = null;
			setData(data);
		}
	}

	window.LekTrailStorage = {
		getViewedIds() {
			return getData().viewed.map(function (v) {
				return v.postId;
			});
		},

		getReadIds() {
			return getData().read.map(function (r) {
				return r.postId;
			});
		},

		addViewed(postId, postData) {
			const data = getData();

			if (findIndexById(data.viewed, postId) !== -1) {
				return;
			}
			if (findIndexById(data.read, postId) !== -1) {
				return;
			}

			const entry = { postId, viewedAt: new Date().toISOString() };
			if (postData && postData.title) {
				entry.title = postData.title;
			}
			if (postData && postData.url) {
				entry.url = postData.url;
			}
			if (postData && postData.excerpt) {
				entry.excerpt = postData.excerpt;
			}
			if (postData && postData.thumbnail) {
				entry.thumbnail = postData.thumbnail;
			}

			data.viewed.push(entry);
			setData(data);
			clearSuggestionsIfNeeded(postId);
		},

		addRead(postId, postData) {
			const data = getData();

			if (findIndexById(data.read, postId) !== -1) {
				return;
			}

			const viewedIndex = findIndexById(data.viewed, postId);
			const viewedEntry =
				viewedIndex !== -1 ? data.viewed[viewedIndex] : null;
			if (viewedIndex !== -1) {
				data.viewed.splice(viewedIndex, 1);
			}

			const entry = { postId, readAt: new Date().toISOString() };
			if (postData && postData.title) {
				entry.title = postData.title;
			} else if (viewedEntry && viewedEntry.title) {
				entry.title = viewedEntry.title;
			}
			if (postData && postData.url) {
				entry.url = postData.url;
			} else if (viewedEntry && viewedEntry.url) {
				entry.url = viewedEntry.url;
			}
			if (postData && postData.excerpt) {
				entry.excerpt = postData.excerpt;
			} else if (viewedEntry && viewedEntry.excerpt) {
				entry.excerpt = viewedEntry.excerpt;
			}
			if (postData && postData.thumbnail) {
				entry.thumbnail = postData.thumbnail;
			} else if (viewedEntry && viewedEntry.thumbnail) {
				entry.thumbnail = viewedEntry.thumbnail;
			}

			data.read.push(entry);
			setData(data);
			clearSuggestionsIfNeeded(postId);
		},

		hasViewed(postId) {
			return findIndexById(getData().viewed, postId) !== -1;
		},

		hasRead(postId) {
			return findIndexById(getData().read, postId) !== -1;
		},

		isTracked(postId) {
			const data = getData();
			return (
				findIndexById(data.viewed, postId) !== -1 ||
				findIndexById(data.read, postId) !== -1
			);
		},

		getViewedCount() {
			return getData().viewed.length;
		},

		getReadCount() {
			return getData().read.length;
		},

		clear() {
			localStorage.removeItem(STORAGE_KEY);
		},

		clearHistory() {
			const data = getData();
			data.viewed = [];
			data.read = [];
			setData(data);
		},

		getViewedPosts() {
			return getData()
				.viewed.map(function (entry) {
					const post = { id: entry.postId };
					if (entry.title) {
						post.title = entry.title;
					}
					if (entry.url) {
						post.url = entry.url;
					}
					if (entry.excerpt) {
						post.excerpt = entry.excerpt;
					}
					if (entry.thumbnail) {
						post.thumbnail = entry.thumbnail;
					}
					return post;
				})
				.reverse();
		},

		getReadPosts() {
			return getData()
				.read.map(function (entry) {
					const post = { id: entry.postId };
					if (entry.title) {
						post.title = entry.title;
					}
					if (entry.url) {
						post.url = entry.url;
					}
					if (entry.excerpt) {
						post.excerpt = entry.excerpt;
					}
					if (entry.thumbnail) {
						post.thumbnail = entry.thumbnail;
					}
					return post;
				})
				.reverse();
		},

		setSuggestions(posts) {
			const data = getData();
			data.suggestions = posts;
			data.suggestionsUpdatedAt = new Date().toISOString();
			setData(data);
		},

		getSuggestions() {
			return getData().suggestions;
		},

		isSuggestionsCacheValid(maxHours) {
			const data = getData();
			if (!data.suggestionsUpdatedAt || data.suggestions.length === 0) {
				return false;
			}
			const updatedAt = new Date(data.suggestionsUpdatedAt).getTime();
			const now = Date.now();
			const maxMs = maxHours * 60 * 60 * 1000;
			return now - updatedAt < maxMs;
		},

		clearSuggestionsCache() {
			const data = getData();
			data.suggestions = [];
			data.suggestionsUpdatedAt = null;
			setData(data);
		},
	};
})();
