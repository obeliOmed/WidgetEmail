# 001 — feat(WidgetEmail): core implementation

**Branch**: `001-feat-email-core-implementation`
**PR**: #1

## Summary

- **WHY**: FacturaScripts forms accept any string in email fields — no normalization (case), no disposable domain rejection, no format feedback for the user.
- **WHAT**: `WidgetEmail` (BaseWidget subclass) + `EmailValidator` (format + disposable domain check) + `DisposableEmailDomains` (blocklist) + `email-widget.js` (blur feedback ✓/⚠/✗). Usage: `<widget type="email" fieldname="email" />`.
- **IMPACT**: Any plugin XMLView gets trimmed/lowercased storage, client-side blur feedback (valid/disposable/invalid), and a server-side validator ready for model `test()`.

## Files

| File | Description |
|---|---|
| `Lib/Widget/WidgetEmail.php` | BaseWidget subclass — type="email", trim+lowercase processFormData |
| `Lib/EmailValidator.php` | validate() checks format + disposable domain; isDisposable() separate check |
| `Lib/DisposableEmailDomains.php` | Static blocklist of known disposable domain providers |
| `Assets/JS/email-widget.js` | Vanilla JS blur feedback (✓ valid / ⚠ disposable / ✗ invalid) |
| `Init.php` | Minimal lifecycle (no tables) |
| `facturascripts.ini` | version=260629.1, min_version=2024.1 |
| `composer.json` | No external dependencies (pure PHP regex + static list) |

## Test plan

- [ ] Install plugin → add `<widget type="email" fieldname="email" />` to XMLView
- [ ] Input `User@EXAMPLE.COM` → saved as `user@example.com` (trim + lowercase)
- [ ] Input `test@mailinator.com` → blur → ⚠ orange badge (disposable domain)
- [ ] Input `notanemail` → blur → ✗ red badge
- [ ] Input `valid@example.com` → blur → ✓ green badge
- [ ] Empty → null stored

## Cross-plugin impact

N/A — standalone utility plugin.
