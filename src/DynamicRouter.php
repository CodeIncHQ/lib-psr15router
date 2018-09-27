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
// Date:     24/09/2018
// Project:  Router
//
declare(strict_types=1);
namespace CodeInc\Router;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Class DynamicRouter
 *
 * @package CodeInc\Router
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
abstract class DynamicRouter extends Router
{
    /**
     * @var string
     */
    protected $controllersNamespace;

    /**
     * @var string
     */
    protected $uriPrefix;

    /**
     * DynamicRouter constructor.
     *
     * @param string $controllersNamespace
     * @param string $uriPrefix
     * @throws RouterException
     */
    public function __construct(string $controllersNamespace, string $uriPrefix)
    {
        if (empty($uriPrefix)) {
            throw RouterException::emptyUriPrefix();
        }
        if (empty($controllersNamespace)) {
            throw RouterException::emptyControllersNamespace();
        }
        $this->controllersNamespace = $controllersNamespace;
        $this->uriPrefix = $uriPrefix;
    }

    /**
     * Sets the controller returned for the URI prefix.
     *
     * @uses DynamicRouter::addRoute()
     * @param string $controllerClass
     * @throws RouterException
     */
    public function setHomeController(string $controllerClass):void
    {
        $this->addRoute($this->uriPrefix, $controllerClass);
    }

    /**
     * @inheritdoc
     * @param ServerRequestInterface $request
     * @return ControllerInterface|null
     */
    public function getController(ServerRequestInterface $request):?ControllerInterface
    {
        // if there is a route mach for a manually defined route
        if ($controller = parent::getController($request)) {
            return $controller;
        }

        // else if the request URI points toward a controller
        $requestUri = $request->getUri()->getPath();
        if (substr($requestUri, 0, strlen($this->uriPrefix)) == $this->uriPrefix) {
            $controllerClass = $this->controllersNamespace
                .str_replace('/', '\\', substr($requestUri, strlen($this->uriPrefix)));
            if (class_exists($controllerClass) && is_subclass_of($controllerClass, ControllerInterface::class)) {
                return $this->instantiate($request, $controllerClass);
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     * @param string $controllerClass
     * @return string
     * @throws RouterException
     */
    public function getControllerUri(string $controllerClass):string
    {
        if (!is_subclass_of($controllerClass, ControllerInterface::class)) {
            throw RouterException::notAController($controllerClass);
        }
        if (!substr($controllerClass, 0, strlen($this->controllersNamespace)) == $controllerClass) {
            throw RouterException::notWithinNamespace($controllerClass, $this->controllersNamespace);
        }
        return $this->uriPrefix
            .str_replace('\\', '/', substr($controllerClass, strlen($this->controllersNamespace)));
    }
}