# Review — PR #1 feat(WidgetEmail): core implementation

**Reviewer**: Javi
**Branch**: `001-feat-email-core-implementation`
**Base**: `develop`

## Pre-merge checklist

- [ ] `facturascripts.ini` version = `260629.1`
- [ ] No FacturaScripts core files modified
- [ ] No external dependencies (pure PHP)

## Smoke test

1. Copy to `Plugins/WidgetEmail/` → enable plugin
2. Add `<widget type="email" fieldname="email" />` to any XMLView
3. Enter `User@EXAMPLE.COM` → save → DB shows `user@example.com`
4. Enter `test@mailinator.com` → blur → ⚠ orange disposable badge
5. Enter `notanemail` → blur → ✗ red badge
6. Enter `valid@example.com` → blur → ✓ green badge
7. Clear → save → DB shows NULL

## Expected result

✓ Lowercase + trimmed storage  
✓ 3-state blur feedback (valid / disposable / invalid)  
✓ Empty → NULL  
✓ No JS errors

## PASS / FAIL

☐ PASS &nbsp;&nbsp; ☐ FAIL

**Notes**:
