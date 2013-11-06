<?php defined('SYSPATH' OR die('No direct access allowed.'));
/**
 * Minimalistic menu config example.
 * Renders a simple list (<li>) of links.
 *
 * @see https://github.com/anroots/kohana-menu/wiki/Configuration-files
 * @author Ando Roots <ando@sqroot.eu>
 */
return array(
	'items'             => array(
		array(
			'url'     => 'issues',
			'title'   => 'nav.issues',
			'icon'    => 'icon-tasks',
		),
		array(
			'url'     => 'users',
			'title'   => 'nav.persons',
			'icon'    => 'icon-user',
			'tooltip' => 'nav.tooltip.persons'
		),
		array(
			'url'     => 'projects',
			'icon'    => 'icon-folder-close',
			'title'   => 'nav.projects',
		),
	),
);