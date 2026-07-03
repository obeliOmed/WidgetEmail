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

    // Disposable domain list has a single source of truth: PHP
    // (DisposableEmailDomains::DOMAINS), injected per-input via the
    // data-disposable-domains attribute (see WidgetEmail::inputHtml()).
    // No hardcoded copy here — avoids the two-files-drift-apart risk.
    function disposableDomainsFor(input) {
        var raw = input.getAttribute('data-disposable-domains');
        if (!raw) { return []; }
        try {
            var parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

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

    function isDisposable(value, domains) {
        var atIdx = value.lastIndexOf('@');
        if (atIdx === -1) { return false; }
        var domain = value.substring(atIdx + 1).toLowerCase();
        return domains.indexOf(domain) !== -1;
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
        } else if (isDisposable(value, disposableDomainsFor(this))) {
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
