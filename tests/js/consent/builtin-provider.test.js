const fs = require('fs');
const path = require('path');

const builtinCode = fs.readFileSync(
    path.join(__dirname, '../../../assets/js/consent/builtin-provider.js'),
    'utf8'
);

beforeEach(() => {
    localStorage.clear();
    delete global.CompletionistBuiltInProvider;
    const script = new Function(builtinCode);
    script();
});

describe('BuiltInProvider: isAvailable', () => {
    test('returns true always', () => {
        expect(CompletionistBuiltInProvider.isAvailable()).toBe(true);
    });
});

describe('BuiltInProvider: hasConsent', () => {
    test('returns null when no consent stored', () => {
        expect(CompletionistBuiltInProvider.hasConsent()).toBe(null);
    });

    test('returns true when consent is granted', () => {
        localStorage.setItem('completionist_consent', 'granted');

        const script = new Function(builtinCode);
        script();

        expect(CompletionistBuiltInProvider.hasConsent()).toBe(true);
    });

    test('returns false when consent is denied', () => {
        localStorage.setItem('completionist_consent', 'denied');

        const script = new Function(builtinCode);
        script();

        expect(CompletionistBuiltInProvider.hasConsent()).toBe(false);
    });
});

describe('BuiltInProvider: setConsent', () => {
    test('setConsent(true) stores granted', () => {
        CompletionistBuiltInProvider.setConsent(true);

        expect(localStorage.getItem('completionist_consent')).toBe('granted');
    });

    test('setConsent(false) stores denied', () => {
        CompletionistBuiltInProvider.setConsent(false);

        expect(localStorage.getItem('completionist_consent')).toBe('denied');
    });

    test('setConsent triggers registered callbacks', () => {
        const callback = jest.fn();
        CompletionistBuiltInProvider.onConsentChange(callback);

        CompletionistBuiltInProvider.setConsent(true);

        expect(callback).toHaveBeenCalledWith(true);
    });
});

describe('BuiltInProvider: onConsentChange', () => {
    test('registers multiple callbacks', () => {
        const cb1 = jest.fn();
        const cb2 = jest.fn();
        CompletionistBuiltInProvider.onConsentChange(cb1);
        CompletionistBuiltInProvider.onConsentChange(cb2);

        CompletionistBuiltInProvider.setConsent(true);

        expect(cb1).toHaveBeenCalledWith(true);
        expect(cb2).toHaveBeenCalledWith(true);
    });
});

describe('BuiltInProvider: clearConsent', () => {
    test('removes consent from localStorage', () => {
        CompletionistBuiltInProvider.setConsent(true);
        expect(localStorage.getItem('completionist_consent')).toBe('granted');

        CompletionistBuiltInProvider.clearConsent();

        expect(localStorage.getItem('completionist_consent')).toBe(null);
    });
});