<?php

namespace LoxBerryPoppins\Frontend\Routing;

use LoxBerryPoppins\Exception\RouteNotFoundException;
use LoxBerryPoppins\Frontend\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Class ControllerExecutor.
 */
class ControllerExecutor
{
    /** @var AbstractController[] */
    private $controllers = [];

    /**
     * ControllerExecutor controller.
     *
     * @param iterable    $controllers
     * @param Environment $twig
     */
    public function __construct(iterable $controllers, Environment $twig)
    {
        foreach ($controllers as $controller) {
            if (!$controller instanceof AbstractController) {
                throw new \RuntimeException('Misconfigured controller or misusage of ControllerExecutor');
            }
            $controller->setTwig($twig);
            $this->controllers[] = $controller;
        }
    }

    /**
     * @param string $controllerClassName
     * @param string $actionName
     *
     * @return Response
     */
    public function getResponse(string $controllerClassName, string $actionName): Response
    {
        foreach ($this->controllers as $controller) {
            if (get_class($controller) === $controllerClassName) {
                if (!method_exists($controller, $actionName)) {
                    throw new RouteNotFoundException(sprintf('Method %s does not exist on controller %s', $actionName, $controllerClassName));
                }

                $controller->setRequest(Request::createFromGlobals());
                $response = $controller->{$actionName}();
                if (!$response instanceof Response) {
                    throw new \RuntimeException('Your controller must return an object of type Response.');
                }

                return $response;
            }
        }

        throw new \RuntimeException(sprintf('Controller %s not found', $controllerClassName));
    }
}
