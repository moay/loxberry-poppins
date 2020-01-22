<?php

namespace LoxBerryPoppins\Frontend\Routing;

use LoxBerryPoppins\Exception\RouteIsNotPublicException;
use LoxBerryPoppins\Exception\RouteIsPublicException;
use LoxBerryPoppins\Exception\RouteNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RouteMatcher.
 */
class RouteMatcher
{
    /** @var array */
    private $routes;

    /** @var Request */
    private $request;

    /**
     * RouteMatcher constructor.
     *
     * @param RoutingConfigurationParser $routingConfigurationParser
     */
    public function __construct(RoutingConfigurationParser $routingConfigurationParser)
    {
        $this->routes = $routingConfigurationParser->getConfiguration();
        $this->request = Request::createFromGlobals();
    }

    /**
     * @param string $route
     * @param bool   $isPublic
     *
     * @return PageRouteConfiguration
     */
    public function getMatchedRoute(bool $isPublic = false): PageRouteConfiguration
    {
        $routes = $this->routes ?? [];

        foreach ($routes as $routeName => $routeConfiguration) {
            if ($this->isCurrentMatchedRoute($routeName, $isPublic)) {
                $configuration = new PageRouteConfiguration();
                $configuration->setControllerClassName($routeConfiguration['controller']);
                $configuration->setMethod($this->request->getMethod());
                $configuration->setAction($routeConfiguration['action']);
                $configuration->setRoute($routeConfiguration['route']);

                return $configuration;
            }
        }

        throw new RouteNotFoundException('No route configuration matches this request.');
    }

    /**
     * @param string $routeName
     * @param bool   $isPublic
     *
     * @return bool
     */
    public function isCurrentMatchedRoute(string $routeName, bool $isPublic = false): bool
    {
        $configuredRoute = $this->routes[$routeName] ?? null;
        if (null === $configuredRoute) {
            return false;
        }
        if ($isPublic && !($configuredRoute['public'] ?? false)) {
            throw new RouteIsNotPublicException(sprintf('The requested route was found but is not publicly available.'));
        }
        if (!$isPublic && ($configuredRoute['public'] ?? false)) {
            throw new RouteIsPublicException(sprintf('The requested route was found but is not an admin route.'));
        }

        $currentRoute = $this->request->query->get('route', '');

        $allowedMethods = explode(',', $configuredRoute['method'] ?? Request::METHOD_GET);
        $requestMethod = strtolower($this->request->getMethod());

        if (
            trim($configuredRoute['route'], '/') === trim($currentRoute, '/') &&
            in_array($requestMethod, array_map('trim', array_map('strtolower', $allowedMethods)))
        ) {
            return true;
        }

        return false;
    }
}
