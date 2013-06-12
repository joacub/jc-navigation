<?php

namespace JcNavigation\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventInterface;
use JcNavigation\Exception\InvalidOptionException;
use JcNavigation\Exception\ProfilerException;
use Nette\Diagnostics\Debugger;
use JcNavigation\Entity\Navigation;

class Admin_IndexController extends AbstractActionController
{
	/**
	 * Zend\Mvc\MvcEvent::EVENT_BOOTSTRAP event callback
	 *
	 * @param  EventInterface $event
	 * @throws Exception\InvalidOptionException
	 * @throws Exception\ProfilerException
	 */
	public function onBootstrap(EventInterface $event)
	{
		if (PHP_SAPI === 'cli') {
			return;
		}
	
		$app = $event->getApplication();
		$em  = $app->getEventManager();
		$sem = $em->getSharedManager();
		$sm  = $app->getServiceManager();
	
		$options = $sm->get('JcNavigation\Config');
	
		if (!$options->isEnabled()) {
			return;
		}
	
		$report = $sm->get('JcNavigation\Report');
	
		if ($options->canFlushEarly()) {
			$em->attachAggregate($sm->get('JcNavigation\FlushListener'));
		}
	
		if ($options->isStrict() && $report->hasErrors()) {
			throw new InvalidOptionException(implode(' ', $report->getErrors()));
		}
	
		$em->attachAggregate($sm->get('JcNavigation\ProfilerListener'));
	
		if ($options->isToolbarEnabled()) {
			$sem->attach('profiler', $sm->get('JcNavigation\ToolbarListener'), null);
		}
	
		if ($options->isStrict() && $report->hasErrors()) {
			throw new ProfilerException(implode(' ', $report->getErrors()));
		}
	}
	
    public function indexAction()
    {
    	$this->onBootstrap($this->getEvent());
    	
    	$repo = $this->getEntityManager()->getRepository('JcNavigation\Entity\Navigation');
    	
    	$navigations = $repo->findBy(array('parent' => null));
    	
    	$options = $this->getServiceLocator()->get('JcNavigation\Config');
    	$collectors = $options->getCollectors();
    	$sm = $this->getServiceLocator();
    	
        return new ViewModel(array('navigations' => $navigations, 'activeMenu' => $this->params()->fromQuery('menu', null), 'repo' => $repo, 'em' => $this->getEntityManager(), 'collectors' => $collectors, 'sm' => $sm));
    }
    
    public function createAction()
    {
    	$em = $this->getEntityManager();
    	$menuName = $this->params()->fromPost('menu-name', null);
    	
    	if($menuName === null)
    		return $this->redirect()->toRoute('zfcadmin/JcNavigation');
    	
    	$entity = new Navigation();
    	
    	$entity->setTitle($menuName);
    	
    	$em->persist($entity);
    	$em->flush();
    	
    	return $this->redirect()->toRoute('zfcadmin/JcNavigation', array(), array('query' => array('menu' => $entity->getId())));
    }
    
    public function addMenuItemAction() 
    {
    	$em = $this->getEntityManager();
    	$items = $this->params()->fromPost('menu-item', null);
    	$menu = $this->params()->fromPost('menu', null);
    	
    	$menuEntity = $em->find('JcNavigation\Entity\Navigation', $menu);
    	
    	$options = $this->getServiceLocator()->get('JcNavigation\Config');
    	
    	$collectors = $options->getCollectors();
    	try {
    	foreach($items as &$item) {
    		$collector = $this->getServiceLocator()->get($collectors[$item['collector']]);
    		
    		$entity = $em->find($collector->getEntity(), $item['id']);
    		
    		$entityNavigation = new Navigation();
    		$entityNavigation->setTitle($collector->getTitle($entity));
    		$entityNavigation->setCollector($collector->getName());
    		$entityNavigation->setParent($menuEntity);
    		$entityNavigation->setReferenceId($item['id']);
    		
    		$em->persist($entityNavigation);
    		$em->flush($entityNavigation);
    		
    		$item['data'] = $entityNavigation;
    		$item['entity'] = $entity;
    		$item['collector'] = $collector;
    		
    	}
    	
    	} catch (\Exception $e) {
    		 
    		echo $e->getMessage();exit;
    		 
    	}
    	
    	$viewModel = new ViewModel(array('menuItems' => $items));
    	$viewModel->setTerminal(true);
    	return $viewModel;
    }
    
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
    	return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    }
}