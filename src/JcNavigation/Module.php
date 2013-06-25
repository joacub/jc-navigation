<?php
namespace JcNavigation;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Nette\Diagnostics\Debugger;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use JcNavigation\View\Helper\Navigation;

class Module implements
    ConfigProviderInterface,
    AutoloaderProviderInterface,
    ServiceProviderInterface,
    ViewHelperProviderInterface
{
	
	
    /**
     * {@InheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * {@InheritDoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }
    
    /**
     * @inheritdoc
     */
    public function getServiceConfig()
    {
    	return array(
    		'aliases' => array(
    			'JcNavigation\ReportInterface' => 'JcNavigation\Report',
    			'jc_navigation_doctrine_em' => 'Doctrine\ORM\EntityManager'
    		),
    		'invokables' => array(
    			'JcNavigation\Report'             => 'JcNavigation\Report',
    			'JcNavigation\EventCollector'     => 'JcNavigation\Collector\EventCollector',
    			'JcNavigation\ExceptionCollector' => 'JcNavigation\Collector\ExceptionCollector',
    			'JcNavigation\RouteCollector'     => 'JcNavigation\Collector\RouteCollector',
    			'JcNavigation\RequestCollector'   => 'JcNavigation\Collector\RequestCollector',
    			'JcNavigation\ConfigCollector'    => 'JcNavigation\Collector\ConfigCollector',
    			'JcNavigation\MailCollector'      => 'JcNavigation\Collector\MailCollector',
    			'JcNavigation\MemoryCollector'    => 'JcNavigation\Collector\MemoryCollector',
    			'JcNavigation\TimeCollector'      => 'JcNavigation\Collector\TimeCollector',
    			'JcNavigation\FlushListener'      => 'JcNavigation\Listener\FlushListener',
    			'JcNavigation\Collector\UriCollector' => 'JcNavigation\Collector\UriCollector'
    		),
    		'factories' => array(
    			'JcNavigation\Profiler' => function ($sm) {
    				$a = new Profiler($sm->get('JcNavigation\Report'));
    				$a->setEvent($sm->get('JcNavigation\Event'));
    				return $a;
    			},
    			'JcNavigation\Config' => function ($sm) {
    				$config = $sm->get('Configuration');
    				$config = isset($config[__NAMESPACE__]) ? $config[__NAMESPACE__] : null;
    				
    				return new Options($config, $sm->get('JcNavigation\Report'));
    			},
    			'JcNavigation\Event' => function ($sm) {
    				$event = new ProfilerEvent();
    				$event->setReport($sm->get('JcNavigation\Report'));
    				$event->setApplication($sm->get('Application'));
    
    				return $event;
    			},
    			'JcNavigation\StorageListener' => function ($sm) {
    				return new Listener\StorageListener($sm);
    			},
    			'JcNavigation\ToolbarListener' => function ($sm) {
    				return new Listener\ToolbarListener($sm->get('ViewRenderer'), $sm->get('JcNavigation\Config'));
    			},
    			'JcNavigation\ProfilerListener' => function ($sm) {
    				return new Listener\ProfilerListener($sm, $sm->get('JcNavigation\Config'));
    			},
    			'JcNavigation' => 'JcNavigation\Navigation\NavigationFactory'
    		),
    	);
    }
    
    public function getViewHelperConfig()
    {
    	return array(
    		'factories' => array(
    			'JcNavigation' => function($sm, $s) {
    				$navigation = new Navigation();
    				return $navigation;
    			}
    		)
    	);
    }
}
