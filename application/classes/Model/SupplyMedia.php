<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing supplies media table
 *
 * @version 01 - Joseph Bosire 2013-06-08
 *
 * PHP version 5
 */
class Model_SupplyMedia extends Model_Base {
	/**
	 * Model's table
	 * @string
	 */
	protected $_table_name = 'fpro_supplies_media';
	
	/**
	 * Model's primary key name
	 * @string
	 */
	protected $_primary_key = 'supply_media_id';
	
	protected $_belongs_to = array('image'=>array('model'=>'Media','foreign_key'=>'media_id'));
	
	public function get_supply_item_media($supply_id){
		return $this->where('supply_id','=',$supply_id)->find_all();
	}
	public function delete_image($supply_media_id){
		$supply_media = ORM::factory('SupplyMedia',$supply_media_id);
		$media = ORM::factory('Media',$supply_media->media_id);
		try{
			$supply_media -> delete();
			$media -> delete();			
			return true;
		}Catch (Exception $e){
			return false;
		}
		
	}
	
}
