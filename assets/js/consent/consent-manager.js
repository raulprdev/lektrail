(function (global) {
	'use strict';

	function createConsentManager(deps) {
		deps = deps || {};

		const providers = deps.providers || [global.LekTrailBuiltInProvider];
		let activeProvider = null;

		function detectProvider() {
			for (let i = 0; i < providers.length; i++) {
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
			const provider = getActiveProvider();
			return provider ? provider.hasConsent() : null;
		}

		function isBuiltInProvider() {
			const provider = getActiveProvider();
			return provider === global.LekTrailBuiltInProvider;
		}

		function onConsentChange(callback) {
			const provider = getActiveProvider();
			if (provider && provider.onConsentChange) {
				provider.onConsentChange(callback);
			}
		}

		function grantConsent() {
			if (
				isBuiltInProvider() &&
				global.LekTrailBuiltInProvider.setConsent
			) {
				global.LekTrailBuiltInProvider.setConsent(true);
			}
		}

		return {
			hasConsent,
			isBuiltInProvider,
			onConsentChange,
			grantConsent,
			getActiveProvider,
		};
	}

	global.LekTrailConsentManager = {
		create: createConsentManager,
	};
})(typeof window !== 'undefined' ? window : this);
