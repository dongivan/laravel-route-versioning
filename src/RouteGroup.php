<?php

namespace Dongivan\RouteVersioning;

use Illuminate\Support\Arr;
use Illuminate\Routing\RouteGroup as ParentRouteGroup;

class RouteGroup extends ParentRouteGroup
{
    /**
     * Merge route groups into a new array.
     *
     * @param  array  $new
     * @param  array  $old
     * @param  bool  $prependExistingPrefix
     * @return array
     */
    public static function merge($new, $old, $prependExistingPrefix = true)
    {
        if (isset($new['domain'])) {
            unset($old['domain']);
        }

        if (isset($new['controller'])) {
            unset($old['controller']);
        }

        $new = array_merge(static::formatAs($new, $old), [
            'namespace' => static::formatNamespace($new, $old),
            'prefix' => static::formatPrefix($new, $old, $prependExistingPrefix),
            'where' => static::formatWhere($new, $old),

            /* This line is added to make version merged */
            'version' => static::formatVersion($new, $old),
        ]);

        return array_merge_recursive(Arr::except(
            $old,
            ['namespace', 'prefix', 'where', 'as', 'version']
        ), $new);
    }

    /**
     * Format the version for the new group attributes.
     *
     * @param array $new
     * @param array $old
     * @return string|null
     */
    protected static function formatVersion($new, $old)
    {
        return $new['version'] ?? ($old['version'] ?? null);
    }
}
