<?php
namespace JcNavigation\Navigation;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Navigation\Service\DefaultNavigationFactory;
use Doctrine\ORM\EntityManager;
use JcNavigation\Entity\Navigation as EntityNavigation;
use JcNavigation\Collector\CollectorInterface;
use Zend\Navigation\Exception\InvalidArgumentException;
use Nette\Diagnostics\Debugger;
use JcNavigation\Collector\AbstractEntityCollector;
use JcNavigation\Collector\AbstractCollector;

class Navigation extends DefaultNavigationFactory
{

    private function buildNavigationArray($serviceLocator, $node = null)
    {
        // FETCH data from table menu :
        $em = $serviceLocator->get('jc_navigation_doctrine_em');
        $em instanceof EntityManager;
        $repo = $em->getRepository('JcNavigation\Entity\Navigation');
        
        if ($node === null) {
            $node = $repo->childrenHierarchy();
        } else {
            $node = $node['__children'];
        }
        
        $view = $serviceLocator->get('viewRenderer');
        
        $options = $serviceLocator->get('JcNavigation\Config');
        $collectors = $options->getCollectors();
        
        $array = array();
        foreach ($node as $key => $row) {
            $collector = null;
            if (isset($collectors[$row['collector']]))
                $collector = $serviceLocator->get($collectors[$row['collector']]);
            $collector instanceof CollectorInterface;
            if(!isset($row['lvl'])) {
            	Debugger::dump($row);
                exit;
            }
            if ($row['lvl'] != 0) {
                switch (true) {
                    case $collector instanceof AbstractEntityCollector:
                        $entity = $em->find($collector->getEntity(), $row['referenceId']);
                        $array['jc_navigation_' . $row['id']] = array(
                            'label' => $row['title'],
                            'route' => $collector->getRouter(),
                            'params' => $collector->getRouterParams($entity),
                            'pages' => $this->buildNavigationArray($serviceLocator, $row),
                            'class' => $row['css'],
                            'target' => ($row['target'] ? '_blank' : null),
                            'title' => $row['titleAttribute']
                        );
                        break;
                    case $collector instanceof AbstractCollector:
                        $url = (string) $row['url'];
                        $array['jc_navigation_' . $row['id']] = array(
                            'label' => $row['title'],
                            'uri' => (strpos($url, "http://") === 0 || strpos($url, "https://") === 0 ? $url : $view->basePath($url)),
                            'pages' => $this->buildNavigationArray($serviceLocator, $row),
                            'class' => $row['css'],
                            'target' => ($row['target'] ? '_blank' : null),
                            'title' => $row['titleAttribute']
                        );
                        break;
                }
            } else {
                $array['jc_navigation_' . $row['id']] = array(
                    'label' => $row['title'],
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
            
            if (! isset($configuration['navigation'])) {
                throw new InvalidArgumentException('Could not find navigation configuration key');
            }
            if (! isset($configuration['navigation'][$this->getName()])) {
                throw new InvalidArgumentException(sprintf('Failed to find a navigation container by the name "%s"', $this->getName()));
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