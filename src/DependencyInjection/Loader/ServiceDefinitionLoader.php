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

        $definition = (new Definition())
            ->setClass(PageRouter::class)
            ->setPublic(true);
        $definitionsToAdd[] = $definition;
        $containerBuilder->setDefinition(PageRouterInterface::class, $definition);

        $definition = (new Definition())
            ->setFactory('@'.LogFileDatabaseFactory::class);
        $definitionsToAdd[] = $definition;
        $containerBuilder->setDefinition(LogFileDatabase::class, $definition);

        $definition = (new Definition())
            ->setClass(Environment::class)
            ->setFactory('@'.TwigEnvironmentFactory::class);
        $definitionsToAdd[] = $definition;
        $containerBuilder->setDefinition(Environment::class, $definition);

        $definition = (new Definition())
            ->setClass(Logger::class)
            ->setFactory('@'.CronLoggerFactory::class);
        $definitionsToAdd[] = $definition;
        $containerBuilder->setDefinition('logger.cron', $definition);

        $definition = (new Definition())
            ->setPublic(true)
            ->setArgument('$cronJobs', $containerBuilder->findTaggedServiceIds('plugin.cron_job'));
        $definitionsToAdd[] = $definition;
        $containerBuilder->setDefinition(CronJobRunner::class, $definition);

        $definition = (new Definition())
            ->setArgument('$controllers', $containerBuilder->findTaggedServiceIds('plugin.controller'));
        $definitionsToAdd[] = $definition;
        $containerBuilder->setDefinition(ControllerExecutor::class, $definition);

        $definition = (new Definition())
            ->setArgument('$extensions', $containerBuilder->findTaggedServiceIds('twig.extension'));
        $definitionsToAdd[] = $definition;
        $containerBuilder->setDefinition(TwigEnvironmentFactory::class, $definition);

        $containerBuilder->addDefinitions($definitionsToAdd);
    }
}
