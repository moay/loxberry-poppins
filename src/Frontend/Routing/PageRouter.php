<?php

namespace LoxBerryPoppins\Frontend\Routing;

use LoxBerryPoppins\Exception\RouteNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Class PageRouter.
 */
class PageRouter implements PageRouterInterface
{
    /** @var ControllerExecutor */
    private $controllerExecutor;

    /** @var Environment */
    private $twig;

    /** @var RouteMatcher */
    private $routeMatcher;

    /**
     * PageRouter constructor.
     *
     * @param ControllerExecutor $controllerExecutor
     * @param Environment        $twig
     * @param RouteMatcher       $routeMatcher
     */
    public function __construct(ControllerExecutor $controllerExecutor, Environment $twig, RouteMatcher $routeMatcher)
    {
        $this->controllerExecutor = $controllerExecutor;
        $this->twig = $twig;
        $this->routeMatcher = $routeMatcher;
    }

    /**
     * @param bool $isPublic
     *
     * @return Response
     */
    public function process(bool $isPublic = false): Response
    {
        try {
            $pageConfiguration = $this->routeMatcher->getMatchedRoute($isPublic);
            $response = $this->controllerExecutor->getResponse(
                $pageConfiguration->getControllerClassName(),
                $pageConfiguration->getAction()
            );
            $response->prepare(Request::createFromGlobals());
        } catch (RouteNotFoundException $exception) {
            $response = new Response($this->twig->render('error/routeNotFound.html.twig', [
                'exception' => $exception,
            ]), Response::HTTP_NOT_FOUND);
        }

        return $response->send();
    }
}
