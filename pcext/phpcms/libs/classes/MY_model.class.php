<?php
/**
 *  MY_model.class.php 数据模型基类
 *
 * @copyright			(C) 2005-2014 
 * @license				
 * @lastmodify			2014-4-15
 */
defined('IN_PHPCMS') or exit('Access Denied');

class MY_model extends model {
	public function __construct($table_name='') {
		if(!$this->db_config){
			$this->db_config = pc_base::load_config('database');
			$this->db_setting = 'default';
			$this->table_name = $table_name;
		}
		parent::__construct();
	}	
}

?>