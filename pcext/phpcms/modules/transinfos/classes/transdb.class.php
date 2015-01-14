<?php
/**
 *基础数据处理
 */
class transdb{
	public $transtables; //所用到的表
	public $transmodels; //所用到的模型
	public $extbase; //extbase
	public $jxscache; //jxscache
	public $cat2cat; //jxscache
	public $extradata; //部分全局数据
	public function __construct(){
		$this->extbase = pc_base::load_sys_class('extbase');
		$this->transtables = array();
		$this->jxscache = pc_base::load_config('jxsself');
		if(!$this->jxscache['iscenter']){
			$this->cat2cat = pc_base::load_config('jxscat2cat');
		}else{
			$this->cat2cat = '';
		}
	}

	/**
	 *获取模型
	 *@param $table tablename or modelid
	 *return object model
	 */
	public function getmodel($table){
		$tablename = is_numeric($table) ? $this->modelid2table($table) : $table;
		if(!isset($this->transmodels[$tablename]))
			$this->transmodels[$tablename] = $this->extbase->loadobject($tablename.'_model');

		return $this->transmodels[$tablename];
	}
	
	/**
	 *模型ID获取表名
	 *@return tablename
	 */
	public function modelid2table($modelid){
		//检查是否存在表
		if(empty($this->transtables[$modelid])){
			$models = getcache('model','commons');
			if(array_key_exists($modelid,$models)){
				$this->transtables[$modelid] = $models[$modelid]['tablename'];
			}else{
				$querytable = $this->getmodel('model')->get_one(" `modelid`='{$modelid}' ");
				$this->transtables[$modelid] = $querytable['type']==3 ? 'form_'.$querytable['tablename'] : $querytable['tablename'];
				$this->transtables['primarykey'][$modelid] = 'dataid';
			}
			
		}
		//检查表结构
		if(empty($this->transtables[$this->transtables[$modelid].'_check'])){
			$this->dbchangeneeded($this->transtables[$modelid]);
		}
		return $this->transtables[$modelid];
	}
	
	/**
	 *检查secretkey
	 *@param $jxsid 发送方jxsid
	 *@return bool
	 */
	public function checksecretkey($postsecretkey,$salt,$jxsid){
		if($jxsid=='9999'||empty($jxsid)||$jxsid=='center'){
			$jxsinfo = $this->jxscache;
			$secretkey = $jxsinfo['default']['secretkey'];
		}else{
			$jxsinfo = $this->getjxsinfo($jxsid);
			$secretkey = $jxsinfo['secretkey'];
		}

		if($postsecretkey!=md5(md5($secretkey).$salt)){
			exit('{"error":"attention,undesirable strangers."}');
		}else{
			return true;
		}
	}
	
	/**
	 *获取经销商信息
	 *@return 
	 */
	public function getjxsinfo($jxsid){
		if(!$this->jxscache['iscenter']){
			return $this->jxscache['default'];
		}
		$model = $this->getmodel('form_jxsgl');
		return $model->get_one(" `jxs_id` = $jxsid ");
	}
	
	/**
	 *获取所有经销商信息
	 *@return 
	 */
	public function getjxsinfos($jxsids=''){
		if(!$this->jxscache['iscenter']){
			return array($this->jxscache['center']['jxs_id']=>$this->jxscache['center']);
		}
		if($jxsids!=''&&is_array($jxsids)){
			$where = ' `jxs_id` in ('.implode(',',$jxsids).') ';
		}else{
			$where = '';
		}
		$model = $this->getmodel('form_jxsgl');
		$jxsinfos = $model->select($where,array('key'=>'jxs_id'));
		return $jxsinfos;
	}
	
	/**
	 *数据库处理
	 *接收方数据表，发送方数据表处理
	 *@return 
	 */
	public function dbchangeneeded($tablename,$whichsql = 'tablealter4trans'){
		$model = $this->getmodel($tablename);
		$fields = $model->get_fields();
		if(!array_key_exists('fromwho',$fields)){
			$model->query($this->sqlneeded($whichsql,$model,$tablename));
		}
		$this->transtables[$tablename.'_check'] = 1;
		
	}
	
	/**
	 *sql语句
	 *@return string $sql
	 */
	public function sqlneeded($whichsql,$model,$tablename){
		$pre = $model->db_tablepre;
		switch($whichsql){
			case 'tablealter4trans':
				$sql = "ALTER TABLE  `".$pre.$tablename."` ADD  `fromwho` INT NOT NULL ,
				ADD  `frommodelid` INT NOT NULL ,ADD  `fromid` INT NOT NULL";
				break;
			case 'forcatid';
				$sql = "ALTER TABLE  `".$pre."_category`  ADD   
				`istransful` TINYINT NOT NULL ";
				break;
		}
		return $sql;
	}
}

?>