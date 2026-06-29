<?php
declare(strict_types=1);

namespace FacturaScripts\Plugins\WidgetEmail\Test;

use PHPUnit\Framework\TestCase;

/**
 * Integration tests for WidgetEmail.
 *
 * Requires FacturaScripts BaseWidget to be available. Tests are skipped
 * automatically when running the plugin standalone without FacturaScripts.
 */
class WidgetEmailTest extends TestCase
{
    private function makeWidget(): object
    {
        if (!class_exists('FacturaScripts\Core\Lib\Widget\BaseWidget')) {
            $this->markTestSkipped('FacturaScripts BaseWidget not available in standalone test run.');
        }

        $widget = new \FacturaScripts\Plugins\WidgetEmail\Lib\Widget\WidgetEmail();
        $widget->fieldname = 'email';
        return $widget;
    }

    private function makeRequest(array $data): object
    {
        return new class($data) {
            public object $request;
            public function __construct(array $data)
            {
                $this->request = new class($data) {
                    private array $data;
                    public function __construct(array $data) { $this->data = $data; }
                    public function get(string $key, mixed $default = null): mixed
                    {
                        return $this->data[$key] ?? $default;
                    }
                };
            }
        };
    }

    public function testProcessFormDataNormalizes(): void
    {
        $widget = $this->makeWidget();
        $model = new \stdClass();
        $request = $this->makeRequest(['email' => '  USER@GMAIL.COM  ']);
        $widget->processFormData($model, $request);
        $this->assertSame('user@gmail.com', $model->email);
    }

    public function testProcessFormDataEmptyBecomesNull(): void
    {
        $widget = $this->makeWidget();
        $model = new \stdClass();
        $request = $this->makeRequest(['email' => '']);
        $widget->processFormData($model, $request);
        $this->assertNull($model->email);
    }

    public function testProcessFormDataWhitespaceOnlyBecomesNull(): void
    {
        $widget = $this->makeWidget();
        $model = new \stdClass();
        $request = $this->makeRequest(['email' => '   ']);
        $widget->processFormData($model, $request);
        $this->assertNull($model->email);
    }

    public function testProcessFormDataPreservesValidEmail(): void
    {
        $widget = $this->makeWidget();
        $model = new \stdClass();
        $request = $this->makeRequest(['email' => 'patient@example.com']);
        $widget->processFormData($model, $request);
        $this->assertSame('patient@example.com', $model->email);
    }
}
