<?php

namespace Dongivan\RouteVersioning;

use Illuminate\Routing\RouteRegistrar as ParentRouteRegistrar;

class RouteRegistrar extends ParentRouteRegistrar
{
    protected $allowedAttributes = [
        'as',
        'controller',
        'domain',
        'middleware',
        'name',
        'namespace',
        'prefix',
        'scopeBindings',
        'where',
        'withoutMiddleware',

        /* Add `version` here */
        'version',
    ];
}
