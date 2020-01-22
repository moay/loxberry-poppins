<?php

namespace LoxBerryPoppins\Frontend\Twig;

use LoxBerry\ConfigurationParser\MiniserverInformation;
use LoxBerry\Logging\Logger;
use LoxBerry\System\Plugin\PluginDatabase;
use LoxBerry\System\Plugin\PluginInformation;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extension\SandboxExtension;
use Twig\Sandbox\SecurityPolicy;

/**
 * Class TwigEnvironmentFactory.
 */
class TwigEnvironmentFactory
{
    const TWIG_CACHE_FOLDER = 'cache/views';
    const TWIG_VIEWS_FOLDER = 'views';

    /** @var string */
    private $rootPath;

    /** @var PluginInformation */
    private $pluginInformation;

    /** @var MiniserverInformation */
    private $miniserverInformation;

    /** @var AbstractExtension|array */
    private $extensions;

    /**
     * TwigEnvironmentFactory constructor.
     *
     * @param AbstractExtension[] $extensions
     * @param string              $rootPath
     * @param $packageName
     * @param PluginDatabase        $pluginDatabase
     * @param MiniserverInformation $miniserverInformation
     */
    public function __construct(
        iterable $extensions,
        string $rootPath,
        $packageName,
        PluginDatabase $pluginDatabase,
        MiniserverInformation $miniserverInformation
    ) {
        $this->rootPath = $rootPath;
        $this->pluginInformation = $pluginDatabase->getPluginInformation($packageName);
        $this->miniserverInformation = $miniserverInformation;
        foreach ($extensions as $extension) {
            if (!$extension instanceof AbstractExtension) {
                throw new \InvalidArgumentException('Injected extensions must be twig extensions.');
            }
            $this->extensions[] = $extension;
        }
    }

    /**
     * @return \Twig\Environment
     *
     * @throws \Twig\Error\LoaderError
     */
    public function __invoke(): Environment
    {
        foreach (explode('/', trim(self::TWIG_CACHE_FOLDER, '/')) as $subfolder) {
            $folder = ($folder ?? $this->rootPath).'/'.$subfolder;
            if (!is_dir($folder) && !mkdir($folder) && !is_dir($folder)) {
                throw new \RuntimeException('Cache folder could not be created.');
            }
        }

        $logLevel = $this->pluginInformation->getLogLevel();

        $loader = new \Twig\Loader\FilesystemLoader($this->rootPath.'/'.trim(self::TWIG_VIEWS_FOLDER, '/'));
        $loader->addPath(__DIR__.'/../../../views');
        $twig = new \Twig\Environment($loader, [
            'cache' => $this->rootPath.'/'.trim(self::TWIG_CACHE_FOLDER, '/'),
            'debug' => Logger::LOGLEVEL_DEBUG === $logLevel,
        ]);

        $this->addExtensions($twig);
        $this->registerGlobals($twig);

        return $twig;
    }

    /**
     * @param Environment $twig
     */
    private function addExtensions(Environment $twig)
    {
        $sandboxPolicy = new SecurityPolicy();
        $twig->addExtension(new SandboxExtension($sandboxPolicy));

        foreach ($this->extensions as $extension) {
            $twig->addExtension($extension);
        }
    }

    /**
     * @param Environment $twig
     */
    private function registerGlobals(Environment $twig)
    {
        $twig->addGlobal('plugin', $this->pluginInformation);
        $twig->addGlobal('miniserver', $this->miniserverInformation);
        $twig->addGlobal('request', Request::createFromGlobals());
    }
}
