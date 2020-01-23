<?php

namespace LoxBerryPoppins\DependencyInjection\Loader;

use LoxBerry\Logging\Database\LogFileDatabase;
use LoxBerry\Logging\Database\LogFileDatabaseFactory;
use LoxBerry\Logging\Logger;
use LoxBerryPoppins\Cron\CronJobRunner;
use LoxBerryPoppins\Cron\CronLoggerFactory;
use LoxBerryPoppins\Frontend\Routing\PageRouter;
use LoxBerryPoppins\Frontend\Routing\PageRouterInterface;
use LoxBerryPoppins\Frontend\Twig\TwigEnvironmentFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Twig\Environment;

/**
 * Class ServiceDefinitionLoader.
 */
class ServiceDefinitionLoader
{
    /** @var string */
    private $pluginRootDirectory;

    /**
     * ServiceDefinitionLoader constructor.
     *
     * @param string $pluginRootDirectory
     */
    public function __construct(string $pluginRootDirectory)
    {
        $this->pluginRootDirectory = $pluginRootDirectory;
    }

    /**
     * @param ContainerBuilder $containerBuilder
     */
    public function registerServiceDefinitions(ContainerBuilder $containerBuilder)
    {
        $definition = $containerBuilder
            ->getDefinition(PageRouter::class)
            ->setPublic(true);
        $containerBuilder->setDefinition(PageRouterInterface::class, $definition);

        $definition = (new Definition())
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setFactory([new Reference(LogFileDatabaseFactory::class), '__invoke']);
        $containerBuilder->setDefinition(LogFileDatabase::class, $definition);

        $definition = (new Definition())
            ->setClass(Environment::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setFactory([new Reference(TwigEnvironmentFactory::class), '__invoke']);
        $containerBuilder->setDefinition(Environment::class, $definition);

        $definition = (new Definition())
            ->setClass(Logger::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setFactory([new Reference(CronLoggerFactory::class), '__invoke']);
        $containerBuilder->setDefinition('logger.cron', $definition);

        $definition = (new Definition())
            ->setPublic(true)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$cronLogger', new Reference('logger.cron'))
            ->setArgument('$cronJobs', $this->getReferencesByTag('plugin.cron_job'));
        $containerBuilder->setDefinition(CronJobRunner::class, $definition);
    }

    /**
     * @param string           $tagName
     * @param ContainerBuilder $containerBuilder
     *
     * @return array
     */
    private function getReferencesByTag(string $tagName, ContainerBuilder $containerBuilder)
    {
        return array_map(function ($serviceId) {
            return new Reference($serviceId);
        }, $containerBuilder->findTaggedServiceIds($tagName));
    }
}
