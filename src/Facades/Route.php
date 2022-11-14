<?php

namespace Dongivan\RouteVersioning\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Routing\PendingResourceRegistration apiResource(string $name, string $controller, array $options = [])
 * @method static \Illuminate\Routing\PendingResourceRegistration resource(string $name, string $controller, array $options = [])
 * @method static \App\Framework\Routing\Route any(string $uri, array|string|callable|null $action = null)
 * @method static \App\Framework\Routing\Route|null current()
 * @method static \App\Framework\Routing\Route delete(string $uri, array|string|callable|null $action = null)
 * @method static \App\Framework\Routing\Route fallback(array|string|callable|null $action = null)
 * @method static \App\Framework\Routing\Route get(string $uri, array|string|callable|null $action = null)
 * @method static \App\Framework\Routing\Route|null getCurrentRoute()
 * @method static \App\Framework\Routing\RouteCollectionInterface getRoutes()
 * @method static \App\Framework\Routing\Route match(array|string $methods, string $uri, array|string|callable|null $action = null)
 * @method static \App\Framework\Routing\Route options(string $uri, array|string|callable|null $action = null)
 * @method static \App\Framework\Routing\Route patch(string $uri, array|string|callable|null $action = null)
 * @method static \App\Framework\Routing\Route permanentRedirect(string $uri, string $destination)
 * @method static \App\Framework\Routing\Route post(string $uri, array|string|callable|null $action = null)
 * @method static \App\Framework\Routing\Route put(string $uri, array|string|callable|null $action = null)
 * @method static \App\Framework\Routing\Route redirect(string $uri, string $destination, int $status = 302)
 * @method static \App\Framework\Routing\Route substituteBindings(\Illuminate\Support\Facades\Route $route)
 * @method static \App\Framework\Routing\Route view(string $uri, string $view, array $data = [], int|array $status = 200, array $headers = [])
 * @method static \App\Framework\Routing\RouteRegistrar as(string $value)
 * @method static \App\Framework\Routing\RouteRegistrar controller(string $controller)
 * @method static \App\Framework\Routing\RouteRegistrar domain(string $value)
 * @method static \App\Framework\Routing\RouteRegistrar middleware(array|string|null $middleware)
 * @method static \App\Framework\Routing\RouteRegistrar name(string $value)
 * @method static \App\Framework\Routing\RouteRegistrar namespace(string|null $value)
 * @method static \App\Framework\Routing\RouteRegistrar prefix(string $prefix)
 * @method static \App\Framework\Routing\RouteRegistrar scopeBindings()
 * @method static \App\Framework\Routing\RouteRegistrar version(int|string $version)
 * @method static \App\Framework\Routing\RouteRegistrar where(array $where)
 * @method static \App\Framework\Routing\RouteRegistrar withoutMiddleware(array|string $middleware)
 * @method static \App\Framework\Routing\RouteRegistrar withoutScopedBindings()
 * @method static \App\Framework\Routing\Router|\App\Framework\Routing\RouteRegistrar group(\Closure|string|array $attributes, \Closure|string $routes)
 * @method static \App\Framework\Routing\ResourceRegistrar resourceVerbs(array $verbs = [])
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
 * @see \App\Framework\Routing\Router
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
