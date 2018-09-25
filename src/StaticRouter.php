<?php
//
// +---------------------------------------------------------------------+
// | CODE INC. SOURCE CODE                                               |
// +---------------------------------------------------------------------+
// | Copyright (c) 2018 - Code Inc. SAS - All Rights Reserved.           |
// | Visit https://www.codeinc.fr for more information about licensing.  |
// +---------------------------------------------------------------------+
// | NOTICE:  All information contained herein is, and remains the       |
// | property of Code Inc. SAS. The intellectual and technical concepts  |
// | contained herein are proprietary to Code Inc. SAS are protected by  |
// | trade secret or copyright law. Dissemination of this information or |
// | reproduction of this material is strictly forbidden unless prior    |
// | written permission is obtained from Code Inc. SAS.                  |
// +---------------------------------------------------------------------+
//
// Author:   Joan Fabrégat <joan@codeinc.fr>
// Date:     05/03/2018
// Time:     11:53
// Project:  Router
//
declare(strict_types = 1);
namespace CodeInc\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;


/**
 * Class AbstractStaticRouter
 *
 * @package CodeInc\Router
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
abstract class StaticRouter implements MiddlewareInterface
{
    /**
     * @var RequestHandlerInterface[]
     */
    protected $handlers = [];

    /**
     * Adds a request handler.
     *
     * @param string $route
     * @param RequestHandlerInterface $requestHandler
     * @throws RouterException
     */
    public function addRequestHandler(string $route, RequestHandlerInterface $requestHandler):void
    {
        $realRoute = strtolower($route);
        if (isset($this->handlers[$realRoute])) {
            throw RouterException::duplicateRoute($route);
        }
        $this->handlers[$realRoute] = $requestHandler;
    }

    /**
     * Adds a routable request handler.
     *
     * @param \CodeInc\Router\RoutableRequestHandlerInterface $routableRequestHandler
     * @throws RouterException
     */
    public function addRoutableRequestHandler(RoutableRequestHandlerInterface $routableRequestHandler):void
    {
        $this->addRequestHandler($routableRequestHandler::getRoute(), $routableRequestHandler);
    }

    /**
     * @inheritdoc
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler):ResponseInterface
    {
        $uriPath = strtolower($request->getUri()->getPath());

        // if there is a direct route matching the request
        if (isset($this->handlers[$uriPath])) {
            $handler = $this->handlers[$uriPath];
        }

        // if there is a pattern route matching the request
        else {
            foreach ($this->handlers as $route => $requestHandler) {
                if (fnmatch($route, $uriPath)) {
                    $handler = $requestHandler;
                    break;
                }
            }
        }

        return $handler->handle($request);
    }
}