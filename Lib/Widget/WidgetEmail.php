<?php
declare(strict_types=1);

namespace FacturaScripts\Plugins\WidgetEmail\Lib\Widget;

use FacturaScripts\Core\Lib\AssetManager;
use FacturaScripts\Core\Lib\Widget\BaseWidget;
use FacturaScripts\Core\Tools;
use FacturaScripts\Plugins\WidgetEmail\Lib\DisposableEmailDomains;

/**
 * Email input widget for FacturaScripts XMLView forms.
 *
 * Usage in XMLView:
 *   <widget type="email" fieldname="email" />
 *
 * Provides:
 * - HTML5 type="email" input with maxlength="254"
 * - Client-side blur feedback (✓ valid / ⚠ disposable / ✗ invalid) via email-widget.js
 * - Server-side normalization (trim + lowercase) in processFormData()
 *
 * Validation is intentionally left to the model's test() method via EmailValidator::validate().
 */
class WidgetEmail extends BaseWidget
{
    /** Registers email-widget.js for client-side blur feedback. */
    protected function assets(): void
    {
        $route = Tools::config('route');
        AssetManager::addJs($route . '/Plugins/WidgetEmail/Assets/JS/email-widget.js');
    }

    /**
     * Renders the email input wrapped in an input-group div.
     * The wrapper allows email-widget.js to append the feedback badge after the input.
     */
    protected function inputHtml($type = 'email', $extraClass = ''): string
    {
        $class = $this->combineClasses($this->css('form-control'), $this->class, $extraClass);
        $value = $this->escapeHtml((string)($this->value ?? ''));

        // Disposable domain list is injected here (single source of truth: PHP) so
        // email-widget.js never carries its own copy that could drift out of sync.
        $disposableJson = $this->escapeHtml(json_encode(DisposableEmailDomains::DOMAINS));

        $input = '<input type="email"'
            . ' name="' . $this->fieldname . '"'
            . ' value="' . $value . '"'
            . ' class="' . $class . '"'
            . ' maxlength="254"'
            . ' autocomplete="email"'
            . ' data-email-validate="1"'
            . " data-disposable-domains='" . $disposableJson . "'"
            . $this->inputHtmlExtraParams()
            . '/>';

        return '<div class="input-group">' . $input . '</div>';
    }

    /**
     * Normalizes the submitted value (trim + lowercase) and assigns it to the model.
     * Empty input sets the field to null.
     * Format validation must be done in model::test() using EmailValidator::validate().
     */
    public function processFormData(&$model, $request): void
    {
        $raw = $request->request->get($this->fieldname, '');

        if (trim((string)$raw) === '') {
            $model->{$this->fieldname} = null;
            return;
        }

        $model->{$this->fieldname} = strtolower(trim((string)$raw));
    }
}
