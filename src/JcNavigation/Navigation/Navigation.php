<?php
namespace JcNavigation\Navigation;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Navigation\Service\DefaultNavigationFactory;
use Doctrine\ORM\EntityManager;
use JcNavigation\Entity\Navigation as EntityNavigation;
use JcNavigation\Collector\CollectorInterface;
use Zend\Navigation\Exception\InvalidArgumentException;
use Nette\Diagnostics\Debugger;

class Navigation extends DefaultNavigationFactory
{
	private function buildNavigationArray($serviceLocator, $node = null)
	{
		//FETCH data from table menu :
		$em = $serviceLocator->get('jc_navigation_doctrine_em');
		$em instanceof EntityManager;
		$repo = $em->getRepository('JcNavigation\Entity\Navigation');
		
		if($node === null) {
			$node = $repo->findBy(array('parent' => null));
		} else {
			$node = $repo->getChildren($node, true);
		}
		
		
		$options = $serviceLocator->get('JcNavigation\Config');
		$collectors = $options->getCollectors();
		
		$array = array();
		foreach($node as $key => $row)
		{
			$row instanceof EntityNavigation;
			$collector = null;
			if(isset($collectors[$row->getCollector()]))
				$collector = $serviceLocator->get($collectors[$row->getCollector()]);
			$collector instanceof CollectorInterface;
			
			if($row->getLevel() != 0) {
				$entity = $em->find($collector->getEntity(), $row->getReferenceId());
				$array['jc_navigation_' . $row->getId()] = array(
					'label' => $row->getTitle(),
					'route' => $collector->getRouter(),
					'params' => $collector->getRouterParams($entity),
					'pages' => $this->buildNavigationArray($serviceLocator, $row),
					'class' => $row->getCss(),
					'target' => ($row->getTarget() ? '_blank' : null),
					'title' => $row->getTitleAttribute()
				);
			} else {
				$array['jc_navigation_' . $row->getId()] = array(
					'label' => $row->getTitle(),
					'uri' => '',
					'pages' => $this->buildNavigationArray($serviceLocator, $row)
				);
			}
			
		}
		return $array;
	}
	
	protected function getPages(ServiceLocatorInterface $serviceLocator)
	{
		if (null === $this->pages) {
			
			$configuration['navigation'][$this->getName()] = $this->buildNavigationArray($serviceLocator);
			
			if (!isset($configuration['navigation'])) {
				throw new InvalidArgumentException('Could not find navigation configuration key');
			}
			if (!isset($configuration['navigation'][$this->getName()])) {
				throw new InvalidArgumentException(sprintf(
						'Failed to find a navigation container by the name "%s"',
						$this->getName()
				));
			}

			$application = $serviceLocator->get('Application');
			$routeMatch  = $application->getMvcEvent()->getRouteMatch();
			$router      = $application->getMvcEvent()->getRouter();
			$pages       = $this->getPagesFromConfig($configuration['navigation'][$this->getName()]);

			$this->pages = $this->injectComponents($pages, $routeMatch, $router);
		}
		return $this->pages;
	}
}