<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'supply_id' => array(
		'not_empty' => 'You must select a supply for which this supply-purchase is intended',
		'numeric' => 'Invalid supply identifier'
	),
	'supplier_id' => array(
		'not_empty' => 'You must select the supplier from which is supply-purchase was procured',
		'numeric' => 'Invalid supplier identifier'
	),
	'package_type_id' => array(
		'not_empty' => 'You must select the package-type for this supply-purchase',
		'numeric' => 'Invalid package-type identifier'
	),
	'supply_purchased_quantity' => array(
		'not_empty' => 'You must supply the purchase-quantity for this supply purchase',
		'numeric' => 'Invalid purchase-quantity'
	),
	'quantity_per_package' => array(
		'not_empty' => 'You must supply the purchased quantity-per-package for this supply purchase',
		'numeric' => 'Invalid quantity-per-package'
	),
	'supply_purchase_date' => array(
		'not_empty' => 'You must supply the purchase-date for this supply purchase',
		'date' => 'Invalid purchase date'
	),
	'cost_per_package' => array(
		'not_empty' => 'You must supply the cost-per-package for this supply purchase',
		'date' => 'Invalid cost-per-package'
	),
	'cost_per_unit' => array(
		'not_empty' => 'You must supply the cost-per-unit for this supply purchase',
		'date' => 'Invalid cost-per-unit'
	)
);
