<?php
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
	)
);