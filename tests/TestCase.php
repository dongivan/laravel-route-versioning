<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
// use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
// use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
// use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
// use Illuminate\Foundation\Http\Kernel as HttpKernel;
// use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
// use Tests\App\App\Exceptions\Handler as ExceptionHandler;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Dongivan\RouteVersioning\Version;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

abstract class TestCase extends BaseTestCase {

    public function createApplication()
    {
        $app = require __DIR__.'/App/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        /* avoid Version save `strict` across test file */
        Version::$strict = null;

        parent::setUp();
    }

    protected function getRouteVersionConfigFile()
    {
        return getcwd() . "/tests/App/config/route-version.php";
    }

    protected function runArtisan($cmd)
    {
        exec("php tests/App/artisan $cmd", $_, $resultCode);
        assertEquals($resultCode, 0);
    }

    protected function setStrictMode($strict)
    {
        file_put_contents($this->getRouteVersionConfigFile(), [
            '<?php

return [
    "headerKey" => "X-VERSION",
    "strict" => ' . ($strict ? 'true' : 'false') . ',
];
'
        ]);
    }

    protected function clearConfigFile()
    {
        @unlink($this->getRouteVersionConfigFile());
    }

}