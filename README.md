# WidgetEmail — Email validator for FacturaScripts

A free FacturaScripts community plugin that adds a `type="email"` form widget with
real-time format validation and disposable-domain detection.

---

## Features

- **HTML5** `type="email"` input with `maxlength="254"` (RFC 5321 limit)
- **Format validation** — `filter_var(FILTER_VALIDATE_EMAIL)` + extra checks:
  - Rejects IP literal domains (`user@[192.168.1.1]`)
  - Rejects single-label domains (`user@localhost`, `user@intranet`)
  - Rejects quoted local parts (`"user name"@domain.com`)
- **Disposable-domain detection** — warns (⚠) on throwaway providers (mailinator, guerrillamail, etc.)
- **Client-side blur feedback** — ✓ valid · ⚠ disposable · ✗ invalid — vanilla JS, no dependencies
- **Normalization** — trim + lowercase on form submit
- **No blocking on disposable** — the widget warns but does not prevent saving (model decides)

---

## Installation

1. Download this plugin and extract it to `Plugins/WidgetEmail/` inside your FacturaScripts installation.
2. Go to **Admin → Plugins** and install **WidgetEmail**.

---

## Usage in XMLView

```xml
<column name="email" numcolumns="6" title="Email">
    <widget type="email" fieldname="email" />
</column>
```

## Server-side validation in your model

```php
use FacturaScripts\Plugins\WidgetEmail\Lib\EmailValidator;

public function test(): bool
{
    if (!empty($this->email) && !EmailValidator::validate($this->email)) {
        $this->toolBox()->log()->error('invalid-email');
        return false;
    }
    return parent::test();
}
```

To also warn on disposable emails (without blocking):

```php
if (!empty($this->email) && EmailValidator::isDisposable($this->email)) {
    $this->toolBox()->log()->info('disposable-email-warning');
    // continue — warning only, not blocking
}
```

---

## Requirements

- FacturaScripts 2024.1 or higher
- PHP 8.0+

---

## License

GPL-3.0-only — see [LICENSE](LICENSE).

---

## Credits

Built by the **[obeliOmed](https://obeliomed.com)** team — open-source SaaS for medical clinics on FacturaScripts.

Contributions welcome via GitHub issues and pull requests.
