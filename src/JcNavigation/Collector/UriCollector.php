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
 * Serializable Collector base class.
 *
 */
class UriCollector extends AbstractCollector
{
	const NAME     = 'jc_navigation_link_collector';
	
	const PRIORITY = 150;
	
	public function getName()
	{
		return self::NAME;
	}
	
	public function getPriority()
	{
		return self::PRIORITY;
	}
	
	public function collect(MvcEvent $mvcEvent)
	{
		
	}
    
}