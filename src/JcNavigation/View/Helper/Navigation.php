<?php

namespace JcNavigation\View\Helper;

use Zend\View\Helper\Navigation as ZfNavigation;

class Navigation extends ZfNavigation 
{
	/**
	 * (non-PHPdoc)
	 * @see \Zend\View\Helper\Navigation::__invoke()
	 */
	public function __invoke($container = null)
	{
		if (null !== $container) {
			$this->setContainer($container);
		}
	
		return $this;
	}
    
}