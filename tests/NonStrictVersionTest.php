<?php

namespace Tests;

use Dongivan\RouteVersioning\Facades\Route;
use Dongivan\RouteVersioning\Version;
use Dongivan\RouteVersioning\Exceptions\VersionFormatException;
use Dongivan\RouteVersioning\Exceptions\GroupVersionParameterException;
use Dongivan\RouteVersioning\Router;
use Tests\App\App\Http\Controllers\TestController;
use Tests\App\App\Http\Controllers\TestV1Controller;
use Tests\App\App\Http\Controllers\TestV1_5Controller;
use Tests\App\App\Http\Controllers\TestV1_10Controller;
use Tests\App\App\Http\Controllers\TestV2Controller;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

class NonStrictVersionTest extends TestCase
{
    public function setUp(): void
    {
        $this->runArtisan("route:clear");

        parent::setUp();

        config()->set("route-version.strict", false);
    }

    public function test_route_facade()
    {
        $router = Route::getFacadeRoot();
        assertTrue($router instanceof Router);
    }

    public function test_routes_set()
    {
        $count = count(Route::getRoutes()->getRoutes());
        Route::get("test", [TestController::class, "index"]);
        assertEquals(count(Route::getRoutes()->getRoutes()), $count + 1);
    }

    public function test_routes_versionable()
    {
        $count = count(Route::getRoutes()->getRoutes());
        Route::version(1)->get("test", [TestV1Controller::class, "index"]);
        assertEquals(count(Route::getRoutes()->getRoutes()), $count + 1);
        Route::version(2)->get("test", [TestV2Controller::class, "index"]);
        assertEquals(count(Route::getRoutes()->getRoutes()), $count + 2);

        $this->getJson("test")->assertStatus(200)->assertContent("VERSION 2 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v1")->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v2")->getJson("test")->assertStatus(200)->assertContent("VERSION 2 INDEX");
    }

