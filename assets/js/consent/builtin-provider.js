(function(global) {
    'use strict';

    var STORAGE_KEY = 'completionist_consent';
    var callbacks = [];

    function isAvailable() {
        return true;
    }

    function hasConsent() {
        try {
            var value = localStorage.getItem(STORAGE_KEY);
            if (value === 'granted') return true;
            if (value === 'denied') return false;
            return null;
        } catch (e) {
            return null;
        }
    }

    function setConsent(granted) {
        try {
            localStorage.setItem(STORAGE_KEY, granted ? 'granted' : 'denied');
            callbacks.forEach(function(cb) { cb(granted); });
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
        isAvailable: isAvailable,
        hasConsent: hasConsent,
        setConsent: setConsent,
        onConsentChange: onConsentChange,
        clearConsent: clearConsent
    };

})(typeof window !== 'undefined' ? window : this);