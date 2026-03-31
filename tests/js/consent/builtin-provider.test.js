const fs = require('fs');
const path = require('path');

const builtinCode = fs.readFileSync(
    path.join(__dirname, '../../../assets/js/consent/builtin-provider.js'),
    'utf8'
);

beforeEach(() => {
    localStorage.clear();
    delete global.LekTrailBuiltInProvider;
    const script = new Function(builtinCode);
    script();
});

describe('BuiltInProvider: isAvailable', () => {
    test('returns true always', () => {
        expect(LekTrailBuiltInProvider.isAvailable()).toBe(true);
    });
});

describe('BuiltInProvider: hasConsent', () => {
    test('returns null when no consent stored', () => {
        expect(LekTrailBuiltInProvider.hasConsent()).toBe(null);
    });

    test('returns true when consent is granted', () => {
        localStorage.setItem('lektrail_consent', 'granted');

        const script = new Function(builtinCode);
        script();

        expect(LekTrailBuiltInProvider.hasConsent()).toBe(true);
    });

    test('returns false when consent is denied', () => {
        localStorage.setItem('lektrail_consent', 'denied');

        const script = new Function(builtinCode);
        script();

        expect(LekTrailBuiltInProvider.hasConsent()).toBe(false);
    });
});

describe('BuiltInProvider: setConsent', () => {
    test('setConsent(true) stores granted', () => {
        LekTrailBuiltInProvider.setConsent(true);

        expect(localStorage.getItem('lektrail_consent')).toBe('granted');
    });

    test('setConsent(false) stores denied', () => {
        LekTrailBuiltInProvider.setConsent(false);

        expect(localStorage.getItem('lektrail_consent')).toBe('denied');
    });

    test('setConsent triggers registered callbacks', () => {
        const callback = jest.fn();
        LekTrailBuiltInProvider.onConsentChange(callback);

        LekTrailBuiltInProvider.setConsent(true);

        expect(callback).toHaveBeenCalledWith(true);
    });
});

describe('BuiltInProvider: onConsentChange', () => {
    test('registers multiple callbacks', () => {
        const cb1 = jest.fn();
        const cb2 = jest.fn();
        LekTrailBuiltInProvider.onConsentChange(cb1);
        LekTrailBuiltInProvider.onConsentChange(cb2);

        LekTrailBuiltInProvider.setConsent(true);

        expect(cb1).toHaveBeenCalledWith(true);
        expect(cb2).toHaveBeenCalledWith(true);
    });
});

describe('BuiltInProvider: clearConsent', () => {
    test('removes consent from localStorage', () => {
        LekTrailBuiltInProvider.setConsent(true);
        expect(localStorage.getItem('lektrail_consent')).toBe('granted');

        LekTrailBuiltInProvider.clearConsent();

        expect(localStorage.getItem('lektrail_consent')).toBe(null);
    });
});