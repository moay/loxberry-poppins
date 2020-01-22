<?php

namespace LoxBerryPoppins\Frontend\Navigation;

use LoxBerryPoppins\Frontend\Routing\RoutingConfigurationParser;

/**
 * Class UrlBuilder.
 */
class UrlBuilder
{
    const ADMIN_BASE_URL = 'admin/plugins';
    const PUBLIC_BASE_URL = 'plugins';

    /** @var string */
    private $packageDirectory;

    /** @var array */
    private $routes;

    /**
     * UrlBuilder constructor.
     *
     * @param RoutingConfigurationParser $routingConfigurationParser
     * @param string                     $packageDirectory
     */
    public function __construct(RoutingConfigurationParser $routingConfigurationParser, $packageDirectory)
    {
        $this->routes = $routingConfigurationParser->getConfiguration();
        $this->packageDirectory = $packageDirectory;
    }

    /**
     * @param string $routeName
     *
     * @return string
     */
    public function getAdminUrl(string $routeName): string
    {
        if (!array_key_exists($routeName, $this->routes ?? []) || ($this->routes[$routeName]['public'] ?? false)) {
            throw new \InvalidArgumentException(sprintf('No admin route with name %s found', $routeName));
        }

        $route = $this->routes[$routeName];

        return $this->buildUrl($route['route']);
    }

    /**
     * @param string $routeName
     *
     * @return string
     */
    public function getPublicUrl(string $routeName): string
    {
        if (!array_key_exists($routeName, $this->routes ?? []) || !($this->routes[$routeName]['public'] ?? false)) {
            throw new \InvalidArgumentException(sprintf('No public route with name %s found', $routeName));
        }

        $route = $this->routes[$routeName];

        return $this->buildUrl($route['route'], true);
    }

    /**
     * @param string $route
     * @param bool   $isPublic
     *
     * @return string
     */
    private function buildUrl(string $route, bool $isPublic = false): string
    {
        return sprintf(
            '/%s/%s/%s',
            trim($isPublic ? self::PUBLIC_BASE_URL : self::ADMIN_BASE_URL, '/'),
            trim($this->packageDirectory, '/'),
            trim($route, '/')
        );
    }
}
