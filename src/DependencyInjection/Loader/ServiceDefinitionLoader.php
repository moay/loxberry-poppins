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

    public function registerServiceDefinitions(ContainerBuilder $containerBuilder)
    {
        $definitionsToAdd = [];

        $definition = new Definition(PageRouterInterface::class);
        $definition->setClass(PageRouter::class);
        $definition->setPublic(true);
        $definitionsToAdd[] = $definition;

        $definition = new Definition(LogFileDatabase::class);
        $definition->setFactory('@'.LogFileDatabaseFactory::class);
        $definitionsToAdd[] = $definition;

        $definition = new Definition(Environment::class);
        $definition->setClass(Environment::class);
        $definition->setFactory('@'.TwigEnvironmentFactory::class);
        $definitionsToAdd[] = $definition;

        $definition = new Definition('logger.cron');
        $definition->setClass(Logger::class);
        $definition->setFactory('@'.CronLoggerFactory::class);
        $definitionsToAdd[] = $definition;

        $definition = new Definition(CronJobRunner::class);
        $definition->setPublic(true);
        $definition->setArgument('$cronJobs', $containerBuilder->findTaggedServiceIds('plugin.cron_job'));
        $definitionsToAdd[] = $definition;

        $definition = new Definition(ControllerExecutor::class);
        $definition->setArgument('$controllers', $containerBuilder->findTaggedServiceIds('plugin.controller'));
        $definitionsToAdd[] = $definition;

        $definition = new Definition(TwigEnvironmentFactory::class);
        $definition->setArgument('$extensions', $containerBuilder->findTaggedServiceIds('twig.extension'));
        $definitionsToAdd[] = $definition;

        $containerBuilder->addDefinitions($definitionsToAdd);
    }
}
