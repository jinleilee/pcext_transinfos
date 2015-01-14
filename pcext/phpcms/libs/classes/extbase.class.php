<?php
defined('IN_PHPCMS') or exit('No permission resources.');
require_once PC_PATH."libs/classes/myexception.class.php";
require_once PC_PATH."libs/classes/filemanage.class.php";
/**
 *model diaoyong,cuowu kongzhi,
 *luojikongzhi,wenjianchazhao
 */
class extbase{
	public $classesloaded;
	public $config;
	public function __construct(){
		$this->init();
	}
	/**
	 *初始化
	 */
	public function init(){
		$this->configs();
	}
	/**
	 *配置
	 */
	public function configs(){
		$this->config['dir'] = array('cache'=>CACHE_PATH,'cache_ext'=>'cache_ext','basecache'=>DIRECTORY_SEPARATOR.'cache_ext'.DIRECTORY_SEPARATOR.'cache');
		$this->config['classdirs'] = array('libs','module_content','module_product');
	}
	/**
	 *加载对象
	 */
	public function loadobject($classname,$canshu=''){
		if($classname=='MY_model'){
			$loadedclassname = $canshu;
		}else{
			if(strpos($classname,'_model')!==false){
				if(!$this->findpath($classname,'model'))
					return $this->makemodel(substr($classname,0,-6));
			}
			$loadedclassname = $classname;
		}
		if(!isset($this->classesloaded[$loadedclassname]) || !is_object($this->classesloaded[$loadedclassname])){
			if($classname=='MY_model'&&class_exists('pc_base')){
				//解决v9加载冲突问题
				pc_base::load_sys_class('model', '', 0);
			}else{
				$this->autoload($classname);
			}
			$object = new $classname($canshu);
			$this->classesloaded[$classname] = $object;
			return $object;
		}else{
			return $this->classesloaded[$classname];
		}
	}
	/**
	 *加载数据库类
	 */
	public function makemodel($name){
		$model = $this->loadobject('MY_model',$name);
		$this->classesloaded[$name] = $model;
		return $model;
	}
	/**
	 *组成路径
	 */
	public function makepath($name,$type){
		if(strpos($type,'module_')!==false){
			$module = substr($type,7);
			$type = 'module';
		}
		switch($type){
			case 'model':
				$path = PC_PATH."model/{$name}.class.php";
				 break;
			case 'libs':
				$path = PC_PATH."libs/classes/{$name}.class.php";
				 break;
			case 'module':
				$path = PC_PATH."modules/{$module}/classes/{$name}.class.php";
				break;
		}
		DIRECTORY_SEPARATOR=='/'||$path=str_replace('/','\\',$path);
		return $path;
	}
	/**
	 *查找路径
	 */
	public function findpath($classname,$type){
		$path = $this->makepath($classname,$type);
		if(file_exists($path)){
			return $path;
		}else{
			return false;
		}	
	}
	/**
	 *包含类
	 */
	public function loadclass($classname,$path=''){
		if(strpos($classname,'_model')!==false && $classname!='MY_model'){
			$path = 'model';
		}
		if($path){
			$path_class = $this->makepath($classname,$path);
			require_once($path_class);
			return $path_class;
		}

		foreach($this->config['classdirs'] as $dir){
			$path_class = $this->findpath($classname,$dir);
			if($path_class){
				if(strpos($classname,'MY_')===0){
					$fclass = substr($classname,3);
					require_once(str_replace($classname,$fclass,$path_class));
				}
				require_once($path_class);
				return $path_class;
			}else{
				throw new myexception('找不到类'.$classname);
			}
		}
	}
	/**
	 *自动加载
	 */
	public function autoload($classname){
		$this->loadclass($classname);
	}
	/**
	 *获取属性
	 */
	public function getproperty($propertyname){
		return isset($this->$propertyname) ? $this->$propertyname : '';
	}
}
?>