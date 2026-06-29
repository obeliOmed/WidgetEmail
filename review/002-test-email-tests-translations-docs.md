# Review — PR #2 test(WidgetEmail): tests, translations, and docs

**Reviewer**: Javi
**Branch**: `002-test-email-tests-translations-docs`
**Base**: `001-feat-email-core-implementation`

## Pre-merge checklist

- [ ] PR #1 already merged
- [ ] 4 translations present (es_ES, en_EN, ca_ES, gl_ES)
- [ ] README.md and LICENSE present

## Test run

```bash
cd Plugins/WidgetEmail && composer install && ./vendor/bin/phpunit
```

Expected: all tests GREEN.

## Expected coverage

- [ ] Valid email accepted, invalid rejected
- [ ] Disposable domain detected (mailinator.com)
- [ ] processFormData lowercases + trims + null on empty

## PASS / FAIL

☐ PASS &nbsp;&nbsp; ☐ FAIL

**Notes**:
