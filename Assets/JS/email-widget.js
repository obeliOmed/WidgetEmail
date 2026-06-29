/**
 * WidgetEmail — client-side blur feedback.
 *
 * Mirrors EmailValidator.php logic so the user gets instant visual feedback
 * without a round-trip. Three states:
 *   ✓ (green)   — valid email, non-disposable domain
 *   ⚠ (orange) — valid format, but disposable/throwaway domain
 *   ✗ (red)     — invalid format
 *
 * Attaches to any <input data-email-validate> element.
 * Vanilla JS, ES5 compatible, no external dependencies.
 */
(function () {
    'use strict';

    // Mirror of DisposableEmailDomains::DOMAINS — keep in sync between PHP and JS.
    var DISPOSABLE_DOMAINS = [
        'guerrillamail.com', 'guerrillamail.net', 'guerrillamail.org',
        'guerrillamail.biz', 'guerrillamail.de', 'guerrillamail.info',
        'guerrillamailblock.com', 'grr.la', 'sharklasers.com',
        '10minutemail.com', '10minutemail.net', '10minutemail.org', '10minutemail.de',
        'mailinator.com', 'mailinater.com', 'suremail.info',
        'spamherelots.com', 'putthisinyourspamdatabase.com',
        'trashmail.com', 'trashmail.at', 'trashmail.io', 'trashmail.me',
        'trashmail.net', 'trashmail.org', 'trashmail.xyz',
        'yopmail.com', 'yopmail.fr', 'cool.fr.nf', 'jetable.fr.nf',
        'nospam.ze.tc',
        'tempmail.com', 'tempmail.net', 'tempmail.org', 'temp-mail.org',
        'temp-mail.io', 'tempr.email', 'tempail.com', 'tempemail.net',
        'throwaway.email', 'throwam.com',
        'maildrop.cc', 'discard.email', 'dispostable.com', 'fakeinbox.com',
        'mailnull.com', 'spam4.me', 'spam.la', 'spamavert.com',
        'spamgourmet.com', 'spamgourmet.net', 'spamgourmet.org',
        'spamhereplease.com', 'mailexpire.com', 'safetymail.info', 'e4ward.com',
        'binkmail.com', 'bobmail.info', 'chammy.info', 'devnullmail.com',
        'filzmail.com', 'get2mail.fr', 'gishpuppy.com', 'hailmail.net',
        'ihateyoualot.info', 'imails.info', 'jnxjn.com', 'klzlk.com',
        'lookugly.com', 'lortemail.dk', 'mailtome.de', 'mailscrap.com',
        'mt2014.com', 'mt2015.com', 'nwldx.com', 'objectmail.com',
        'obobbo.com', 'oneoffmail.com', 'pepbot.com', 'pfui.ru',
        'qq.my', 'rklips.com', 'rmqkr.net', 'royal.net', 'rppkn.com',
        's0ny.net', 'sandelf.de', 'shieldedmail.com', 'snakemail.com',
        'sogetthis.com', 'soodonims.com', 'supergreatmail.com',
        'sweetxxx.de', 'tafmail.com', 'tagyourself.com', 'teewars.org',
        'tknzz.email', 'tlpn.org', 'tmpjoe.com', 'trbvm.com', 'turual.com',
        'uggsrock.com', 'uroid.com', 'vomoto.com', 'wuzupmail.net',
        'xemaps.com', 'xents.com', 'xmaily.com', 'xoxy.net',
        'yep.it', 'yogamaven.com', 'yuurok.com', 'z1p.biz',
        'zippymail.info', 'zoemail.com', 'zomg.info'
    ];

    /**
     * Client-side email format check.
     * Matches the server-side EmailValidator::validate() rejection rules:
     * - IP literals ([192.168.1.1])
     * - Single-label domains (localhost)
     * - Quoted local parts ("user"@domain.com)
     * Full RFC 5322 compliance is delegated to the server.
     */
    function isValidEmail(value) {
        if (!value || value.length > 254) { return false; }

        var atIdx = value.lastIndexOf('@');
        if (atIdx < 1) { return false; }

        var local = value.substring(0, atIdx);
        var domain = value.substring(atIdx + 1);

        // Reject quoted local parts
        if (local.charAt(0) === '"') { return false; }
        // Reject IP literal domains
        if (domain.charAt(0) === '[') { return false; }
        // Reject single-label domains
        if (domain.indexOf('.') === -1) { return false; }

        return /^[^\s@"]+@[^\s@\[]+\.[^\s@]{2,}$/.test(value);
    }

    function isDisposable(value) {
        var atIdx = value.lastIndexOf('@');
        if (atIdx === -1) { return false; }
        var domain = value.substring(atIdx + 1).toLowerCase();
        return DISPOSABLE_DOMAINS.indexOf(domain) !== -1;
    }

    function clearFeedback(input) {
        var badge = input.parentNode.querySelector('.email-feedback-badge');
        if (badge) { badge.parentNode.removeChild(badge); }
    }

    function showBadge(input, icon, cssClass) {
        clearFeedback(input);
        var badge = document.createElement('span');
        badge.className = 'email-feedback-badge input-group-text ' + cssClass;
        badge.textContent = icon;
        badge.style.cssText = 'font-size:0.85rem;min-width:2.5rem;text-align:center;user-select:none;';
        input.insertAdjacentElement('afterend', badge);
    }

    function onBlur() {
        var value = this.value.trim().toLowerCase();
        if (!value) { clearFeedback(this); return; }

        if (!isValidEmail(value)) {
            showBadge(this, '✗', 'text-danger');   // ✗
        } else if (isDisposable(value)) {
            showBadge(this, '⚠', 'text-warning');  // ⚠
        } else {
            showBadge(this, '✓', 'text-success');  // ✓
        }
    }

    function onReset() {
        var form = this;
        form.querySelectorAll('[data-email-validate]').forEach(function (input) {
            clearFeedback(input);
        });
    }

    function initEmailWidget() {
        document.querySelectorAll('input[data-email-validate]').forEach(function (input) {
            input.addEventListener('blur', onBlur);
        });
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('reset', onReset);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEmailWidget);
    } else {
        initEmailWidget();
    }
})();
