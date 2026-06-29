# 002 — test(WidgetEmail): tests, translations, and docs

**Branch**: `002-test-email-tests-translations-docs`
**PR**: #2
**Sequential to**: PR #1

## Summary

- **WHY**: PR #1 has no tests or docs.
- **WHAT**: PHPUnit suite (EmailValidatorTest + WidgetEmailTest) + 4 translations (es/en/ca/gl) + README + LICENSE.
- **IMPACT**: Standalone `phpunit` without FacturaScripts. Consumer developers have README with copy-paste usage.

## Files

| File | Description |
|---|---|
| `Test/EmailValidatorTest.php` | Tests for validate() (format, disposable, edge cases) + isDisposable() |
| `Test/WidgetEmailTest.php` | Tests for processFormData (lowercase, trim, null on empty) |
| `Translation/{es_ES,en_EN,ca_ES,gl_ES}.json` | 4 locale files |
| `README.md` | Installation + XMLView usage + model::test() pattern |
| `LICENSE` | GPL-3.0-only |

## Test plan

```bash
composer install && ./vendor/bin/phpunit
```

- [ ] Valid formats accepted: `user@example.com`, `user+tag@sub.domain.org`
- [ ] Invalid rejected: `notanemail`, `@domain.com`, `user@`
- [ ] Disposable domains flagged: `mailinator.com`, `guerrillamail.com`
- [ ] processFormData: `User@EXAMPLE.COM` → `user@example.com`, empty → null

## Cross-plugin impact

N/A
