<?php

namespace Dongivan\RouteVersioning;

use Illuminate\Routing\Route as ParentRoute;
use Dongivan\RouteVersioning\Matching\VersionValidator;
use Illuminate\Http\Request;
use Illuminate\Routing\Matching\MethodValidator;
use Symfony\Component\Routing\Route as SymfonyRoute;

class Route extends ParentRoute
{
    /**
     * Get the route validators for the instance.
     *
     * @return array
     */
    public static function getValidators()
    {
        if (isset(static::$validators)) {
            return static::$validators;
        }

        /* Add VersionValidator */
        return static::$validators = array_merge(parent::getValidators(), [
            new VersionValidator
        ]);
    }

    /**
     * Determine if the route matches a given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  bool  $includingMethod
     * @return bool
     */
    public function matches(Request $request, $includingMethod = true)
    {
        $this->compileRoute();

        /* This line is modified from `self::getValidators()` to `static::getValidators()` */
        foreach (static::getValidators() as $validator) {
            if (!$includingMethod && $validator instanceof MethodValidator) {
                continue;
            }

            if (!$validator->matches($this, $request)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the version of this route.
     * @return string|null
     */
    public function getVersion()
    {
        return $this->action["version"] ?? null;
    }

    /**
     * Convert the route to a Symfony route.
     *
     * @return \Symfony\Component\Routing\Route
     */
    public function toSymfonyRoute()
    {
        $requestVersion = "request.headers.get('" . Version::getHeaderKey() . "')";
        $routerVersion = isset($this->action["version"]) ? "'{$this->action["version"]}'" : "null";

        return new SymfonyRoute(
            preg_replace('/\{(\w+?)\?\}/', '{$1}', $this->uri()),
            $this->getOptionalParameterNames(),
            $this->wheres,
            ['utf8' => true],
            $this->getDomain() ?: '',
            [],
            $this->methods,

            /*
                Codes below this comment are added to cache version checking.
                And it needs `symfony/expression-language` to compile.
            */
            Version::isStrict()
                ? "$requestVersion === $routerVersion"
                : ($routerVersion === "null"
                    ? "true"
                    : "$requestVersion === null || $requestVersion >= $routerVersion"
                )
        );
    }
}
