<?php

namespace Phantasm\Http;

use Phantasm\Contracts\Foundation\Application;
use Phantasm\Contracts\Http\Kernel as KernelContract;
use Phantasm\Foundation\Attributes\Singleton;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

#[Singleton(KernelContract::class)]
class Kernel implements KernelContract, RequestHandlerInterface
{
    protected array $middleware = [];

    public function __construct(
        protected Application $app,
    ) {}

    /**
     * Add middleware to the stack.
     */
    public function pushMiddleware(MiddlewareInterface $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * Handle the request through middleware stack.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = $this->app->get(Router::class);

        $chain = new MiddlewareChain($this->middleware, $handler);

        return $chain->handle($request);
    }

    /**
     * Run the kernel (from global entry point).
     */
    public function run(): void
    {
        $request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
        $response = $this->handle($request);

        // Emit response
        http_response_code($response->getStatusCode());
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }
        echo $response->getBody();
    }
}
