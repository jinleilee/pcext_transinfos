<?php
/**
 *数据接收以及处理
 */
class info2change extends transdb{
	public function __construct(){
		parent::__construct();
	}
	/**
	 *插入数据
	 *@param array  $array2post
	 *	jxsid=>(
	 *		modelid=>array(
	 *			'master'=>array(
	 *				id=>array('id'=>num,'catid'=>num[,...])[,...]
	 *			),
	 *			'slave'=>array(
	 *				id=>array('id'=>num,'content'=>content[,...])[,...]'
	 *			)
	 *		)[,
	 *		...]
	 *		//secretkey => secretkey
	 *		//salt => salt
	 *		//fromwho => fromwho
	 *		//action => action
	 *		//data => array('update'=>1[,...])
	 *	)
	 */
	public function dealpostcon($recieves){
		$this->checksecretkey($recieves['secretkey'],$recieves['salt'],$recieves['data']['jxsid']);
		$combines = array('fromwho'=>$recieves['fromwho'],'data'=>$recieves['data']);
		unset($recieves['secretkey'],$recieves['salt'],$recieves['fromwho'],$recieves['data'],$recieves['action']);
		$dealtype = isset($combines['data']['update']) ? 'update' : 'insert';
		$this->extradata['i2c_dealtype'] = $dealtype;//设置属性新建or更新
		$returnstatus = array();
		$returnstatus['dealfail'] = '';
		foreach($recieves as $modelid=>$ids2add){
			if($this->cat2cat!=''){
				$modelid = $this->cat2cat['cenmid2mid'][$modelid];
			}
			$tablename = $this->modelid2table($modelid);
			$model = $this->getmodel($tablename);
			$primarykey = empty($this->transtables['primarykey'][$modelid]) ? 'id' : $this->transtables['primarykey'][$modelid];
			$combine = $combines + array('modelid'=>$modelid,'primarykey'=>$primarykey);
            $dealtype=='update'&&$combine['update_mtable'] = $tablename;
			$ids2add['master'] = $this->ids2add_combine($ids2add['master'],$combine);
			$masterid = $this->mult_cu($ids2add['master'],$model,$combine,$dealtype);
			if($masterid||$dealtype=='update'){
				if(!$model->table_exists($tablename.'_data')){
					$returnstatus[$modelid] = true;
					continue;
				}
				//检查表结构
				if(empty($this->transtables[$tablename.'_data'.'_check'])){
					$this->dbchangeneeded($tablename.'_data');
				}
				$combine['masterid'] = $masterid;
				$combine['masterfromids'] = array_keys($ids2add['master']);
				$ids2add['slave'] = $this->ids2add_combine($ids2add['slave'],$combine);
				$modelcontent = $this->getmodel($tablename.'_data');
				$hc_slaveinsert = $this->mult_cu($ids2add['slave'],$modelcontent,$combine,$dealtype);
				if($hc_slaveinsert !== false){
					$returnstatus[$modelid] = true;
				}else{
					$returnstatus[$modelid] = false;
				}
			}else{
				$returnstatus[$modelid] = false;
			}
			if(!$returnstatus[$modelid]){
				$returnstatus['dealfail'] = "modelid : {$modelid} id : ".array_keys($ids2add['master'])." ; ";
				$dealtype=='insert'&&$this->rollback($combine);
			}
		}
		if($returnstatus['dealfail'] != ''){
			$returnstatus['dealend'] = 'fail';
		}else{
			$returnstatus['dealend'] = 'success';
		}
		echo serialize($returnstatus);
		//return serialize($returnstatus);
	}
	/**
	 *删除数据
	 *@param $recieves 
	 *@$type insert update
	 *@return $rs 返回状态 
	 */
	public function deleteids($recieves){
		if(!defined("FROMMYADMIN")){
			$this->checksecretkey($recieves['secretkey'],$recieves['salt'],$recieves['data']['jxsid']);
			$combines = array('fromwho'=>$recieves['fromwho'],'data'=>$recieves['data']);
			unset($recieves['secretkey'],$recieves['salt'],$recieves['fromwho'],$recieves['data'],$recieves['action']);
		}
		
		$modelid = intval($recieves['modelid']);
		$returnstatus['dealfail'] = '';
		if(!empty($recieves['ids'])){
			$ids = implode(',',$recieves['ids']);
			$tablename = $this->modelid2table($modelid);
			$model = $this->getmodel($tablename);
			$primarykey = empty($this->transtables['primarykey'][$modelid]) ? 'id' : $this->transtables['primarykey'][$modelid];
			$where4del = defined("FROMMYADMIN") ? " `$primarykey` IN ($ids) " : " `fromid` IN ($ids) ";
			if(!$model->delete($where4del)){
				$returnstatus['dealfail'] .= 'query fail=>'.$where4del;
			}
			if($model->table_exists($tablename.'_data')){
				$model_content = $this->getmodel($tablename.'_data');
				if(!$model_content->delete($where4del)){
					$returnstatus['dealfail'] .= 'query fail=>'.$where4del;
				}
			}			
		}else{
			$returnstatus['dealfail'] .= 'Nothing selected!';
		}

		$returnstatus['dealend'] = $returnstatus['dealfail']!='' ? 'fail' : 'success';
		if(!$this->jxscache['iscenter']){
			echo serialize($returnstatus);
		}else{
			return $returnstatus;
		}
	}
	/**
	 *@$data 
	 *@$model object 
	 *@$type insert update
	 *@return $rs 返回状态 
	 */
	public function mult_cu($data,$model,$combine,$type = 'insert'){
		$cu_type = ($type == 'insert') ? 'lots4id' : 'lots4idreplace';
		return $model->simp_cu($data,$cu_type);
	}
	/**
	 *@$ids2add fromwho(100->官网，1,2..->经销商) frommodelid(模型id) fromid(内容id)
	 *@$combine fromwho data modelid masterid(插入主表返回id) masterfromids(传输ids)
	 *@return $rs 返回状态 
	 */
	public function ids2add_combine($ids2add,$combine){
		//isset($combine['masterid']) && $combine['masterid']--;
		//if语句-->获取需要update的ids
		$primarykey = $combine['primarykey'];
		if($this->extradata['i2c_dealtype']=='update'){
			if(!isset($this->extradata['i2c_updateids'][$combine['modelid']])){
				ksort($ids2add);
				$fromids = array_keys($ids2add);
				$where = " `fromid` in (".implode(',',$fromids).") ";
				$cases = array('data'=>'*','key'=>'fromid','order'=>' `fromid` ASC ');//`id`,`url`,`fromid`
				$model = $this->getmodel($combine['update_mtable']);
				$updateids = $model->select($where,$cases);
				$this->extradata['i2c_updateids'][$combine['modelid']] = $updateids;			
			}else{
				$updateids = $this->extradata['i2c_updateids'][$combine['modelid']];
			}
		}
		$slave_update_delids = '';
		foreach($ids2add as $id => $id2add){
			$id2add['fromwho'] = $combine['fromwho'];
			if($id2add['fromwho']!='9999'&&isset($id2add['status'])) $id2add['status']=1; 
			$id2add['frommodelid'] = $combine['modelid'];
			$id2add['fromid'] = $id;
			if($this->extradata['i2c_dealtype']=='update'){
				$tempmaster = $updateids[$id2add[$primarykey]];
				$id2add[$primarykey] = $tempmaster[$primarykey];
				if(!isset($combine['masterid'])){
					$slave_update_delids .= $id2add[$primarykey].',';
					isset($tempmaster['url']) && $id2add['url'] = $tempmaster['url'];
				} 
				//unset($id2add['url']);
			}else{
				if(isset($combine['masterid'])){
					if(in_array($id,$combine['masterfromids'])){
						$id2add[$primarykey] = $combine['masterid']++;
					}
					$ids2add[$primarykey] = $id2add;
					$pattern = array("/^(.*)\/index\.php/","/catid=(\d+)/","/&id=(\d+)/");
					$replace = array(APP_PATH.'index.php',"catid={$this->extradata['catid4updateurl']}","&id={$id2add['id']}");
					if(isset($this->extradata['oldurl4updateurl'][$id])){
						$updateurl_u['url'][] =preg_replace($pattern,$replace,$this->extradata['oldurl4updateurl'][$id]);
						$updateurl_c[] =array("$primarykey"=>$id2add[$primarykey]); 					
					}
				}else{
					$id2add[$primarykey] = '';
					isset($id2add['url']) && $this->extradata['oldurl4updateurl'][$id] = $id2add['url'];
				}			
			}

			if(isset($id2add['catid']) && $this->cat2cat!=''){
				$id2add['catid'] = $this->cat2cat['cencat2cat'][$id2add['catid']];
			}
			if(empty($this->extradata['catid4updateurl'])&&!empty($id2add['catid'])){
				$this->extradata['catid4updateurl'] = $id2add['catid'];//设置catidforurlupdate
			}		
			$idscombine[$id] = $id2add;		
		}
		if(isset($updateurl_u)){
			$model = $this->getmodel($combine['modelid']);
			$model->update_lots($updateurl_u,$updateurl_c);
		}
		if($this->extradata['i2c_dealtype']=='update'&&!isset($combine['masterid'])){
			$model_content = $this->getmodel($combine['update_mtable'].'_data');
			if($model_content->table_exists($combine['update_mtable'].'_data'))
			$model_content->delete(" `$primarykey` IN(".rtrim($slave_update_delids,',').') ');
		}
		return $idscombine;
	}
	/**
	 *删除没添加成功的内容
	 *@param $combine fromwho modelid masterid(插入主表返回id) masterfromids(传输ids)
	 *@return $rs 返回状态 
	 */
	public function rollback($combine){
		$tablename = $this->modelid2table($combine['modelid']);
		$model = $this->getmodel($tablename);
		$ids = implode(',',$combine['masterfromids']);
		$where = " `fromwho` = '{$combine['fromwho']}' and `fromid` in ({$ids}) ";
		$del_master = $model->delete($where);
		if(isset($combine['masterid'])){
			$modelcontent = $this->getmodel($tablename.'_data');
			$del_slave = $modelcontent->delete($where);
		}
		if($del_master&&$del_slave){
			return true;
		}else{
			return false;		
		}
	}
	
}
?>
