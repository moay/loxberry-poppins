<?php

namespace LoxBerryPoppins\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class PluginParameterAutoBinderCompilerPass.
 */
class PluginParameterAutoBinderCompilerPass implements CompilerPassInterface
{
    const BOUND_ARGUMENTS = [
        '$pluginDirectory' => '%plugin.directory%',
        '$pluginName' => '%plugin.name%',
        '$rootPath' => '%runtime.root_dir%',
    ];

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $definition) {
            foreach (self::BOUND_ARGUMENTS as $argumentName => $replacement) {
                $arguments = $definition->getArguments();
                if (array_key_exists($argumentName, $arguments)) {
                    $arguments[$argumentName] = $replacement;
                    $definition->setArguments($arguments);
                }
            }
        }
    }
}
