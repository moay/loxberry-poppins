<?php

namespace LoxBerryPoppins\Frontend\Twig\Extensions;

use LoxBerry\System\Paths;
use LoxBerry\System\Plugin\PluginPathProvider;
use LoxBerryPoppins\Frontend\Navigation\UrlBuilder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class AssetUrlBuilder.
 */
class AssetUrlBuilder extends AbstractExtension
{
    const ASSET_FOLDER = 'assets';

    /** @var PluginPathProvider */
    private $pluginPathProvider;

    /** @var string */
    private $packageName;

    /** @var string */
    private $packageDirectory;

    /**
     * RouteUrlBuilder constructor.
     *
     * @param PluginPathProvider $pluginPathProvider
     * @param string             $packageName
     * @param string             $packageDirectory
     */
    public function __construct(PluginPathProvider $pluginPathProvider, $packageName, $packageDirectory)
    {
        $this->pluginPathProvider = $pluginPathProvider;
        $this->pluginPathProvider->setPluginName($packageName);
        $this->packageName = $packageName;
        $this->packageDirectory = $packageDirectory;
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('assetUrl', [$this, 'getAssetUrl']),
        ];
    }

    /**
     * @param string $assetFilePath
     *
     * @return string
     */
    public function getAssetUrl(string $assetFilePath)
    {
        $realPath = rtrim($this->pluginPathProvider->getPath(Paths::PATH_PLUGIN_HTML), '/')
            .'/'.self::ASSET_FOLDER
            .'/'.ltrim($assetFilePath, '/');

        if (!file_exists($realPath)) {
            throw new \InvalidArgumentException();
        }

        return sprintf(
            '/%s/%s/%s/%s',
            trim(UrlBuilder::PUBLIC_BASE_URL, '/'),
            $this->packageDirectory,
            self::ASSET_FOLDER,
            ltrim($assetFilePath, '/')
        );
    }
}