    public function test_routes_version_fallback()
    {
        $count = count(Route::getRoutes()->getRoutes());
        Route::version(1)->get("test", [TestV1Controller::class, "index"]);
        assertEquals(count(Route::getRoutes()->getRoutes()), $count + 1);

        $this->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v1")->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v2")->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
    }

    public function test_minor_version_fallback()
    {
        Route::version("v1.5")->get("test", [TestV1_5Controller::class, "index"]);
        Route::version("v1.10")->get("test", [TestV1_10Controller::class, "index"]);

        $this->getJson("test")->assertStatus(200)->assertContent("VERSION 1.10 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v1.15")->getJson("test")->assertStatus(200)->assertContent("VERSION 1.10 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v1.10")->getJson("test")->assertStatus(200)->assertContent("VERSION 1.10 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v1.8")->getJson("test")->assertStatus(200)->assertContent("VERSION 1.5 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v1.5")->getJson("test")->assertStatus(200)->assertContent("VERSION 1.5 INDEX");
    }

    public function test_group_routes_versionable()
    {
        Route::version("v1")->group(function () {
            Route::get("test", [TestV1Controller::class, "index"]);
            Route::post("test", [TestV1Controller::class, "store"]);
            Route::get("test/{id}", [TestV1Controller::class, "show"]);
        });
        Route::version("v2")->group(function () {
            Route::get("test", [TestV2Controller::class, "index"]);
            Route::post("test", [TestV2Controller::class, "store"]);
            Route::get("test/{id}", [TestV2Controller::class, "show"]);
        });

        $this->getJson("test")->assertStatus(200)->assertContent("VERSION 2 INDEX");
        $this->postJson("test")->assertStatus(200)->assertContent("VERSION 2 STORE");
        $this->getJson("test/2")->assertStatus(200)->assertContent("VERSION 2 SHOW 2");
        $this->withHeader(Version::getHeaderKey(), "v1")->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v1")->postJson("test")->assertStatus(200)->assertContent("VERSION 1 STORE");
        $this->withHeader(Version::getHeaderKey(), "v1")->getJson("test/1")->assertStatus(200)->assertContent("VERSION 1 SHOW 1");
        $this->withHeader(Version::getHeaderKey(), "v2")->getJson("test")->assertStatus(200)->assertContent("VERSION 2 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v2")->postJson("test")->assertStatus(200)->assertContent("VERSION 2 STORE");
        $this->withHeader(Version::getHeaderKey(), "v2")->getJson("test/2")->assertStatus(200)->assertContent("VERSION 2 SHOW 2");
    }

    public function test_group_routes_fallback()
    {
        Route::post("test", [TestController::class, "store"]);
        Route::version("v1")->group(function () {
            Route::get("test", [TestV1Controller::class, "index"]);
            Route::post("test", [TestV1Controller::class, "store"]);
            Route::get("test/{id}", [TestV1Controller::class, "show"]);
        });
        Route::version("v2")->group(function () {
            Route::get("test/{id}", [TestV2Controller::class, "show"]);
        });

        $this->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
        $this->postJson("test")->assertStatus(200)->assertContent("VERSION 1 STORE");
        $this->getJson("test/2")->assertStatus(200)->assertContent("VERSION 2 SHOW 2");
        $this->withHeader(Version::getHeaderKey(), "v1")->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v1")->postJson("test")->assertStatus(200)->assertContent("VERSION 1 STORE");
        $this->withHeader(Version::getHeaderKey(), "v1")->getJson("test/1")->assertStatus(200)->assertContent("VERSION 1 SHOW 1");
        $this->withHeader(Version::getHeaderKey(), "v2")->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v2")->postJson("test")->assertStatus(200)->assertContent("VERSION 1 STORE");
        $this->withHeader(Version::getHeaderKey(), "v2")->getJson("test/2")->assertStatus(200)->assertContent("VERSION 2 SHOW 2");
    }

    public function test_set_version_in_group_routes()
    {
        Route::version("v1")->group(function () {
            Route::get("test", [TestV1Controller::class, "index"]);
            Route::post("test", [TestV1Controller::class, "store"]);
            Route::get("test/{id}", [TestV1Controller::class, "show"]);
            Route::version("v2")->get("test/{id}", [TestV2Controller::class, "show"]);
        });

        $this->withHeader(Version::getHeaderKey(), "v1")->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v1")->postJson("test")->assertStatus(200)->assertContent("VERSION 1 STORE");
        $this->withHeader(Version::getHeaderKey(), "v1")->getJson("test/1")->assertStatus(200)->assertContent("VERSION 1 SHOW 1");
        $this->withHeader(Version::getHeaderKey(), "v2")->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v2")->postJson("test")->assertStatus(200)->assertContent("VERSION 1 STORE");
        $this->withHeader(Version::getHeaderKey(), "v2")->getJson("test/2")->assertStatus(200)->assertContent("VERSION 2 SHOW 2");
    }

    public function test_versioning_resource_routes()
    {
        Route::version("v1")->group(function () {
            Route::resource("test", TestV1Controller::class)->only(["index", "store", "show"]);
        });
        Route::version("v2")->group(function () {
            Route::resource("test", TestV2Controller::class)->only(["index", "store", "show"]);
        });
        $this->withHeader(Version::getHeaderKey(), "v1")->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v1")->postJson("test")->assertStatus(200)->assertContent("VERSION 1 STORE");
        $this->withHeader(Version::getHeaderKey(), "v1")->getJson("test/1")->assertStatus(200)->assertContent("VERSION 1 SHOW 1");
        $this->withHeader(Version::getHeaderKey(), "v2")->getJson("test")->assertStatus(200)->assertContent("VERSION 2 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v2")->postJson("test")->assertStatus(200)->assertContent("VERSION 2 STORE");
        $this->withHeader(Version::getHeaderKey(), "v2")->getJson("test/2")->assertStatus(200)->assertContent("VERSION 2 SHOW 2");
    }

    public function test_routes_without_version()
    {
        Route::get("test", [TestController::class, "index"]);

        $this->getJson("test")->assertStatus(200)->assertContent("NO VERSION INDEX");
    }

    public function test_routes_without_version_fallback_to_latest_version()
    {
        Route::get("test", [TestController::class, "index"]);
        Route::version(1)->get("test", [TestV1Controller::class, "index"]);

        $this->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
    }

    public function test_integer_version()
    {
        Route::version(1)->get("test", [TestV1Controller::class, "index"]);

        $this->withHeader(Version::getHeaderKey(), "v1")->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
    }

    public function test_string_version_with_v_leading()
    {
        Route::version("v1")->get("test", [TestV1Controller::class, "index"]);

        $this->withHeader(Version::getHeaderKey(), "v1")->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
    }

    public function test_complex_string_version_with_v_leading()
    {
        Route::version("v1.2.3")->get("test", [TestV1Controller::class, "index"]);

        $this->withHeader(Version::getHeaderKey(), "v1.2.3")->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
    }

    public function test_complex_string_version_without_v_leading()
    {
        Route::version("1.2.3")->get("test", [TestV1Controller::class, "index"]);

        $this->withHeader(Version::getHeaderKey(), "v1.2.3")->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
    }

    public function test_illegal_version()
    {
        $this->expectException(VersionFormatException::class);

        Route::version("vv1.2.3")->get("test", [TestV1Controller::class, "index"]);
    }

    public function test_illegal_version_while_group_versioning()
    {
        $this->expectException(VersionFormatException::class);

        Route::version("vv1")->group(function () {
            Route::get("test", [TestV1Controller::class, "index"]);
        });
    }

    public function test_no_version_while_group_versioning()
    {
        $this->expectException(GroupVersionParameterException::class);

        Route::version()->group(function () {
            Route::get("test", [TestV1Controller::class, "index"]);
        });
    }
}
