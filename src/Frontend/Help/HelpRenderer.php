<?php

namespace LoxBerryPoppins\Frontend\Help;

use LoxBerryPoppins\Frontend\Routing\RouteMatcher;
use Twig\Environment;

/**
 * Class HelpRenderer.
 */
class HelpRenderer
{
    /** @var HelpConfigurationParser */
    private $configurationParser;

    /** @var RouteMatcher */
    private $routeMatcher;

    /**
     * HelpRenderer constructor.
     *
     * @param HelpConfigurationParser $configurationParser
     * @param RouteMatcher            $routeMatcher
     */
    public function __construct(HelpConfigurationParser $configurationParser, RouteMatcher $routeMatcher)
    {
        $this->configurationParser = $configurationParser;
        $this->routeMatcher = $routeMatcher;
    }

    /**
     * @return string|null
     */
    public function getHelpUrl(): ?string
    {
        return $this->configurationParser->getConfiguration()['url'] ?? null;
    }

    /**
     * @param string      $routeName
     * @param Environment $twig
     *
     * @return string|null
     */
    public function getHelpContents(Environment $twig): ?string
    {
        $routeName = $this->routeMatcher->getMatchedRoute()->getRouteName();

        if (!array_key_exists($routeName, $this->configurationParser->getConfiguration()['pages'] ?? [])) {
            return null;
        }

        $viewFile = $this->configurationParser->getConfiguration()['pages'][$routeName];

        return $twig->render($viewFile);
    }
}
