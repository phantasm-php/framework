<?php

namespace Phantasm\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareChain implements RequestHandlerInterface
{
    private array $stack;
    private int $index = 0;

    public function __construct(array $middlewareStack, private RequestHandlerInterface $finalHandler)
    {
        $this->stack = $middlewareStack;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($this->stack[$this->index])) {
            // No more middleware: call final handler
            return $this->finalHandler->handle($request);
        }

        $middleware = $this->stack[$this->index];
        $this->index++;

        return $middleware->process($request, $this);
    }
}
