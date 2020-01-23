<?php

namespace LoxBerryPoppins;

use LoxBerry\ConfigurationParser\ConfigurationParser;
use LoxBerry\Logging\Logger;
use LoxBerry\System\LowLevelExecutor;
use LoxBerry\System\PathProvider;
use LoxBerry\System\Plugin\PluginDatabase;
use LoxBerryPoppins\DependencyInjection\PluginParameterLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class PluginKernel.
 */
class PluginKernel
{
    const CONFIG_DIRECTORY = '/config';
    const LIBRARY_CONFIG_DIRECTORY = __DIR__.'/../config';
    const ORIGINAL_PLUGIN_CONFIGURATION = '/config/plugin.cfg';
    const DEFAULT_SERVICES_CONFIGURATION = 'services_default.yaml';
    const PLUGIN_SERVICES_CONFIGURATION = 'services.yaml';

    /** @var ContainerBuilder */
    private $container;

    /** @var string */
    private $pluginRootDirectory;

    /**
     * PluginKernel constructor.
     *
     * @param string $pluginRootDirectory
     */
    public function __construct(string $pluginRootDirectory)
    {
        $this->pluginRootDirectory = rtrim($pluginRootDirectory, '/');
        $this->setupErrorHandler();
        $this->loadContainer();
    }

    private function loadContainer()
    {
        $containerBuilder = new ContainerBuilder();

        $pluginParameterLoader = new PluginParameterLoader(
            $this->pluginRootDirectory.self::ORIGINAL_PLUGIN_CONFIGURATION,
            $this->pluginRootDirectory
        );
        $pluginParameterLoader->loadPluginParameters($containerBuilder);

        $defaultsLoader = new YamlFileLoader($containerBuilder, new FileLocator(self::LIBRARY_CONFIG_DIRECTORY));
        $defaultsLoader->load(self::DEFAULT_SERVICES_CONFIGURATION);

        $pluginLoader = new YamlFileLoader($containerBuilder, new FileLocator($this->pluginRootDirectory.self::CONFIG_DIRECTORY));
        $pluginLoader->load(self::PLUGIN_SERVICES_CONFIGURATION);

        $containerBuilder->compile();

        $this->container = $containerBuilder;
    }

    /**
     * @return ContainerBuilder
     */
    public function getContainer(): ContainerBuilder
    {
        return $this->container;
    }

    private function setupErrorHandler()
    {
        $pluginDataBase = new PluginDatabase(new PathProvider(new LowLevelExecutor()));
        if (file_exists($this->pluginRootDirectory.self::ORIGINAL_PLUGIN_CONFIGURATION)) {
            $configuration = new ConfigurationParser($this->pluginRootDirectory.self::ORIGINAL_PLUGIN_CONFIGURATION);
            $pluginName = $configuration->get('PLUGIN', 'NAME');
            $logLevel = $pluginDataBase->getPluginInformation($pluginName)->getLogLevel();
        }
        if (isset($logLevel) && Logger::LOGLEVEL_DEBUG === $logLevel) {
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
            $whoops = new \Whoops\Run();
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
            $whoops->register();
        }
    }
}
