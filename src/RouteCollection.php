<?php

namespace Dongivan\RouteVersioning;

use Illuminate\Routing\RouteCollection as ParentRouteCollection;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;

class RouteCollection extends ParentRouteCollection
{
    /**
     * Determine if a route in the array matches the request.
     *
     * @param  \Illuminate\Routing\Route[]  $routes
     * @param  \Illuminate\Http\Request  $request
     * @param  bool  $includingMethod
     * @return \Illuminate\Routing\Route|null
     */
    protected function matchAgainstRoutes(array $routes, $request, $includingMethod = true)
    {
        [$fallbacks, $routes] = collect($routes)->partition(function ($route) {
            return $route->isFallback;
        });

        /* Here $routes will be sort by version desc */
        return Version::sortRoutes($routes)
            ->merge($fallbacks)->first(
                fn (Route $route) => $route->matches($request, $includingMethod)
            );
    }

    /**
     * Add the given route to the arrays of routes.
     *
     * @param  Route  $route
     * @return void
     */
    protected function addToCollections($route)
    {
        $domainAndUri = $route->getDomain() . $route->uri();
        $version = $route->action["version"] ?? null;

        foreach ($route->methods() as $method) {
            /* The keys of second array are modified from `$domainAndUri` to current */
            $this->routes[$method][$version ? "$domainAndUri@$version" : $domainAndUri] = $route;
        }

        /* The keys of `$this->allRoutes` are modified from `$method . $domainAndUri` to current */
        $this->allRoutes[$method . $domainAndUri . $version] = $route;
    }

    /**
     * Convert the collection to a Symfony RouteCollection instance.
     *
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function toSymfonyRouteCollection()
    {
        $symfonyRoutes = new SymfonyRouteCollection;

        /*
            This function is used to generate cached route file with Symfony,
            and the Symfony will also be used while finding route in the cache
            file. Therefore we have to sort routes here, so that versioning
            could work well in Symfony.
        */
        /** @var array $routes */
        $routes = Version::sortRoutes($this->getRoutes())->all();

        foreach ($routes as $route) {
            if (!$route->isFallback) {
                $symfonyRoutes = $this->addToSymfonyRoutesCollection($symfonyRoutes, $route);
            }
        }

        foreach ($routes as $route) {
            if ($route->isFallback) {
                $symfonyRoutes = $this->addToSymfonyRoutesCollection($symfonyRoutes, $route);
            }
        }

        /* This line is from parent RouteCollection (not from AbstractRouteCollection) */
        $this->refreshNameLookups();

        return $symfonyRoutes;
    }

    /**
     * Compile the routes for caching.
     *
     * @return array
     */
    public function compile()
    {
        $compiled = $this->dumper()->getCompiledRoutes();
        /* This line is added in order to compile `conditions` into cache file. */
        $compiled[4] = $this->dumper()->getCompiledRoutes(true)[4];

        $attributes = [];

        foreach ($this->getRoutes() as $route) {
            $attributes[$route->getName()] = [
                'methods' => $route->methods(),
                'uri' => $route->uri(),
                'action' => $route->getAction(),
                'fallback' => $route->isFallback,
                'defaults' => $route->defaults,
                'wheres' => $route->wheres,
                'bindingFields' => $route->bindingFields(),
                'lockSeconds' => $route->locksFor(),
                'waitSeconds' => $route->waitsFor(),
                'withTrashed' => $route->allowsTrashedBindings(),
            ];
        }

        return compact('compiled', 'attributes');
    }
}
