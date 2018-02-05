<?php

namespace ErrorHeroModule\Middleware\Routed\Preview;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ErrorPreviewAction implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $action = $request->getAttribute('action', 'exception');

        if ($action === 'exception') {
            throw new \Exception('a sample error preview');
        }

        if ($action === 'php7error' && \class_exists(\Error::class)) {
            throw new \Error('error of php 7');
        }

        $array = [];
        $array[1]; // E_NOTICE
    }
}
