<?php

namespace LoxBerryPoppins\Frontend\Routing;

use LoxBerryPoppins\Exception\RouteIsNotPublicException;
use LoxBerryPoppins\Exception\RouteIsPublicException;
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

    /** @var string */
    private $packageDirectory;

    /**
     * PageRouter constructor.
     *
     * @param ControllerExecutor $controllerExecutor
     * @param Environment        $twig
     * @param RouteMatcher       $routeMatcher
     * @param string             $packageDirectory
     */
    public function __construct(
        ControllerExecutor $controllerExecutor,
        Environment $twig,
        RouteMatcher $routeMatcher,
        $packageDirectory
    ) {
        $this->controllerExecutor = $controllerExecutor;
        $this->twig = $twig;
        $this->routeMatcher = $routeMatcher;
        $this->packageDirectory = $packageDirectory;
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
            $response = new Response($this->twig->render('error/baseError.html.twig', [
                'title' => 'Page not found',
                'errorMessage' => 'The page you requested was not found',
                'packageDirectory' => $this->packageDirectory,
            ]), Response::HTTP_NOT_FOUND);
        } catch (RouteIsNotPublicException $exception) {
            $response = new Response($this->twig->render('error/baseError.html.twig', [
                'title' => 'Request not permitted',
                'errorMessage' => 'The page you requested requires authentication',
                'packageDirectory' => $this->packageDirectory,
            ]), Response::HTTP_FORBIDDEN);
        } catch (RouteIsPublicException $exception) {
            $response = new Response($this->twig->render('error/baseError.html.twig', [
                'title' => 'Request not permitted',
                'errorMessage' => 'The page you requested requires a call via public url',
                'packageDirectory' => $this->packageDirectory,
            ]), Response::HTTP_FORBIDDEN);
        }

        return $response->send();
    }
}
