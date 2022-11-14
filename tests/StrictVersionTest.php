<?php

namespace Tests;

use Illuminate\Support\Facades\Route;
use Dongivan\RouteVersioning\Version;
use Tests\App\App\Http\Controllers\TestController;
use Tests\App\App\Http\Controllers\TestV1Controller;
use Tests\App\App\Http\Controllers\TestV1_5Controller;
use Tests\App\App\Http\Controllers\TestV1_10Controller;
use Tests\App\App\Http\Controllers\TestV2Controller;


class StrictVersionTest extends TestCase
{
    public function setUp(): void
    {
        $this->runArtisan("route:clear");

        parent::setUp();

        config()->set("route-version.strict", true);
    }

    public function test_routes_versionable()
    {

        Route::version(1)->get("test", [TestV1Controller::class, "index"]);
        Route::version(2)->get("test", [TestV2Controller::class, "index"]);

        $this->getJson("test")->assertStatus(404);
        $this->withHeader(Version::getHeaderKey(), "v1")->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v2")->getJson("test")->assertStatus(200)->assertContent("VERSION 2 INDEX");
    }

    public function test_routes_version_fallback()
    {
        Route::version(1)->get("test", [TestV1Controller::class, "index"]);

        $this->withHeader(Version::getHeaderKey(), "v1")->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v2")->getJson("test")->assertStatus(404);
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

        $this->getJson("test")->assertStatus(404);
        $this->postJson("test")->assertStatus(404);
        $this->getJson("test/2")->assertStatus(404);
        $this->withHeader(Version::getHeaderKey(), "v1")->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v1")->postJson("test")->assertStatus(200)->assertContent("VERSION 1 STORE");
        $this->withHeader(Version::getHeaderKey(), "v1")->getJson("test/1")->assertStatus(200)->assertContent("VERSION 1 SHOW 1");
        $this->withHeader(Version::getHeaderKey(), "v2")->getJson("test")->assertStatus(200)->assertContent("VERSION 2 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v2")->postJson("test")->assertStatus(200)->assertContent("VERSION 2 STORE");
        $this->withHeader(Version::getHeaderKey(), "v2")->getJson("test/2")->assertStatus(200)->assertContent("VERSION 2 SHOW 2");
    }

    public function test_group_routes_fallback()
    {
        Route::version("v1")->group(function () {
            Route::get("test", [TestV1Controller::class, "index"]);
            Route::post("test", [TestV1Controller::class, "store"]);
            Route::get("test/{id}", [TestV1Controller::class, "show"]);
        });
        Route::version("v2")->group(function () {
            Route::get("test/{id}", [TestV2Controller::class, "show"]);
        });

        $this->getJson("test")->assertStatus(404);
        $this->postJson("test")->assertStatus(404);
        $this->getJson("test/2")->assertStatus(404);
        $this->withHeader(Version::getHeaderKey(), "v1")->getJson("test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v1")->postJson("test")->assertStatus(200)->assertContent("VERSION 1 STORE");
        $this->withHeader(Version::getHeaderKey(), "v1")->getJson("test/1")->assertStatus(200)->assertContent("VERSION 1 SHOW 1");
        $this->withHeader(Version::getHeaderKey(), "v2")->getJson("test")->assertStatus(404);
        $this->withHeader(Version::getHeaderKey(), "v2")->postJson("test")->assertStatus(404);
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
        $this->withHeader(Version::getHeaderKey(), "v2")->getJson("test")->assertStatus(404);
        $this->withHeader(Version::getHeaderKey(), "v2")->postJson("test")->assertStatus(404);
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

    public function test_routes_without_version_in_strict_mode()
    {
        Route::get("test", [TestController::class, "index"]);
        Route::version(1)->get("test", [TestV1Controller::class, "index"]);

        $this->getJson("test")->assertStatus(200)->assertContent("NO VERSION INDEX");
    }
}
