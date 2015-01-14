<?php
class myexception extends Exception{

	public function errorhandle($deal="show"){
		$str_error = '文件：'.$this->getFile().'在第'.$this->getLine().'行出错：'.$this->getMessage()."</br>\n".'Stack trace:'.$this->getTraceAsString()."</br>\n";
		if(strpos($deal,'save')!==false){
			$filename = 'error_'.substr(time(),0,-7);
			$filemanage = new filemanage;
			$filemanage->newfile($filename,'cache_ext',$str_error,'a');
		}
		if(strpos($deal,'show')!==false){
			echo $str_error;
		}
	}
}
function myexception_handler($exception) {
  echo $exception->errorhandle()."\n";
}
set_exception_handler('myexception_handler');

?>