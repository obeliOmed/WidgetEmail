<?php
declare(strict_types=1);

namespace FacturaScripts\Plugins\WidgetEmail\Lib;

/**
 * Email address validator for FacturaScripts forms.
 *
 * Validates RFC 5322 format, enforces RFC 5321 length limit (254 chars), and
 * detects disposable/throwaway domains. Has no FacturaScripts dependencies and
 * can be used standalone.
 *
 * Usage in model::test():
 *   if (!empty($this->email) && !EmailValidator::validate($this->email)) {
 *       $this->toolBox()->log()->error('invalid-email');
 *       return false;
 *   }
 */
class EmailValidator
{
    /**
     * Returns true when the email passes all format checks:
     * - Non-empty and ≤ 254 chars (RFC 5321)
     * - Passes filter_var(FILTER_VALIDATE_EMAIL)
     * - Domain is not an IP literal ([192.168.1.1])
     * - Domain is not single-label (localhost, localdomain, etc.)
     * - Local part is not quoted ("user name"@domain.com)
     */
    public static function validate(string $email): bool
    {
        $normalized = self::normalize($email);

        if ($normalized === '' || strlen($normalized) > 254) {
            return false;
        }

        if (!filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $atPos = strrpos($normalized, '@');
        $local = substr($normalized, 0, (int)$atPos);
        $domain = substr($normalized, (int)$atPos + 1);

        // Reject IP literal domains: user@[192.168.1.1], user@[IPv6:...]
        if ($domain !== '' && $domain[0] === '[') {
            return false;
        }

        // Reject single-label domains: localhost, localdomain, intranet, etc.
        if (strpos($domain, '.') === false) {
            return false;
        }

        // Reject quoted local parts: "user name"@domain.com
        // filter_var accepts them; we reject for clinical context UX clarity.
        if ($local !== '' && $local[0] === '"') {
            return false;
        }

        return true;
    }

    /**
     * Returns true when the email domain is in the disposable-domains blacklist.
     *
     * Does NOT check email validity — call validate() first when needed.
     * Disposable detection is advisory: the widget shows a warning (⚠) but
     * does not block saving. The consuming model decides whether to block.
     */
    public static function isDisposable(string $email): bool
    {
        $normalized = self::normalize($email);
        $atPos = strrpos($normalized, '@');

        if ($atPos === false) {
            return false;
        }

        $domain = substr($normalized, (int)$atPos + 1);
        return in_array($domain, DisposableEmailDomains::DOMAINS, true);
    }

    /**
     * Normalizes email: trim whitespace + lowercase.
     * Emails are case-insensitive in practice (RFC 5321 §2.4).
     */
    public static function normalize(string $email): string
    {
        return strtolower(trim($email));
    }
}
