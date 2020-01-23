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
    private $pluginName;

    /** @var string */
    private $pluginDirectory;

    /**
     * RouteUrlBuilder constructor.
     *
     * @param PluginPathProvider $pluginPathProvider
     * @param string             $pluginName
     * @param string             $pluginDirectory
     */
    public function __construct(PluginPathProvider $pluginPathProvider, $pluginName, $pluginDirectory)
    {
        $this->pluginPathProvider = $pluginPathProvider;
        $this->pluginPathProvider->setPluginName($pluginName);
        $this->pluginName = $pluginName;
        $this->pluginDirectory = $pluginDirectory;
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
            throw new \InvalidArgumentException(sprintf('Asset file %s not found in assets folder', $assetFilePath));
        }

        return sprintf(
            '/%s/%s/%s/%s',
            trim(UrlBuilder::PUBLIC_BASE_URL, '/'),
            $this->pluginDirectory,
            self::ASSET_FOLDER,
            ltrim($assetFilePath, '/')
        );
    }
}
