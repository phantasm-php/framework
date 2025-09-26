<?php

namespace Phantasm\Http;

use GuzzleHttp\Psr7\Response;
use Phantasm\Foundation\Attributes\Singleton;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Singleton]
class Router implements RequestHandlerInterface
{
    protected array $routes = [];

    public function get(string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute(['GET'], $path, $handler, $middleware);
    }

    public function post(string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute(['POST'], $path, $handler, $middleware);
    }

    public function put(string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute(['PUT'], $path, $handler, $middleware);
    }

    public function patch(string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute(['PATCH'], $path, $handler, $middleware);
    }

    public function delete(string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute(['DELETE'], $path, $handler, $middleware);
    }

    public function options(string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute(['OPTIONS'], $path, $handler, $middleware);
    }

    public function any(string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $path, $handler);
    }

    protected function addRoute(array $methods, string $path, callable $handler, array $middleware = []): void
    {
        $this->routes[] = compact('methods', 'path', 'handler', 'middleware');
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();

        foreach ($this->routes as $route) {
            $pattern = preg_replace('#\{[a-zA-Z0-9_]+\}#', '([a-zA-Z0-9_]+)', $route['path']);
            if ($route['method'] === $method && preg_match('#^'.$pattern.'$#', $path, $matches)) {
                array_shift($matches); // remove full match

                // Wrap the route handler in middleware chain
                $finalHandler = new class($route['handler'], $matches) implements RequestHandlerInterface {
                    private $handler;
                    private $params;
                    public function __construct(callable $handler, array $params)
                    {
                        $this->handler = $handler;
                        $this->params = $params;
                    }
                    public function handle(ServerRequestInterface $request): ResponseInterface
                    {
                        return call_user_func_array($this->handler, array_merge([$request], $this->params));
                    }
                };

                if (!empty($route['middleware'])) {
                    $chain = new MiddlewareChain($route['middleware'], $finalHandler);
                    return $chain->handle($request);
                }

                return $finalHandler->handle($request);
            }
        }

        return new Response(404, [], 'Not Found');
    }
}
