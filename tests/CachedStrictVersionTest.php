<?php

namespace Tests;

use Dongivan\RouteVersioning\Version;

class CachedStrictVersionTest extends TestCase
{
    public function setUp(): void
    {
        $this->runArtisan("route:clear");

        $this->setStrictMode(true);

        $this->runArtisan("route:cache");

        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->clearConfigFile();

        $this->runArtisan("route:clear");

        parent::tearDown();
    }

    public function test_cached_routes_versionable()
    {
        $this->getJson("cached-test")->assertStatus(200)->assertContent("NO VERSION INDEX");
        $this->withHeader(Version::getHeaderKey(), "v1")->getJson("cached-test")->assertStatus(200)->assertContent("VERSION 1 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v1.2")->getJson("cached-test")->assertStatus(404);
        $this->withHeader(Version::getHeaderKey(), "v1.5")->getJson("cached-test")->assertStatus(200)->assertContent("VERSION 1.5 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v1.8")->getJson("cached-test")->assertStatus(404);
        $this->withHeader(Version::getHeaderKey(), "v1.10")->getJson("cached-test")->assertStatus(200)->assertContent("VERSION 1.10 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v1.15")->getJson("cached-test")->assertStatus(404);
        $this->withHeader(Version::getHeaderKey(), "v2")->getJson("cached-test")->assertStatus(200)->assertContent("VERSION 2 INDEX");
        $this->withHeader(Version::getHeaderKey(), "v3")->getJson("cached-test")->assertStatus(404);
    }
}
