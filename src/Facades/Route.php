<?php

namespace Dongivan\RouteVersioning\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Routing\PendingResourceRegistration apiResource(string $name, string $controller, array $options = [])
 * @method static \Illuminate\Routing\PendingResourceRegistration resource(string $name, string $controller, array $options = [])
 * @method static \Dongivan\RouteVersioning\Route any(string $uri, array|string|callable|null $action = null)
 * @method static \Dongivan\RouteVersioning\Route|null current()
 * @method static \Dongivan\RouteVersioning\Route delete(string $uri, array|string|callable|null $action = null)
 * @method static \Dongivan\RouteVersioning\Route fallback(array|string|callable|null $action = null)
 * @method static \Dongivan\RouteVersioning\Route get(string $uri, array|string|callable|null $action = null)
 * @method static \Dongivan\RouteVersioning\Route|null getCurrentRoute()
 * @method static \Dongivan\RouteVersioning\RouteCollectionInterface getRoutes()
 * @method static \Dongivan\RouteVersioning\Route match(array|string $methods, string $uri, array|string|callable|null $action = null)
 * @method static \Dongivan\RouteVersioning\Route options(string $uri, array|string|callable|null $action = null)
 * @method static \Dongivan\RouteVersioning\Route patch(string $uri, array|string|callable|null $action = null)
 * @method static \Dongivan\RouteVersioning\Route permanentRedirect(string $uri, string $destination)
 * @method static \Dongivan\RouteVersioning\Route post(string $uri, array|string|callable|null $action = null)
 * @method static \Dongivan\RouteVersioning\Route put(string $uri, array|string|callable|null $action = null)
 * @method static \Dongivan\RouteVersioning\Route redirect(string $uri, string $destination, int $status = 302)
 * @method static \Dongivan\RouteVersioning\Route substituteBindings(\Illuminate\Support\Facades\Route $route)
 * @method static \Dongivan\RouteVersioning\Route view(string $uri, string $view, array $data = [], int|array $status = 200, array $headers = [])
 * @method static \Dongivan\RouteVersioning\RouteRegistrar as(string $value)
 * @method static \Dongivan\RouteVersioning\RouteRegistrar controller(string $controller)
 * @method static \Dongivan\RouteVersioning\RouteRegistrar domain(string $value)
 * @method static \Dongivan\RouteVersioning\RouteRegistrar middleware(array|string|null $middleware)
 * @method static \Dongivan\RouteVersioning\RouteRegistrar name(string $value)
 * @method static \Dongivan\RouteVersioning\RouteRegistrar namespace(string|null $value)
 * @method static \Dongivan\RouteVersioning\RouteRegistrar prefix(string $prefix)
 * @method static \Dongivan\RouteVersioning\RouteRegistrar scopeBindings()
 * @method static \Dongivan\RouteVersioning\RouteRegistrar version(int|string $version)
 * @method static \Dongivan\RouteVersioning\RouteRegistrar where(array $where)
 * @method static \Dongivan\RouteVersioning\RouteRegistrar withoutMiddleware(array|string $middleware)
 * @method static \Dongivan\RouteVersioning\RouteRegistrar withoutScopedBindings()
 * @method static \Dongivan\RouteVersioning\Router|\Dongivan\RouteVersioning\RouteRegistrar group(\Closure|string|array $attributes, \Closure|string $routes)
 * @method static \Dongivan\RouteVersioning\ResourceRegistrar resourceVerbs(array $verbs = [])
 * @method static string|null currentRouteAction()
 * @method static string|null currentRouteName()
 * @method static void apiResources(array $resources, array $options = [])
 * @method static void bind(string $key, string|callable $binder)
 * @method static void macro(string $name, object|callable $macro)
 * @method static void model(string $key, string $class, \Closure|null $callback = null)
 * @method static void pattern(string $key, string $pattern)
 * @method static void resources(array $resources, array $options = [])
 * @method static void substituteImplicitBindings(\Illuminate\Support\Facades\Route $route)
 * @method static boolean uses(...$patterns)
 * @method static boolean is(...$patterns)
 * @method static boolean has(string $name)
 * @method static mixed input(string $key, string|null $default = null)
 *
 * @see \Dongivan\RouteVersioning\Router
 */
class Route extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'router';
    }
}
