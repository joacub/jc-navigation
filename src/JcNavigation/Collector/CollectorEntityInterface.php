<?php
/**
 * Zend Developer Tools for Zend Framework (http://framework.zend.com/)
 *
 * @link       http://github.com/zendframework/JcNavigation for the canonical source repository
 * @copyright  Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd New BSD License
 */

namespace JcNavigation\Collector;

/**
 * Collector Interface.
 *
 */
interface CollectorEntityInterface extends CollectorInterface
{
    /**
     * Collects entity.
     *
     */
    public function getEntity();
    
    /**
     * Collects router.
     */
    public function getRouter();
    
    /**
     * devuelve los parametros necesrios para formar la ruta
     */
    public function getRouterParams($entity);
    
}