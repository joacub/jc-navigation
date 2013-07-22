<?php
namespace JcNavigation\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventInterface;
use JcNavigation\Exception\InvalidOptionException;
use JcNavigation\Exception\ProfilerException;
use Nette\Diagnostics\Debugger;
use JcNavigation\Entity\Navigation;
use JcNavigation\Collector\AbstractEntityCollector;
use JcNavigation\Collector\AbstractCollector;

class Admin_IndexController extends AbstractActionController
{

	/**
	 * Zend\Mvc\MvcEvent::EVENT_BOOTSTRAP event callback
	 *
	 * @param EventInterface $event        	
	 * @throws Exception\InvalidOptionException
	 * @throws Exception\ProfilerException
	 */
	public function onBootstrap (EventInterface $event)
	{
		if (PHP_SAPI === 'cli') {
			return;
		}
		
		$app = $event->getApplication();
		$em = $app->getEventManager();
		$sem = $em->getSharedManager();
		$sm = $app->getServiceManager();
		
		$options = $sm->get('JcNavigation\Config');
		
		if (! $options->isEnabled()) {
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
			$sem->attach('profiler', $sm->get('JcNavigation\ToolbarListener'), 
					null);
		}
		
		if ($options->isStrict() && $report->hasErrors()) {
			throw new ProfilerException(implode(' ', $report->getErrors()));
		}
	}

	public function indexAction ()
	{
		$this->onBootstrap($this->getEvent());
		
		$repo = $this->getEntityManager()->getRepository(
				'JcNavigation\Entity\Navigation');
		
		$navigations = $repo->findBy(array(
			'parent' => null
		));
		
		$options = $this->getServiceLocator()->get('JcNavigation\Config');
		$collectors = $options->getCollectors();
		$sm = $this->getServiceLocator();
		
		return new ViewModel(
				array(
					'navigations' => $navigations,
					'activeMenu' => $this->params()->fromQuery('menu', null),
					'repo' => $repo,
					'em' => $this->getEntityManager(),
					'collectors' => $collectors,
					'sm' => $sm
				));
	}

	public function createAction ()
	{
		$em = $this->getEntityManager();
		$menuName = $this->params()->fromPost('menu-name', null);
		
		if ($menuName === null)
			return $this->redirect()->toRoute('zfcadmin/JcNavigation');
		
		$entity = new Navigation();
		
		$entity->setTitle($menuName);
		
		$em->persist($entity);
		$em->flush();
		
		return $this->redirect()->toRoute('zfcadmin/JcNavigation', array(), 
				array(
					'query' => array(
						'menu' => $entity->getId()
					)
				));
	}

	public function updateAction ()
	{
		$em = $this->getEntityManager();
		$menuId = $this->params()->fromPost('menu', null);
		$menuName = $this->params()->fromPost('menu-name', null);
		$menu_item_db_id = $this->params()->fromPost('menu-item-db-id', null);
		$post = $this->params()->fromPost();
		
		if ($menuName === null || $menuId === null)
			return $this->redirect()->toRoute('zfcadmin/JcNavigation');
		
		$repo = $em->getRepository('JcNavigation\Entity\Navigation');
		
		$menuEntity = $em->find('JcNavigation\Entity\Navigation', $menuId);
		
		$_children = $repo->getChildren($menuEntity);
		
		$children = array();
		foreach ($_children as $child) {
			$children[$child->getId()] = $child;
		}
		
		unset($_children);
		
		$menuEntity->setTitle($menuName);
		
		$em->persist($menuEntity);
		
		$postFields = array(
			'menu-item-db-id',
			'menu-item-object-id',
			'menu-item-object',
			'menu-item-parent-id',
			'menu-item-position',
			'menu-item-collector',
			'menu-item-title',
			'menu-item-url',
			'menu-item-description',
			'menu-item-attr-title',
			'menu-item-target',
			'menu-item-classes',
			'menu-item-xfn'
		);
		
		$first = true;
		
		foreach ((array) $menu_item_db_id as $_key => $k) {
		    
			// Menu item title can't be blank
			if (empty($post['menu-item-title'][$_key]))
				continue;
			
			$args = array();
			foreach ($postFields as $field)
				$args[$field] = isset($post[$field][$_key]) ? $post[$field][$_key] : '';
			
			$item = $children[$args['menu-item-db-id']];
			$item instanceof Navigation;
			
			$item->setTitle($args['menu-item-title']);
			$item->setTitleAttribute($args['menu-item-attr-title']);
			$item->setCss($args['menu-item-classes']);
			$item->setDescription($args['menu-item-description']);
			$item->setTarget((bool) $args['menu-item-target']);
			$item->setUrl($args['menu-item-url']);
			
			$parent = $em->find('JcNavigation\Entity\Navigation', 
					$args['menu-item-parent-id']);
			$item->setParent($parent);
			
			$em->persist($item);
			$em->flush();
			try {
			    if(!$first)
			     $repo->moveDown($item, 1);
			} catch(\Exception $e) {
			    Debugger::dump($item);
			    Debugger::dump($parent);
			    echo $e->getMessage();
				exit;
			} 
			
			unset($children[$args['menu-item-db-id']]);
			
			$first = false;
		}
		
		foreach ($children as $child) {
			$em->remove($child);
		}
		
		$em->flush();
		
		return $this->redirect()->toRoute('zfcadmin/JcNavigation', array(), 
				array(
					'query' => array(
						'menu' => $menuEntity->getId()
					)
				));
	}

	public function addMenuItemAction ()
	{
		$em = $this->getEntityManager();
		$items = $this->params()->fromPost('menu-item', null);
		$menu = $this->params()->fromPost('menu', null);
		
		$menuEntity = $em->find('JcNavigation\Entity\Navigation', $menu);
		
		$options = $this->getServiceLocator()->get('JcNavigation\Config');
		
		$collectors = $options->getCollectors();
		try {
			foreach ($items as &$item) {
				$collector = $this->getServiceLocator()->get(
						$collectors[$item['collector']]);
				$entity = null;
				switch (true) {
					case $collector instanceof AbstractEntityCollector:
						$entity = $em->find($collector->getEntity(), $item['id']);
						
						$entityNavigation = new Navigation();
						$entityNavigation->setTitle($collector->getTitle($entity));
						$entityNavigation->setCollector($collector->getName());
						$entityNavigation->setParent($menuEntity);
						$entityNavigation->setReferenceId($item['id']);
						break;
					case $collector instanceof AbstractCollector:
						$entityNavigation = new Navigation();
						$entityNavigation->setTitle($item['menu-item-title']);
						$entityNavigation->setCollector($collector->getName());
						$entityNavigation->setParent($menuEntity);
						$entityNavigation->setUrl($item['menu-item-url']);
						break;
				}
				
				$em->persist($entityNavigation);
				$em->flush($entityNavigation);
				
				$item['data'] = $entityNavigation;
				$item['entity'] = $entity;
				$item['collector'] = $collector;
			}
		} catch (\Exception $e) {
			
			echo $e->getMessage();
			exit();
		}
		
		$viewModel = new ViewModel(array(
			'menuItems' => $items
		));
		$viewModel->setTerminal(true);
		return $viewModel;
	}

	/**
	 *
	 * @return \Doctrine\ORM\EntityManager
	 */
	protected function getEntityManager ()
	{
		return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
	}
}