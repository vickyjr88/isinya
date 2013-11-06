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
			'url'     => Request::current()->url() . '#',
			'title'   => 'nav.site.dashboard',
			'icon'    => 'icon-dashboard',
			'tooltip' => 'nav.site.dashboard.tooltip',
			//'section' => 'General' // All subsequent menu items will be under this section until the next item that has this property and so on
		),
		array(
			'url'     => Request::current()->url() . '#',
			'title'   => 'nav.site.equipment',
			'icon'    => 'icon-wrench',
			'tooltip' => 'nav.site.equipment.tooltip',
		),
		array(
			'url'     => Request::current()->url() . '#',
			'title'   => 'nav.site.inventory',
			'icon'    => 'icon-shopping-cart',
			'tooltip' => 'nav.site.inventory.tooltip',
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
							),
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
							),
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
					'url'     => Request::current()->url() . '#',
					'title'   => 'nav.site.reports',
					'icon'    => 'icon-file',
					'tooltip' => 'nav.site.reports.tooltip',
				),*/
		
		array(
			'url'     => Request::current()->url() . '#',
			'title'   => 'nav.site.settings',
			'icon'    => 'icon-cog',
			'tooltip' => 'nav.site.settings.tooltip',
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