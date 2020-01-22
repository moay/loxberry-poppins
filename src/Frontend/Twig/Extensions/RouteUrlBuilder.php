<?php

namespace LoxBerryPoppins\Frontend\Twig\Extensions;

use LoxBerryPoppins\Frontend\Navigation\UrlBuilder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class RouteUrlBuilder.
 */
class RouteUrlBuilder extends AbstractExtension
{
    /** @var UrlBuilder */
    private $urlBuilder;

    /**
     * RouteUrlBuilder constructor.
     *
     * @param UrlBuilder $urlBuilder
     */
    public function __construct(UrlBuilder $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('publicRouteUrl', [$this->urlBuilder, 'getPublicUrl']),
            new TwigFunction('adminRouteUrl', [$this->urlBuilder, 'getAdminUrl']),
        ];
    }
}
