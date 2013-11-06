<?php defined('SYSPATH') or die('No direct script access.');
class Model_Base extends ORM {
	
	public function __get($column)
	{
		if (substr($column, 0, 1) == '_'){ // for use with internal property caches
			if (!array_key_exists($column, $this->_object))
				return null;
		}
		return $this->get($column);
	}
	
	public function set($column, $value){
	    if ($column == '@index') 
	       $this->_object['@index'] = null;
		if (substr($column, 0, 1) == '_') // for use with internal property caches
			$this->_object[$column] = null;
		parent::set($column, $value);
	}
	
	protected function _get_table_columns(array $objects){
		$_final_table_columns = array();
		foreach ($objects as $obj) {
			if (array_key_exists($obj, ORM::$_column_cache))
				$_final_table_columns = array_merge($_final_table_columns, ORM::$_column_cache[$obj]);
		}
		return $_final_table_columns;
	}
	
	protected function _search_list($search_field, $search_value, $table_columns){
		if (is_string($search_field) && strlen($search_field) > 0)
			$search_field = array($search_field);
		if ($search_field && $search_value){
			$pass = 1;
			foreach ($search_field as $field) {
				if (strtolower($field) == 'id')
					$field = $this->primary_key();
				$comparator = ($table_columns[$field]['type'] == 'string') ? 'LIKE' : '=';
				$search_value = ($table_columns[$field]['type'] == 'string') ? '%' . $search_value . '%' : $search_value;
				$search_value = (strpos($table_columns[$field]['type'], 'int') !== false) ? intval($search_value) : $search_value;
				if (array_key_exists($field, $table_columns) && $pass > 1)
					$this->or_where($field, $comparator, $search_value);
				elseif (array_key_exists($field, $table_columns) && $pass == 1)
					$this->where($field, $comparator, $search_value);
				$pass++;
			}
		}
	}
	
	/**
	 * Function that takes in an array of ORM objects (such as one returned by a
	 * "orm->find_all()" call, and returns a normal array (suitable, for example, for 
	 * JSON encoding)
	 
	 * @param $obj_array Array of ORM objects
	 *
	 * @return "normal array" of orm-array input
	 */
	public static function convert_orm_array_to_array($obj_array) {
		return array_map(create_function( '$obj', 'return $obj->as_array();'), $obj_array);
	}
	
	/**
	 * Function that takes in an array of ORM objects (such as one returned by a
	 * "orm->find_all()" call, and returns a normal array suitable for 
	 * JSON encoding for jQUery.DataTables. Column labels are excluded as data-tables requires.
	 * 
	 * @param $obj_array Array of ORM objects or native array of row data
	 * @param $$fields Array of fields to be in the JSON returned by this function
	 * 
	 * @return "normal array" of orm-array input
	 */
	public static function format_orm_array_for_datatable_json($obj_array, $fields = array()) {
		$dt_array = array();
		if (is_object($obj_array)) {
			$data_array = self::convert_orm_array_to_array($obj_array);
		} else {
			$data_array = $obj_array;
		}
		if (is_array($data_array) && is_array($fields) && count($fields) == 0) {
			$count = 0;
			foreach ($data_array as $data_item) {
				if (is_object($data_item)) {
					$data_item = $data_item->as_array();
					foreach ($data_item as $item) {
						$dt_array[$count][] = $item;
					}
				} else {
					$dt_array[$count][] = $data_item;
				}
				$count++;
			}
			return $dt_array;
		} elseif (is_array($data_array) && is_array($fields)) {
			$count = 0;
			foreach ($data_array as $field => $data_item) {
				if (is_object($data_item)) {
					$data_item = $data_item->as_array();
					foreach ($fields as $field) {
						$dt_array[$count][] = $data_item[$field];
					}
				} else {
					$dt_array[$count][] = $data_item;
				}
				$count++;
			}
			return $dt_array;
		}
		$count = 0;
		foreach($data_array as $data_item) {
			foreach ($data_item as $item_value) {
				$dt_array[$count][] = $item_value;
			}
			$count++;
		}
		return $dt_array;
	}   
}