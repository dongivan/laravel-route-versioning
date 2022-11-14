<?php

namespace Dongivan\RouteVersioning\Exceptions;

class GroupVersionParameterException extends VersionException
{
    public function __construct()
    {
        parent::__construct("Method `version` needs 1 parameter to indicate the version.");
    }
}
