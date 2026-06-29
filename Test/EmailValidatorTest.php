<?php
declare(strict_types=1);

namespace FacturaScripts\Plugins\WidgetEmail\Test;

use FacturaScripts\Plugins\WidgetEmail\Lib\EmailValidator;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for EmailValidator.
 *
 * No FacturaScripts dependency — runs standalone.
 */
class EmailValidatorTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Normalization
    // -------------------------------------------------------------------------

    public function testNormalizationLowercase(): void
    {
        $this->assertSame('user@gmail.com', EmailValidator::normalize('USER@GMAIL.COM'));
    }

    public function testNormalizationTrim(): void
    {
        $this->assertSame('user@gmail.com', EmailValidator::normalize('  user@gmail.com  '));
    }

    public function testNormalizationBoth(): void
    {
        $this->assertSame('user@gmail.com', EmailValidator::normalize('  USER@GMAIL.COM  '));
    }

    // -------------------------------------------------------------------------
    // Valid emails
    // -------------------------------------------------------------------------

    /** @dataProvider validEmailProvider */
    public function testValidEmails(string $email): void
    {
        $this->assertTrue(EmailValidator::validate($email), "Should be valid: $email");
    }

    public static function validEmailProvider(): array
    {
        return [
            ['user@gmail.com'],
            ['user@example.com'],
            ['user@subdomain.example.com'],
            ['user.name@example.com'],
            ['user+tag@gmail.com'],         // subaddressing (RFC)
            ['user-name@example.org'],
            ['user_name@example.net'],
            ['USER@GMAIL.COM'],             // normalized before check
            ['123@numbers.com'],
            ['a@b.co'],
            ['very.long.address@example-domain.co.uk'],
            ['firstname.lastname@company.es'],
            ['user@xn--mnchen-3ya.de'],     // punycode form of münchen.de
        ];
    }

    // -------------------------------------------------------------------------
    // Invalid emails
    // -------------------------------------------------------------------------

    /** @dataProvider invalidEmailProvider */
    public function testInvalidEmails(string $email, string $reason): void
    {
        $this->assertFalse(EmailValidator::validate($email), "Should be invalid ($reason): $email");
    }

    public static function invalidEmailProvider(): array
    {
        return [
            ['notanemail',          'no @ sign'],
            ['@nodomain.com',       'empty local part'],
            ['user@',               'empty domain'],
            ['user@domain',         'single-label domain (no dot)'],
            ['user@localhost',      'localhost — single-label, no dot'],
            ['user @gmail.com',     'space in local part'],
            ['user@gm ail.com',    'space in domain'],
            ['user@@gmail.com',     'double @'],
            ['',                    'empty string'],
            ['user@[192.168.1.1]',  'IP literal domain'],
            ['user@[IPv6:2001:db8::1]', 'IPv6 literal domain'],
            ['"user name"@gmail.com', 'quoted local part'],
            ['"quoted"@example.com', 'quoted local part (simple)'],
        ];
    }

    // -------------------------------------------------------------------------
    // Length limits (RFC 5321: max 254 chars)
    // -------------------------------------------------------------------------

    public function testTooLong255chars(): void
    {
        // Build a 255-char email: 1 char local + @ + 253 char domain.
        // Each domain label ≤63 chars (RFC). filter_var rejects the total >254.
        $domain = str_repeat('b', 62) . '.' . str_repeat('c', 62) . '.' . str_repeat('d', 62) . '.' . str_repeat('e', 63);
        // domain = 62+1+62+1+62+1+63 = 252, total with 'a@' = 1+1+252 = 254 → still valid
        // Add one more char to domain to make total 255:
        $domain = str_repeat('b', 62) . '.' . str_repeat('c', 62) . '.' . str_repeat('d', 62) . '.' . str_repeat('e', 63) . 'x';
        // domain = 253, total = 1+1+253 = 255 → reject
        $email = 'a@' . $domain;
        $this->assertSame(255, strlen($email));
        $this->assertFalse(EmailValidator::validate($email));
    }

    public function testExact254charsValid(): void
    {
        // 1 char local + @ + 252 char domain = 254 total. All labels ≤63 chars.
        // domain = 62+'b' + '.' + 62+'c' + '.' + 62+'d' + '.' + 63+'e' = 62+1+62+1+62+1+63 = 252
        $domain = str_repeat('b', 62) . '.' . str_repeat('c', 62) . '.' . str_repeat('d', 62) . '.' . str_repeat('e', 63);
        $email = 'a@' . $domain;
        $this->assertSame(254, strlen($email));
        $this->assertTrue(EmailValidator::validate($email));
    }

    public function testEmptyReturnsFalse(): void
    {
        $this->assertFalse(EmailValidator::validate(''));
    }

    // -------------------------------------------------------------------------
    // Disposable domain detection
    // -------------------------------------------------------------------------

    public function testDisposableMailinator(): void
    {
        $this->assertTrue(EmailValidator::isDisposable('test@mailinator.com'));
    }

    public function testDisposableGuerrillamail(): void
    {
        $this->assertTrue(EmailValidator::isDisposable('test@guerrillamail.com'));
    }

    public function testDisposable10minutemail(): void
    {
        $this->assertTrue(EmailValidator::isDisposable('user@10minutemail.com'));
    }

    public function testDisposableYopmail(): void
    {
        $this->assertTrue(EmailValidator::isDisposable('user@yopmail.com'));
    }

    public function testDisposableTrashmail(): void
    {
        $this->assertTrue(EmailValidator::isDisposable('user@trashmail.com'));
    }

    public function testNotDisposableGmail(): void
    {
        $this->assertFalse(EmailValidator::isDisposable('user@gmail.com'));
    }

    public function testNotDisposableOutlook(): void
    {
        $this->assertFalse(EmailValidator::isDisposable('user@outlook.com'));
    }

    public function testNotDisposableCustomDomain(): void
    {
        $this->assertFalse(EmailValidator::isDisposable('user@clinica-maser.es'));
    }

    public function testIsDisposableCaseInsensitive(): void
    {
        // normalize() lowercases, so isDisposable handles uppercase input
        $this->assertTrue(EmailValidator::isDisposable('user@MAILINATOR.COM'));
    }

    public function testIsDisposableNoAtReturnsFalse(): void
    {
        $this->assertFalse(EmailValidator::isDisposable('notanemail'));
    }

    // -------------------------------------------------------------------------
    // validate() entry point via disposable (valid format, disposable domain)
    // -------------------------------------------------------------------------

    public function testValidFormatDisposableDomainValidateReturnsTrue(): void
    {
        // validate() does NOT reject disposables — only isDisposable() flags them.
        // The model/consumer decides whether to block.
        $this->assertTrue(EmailValidator::validate('user@mailinator.com'));
    }

    // -------------------------------------------------------------------------
    // IDN / edge cases
    // -------------------------------------------------------------------------

    public function testIdnPunycodeAccepted(): void
    {
        // Punycode form is always accepted: filter_var handles ASCII domains correctly.
        $this->assertTrue(EmailValidator::validate('user@xn--mnchen-3ya.de'));
    }

    public function testIdnRawReturnsBoolNoException(): void
    {
        // filter_var behavior on raw IDN ('münchen.de') varies by PHP version and OS.
        // The contract is: must return bool, never throw.
        $result = EmailValidator::validate('user@münchen.de');
        $this->assertIsBool($result);
    }
}
