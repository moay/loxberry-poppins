<?php

namespace Tests\DependencyInjection;

use LoxBerryPoppins\DependencyInjection\Loader\PluginParameterLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class PluginParameterLoaderTest.
 */
class PluginParameterLoaderTest extends TestCase
{
    public function testParametersAreLoadedProperly()
    {
        $loader = new PluginParameterLoader(__DIR__.'/testableConfig.cfg', __DIR__);
        $container = $this->createMock(ContainerBuilder::class);
        $container
            ->expects($this->exactly(5))
            ->method('setParameter')
            ->withConsecutive(
                ['plugin.name', 'myplugin'],
                ['plugin.version', '0.4.0'],
                ['plugin.directory', 'mypluginfolder'],
                ['plugin.title', 'My Plugin'],
                ['runtime.root_dir', realpath(__DIR__)]
            );

        $loader->loadPluginParameters($container);
    }

    public function testThrowsIfConfigFileDoesNotExist()
    {
        $loader = new PluginParameterLoader(__DIR__.'/nonexistingFile.cfg', __DIR__);
        $container = $this->createMock(ContainerBuilder::class);
        $this->expectException(\RuntimeException::class);

        $loader->loadPluginParameters($container);
    }
}
