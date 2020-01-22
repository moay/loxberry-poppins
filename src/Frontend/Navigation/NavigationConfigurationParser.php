<?php

namespace LoxBerryPoppins\Frontend\Navigation;

use Symfony\Component\Yaml\Yaml;

/**
 * Class NavigationConfigurationParser.
 */
class NavigationConfigurationParser
{
    /** @var array */
    private $navigationConfiguration;

    /**
     * NavigationConfigurationParser constructor.
     *
     * @param string $rootPath
     */
    public function __construct($rootPath)
    {
        $this->navigationConfiguration = Yaml::parseFile(rtrim($rootPath, '/').'/config/navigation.yaml');
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->navigationConfiguration;
    }
}
