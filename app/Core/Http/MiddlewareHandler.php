<?php

namespace App\Core\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class MiddlewareHandler implements RequestHandlerInterface
{
    private array $middlewares;
    private RequestHandlerInterface $handler;
    private int $current = 0;

    public function __construct(array $middlewares, RequestHandlerInterface $handler)
    {
        $this->middlewares = $middlewares;
        $this->handler = $handler;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->current >= count($this->middlewares)) {
            return $this->handler->handle($request);
        }

        $middleware = $this->middlewares[$this->current];
        $this->current++;

        if ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        }

        if (is_string($middleware) && class_exists($middleware)) {
            $instance = new $middleware();
            if ($instance instanceof MiddlewareInterface) {
                return $instance->process($request, $this);
            }
        }

        throw new \Exception('Invalid middleware: ' . $middleware);
    }
}
