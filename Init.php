<?php
declare(strict_types=1);

namespace FacturaScripts\Plugins\WidgetEmail;

use FacturaScripts\Core\Base\InitClass;

/**
 * Plugin lifecycle hooks. Widget auto-registration is handled by FacturaScripts PluginsDeploy
 * — no manual registration needed here.
 */
class Init extends InitClass
{
    public function init(): void
    {
    }

    public function update(): void
    {
    }

    public function uninstall(): void
    {
    }
}
