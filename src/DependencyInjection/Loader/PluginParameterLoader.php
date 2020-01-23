<?php

namespace LoxBerryPoppins\DependencyInjection\Loader;

use LoxBerry\ConfigurationParser\ConfigurationParser;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class PluginParameterLoader.
 */
class PluginParameterLoader
{
    /** @var string */
    private $pathToConfigFile;

    /** @var string */
    private $pluginRootDirectory;

    /**
     * PluginParameterLoader constructor.
     *
     * @param string $pathToConfigFile
     * @param string $pluginRootDirectory
     */
    public function __construct(string $pathToConfigFile, string $pluginRootDirectory)
    {
        $this->pathToConfigFile = $pathToConfigFile;
        $this->pluginRootDirectory = $pluginRootDirectory;
    }

    /**
     * @param ContainerBuilder $containerBuilder
     *
     * @throws \Config_Lite_Exception
     */
    public function loadPluginParameters(ContainerBuilder $containerBuilder)
    {
        if (!file_exists($this->pathToConfigFile)) {
            throw new \RuntimeException('Cannot load plugin configuration. Configuration file missing.');
        }

        $configuration = new ConfigurationParser($this->pathToConfigFile);

        $containerBuilder->setParameter('plugin.name', $configuration->get('PLUGIN', 'NAME'));
        $containerBuilder->setParameter('plugin.version', $configuration->get('PLUGIN', 'VERSION'));
        $containerBuilder->setParameter('plugin.directory', $configuration->get('PLUGIN', 'FOLDER'));
        $containerBuilder->setParameter('plugin.title', $configuration->get('PLUGIN', 'TITLE'));
        $containerBuilder->setParameter('runtime.root_dir', realpath($this->pluginRootDirectory));
    }
}
