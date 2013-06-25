<?php
namespace JcNavigation;

return array(
	'doctrine' => array(
		'driver' => array(
			__NAMESPACE__ . '_driver' => array(
				'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
				'cache' => 'array',
				'paths' => array(
					__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity'
				)
			),
			'orm_default' => array(
				'drivers' => array(
					__NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
				)
			)
		)
	),
	'asset_manager' => array(
		'resolver_configs' => array(
			'paths' => array(
				__DIR__ . '/../public/'
			)
		)
	),
	'view_manager' => array(
		'template_path_stack' => array(
			'default' => __DIR__ . '/../view',
		),
	),
	'router' => array(
		'routes' => array(
			'zfcadmin' => array(
				'child_routes' => array(
					__NAMESPACE__ => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/' . __NAMESPACE__ . '[/:action]',
							'defaults' => array(
								'controller' => __NAMESPACE__ . '\Controller\Admin\Index',
								'action' => 'index',
							),
						),
					)
				),
			),
		),
	),
	
	'controllers' => array(
		'invokables' => array(
			__NAMESPACE__ . '\Controller\Admin\Index' => __NAMESPACE__ . '\Controller\Admin_IndexController',
		),
	),
	'navigation' => array(
		'admin' => array(
			'jc-navigation' => array(
				'label' => 'Menús de nevagación',
				'route' => 'zfcadmin/' . __NAMESPACE__,
			),
		),
	),
	'JcNavigation' => array(
		'profiler' => array(
			'collectors' => array(
				'jc_navigation_links_collector' => 'JcNavigation\\Collector\\UriCollector'
			)
		),
		'toolbar' => array(
			'entries' => array(
				'jc_navigation_links_collector' => 'jc-navigation/toolbar/jc-navigation-links'
			)
		)
	)
);