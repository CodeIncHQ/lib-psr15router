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
// Date:     08/10/2018
// Project:  Router
//
declare(strict_types=1);
namespace CodeInc\Router\Resolvers;
use CodeInc\Router\Exceptions\NotAResolverException;


/**
 * Class HandlerResolverAggregator
 *
 * @package CodeInc\Router\Resolvers
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class ResolverAggregator implements ResolverInterface, \Countable, \Iterator
{
    /**
     * @var ResolverInterface[]
     */
    private $resolvers = [];

    /**
     * @var int
     */
    private $iteratorPosition = 0;

    /**
     * HandlerResolverAggregator constructor.
     *
     * @param iterable|null $resolvers
     */
    public function __construct(?iterable $resolvers = null)
    {
        if ($resolvers !== null) {
            $this->addResolvers($resolvers);
        }
    }

    /**
     * Adds a resolver.
     *
     * @param ResolverInterface $resolver
     */
    public function addResolver(ResolverInterface $resolver):void
    {
        $this->resolvers[] = $resolver;
    }

    /**
     * Adds multiple resolvers. Only object implementing ResolverInterface will be added.
     *
     * @param iterable|ResolverInterface[] $resolvers
     */
    public function addResolvers(iterable $resolvers):void
    {
        foreach ($resolvers as $resolver) {
            if (!$resolver instanceof ResolverInterface) {
                throw new NotAResolverException($resolver);
            }
            $this->addResolver($resolver);
        }
    }

    /**
     * @inheritdoc
     * @param string $route
     * @return string|null
     */
    public function getControllerClass(string $route):?string
    {
        foreach ($this->resolvers as $resolver) {
            if (($handlerClass = $resolver->getControllerClass($route)) !== null) {
                return $handlerClass;
            }
        }
        return null;
    }

    /**
     * @inheritdoc
     * @param string $controllerClass
     * @return string|null
     */
    public function getControllerRoute(string $controllerClass):?string
    {
        foreach ($this->resolvers as $resolver) {
            if (($route = $resolver->getControllerRoute($controllerClass)) !== null) {
                return $route;
            }
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function rewind():void
    {
        $this->iteratorPosition = 0;
    }

    /**
     * @inheritdoc
     */
    public function next():void
    {
        $this->iteratorPosition++;
    }

    /**
     * @inheritdoc
     * @return bool
     */
    public function valid():bool
    {
        return array_key_exists($this->iteratorPosition, $this->resolvers);
    }

    /**
     * @inheritdoc
     * @return ResolverInterface
     */
    public function current():ResolverInterface
    {
        return $this->resolvers[$this->iteratorPosition];
    }

    /**
     * @inheritdoc
     * @return int
     */
    public function key():int
    {
        return $this->iteratorPosition;
    }

    /**
     * @inheritdoc
     * @return int
     */
    public function count():int
    {
        return count($this->resolvers);
    }
}