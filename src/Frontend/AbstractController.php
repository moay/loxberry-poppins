<?php

namespace LoxBerryPoppins\Frontend;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Class AbstractController.
 */
abstract class AbstractController
{
    /** @var Request */
    private $request;

    /** @var Environment */
    private $twig;

    /**
     * @param Environment $twig
     */
    public function setTwig(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    protected function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param string     $view
     * @param array|null $parameters
     *
     * @return Response
     */
    protected function render(string $view, ?array $parameters = []): Response
    {
        if (!$this->twig instanceof Environment) {
            throw new \LogicException('Twig must be passed to controller before trying to render');
        }

        $content = $this->twig->render($view, $parameters);

        return new Response($content);
    }

    /**
     * @param array $data
     *
     * @return JsonResponse
     */
    protected function json(array $data): JsonResponse
    {
        return new JsonResponse($data);
    }

    /**
     * @param string $url
     *
     * @return RedirectResponse
     */
    protected function redirect(string $url): RedirectResponse
    {
        return new RedirectResponse($url);
    }
}
