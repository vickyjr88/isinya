<?php defined('SYSPATH' OR die('No direct access allowed.'));
/**
 * Test data, a dummy menu
 *
 * @author Ando Roots <ando@sqroot.eu>
 */
return [

	'view'              => 'templates/menu/bootstrap/navbar',

	'active_item_class'     => 'active',

	'guess_active_item' => FALSE,

	'items'             => [
		[
			'url'     => 'issues',
			'title'   => 'nav.issues',
			'icon'    => 'icon-tasks',
			'tooltip' => 'nav.tooltip.issues'
		],
		[
			'url'     => 'users',
			'title'   => 'nav.persons',
			'icon'    => 'icon-user',
			'tooltip' => 'nav.tooltip.persons'
		],
		[
			'url'     => 'projects',
			'icon'    => 'icon-folder-close',
			'title'   => 'nav.projects',
			'tooltip' => 'nav.tooltip.projects',
			'visible' => Menu_ACL::is_admin()
		],
		[
			'url'     => 'reports',
			'title'   => 'nav.reports',
			'icon'    => 'icon-list-ol',
			'tooltip' => 'nav.reports.tooltip',
			'items'   => [
				[
					'url'     => 'logs',
					'icon'    => 'icon-align-justify',
					'title'   => 'nav.reports.logs',
					'tooltip' => 'nav.reports.tooltip.logs'
				],
				[
					'icon'    => 'icon-amazon',
					'title'   => 'nav.reports.bills',
					'tooltip' => 'nav.reports.tooltip.bills'
				]
			]
		],
	],
];