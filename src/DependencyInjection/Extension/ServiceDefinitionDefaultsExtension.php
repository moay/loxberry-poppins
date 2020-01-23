<?php

namespace LoxBerryPoppins\DependencyInjection\Extension;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class ServiceDefinitionDefaultsExtension.
 */
class ServiceDefinitionDefaultsExtension extends Extension
{
    const LIBRARY_CONFIG_DIRECTORY = __DIR__.'/../../../config';
    const DEFAULT_SERVICES_CONFIGURATION = 'services_default.yaml';

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $defaultsLoader = new YamlFileLoader($container, new FileLocator(self::LIBRARY_CONFIG_DIRECTORY));
        $defaultsLoader->load(self::DEFAULT_SERVICES_CONFIGURATION);
    }
}
