<?php

namespace Dongivan\RouteVersioning\Exceptions;

class VersionFormatException extends VersionException
{
    public function __construct($version)
    {
        parent::__construct("Version parse error: `$version`.");
    }
}
