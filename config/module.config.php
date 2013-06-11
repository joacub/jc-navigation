<?php
namespace JcNavigation;

return array(
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
						'type' => 'Literal',
						'options' => array(
							'route' => '/' . __NAMESPACE__,
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
);