<?php

namespace Dongivan\RouteVersioning\Matching;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Matching\ValidatorInterface;
use Dongivan\RouteVersioning\Version;

class VersionValidator implements ValidatorInterface
{
    /**
     * Validate a given rule against a route and request.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function matches(Route $route, Request $request)
    {
        $strict = Version::isStrict();
        $requestVersion = $request->header(Version::getHeaderKey());
        $routeVersion = $route->action["version"] ?? null;

        return $strict
            ? Version::compareVersions($requestVersion, $routeVersion) == 0
            : ($requestVersion === null
                || $routeVersion === null
                || Version::compareVersions($requestVersion, $routeVersion) >= 0
            );
    }
}
