<?php

namespace JcNavigation\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventInterface;
use JcNavigation\Exception\InvalidOptionException;
use JcNavigation\Exception\ProfilerException;

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
        return new ViewModel();
    }
}