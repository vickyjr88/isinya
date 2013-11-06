<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Beautiful Asset Cache
 *
 * PHP version 5
 *
 * @category  Asset Cache
 * @package   Beautiful
 * @author    Luke Morton
 * @author    Hezron Obuchele <hezron4k@gmail.com>
 * @copyright Luke Morton, 2011
 * @license   MIT
 * @version   Release: 0.1.2
 * @link      https://bitbucket.org/hezbucho/efficiency-pro
 */
class Asset_Cache extends Beautiful_Asset_Cache
{


	/**
	 * Get asset locations as array
	 *
	 * @return array
	 */
	protected function _asset_locations() {
		$locations = array();

		foreach ($this->_group->as_array() as $_asset) {
			$locations[] = $_asset->location();
		}

		return $locations;
	}


}
