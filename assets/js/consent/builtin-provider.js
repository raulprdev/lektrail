(function (global) {
	'use strict';

	const STORAGE_KEY = 'completionist_consent';
	const callbacks = [];

	function isAvailable() {
		return true;
	}

	function hasConsent() {
		try {
			const value = localStorage.getItem(STORAGE_KEY);
			if (value === 'granted') {
				return true;
			}
			if (value === 'denied') {
				return false;
			}
			return null;
		} catch (e) {
			return null;
		}
	}

	function setConsent(granted) {
		try {
			localStorage.setItem(STORAGE_KEY, granted ? 'granted' : 'denied');
			callbacks.forEach(function (cb) {
				cb(granted);
			});
		} catch (e) {
			// Storage unavailable
		}
	}

	function onConsentChange(callback) {
		callbacks.push(callback);
	}

	function clearConsent() {
		try {
			localStorage.removeItem(STORAGE_KEY);
		} catch (e) {
			// Storage unavailable
		}
	}

	global.CompletionistBuiltInProvider = {
		isAvailable,
		hasConsent,
		setConsent,
		onConsentChange,
		clearConsent,
	};
})(typeof window !== 'undefined' ? window : this);
