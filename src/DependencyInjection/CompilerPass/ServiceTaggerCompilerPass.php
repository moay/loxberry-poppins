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
    const CLASS_TAG_MAP = [
        CronJobInterface::class => 'plugin.cron_job',
        AbstractController::class => 'plugin.controller',
        AbstractExtension::class => 'twig.extension',
    ];

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $definition) {
            foreach (self::CLASS_TAG_MAP as $className => $tag) {
                if (is_subclass_of($definition->getClass(), $className)) {
                    $definition->addTag($tag);
                }
            }
        }
    }
}
