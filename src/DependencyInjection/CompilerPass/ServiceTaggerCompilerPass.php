<?php

namespace LoxBerryPoppins\DependencyInjection\CompilerPass;

use LoxBerryPoppins\Cron\CronJobInterface;
use LoxBerryPoppins\Frontend\AbstractController;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Twig\Extension\AbstractExtension;

/**
 * Class ServiceTaggerCompilerPass.
 */
class ServiceTaggerCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $definition) {
            if (is_subclass_of($definition->getClass(), CronJobInterface::class)) {
                $definition->addTag('plugin.cron_job');
            }
            if (is_subclass_of($definition->getClass(), AbstractController::class)) {
                $definition->addTag('plugin.controller');
            }
            if (is_subclass_of($definition->getClass(), AbstractExtension::class)) {
                $definition->addTag('twig.extension');
            }
        }
    }
}
