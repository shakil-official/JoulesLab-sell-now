<?php

namespace App\Core\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Nyholm\Psr7\ServerRequest as Psr7ServerRequest;

class Request extends Psr7ServerRequest
{
    private array $routeParams = [];

    public function __construct(
        string $method,
        UriInterface|string $uri,
        array $headers = [],
        mixed $body = null,
        string $version = '1.1',
        array $serverParams = []
    ) {
        if (is_string($uri)) {
            $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
            $uri = $factory->createUri($uri);
        }

        parent::__construct($method, $uri, $headers, $body, $version, $serverParams);
    }

    public static function fromGlobals(): static
    {
        $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $creator = new \Nyholm\Psr7Server\ServerRequestCreator(
            $factory,
            $factory,
            $factory,
            $factory
        );

        $request = $creator->fromGlobals();
        
        $newRequest = new static(
            $request->getMethod(),
            $request->getUri(),
            $request->getHeaders(),
            $request->getBody(),
            $request->getProtocolVersion(),
            $request->getServerParams()
        );
        
        // Copy parsed body and other important properties
        $newRequest = $newRequest
            ->withParsedBody($request->getParsedBody())
            ->withQueryParams($request->getQueryParams())
            ->withUploadedFiles($request->getUploadedFiles())
            ->withCookieParams($request->getCookieParams());
        
        return $newRequest;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    public function getRouteParam(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $data = $this->getParsedBody();
        
        if (is_array($data)) {
            return $data[$key] ?? $default;
        }

        return $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->getQueryParams()[$key] ?? $default;
    }

    public function file(string $key): ?\Psr\Http\Message\UploadedFileInterface
    {
        $files = $this->getUploadedFiles();
        return $files[$key] ?? null;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->getHeaderLine($key) ?: $default;
    }

    public function ip(): string
    {
        return $this->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function userAgent(): string
    {
        return $this->getHeaderLine('User-Agent');
    }

    public function isJson(): bool
    {
        return str_contains($this->getHeaderLine('Content-Type'), 'application/json');
    }

    public function isAjax(): bool
    {
        return strtolower($this->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($method) === $this->getMethod();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query($key, $this->input($key, $default));
    }

    public function all(): array
    {
        $query = $this->getQueryParams();
        $body = $this->getParsedBody() ?? [];
        
        return array_merge($query, is_array($body) ? $body : []);
    }
}
