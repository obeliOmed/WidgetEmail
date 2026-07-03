# WidgetEmail — Documentación técnica

## Arquitectura

```
Lib/Widget/WidgetEmail.php        extends BaseWidget — type="email"
Lib/EmailValidator.php            validate() + isDisposable() + normalize() — sin deps FS
Lib/DisposableEmailDomains.php    const array ~100 dominios desechables conocidos
Assets/JS/email-widget.js         espejo cliente: regex + misma lista de dominios
```

## Flujo de datos

1. **Cliente (JS)**: valida en `blur`, pinta ✓/⚠/✗. La lista `DISPOSABLE_DOMAINS` en JS es una copia manual de `DisposableEmailDomains::DOMAINS` (PHP) — **si se actualiza una, hay que actualizar la otra**.
2. **Servidor (PHP)**: `WidgetEmail::processFormData()` normaliza (`strtolower(trim())`) y guarda tal cual, vacío → `null`. No bloquea guardado por dominio desechable (solo por formato, y solo si el modelo consumidor llama a `EmailValidator::validate()` en su `test()`).

## `EmailValidator::validate()`

```php
1. normalize()                          // trim + strtolower
2. longitud total ≤ 254                 // RFC 5321
3. filter_var(FILTER_VALIDATE_EMAIL)     // base RFC 5322 vía PHP nativo
4. rechazo extra:
   - dominio empieza por '['            // literal IP, ej. user@[192.168.1.1]
   - dominio sin punto                  // single-label, ej. user@localhost
   - parte local empieza por '"'        // local part citado, ej. "user"@dominio.com
```

`filter_var` ya impone el límite de 64 caracteres en la parte local (RFC 5321) — no se reimplementa.

## `EmailValidator::isDisposable()`

Comparación exacta (`in_array($domain, DisposableEmailDomains::DOMAINS, true)`) del dominio normalizado contra la lista estática. No usa wildcards ni subdominios — `sub.mailinator.com` NO se detecta si solo `mailinator.com` está en la lista (limitación conocida, ver abajo).

## Casos especiales (IDN — dominios internacionalizados)

`filter_var` en PHP 8.2 **rechaza dominios UTF-8 sin punycode** (`user@münchen.de` falla) — esto es un comportamiento nativo de PHP, no un bug del widget. Si necesitas soportar dominios internacionalizados, el dominio debe convertirse a punycode ANTES de pasar por el validador (`user@xn--mnchen-3ya.de` sí es válido).

## Limitaciones conocidas

- Lista de dominios desechables (~100 entradas) es estática y manual — no se actualiza automáticamente. Nuevos servicios de correo temporal no estarán cubiertos hasta editar `DisposableEmailDomains::DOMAINS` + su espejo JS.
- No detecta subdominios de un dominio desechable conocido (`xyz.mailinator.com`).
- Dominios IDN sin convertir a punycode fallan validación (comportamiento de PHP `filter_var`, no del widget).
- Aviso de "desechable" es solo informativo — nunca bloquea guardado.

## Tests

`Test/EmailValidatorTest.php` — 45 casos: normalización, válidos RFC (13), inválidos (13, incluye localhost/IP literal/local citado), límites de longitud (254 total / 64 local), desechables (10), IDN (2, punycode vs raw). Ejecutar: `phpunit --testsuite "Validator (standalone)"`.
