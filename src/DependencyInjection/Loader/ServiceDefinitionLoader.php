<?php

namespace LoxBerryPoppins\DependencyInjection\Loader;

use LoxBerry\Logging\Database\LogFileDatabase;
use LoxBerry\Logging\Database\LogFileDatabaseFactory;
use LoxBerry\Logging\Logger;
use LoxBerryPoppins\Cron\CronJobRunner;
use LoxBerryPoppins\Cron\CronLoggerFactory;
use LoxBerryPoppins\Frontend\Routing\ControllerExecutor;
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
        $definition = $containerBuilder->getDefinition(PageRouter::class);
        $definition->setPublic(true);
        $containerBuilder->setDefinition(PageRouterInterface::class, $definition);

        $definition = (new Definition())
            ->setFactory([new Reference(LogFileDatabaseFactory::class)]);
        $containerBuilder->setDefinition(LogFileDatabase::class, $definition);

        $definition = (new Definition())
            ->setClass(Environment::class)
            ->setFactory([new Reference(TwigEnvironmentFactory::class)]);
        $containerBuilder->setDefinition(Environment::class, $definition);

        $definition = (new Definition())
            ->setClass(Logger::class)
            ->setFactory([new Reference(CronLoggerFactory::class)]);
        $containerBuilder->setDefinition('logger.cron', $definition);

        $definition = (new Definition())
            ->setPublic(true)
            ->setArgument('$cronJobs', $containerBuilder->findTaggedServiceIds('plugin.cron_job'))
            ->setArgument('$cronLogger', new Reference('logger.cron'));
        $containerBuilder->setDefinition(CronJobRunner::class, $definition);

        $definition = (new Definition())
            ->setArgument('$controllers', $this->getReferencesByTag('plugin.controller', $containerBuilder));
        $containerBuilder->setDefinition(ControllerExecutor::class, $definition);

        $definition = (new Definition())
            ->setArgument('$extensions', $this->getReferencesByTag('twig.extension', $containerBuilder));
        $containerBuilder->setDefinition(TwigEnvironmentFactory::class, $definition);
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
