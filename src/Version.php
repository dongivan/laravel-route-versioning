<?php

namespace Dongivan\RouteVersioning;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Dongivan\RouteVersioning\Exceptions\VersionFormatException;
use Illuminate\Support\Collection;

class Version
{
    /**
     * The version key in the header of the request.
     *
     * @var string|
     */
    public static $headerKey;

    /**
     * Determine if the router should find the route by version strictly.
     * False to enable version fallback.
     *
     * @var bool
     */
    public static $strict;

    /**
     * Get the version key from config.
     * Default as "X-VERSION".
     *
     * @return string
     * @throws BindingResolutionException
     */
    public static function getHeaderKey()
    {
        if (!isset(static::$headerKey)) {
            static::$headerKey = config("route-version.headerKey", "X-VERSION");
        }

        return static::$headerKey;
    }

    /**
     * Get the version strict from config.
     * Default as false.
     *
     * @return bool
     * @throws BindingResolutionException
     */
    public static function isStrict()
    {
        if (!isset(static::$strict)) {
            static::$strict = config("route-version.strict", false);
        }

        return static::$strict;
    }

    /**
     * Format versions from int or string to a "v" leading string.
     *
     * @param int|string $version
     * @return string
     * @throws Exception
     */
    public static function format(int|string $version)
    {
        if (is_int($version)) {
            $version = "v$version";
        } else if (is_string($version) && preg_match("/^(\\d+\\.){0,2}(\\d+)$/", $version)) {
            $version = "v$version";
        }
        if (!preg_match("/^v(\\d+\\.){0,2}(\\d+)$/", $version)) {
            throw new VersionFormatException($version);
        }

        return $version;
    }

    /**
     * A simple implementation of version compare.
     * The parameters must be leading wtih char `v`, like "v1", "v2.0.1".
     * And only first three version numbers will be compare which means
     * `compareVersions("v2.0.1.1", "v2.0.1.2") === 0`.
     *
     * @param string|null $a
     * @param string|null $b
     * @return int
     */
    public static function compareVersions($a, $b)
    {
        if ($a === $b) {
            return 0;
        }
        if ($a === null) {
            return -1;
        }
        if ($b === null) {
            return 1;
        }

        $a = explode(".", substr($a, 1));
        $b = explode(".", substr($b, 1));

        for ($i = 0; $i < 3; $i++) {
            $ai = $a[$i] ?? 0;
            $bi = $b[$i] ?? 0;
            if ($ai > $bi) {
                return 1;
            } else if ($ai < $bi) {
                return -1;
            }
        }

        return 0;
    }

    /**
     * sort routes by version desc.
     *
     * @param Collection|array $routes
     * @return Collection
     */
    public static function sortRoutes(Collection|array $routes)
    {
        if (is_array($routes)) {
            $routes = collect($routes);
        }

        return $routes->sort(function ($a, $b) {
            /* Becareful: versioning DESC needs the opposite of the comparison result */
            return -static::compareVersions(
                $a->action["version"] ?? null,
                $b->action["version"] ?? null
            );
        });
    }
}
