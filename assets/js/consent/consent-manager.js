(function(global) {
    'use strict';

    function createConsentManager(deps) {
        deps = deps || {};

        var providers = deps.providers || [global.CompletionistBuiltInProvider];
        var activeProvider = null;

        function detectProvider() {
            for (var i = 0; i < providers.length; i++) {
                if (providers[i] && providers[i].isAvailable()) {
                    return providers[i];
                }
            }
            return null;
        }

        function getActiveProvider() {
            if (!activeProvider) {
                activeProvider = detectProvider();
            }
            return activeProvider;
        }

        function hasConsent() {
            var provider = getActiveProvider();
            return provider ? provider.hasConsent() : null;
        }

        function isBuiltInProvider() {
            var provider = getActiveProvider();
            return provider === global.CompletionistBuiltInProvider;
        }

        function onConsentChange(callback) {
            var provider = getActiveProvider();
            if (provider && provider.onConsentChange) {
                provider.onConsentChange(callback);
            }
        }

        function grantConsent() {
            if (isBuiltInProvider() && global.CompletionistBuiltInProvider.setConsent) {
                global.CompletionistBuiltInProvider.setConsent(true);
            }
        }

        return {
            hasConsent: hasConsent,
            isBuiltInProvider: isBuiltInProvider,
            onConsentChange: onConsentChange,
            grantConsent: grantConsent,
            getActiveProvider: getActiveProvider
        };
    }

    global.CompletionistConsentManager = {
        create: createConsentManager
    };

})(typeof window !== 'undefined' ? window : this);