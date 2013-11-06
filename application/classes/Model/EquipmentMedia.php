<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing equipment mdia table
 *
 * PHP version 5.3
 *
 * @category  Models
 * @package   Efficiency_Pro
 * @author    Hezron Obuchele <hezron4k@gmail.com>
 * @author    Joseph Bosire <kashboss@gmail.com>
 * @author    Victor Koech <kipmasi@gmail.com>
 * @author    Brian Mwadime <brianmwadime@gmail.com>
 * @copyright 2013 CodeHive (BeeBuy Investments Ltd.)
 * @license   https://bitbucket.org/hezbucho/efficiency-pro/blob/master/licence.txt EULA
 * @version   Release: 0.0.5
 * @link      https://bitbucket.org/hezbucho/efficiency-pro
 */
class Model_EquipmentMedia extends Model_Base 
{
	
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_equipment_media';
	
	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'equipment_media_id';
	
	protected $_belongs_to = array('image' => array('model' => 'Media', 'foreign_key' => 'media_id'));
	
	
	/**
	 * Function to get equipment item media id's
	 * 
	 * @param int $equipment_id 
	 * @return returns sql statement with speific media id
	 */
	public function get_equipment_item_media($equipment_id) {
		return $this->where('equipment_id', '=', $equipment_id)->find_all();
	}
	
	
	/**
	 * Function to delete equipment image
	 *  
	 * @param int $equipment_media_id 
	 * @return bool
	 */
	public function delete_image($equipment_media_id) {
		$equipment_media = ORM::factory('SupplyMedia', $equipment_media_id);
		$media = ORM::factory('Media', $equipment_media->media_id);
		try {
			$equipment_media -> delete();
			$media -> delete();			
			return true;
		} catch (Exception $e) {
			return false;
		}
		
	}
	
	
}
