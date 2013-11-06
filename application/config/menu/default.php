<?php defined('SYSPATH' OR die('No direct access allowed.'));
/**
 * Example config for Twitter Bootstrap main navbar menu
 *
 * @see https://github.com/anroots/kohana-menu/wiki/Configuration-files
 * @author Ando Roots <ando@sqroot.eu>
 */
return array(
	'guess_active_item' => true,
	'items'             => array(
		array(
			'url'     => 'dashboard',
			'title'   => 'nav.site.dashboard',
			'icon'    => 'icon-dashboard',
			'tooltip' => 'nav.site.dashboard.tooltip',
		),
		array(
			'url'     => Request::current()->url() . '#',
			'title'   => 'nav.site.people',
			'icon'    => 'icon-group',
			'tooltip' => 'nav.site.people.tooltip',
			'items'	  => array(
							array(
								'url'     => 'clients',
								'title'   => 'nav.site.people.clients',
								'icon'    => 'icon-exchange',
								'tooltip' => 'nav.site.people.clients.tooltip',
								'permission' => 'CLIENTS_VIEW'
							),
							array(
								'url'     => 'personnel',
								'title'   => 'nav.site.people.personnel',
								'icon'    => 'icon-undo',
								'tooltip' => 'nav.site.people.personnel.tooltip',
								'permission' => 'PERSONNEL_VIEW'
							)
						)
		),
		/*
		array(
					'url'     => Request::current()->url() . '#',
					'title'   => 'nav.site.jobs',
					'icon'    => 'icon-tasks',
					'tooltip' => 'nav.site.jobs.tooltip',
					'items'	  => array(
									array(
										'url'     => 'jobs/clients',
										'title'   => 'nav.site.jobs.external',
										'icon'    => 'icon-exchange',
										'tooltip' => 'nav.site.jobs.external.tooltip'
									),
									array(
										'url'     => 'jobs/internal',
										'title'   => 'nav.site.jobs.internal',
										'icon'    => 'icon-undo',
										'tooltip' => 'nav.site.jobs.internal.tooltip'
									)
									
								),
					'permission' => 'JOBS_VIEW'
				),
		array(
			'url'     => 'tasks',
			'title'   => 'nav.site.tasks',
			'icon'    => 'icon-edit',
			'tooltip' => 'nav.site.tasks.tooltip',
			'permission' => 'TASKS_VIEW'
		),*/
		array(
			'url'     => 'equipment',
			'title'   => 'nav.site.equipment',
			'icon'    => 'icon-wrench',
			'tooltip' => 'nav.site.equipment.tooltip',
			'permission' => 'EQUIPMENT_VIEW'
		),
		array(
			'url'     => Request::current()->url() . '#',
			'title'   => 'nav.site.inventory',
			'icon'    => 'icon-shopping-cart',
			'tooltip' => 'nav.site.inventory.tooltip',
			'permission' => 'INVENTORY_VIEW',
			'items'	  => array(
							array(
								'url'     => 'supplies',
								'title'   => 'nav.site.inventory.supplies',
								'icon'    => 'icon-shopping-cart',
								'tooltip' => 'nav.site.inventory.supplies.tooltip',
							),
							array(
								'url'     => 'suppliers',
								'title'   => 'nav.site.suppliers',
								'icon'    => 'icon-group',
								'tooltip' => 'nav.site.suppliers.tooltip',
							),
							array(
								'url'     => 'supplyTypes',
								'title'   => 'nav.site.categories',
								'icon'    => 'icon-shopping-cart',
								'tooltip' => 'nav.site.categories.tooltip',
							),
							array(
								'url'     => 'packageTypes',
								'title'   => 'nav.site.packages',
								//'icon'    => 'icon-group',
								'tooltip' => 'nav.site.packages.tooltip',
							)
							,
							array(
								'url'     => 'supplyLocations',
								'title'   => 'nav.site.locations',
								//'icon'    => 'icon-group',
								'tooltip' => 'nav.site.locations.tooltip',
							),
							array(
								'url'     => 'supplyUnits',
								'title'   => 'nav.site.units',
								//'icon'    => 'icon-group',
								'tooltip' => 'nav.site.units.tooltip',
							)
							,
							array(
								'url'     => 'supplyShelves',
								'title'   => 'nav.site.shelves',
								//'icon'    => 'icon-group',
								'tooltip' => 'nav.site.locations.tooltip',
							),
							array(
								'url'     => 'report',
								'title'   => 'nav.site.reports',
								//'icon'    => 'icon-group',
								'tooltip' => 'nav.site.reports.tooltip',
							)
						)
		),
		/*
		array(
					'url'     => 'reports',
					'title'   => 'nav.site.reports',
					'icon'    => 'icon-file',
					'tooltip' => 'nav.site.reports.tooltip',
					'permission' => 'REPORTS_VIEW'
				),*/
		
		array(
			'url'     => 'settings',
			'title'   => 'nav.site.settings',
			'icon'    => 'icon-cog',
			'tooltip' => 'nav.site.settings.tooltip',
			'permission' => 'SETTINGS_VIEW',
			'items'	  => array(
							array(
								'url'     => 'roles',
								'title'   => 'nav.site.roles',
								'tooltip' => 'nav.site.roles.tooltip'
							)
						)
		),
		
	),
);