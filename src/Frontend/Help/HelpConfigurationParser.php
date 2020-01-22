<?php

namespace LoxBerryPoppins\Frontend\Help;

use Symfony\Component\Yaml\Yaml;

/**
 * Class HelpConfigurationParser.
 */
class HelpConfigurationParser
{
    const HELP_CONFIGURATION = '/config/pagehelp.yaml';

    /** @var array */
    private $helpConfiguration;

    /**
     * HelpConfigurationParser constructor.
     *
     * @param string $rootPath
     */
    public function __construct($rootPath)
    {
        $this->helpConfiguration = Yaml::parseFile(rtrim($rootPath, '/').self::HELP_CONFIGURATION);
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->helpConfiguration;
    }
}
