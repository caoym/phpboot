<?php

namespace PhpBoot\Metas;


class RouteGroupMeta
{
    /**
     * @var string
     * the prefix path for all routes of the controller
     */
    private $path;

    /**
     * @var string
     */
    private $doc = "";

    /**
     * @var RouteMeta[]
     */
    private $routes=[];
}