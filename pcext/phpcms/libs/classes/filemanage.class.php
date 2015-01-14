<?php
class filemanage{
	public $basecachedir;
	public $basedir;
	
	public function __construct(){
		$extbase = new extbase;
		$initsys = $extbase->loadobject('extbase');
		$config = $initsys->getproperty('config');
		$this->basedir = $config['dir'];
	}
	
	public function newfile($filename = '',$type = 'cache_ext',$content = '',$mode = 'a'){
		$filename == '' && $filename = $type.'_'.substr(time(),0,-5);
		$filepath = $this->basedir['cache'].$this->basedir[$type];
		$file = $filepath.$filename;
		if($this->newdir($filepath)){
			$handle = fopen($file,$mode);
			if(fwrite($handle,$content)){
				fclose($handle);
			}
		}
	}
	/**
	 *@param string $dir %patha/pathb%
	 */
	public function newdir($dir,$mode = '777'){
		$dirs = fathersofpath($dir,DIRECTORY_SEPARATOR);
		foreach($dirs as $dir){
			if(is_dir($dir)) continue;
				if(!mkdir($this->basedir.$dir,'777')){
				throw new myexception('can not make dir'.$this->basedir.$dir); 
				}
			}
		}
	/**
	 *@param string $path %patha/pathb% 
	 *@return array $rs array('patha','patha/pathb')
	 */
	public function fathersofpath($path,$seprator){
		$index=0;
		while($index = strpos($path,$seprator,$index+1)){
			$rs[] = substr($path,0,$index);
		}
		return $rs;
	}
}
?>