<?php
/**
 *数据传输
 */
class simpcommunicate{
	public $secretkeys;
	public $requestvars;
	public $info2trans;
	public function __construct(){
		$this->info2trans = new info2trans();
		$this->init();
	}
	public function init(){
		/*$requestvars array(jxsids[,modelid,ids||id->modelids])*/
		$this->requestvars = $this->gethttpvars();
		$this->extbase = pc_base::load_sys_class('extbase');
	}
	/**
	 *
	 *@return $rs 返回状态
	 */
	public function simpcurl($arr2post,$url){
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_HEADER,0);
		curl_setopt($ch,CURLOPT_TIMEOUT,3);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($arr2post));
		$output = curl_exec($ch);
		$errorcode = curl_errno($ch);
		curl_close($ch);
		//var_dump($arr2post);
		//var_dump($url);
		//var_dump($output);
		//var_dump($errorcode);
		//exit;
		if(0!==$errorcode){
			return $errorcode;
		}else{
			return $output;
		}
	}
	/**
	 *提交推送申请后获取格式化数组
	 *post  
	 *ids array(1,2,3[,...]),modelid or ids array(1=>mid1,2=>mid2,3=>  
	 *mid3[,...])
	 *@return $array2post 
		jxsid=>([...,]
			secretkey => secretkey
			salt => salt
			fromwho => fromwho
			data =>array('jxsid(fromwho)'=>'','catid'=>'catid','limit'=>'')
			url =>url
			action =>'catid|count|simpdata|postcons'
		)
	 */
	public function precurl(){
		$requests = $this->requestvars;
		!isset($requests['action'])&&$requests['action'] = 'postcons' ; 
		if($requests['action'] == 'postcons'){
			$array2post = $this->precurl_conts($requests);
		}else{
			$array2post = array('ids'=>$requests['ids'],'modelid'=>$requests['modelid']);
			$array2post = $this->precurl_public($requests,$array2post);
		}
		return $array2post;
	}
	
	/**
	 *提交推送申请后获取格式化数组
	 *post ids array(1,2,3[,...]),modelid or ids array(1=>mid1,2=>mid2,3=>  
	 *mid3[,...])
	 *@return array  $array2post
		jxsid=>(
			modelid=>array(
				'master'=>array(
					id=>array('id'=>num,'catid'=>num[,...])[,...]
				),
				'slave'=>array(
					id=>array('id'=>num,'content'=>content[,...])[,...]'
				)
			)[,
			...],publicdata
		)
	 */
	public function precurl_conts($requests){
		//$requests = $this->requestvars;
		$array2post = array();
		
		if(!empty($requests['modelid'])){
			$array2post = $this->info2trans->getpostcon_fromids(array($requests['modelid']=>$requests['ids']));
		}else{
			if(empty($requests['ids'])||!is_array($requests['ids'])) return false;
			$array2post = array_flip($requests['ids']);
			array_walk($array2post,array($this,'precurl_getpost'),$requests['ids']);
		}
		return $this->precurl_public($requests,$array2post);
	}
	/**
	 *循环指定数据到相应接收方ID下
	 *@param $requests requests
	 *@param $array2post array 需要提交数据
	 *@return $array2post
	 */
	public function precurl_public($requests,$array2post){
		if(!empty($requests['jxsids'])){
			$jxsids = $requests['jxsids'];
		}elseif(!empty($requests['jxsid'])){
			$jxsids = array($requests['jxsid']);
		}else{
			$jxsids = '';
		}
		$jxsinfos = $this->info2trans->getjxsinfos($jxsids);
		if(!empty($jxsinfos)){
			foreach($jxsinfos as $jxsifo){
				$hc_arr2post =  $array2post;
				$hc_arr2post['salt'] = rand(999,9999);
				$hc_arr2post['secretkey'] = md5(md5($jxsifo['secretkey']).$hc_arr2post['salt']);
				$hc_arr2post['url'] = $jxsifo['posturl'];	
				$hc_arr2post['action'] = $requests['action'];
				$hc_arr2post['fromwho'] = $this->info2trans->jxscache['default']['jxs_id'];
				isset($requests['data'])&&$hc_arr2post['data'] = $requests['data'];
				$deal2post[$jxsifo['jxs_id']] = $hc_arr2post;		
			}
		}
		return $deal2post;
	}
	
	/**
	 *执行curl
	 *@need $curldata array('jxsid'=>array('url'=>$url,'action'=>'action'[,...]))
	 *@return 
	 */
	public function runcurl(){
		$cruldata = $this->precurl();
		$output = array();
		$outmsg = '';
		foreach($cruldata as $jxsid=>$jxsdata){
			$url = $jxsdata['url'];
			unset($jxsdata['url']);
			$run = $this->simpcurl($jxsdata,$url);
			$output[$jxsid] = unserialize($run);
			$meta = $this->metacurl($output[$jxsid]);
			if($meta!='success'){
				$outmsg .= " SYSID {$jxsid} =>".$meta;//$run
			}
		}
		if($outmsg != '')
			$outmsg = '操作失败！error:'.$outmsg;
		return $outmsg;
	}
	
	/**
	 *判断结果
	 *@return 
	 */
	public function metacurl($curlresult){
		if($curlresult['dealend']=='success'){
			return 'success';
		}elseif($curlresult['dealend']=='fail'){
			return $curlresult['dealfail'];
		}else{
			return $curlresult['dealfail'];
		}
	}

	/**
	 *callback of precurl
	 */
	private function precurl_getpost(&$item,$key,$ids){
		$item = $this->info2trans->getpostcon_fromids(array_keys($ids,$key));
	}
	/**
	 *时间太长可以跳转
	 *@return 
	 */
	public function headerfortime(){
	
	}
	/**
	 *获取传值
	 *@return 
	 */
	public function gethttpvars(){
		return $_REQUEST;
	}
}
?>