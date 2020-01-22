<?php

namespace LoxBerryPoppins\Frontend\Routing;

use Symfony\Component\Yaml\Yaml;

/**
 * Class RoutingConfigurationParser.
 */
class RoutingConfigurationParser
{
    const ROUTING_CONFIGURATION = '/config/routes.yaml';

    /** @var array */
    private $routingConfiguration;

    /**
     * RoutingConfigurationParser constructor.
     *
     * @param string $rootPath
     */
    public function __construct($rootPath)
    {
        $this->routingConfiguration = Yaml::parseFile(rtrim($rootPath, '/').self::ROUTING_CONFIGURATION);
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->routingConfiguration;
    }
}
