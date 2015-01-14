<?php
/**
 *数据查询以备传输
 */
class info2trans extends transdb{

	public function __construct(){
		parent::__construct();
	}

	/**
	 *获取所有要发送文章信息
	 *@param array(modelid=>array(id1,id2[,...]))
	 *@param $islocal外部调用为0(通过action)
	 *@return array(modelid=>('master'=>array(),'slave'=>array()))
	 */
	public function getpostcon_fromids($midsandids,$islocal = 1){
		if($islocal == 0){
			$this->checksecretkey($midsandids['secretkey'],$midsandids['salt'],$midsandids['data']['jxsid']);
			unset($midsandids['secretkey'],$midsandids['salt']);
		}
		$postcon = array();
		foreach($midsandids as $modelid=>$ids){
			$tablename = $this->modelid2table($modelid);
			$model = $this->getmodel($tablename);
			$primarykey = empty($this->transtables['primarykey'][$modelid]) ? 'id' : $this->transtables['primarykey'][$modelid];
			$where = " `$primarykey` in (".implode(',',$ids).") ";
			$hc_midmaster = $model->select($where,array('key'=>"$primarykey"));
			if($this->cat2cat!=''){
				$modelid2post = $this->cat2cat['cenmid2mid'][$modelid];
			}else{
				$modelid2post = $modelid;
			}
			$postcon[$modelid2post]['master'] = $this->postarr_combine($hc_midmaster);
			if($model->table_exists($tablename.'_data')){
				$model_content = $this->getmodel($tablename.'_data');
				$postcon[$modelid2post]['slave'] = $model_content->select($where,array('key'=>"$primarykey"));
			}
		}
		return $postcon;
	}
	
	/**
	 *提交数据处理(jxs2ceter catid对应处理)
	 *@param $postconmaster
	 *@return array  
	 */
	public function postarr_combine($postconmaster,$combine = ''){
		if($this->jxscache['iscenter']){
			return $postconmaster;
		}
		//经销商到中心过滤
		foreach($postconmaster as $id=>$master){
			if(isset($master['catid']) && $this->cat2cat!=''){
				$master['catid'] = $this->cat2cat['cat2cencat'][$master['catid']];	
			}
			$combinedmaster[$id] = $master;
		}
		return $combinedmaster;
	}
	
	/**
	 *远程获取所有栏目信息
	 *@return 
	 */
	public function getcatids($recieves){
		$this->checksecretkey($recieves['secretkey'],$recieves['salt'],$recieves['data']['jxsid']);
		$model = $this->getmodel('category');
		$catidstransable = $model->select(array('istransful'=>'1'),array('key'=>'catid'));
		return $catidstransable;
	}
	/**
	 *获取可采集推送表单
	 *@return 
	 */
	public function getextforms(){
		$formstransable = $this->getmodel('model')->select(" `type`=3 and `description` like '%pushandgetallow%' ");
		return $formstransable;
	}
	
	/**
	 *远程获取基本信息
	 *@param $recieves array('catid','limit')
	 *@return 
	 */
	public function getsimpbycatids($recieves,$getdata=''){
		$this->checksecretkey($recieves['secretkey'],$recieves['salt'],$recieves['data']['jxsid']);
		$catid = $recieves['catid'];
		$limit = $recieves['limit'];
		if(strpos($catid,'form_')!==false){
			$formid = substr($catid,5);
			return $this->getsimpbycatids_form($formid,$limit,$getdata);
		}
		$categorys = getcache('category_content_1','commons');
		$modelid = $categorys[$catid]['modelid'];
		$model = $this->getmodel($modelid);
		$where = array('catid'=>$catid);
		if($getdata==''){
			$count = $model->count($where);
			return array('nums'=>$count,'modelid'=>$modelid);
		}else{
			$cases = array('limit'=>$limit);
			return $model->select($where,$cases);
		}
	}
	/**
	 *获取form信息
	 *@param $formid,$limit,$getdata
	 *@return 
	 */
	 public function getsimpbycatids_form($formid,$limit,$getdata){
		$table = $this->modelid2table($formid);
		$model = $this->getmodel($table);
		$modelfield = $this->getmodel('model_field');
		if($getdata==''){
			$count = $model->count('');
			return array('nums'=>$count,'modelid'=>$formid);
		}else{
			$cases = array('limit'=>$limit);
			$forminfos = $model->select('',$cases);
			$forminfos['field2name'] = $modelfield->select(array('modelid'=>$formid),array('data'=>' `field`,`name`,`formtype`,`setting` '));
			return $forminfos;
		}
	 }
	/**
	 *远程获取数据总入口
	 *@$getdata array('action'=>'catid|count|simpdata|postcons',
				//secretkey => secretkey
				//salt => salt
				//fromwho => fromwho
				//data =>array('catid'=>'catid','limit'=>'','jxsid'=>'')
	 		)
	 *@return $rs 返回状态 
	 */
	 
}

?>