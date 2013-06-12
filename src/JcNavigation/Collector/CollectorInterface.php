<?php
/**
 * Zend Developer Tools for Zend Framework (http://framework.zend.com/)
 *
 * @link       http://github.com/zendframework/JcNavigation for the canonical source repository
 * @copyright  Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd New BSD License
 */

namespace JcNavigation\Collector;

use Zend\Mvc\MvcEvent;

/**
 * Collector Interface.
 *
 */
interface CollectorInterface
{
    /**
     * Collector Name.
     *
     * @return string
     */
    public function getName();

    /**
     * Collector Priority.
     *
     * @return integer
     */
    public function getPriority();

    /**
     * Collects data.
     *
     * @param MvcEvent $mvcEvent
     */
    public function collect(MvcEvent $mvcEvent);
    
    /**
     * Collects entity.
     *
     */
    public function getEntity();
    
    /**
     * Collects column.
     *
     */
    public function getTitle($entity);
    
    /**
     * Collects router.
     */
    public function getRouter();
    
    /**
     * devuelve los parametros necesrios para formar la ruta
     */
    public function getRouterParams($entity);
    
}