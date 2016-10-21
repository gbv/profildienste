<?php

namespace Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PublicPathMiddleware {

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
        $uri = $request->getUri();
        $path = preg_replace('/^\/api\//', '', $uri->getPath());
        return $next($request->withUri($uri->withPath($path)), $response);
    }
}