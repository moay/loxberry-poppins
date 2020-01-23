<?php

namespace LoxBerryPoppins\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class TagAutoBinderCompilerPass.
 */
class TagAutoBinderCompilerPass implements CompilerPassInterface
{
    const BOUND_TAGS = [
        '$cronJobs' => 'plugin.cron_job',
        '$controllers' => 'plugin.controller',
        '$extensions' => 'twig.extension',
    ];

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $definition) {
            foreach (self::BOUND_TAGS as $argumentName => $tagName) {
                $arguments = $definition->getArguments();
                if (array_key_exists($argumentName, $arguments)) {
                    $arguments[$argumentName] = $this->getReferencesByTag($tagName, $container);
                    $definition->setArguments($arguments);
                }
            }
        }
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
