<?php
/**
 * 传输数据操作类
 */

defined('IN_PHPCMS') or exit('No permission resources.');
$session_storage = 'session_'.pc_base::load_config('system','session_storage');
pc_base::load_sys_class($session_storage);
pc_base::load_app_class('simpcommunicate','',0);
pc_base::load_app_class('transdb','',0);
pc_base::load_app_class('info2trans','',0);
pc_base::load_app_class('info2change','',0);
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
/**
 *
 */
class index{
	public $extbase; //易用扩展类
	public $communicate; //传输类
	public $info2trans; //获取数据类
	public $info2change; //更改数据类
	public $transinfopath; //本模块path
	public $request; //外来数据
	public function __construct(){
		$this->extbase = pc_base::load_sys_class('extbase');
		$this->communicate = new simpcommunicate;
		$this->info2trans = new info2trans;
		$this->info2change = new info2change;
		$this->transinfopath = dirname(__FILE__).DIRECTORY_SEPARATOR;
		$this->request = $_REQUEST;
	}
	/**
	 *初始化
	 *@return $rs 返回状态
	 */
	public function init(){
		if($_POST['action']=='postcons'){
			$rs = $this->postcons();
			return $rs['dealfail'];
		}elseif($_POST['action']=='deleteids'){
			$rs = $this->deleteids();
			return $rs['dealfail'];
		}else{
			exit('Action wrong');
		}
		
	}
	/**
	 *接收并更新数据
	 *@return $rs 返回状态
	 */
	public function postcons(){
		return $this->info2change->dealpostcon($_POST);
	}
	/**
	 *接收并删除数据
	 *@return $rs 返回状态
	 */
	public function deleteids(){
		return $this->info2change->deleteids($_POST);
	}
	/**
	 *发送数据
	 *@return $rs 返回状态
	 */
	public function dealsent(){
		$this->check_admin();
		$msg = $this->communicate->runcurl();
		$outmsg = $this->endsent($msg);
		showmessage($outmsg);
	}
	/**
	 *发送后处理数据
	 *@return $rs 返回状态
	 */
	private function endsent($msg){
		if($msg==''){
			$outmsg="操作成功！";
			if($_REQUEST['action']=='deleteids'){
				define("FROMMYADMIN",1);
				$delself = $this->info2change->deleteids($this->request);
				$delself['dealfail'] != '' && $outmsg="删除本网信息失败！";
			}
		}else{
			$outmsg=$msg;
		} 
		return $outmsg;
	}
	/**
	 *数据采集
	 *@return $rs 返回状态
	 */
	public function gaininfos(){
		$recieves = $this->request;
		$infotype = !empty($recieves['infotype']) ? $recieves['infotype'] : '';
		$jxsinfos = $this->info2trans->getjxsinfos('');
		$iscenter = $this->info2trans->jxscache['iscenter'];
		switch($infotype){
			case 'catids':
				$catinfos = $this->info2trans->getcatids($recieves); 
				!$iscenter && $catinfos = $catinfos+$this->gaininfos4formext($recieves,$infotype);
				$catinfos = $this->gaininfos4url($catinfos,'simpbycatids');
				include $this->gettpl('catids');
			break;
			
			case 'simpbycatids':
				$page = intval($recieves['page']);
				$page < 1 && $page = 1;
				$perpage = 20;
				$recieves['limit'] = ($page-1)*$perpage.','.$perpage;
				$count = $this->info2trans->getsimpbycatids($recieves);
				extract($count);
				$pages = pages($nums,$page,$perpage);
				$simpinfos = $this->info2trans->getsimpbycatids($recieves,1);
				if(isset($simpinfos['field2name'])){
					!$iscenter && $field2name = $simpinfos['field2name'];
					unset($simpinfos['field2name']);
				}
				include $this->gettpl('simpbycatids');
			break;
			
			default:
				$jxsinfos = $this->gaininfos4url($jxsinfos,'catids');
				include $this->gettpl('gaininfos');
			break;
		}
	}
	/**
	 *处理表单模型
	 *@return $rs 返回状态
	 */	
	private function gaininfos4formext($infos,$infotype){
		if($infotype=='catids'){
			$formstransable = $this->info2trans->getextforms($recieves);
			foreach($formstransable as $formtransable ){
				$formtransable['catid'] = 'form_'.$formtransable['modelid'];
				$formtransable['catname'] = $formtransable['name'];
				$form2cat[$formtransable['catid']] = $formtransable;
			}
		}elseif($infotype=='simpbycatids'){
			if(isset($simpinfos['field2name'])){
				$form2cat['field2name'] = $simpinfos['field2name'];
				unset($simpinfos['field2name']);
			}
			$form2cat['simpinfos'] = $simpinfos;
		}else{
			$form2cat = array();
		}
		return $form2cat;
	}
	/**
	 *获取地址(abstract)配置传输参数
	 *@return $rs 返回状态
	 */	
	private function gaininfos4url($infos,$infotype){
		$localjxs = $this->info2trans->jxscache;
		foreach($infos as $infoid => $info){
			$topost = array('a'=>'gaininfos','infotype'=>$infotype,'salt'=>rand(999,9999));
			//经销商端发送获取secretkey 'catids'or'simpbycatids'
			if(!isset($info['secretkey'])){
				$jxsid = $this->request['data']['jxsid'];
				empty($jxsid)&&$jxsid = '';
				$jxsinfo = $this->info2trans->getjxsinfo($jxsid);
				$info['secretkey'] = $jxsinfo['secretkey'];
				//采集官网时url是官网url，secret是经销商secret
				if($this->info2trans->jxscache['iscenter']){
					$info['posturl'] = $localjxs['default']['posturl'];
					$topost['data']['jxsid'] = $jxsid;
				}else{
					$info['posturl'] = $jxsinfo['posturl'];
				}
			}
			$topost['secretkey'] = md5(md5($info['secretkey']).$topost['salt']);
			if(!$this->info2trans->jxscache['iscenter']){
				$topost['data']['jxsid'] = $localjxs['default']['jxs_id'];
			}
			//其他传送共用信息
			if(!empty($info['catid'])){
				$topost['catid'] = $info['catid'];
				$topost['catname'] = $info['catname'];
			}
			!empty($info['jxs_name']) && $topost['jxsname'] = $info['jxs_name'];
			$info['url4cats'] = $info['posturl']."&".http_build_query($topost);
			$infos[$infoid] = $info;
		}
		return $infos;
	}
	/**
	 * 判断用户是否已经登陆和用户组权限
	 */
	public function check_admin() {
		if(ROUTE_M =='admin' && ROUTE_C =='index' && in_array(ROUTE_A, array('login', 'public_card'))) {
			return true;
		} else {
			if(!isset($_SESSION['userid']) || !isset($_SESSION['roleid']) || !$_SESSION['userid'] || !$_SESSION['roleid']){
				var_dump($_SESSION);exit;
				showmessage('请登录后操作！','?m=admin&c=index&a=login');			
			}
			if($_SESSION['roleid'] != 1) showmessage('您没操作权限，请联系网站管理员！','?m=admin&c=index&a=login');
		}
	}
	
	
	/**
	 *接收数据
	 *@return $rs 返回状态
	 */
	public function dealrecieve(){
		$rs = $this->info2change->dealpostcon($_POST);
		return $rs;
	}
	
	public function gettpl($tplname){
		return $this->transinfopath.'templates'.DIRECTORY_SEPARATOR.$tplname.'.tpl.php';
	}
	
	
	
}
/*to do*/
?>