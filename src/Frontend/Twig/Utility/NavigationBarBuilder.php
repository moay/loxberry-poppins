<?php

namespace LoxBerryPoppins\Frontend\Twig\Utility;

use LoxBerryPoppins\Frontend\Navigation\NavigationConfigurationParser;
use LoxBerryPoppins\Frontend\Navigation\UrlBuilder;
use LoxBerryPoppins\Frontend\Routing\RouteMatcher;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class NavigationBarBuilder.
 */
class NavigationBarBuilder
{
    /** @var array */
    private $navigationConfiguration;

    /** @var RouteMatcher */
    private $routeMatcher;

    /** @var UrlBuilder */
    private $urlBuilder;

    /**
     * NavigationBarBuilder constructor.
     *
     * @param NavigationConfigurationParser $navigationConfigurationParser
     * @param RouteMatcher                  $routeMatcher
     * @param UrlBuilder                    $urlBuilder
     */
    public function __construct(
        NavigationConfigurationParser $navigationConfigurationParser,
        RouteMatcher $routeMatcher,
        UrlBuilder $urlBuilder
    ) {
        $this->navigationConfiguration = $navigationConfigurationParser->getConfiguration();
        $this->request = Request::createFromGlobals();
        $this->routeMatcher = $routeMatcher;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return string
     */
    public function getNavigationBarHtml(): string
    {
        if (0 === count($this->navigationConfiguration)) {
            return '';
        }

        $navigationBar = '<div data-role="navbar"><ul>';
        foreach ($this->navigationConfiguration as $index => $navigationItem) {
            if (!array_key_exists('route', $navigationItem)) {
                throw new \RuntimeException('Route must be configured on all navigation items');
            }

            $navigationBar .= sprintf(
                '<li><div style="position:relative"><a href="%s" %s %s>%s</a></div></li>',
                $this->urlBuilder->getAdminUrl($navigationItem['route']),
                array_key_exists('target', $navigationItem) ? 'target="'.$navigationItem['target'].'"' : '',
                $this->routeMatcher->isCurrentMatchedRoute($navigationItem['route'], false) ?
                    'class="ui-btn-active"' : '',
                $navigationItem['title'] ?? $index
            );
        }
        $navigationBar .= '</ul></div>';

        return $navigationBar;
    }
}
