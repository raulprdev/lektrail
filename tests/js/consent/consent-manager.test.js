const fs = require('fs');
const path = require('path');

const builtinCode = fs.readFileSync(
    path.join(__dirname, '../../../assets/js/consent/builtin-provider.js'),
    'utf8'
);

const managerCode = fs.readFileSync(
    path.join(__dirname, '../../../assets/js/consent/consent-manager.js'),
    'utf8'
);

function mockProvider(options) {
    options = options || {};
    return {
        isAvailable: jest.fn(function() { return options.available !== false; }),
        hasConsent: jest.fn(function() { return options.consent; }),
        onConsentChange: jest.fn(),
        setConsent: jest.fn()
    };
}

beforeEach(() => {
    localStorage.clear();
    delete global.LekTrailBuiltInProvider;
    delete global.LekTrailConsentManager;
    delete global.LekTrailCookieYesProvider;
    delete global.LekTrailComplianzProvider;

    const script1 = new Function(builtinCode);
    script1();
    const script2 = new Function(managerCode);
    script2();
});

describe('ConsentManager: provider detection', () => {
    test('uses first available provider', () => {
        var provider1 = mockProvider({ available: true, consent: true });
        var provider2 = mockProvider({ available: true, consent: false });

        var manager = LekTrailConsentManager.create({
            providers: [provider1, provider2]
        });

        expect(manager.hasConsent()).toBe(true);
        expect(provider1.hasConsent).toHaveBeenCalled();
        expect(provider2.hasConsent).not.toHaveBeenCalled();
    });

    test('skips unavailable providers', () => {
        var provider1 = mockProvider({ available: false });
        var provider2 = mockProvider({ available: true, consent: true });

        var manager = LekTrailConsentManager.create({
            providers: [provider1, provider2]
        });

        expect(manager.hasConsent()).toBe(true);
        expect(provider2.hasConsent).toHaveBeenCalled();
    });

    test('falls back to BuiltInProvider when no providers given', () => {
        var manager = LekTrailConsentManager.create();

        expect(manager.hasConsent()).toBe(null);
        expect(manager.isBuiltInProvider()).toBe(true);
    });
});

describe('ConsentManager: hasConsent', () => {
    test('delegates to active provider', () => {
        var provider = mockProvider({ consent: true });

        var manager = LekTrailConsentManager.create({
            providers: [provider]
        });

        expect(manager.hasConsent()).toBe(true);
    });

    test('returns null when provider returns null', () => {
        var provider = mockProvider({ consent: null });

        var manager = LekTrailConsentManager.create({
            providers: [provider]
        });

        expect(manager.hasConsent()).toBe(null);
    });

    test('returns false when provider returns false', () => {
        var provider = mockProvider({ consent: false });

        var manager = LekTrailConsentManager.create({
            providers: [provider]
        });

        expect(manager.hasConsent()).toBe(false);
    });
});

describe('ConsentManager: isBuiltInProvider', () => {
    test('returns true when using BuiltInProvider', () => {
        var manager = LekTrailConsentManager.create({
            providers: [LekTrailBuiltInProvider]
        });

        expect(manager.isBuiltInProvider()).toBe(true);
    });

    test('returns false when using external provider', () => {
        var externalProvider = mockProvider({ consent: true });

        var manager = LekTrailConsentManager.create({
            providers: [externalProvider]
        });

        expect(manager.isBuiltInProvider()).toBe(false);
    });
});

describe('ConsentManager: grantConsent', () => {
    test('calls setConsent on BuiltInProvider', () => {
        var manager = LekTrailConsentManager.create({
            providers: [LekTrailBuiltInProvider]
        });

        manager.grantConsent();

        expect(LekTrailBuiltInProvider.hasConsent()).toBe(true);
    });

    test('does nothing when using external provider', () => {
        var externalProvider = mockProvider({ consent: null });

        var manager = LekTrailConsentManager.create({
            providers: [externalProvider]
        });

        manager.grantConsent();

        expect(externalProvider.setConsent).not.toHaveBeenCalled();
    });
});

describe('ConsentManager: onConsentChange', () => {
    test('registers callback with active provider', () => {
        var provider = mockProvider({ consent: null });

        var manager = LekTrailConsentManager.create({
            providers: [provider]
        });

        var callback = jest.fn();
        manager.onConsentChange(callback);

        expect(provider.onConsentChange).toHaveBeenCalled();
    });

    test('callback is triggered when consent changes', () => {
        var manager = LekTrailConsentManager.create({
            providers: [LekTrailBuiltInProvider]
        });

        var callback = jest.fn();
        manager.onConsentChange(callback);

        LekTrailBuiltInProvider.setConsent(true);

        expect(callback).toHaveBeenCalledWith(true);
    });
});