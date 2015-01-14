<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"<?php if(isset($addbg)) { ?> class="addbg"<?php } ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET?>" />
<meta http-equiv="X-UA-Compatible" content="IE=7" />
<title><?php echo L('website_manage');?></title>
<link href="<?php echo CSS_PATH?>reset.css" rel="stylesheet" type="text/css" />
<link href="<?php echo CSS_PATH;?>zh-cn-system.css" rel="stylesheet" type="text/css" />
<link href="<?php echo CSS_PATH?>table_form.css" rel="stylesheet" type="text/css" />

<link rel="stylesheet" type="text/css" href="<?php echo CSS_PATH?>style/<?php echo SYS_STYLE;?>-styles1.css" title="styles1" media="screen" />
<link rel="alternate stylesheet" type="text/css" href="<?php echo CSS_PATH?>style/zh-cn-styles2.css" title="styles2" media="screen" />
<link rel="alternate stylesheet" type="text/css" href="<?php echo CSS_PATH?>style/zh-cn-styles3.css" title="styles3" media="screen" />
<link rel="alternate stylesheet" type="text/css" href="<?php echo CSS_PATH?>style/zh-cn-styles4.css" title="styles4" media="screen" />
<script language="javascript" type="text/javascript" src="<?php echo JS_PATH?>jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo JS_PATH?>admin_common.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo JS_PATH?>styleswitch.js"></script>
</head>
<body>

<div class="subnav">
    <div class="content-menu ib-a blue line-x">
		<a class="add fb" href=""><em><?php echo $recieves['catname']; ?>·文章列表</em></a>　  
		<a href="javascript:window.history.go(-1);" class="on"><em>返回上一页</em></a>  
	</div>
</div>

<style type="text/css">
	html{_overflow-y:scroll}
</style>
<form name="myform" action="?m=admin&c=position&a=listorder" method="post">
<div class="pad_10">
<div class="table-list">
    <table width="100%" cellspacing="0">
 <?php if(isset($field2name)){ ?>
        <thead>
            <tr>
            <th width="5%"><input type="checkbox" value="" id="check_box" onclick="selectall('ids[]');"></th>
            <th width="5%"  align="left">DATAID</th>
			<th width="30%"><?php echo rtrim($field2name[0]['name'],'：');?></th>
            <th width="30%"><?php echo rtrim($field2name[1]['name'],'：');?></th>
			<th width="18%">时间</th>
			<th width="12%">查看更多</th>
            </tr>
        </thead>
    <tbody>
 <?php 
if(is_array($simpinfos)){
	function hc_box_match_name($setting,$myvalue){
		if(strpos($myvalue,',')!==false){
			$value_arr = explode(',',$myvalue);
			$myvalue = '';
			foreach($value_arr as $v){
				$pattern = "/.*'options' => (?:(?:'[^']+\n)|(?:'))([^']+)\|".$v."[^']*',.*/";
				if(preg_match($pattern,$setting,$v)) $myvalue.=$v[1].',';
			}
			return rtrim($myvalue,',');
		}else{
			$pattern = "/.*'options' => (?:(?:'[^']+\n)|(?:'))([^']+)\|".$myvalue."[^']*',.*/";
			if(preg_match($pattern,$setting,$myvalue)){
				return $myvalue[1];
			}else{
				return '';
			}		
		}
	}
	foreach($simpinfos as $simpinfo){
?>   
	<tr>
	<td width="5%" align="center"><input class="inputcheckbox " name="ids[]" value="<?php echo $simpinfo['dataid']?>" type="checkbox"></td>
	<td width="5%"  align="left"><?php echo $simpinfo['dataid']?></td>
	<td  width="30%" align="center"><?php echo $simpinfo[$field2name[0]['field']]; ?></td>
	<td width="30%" align="center">
	<?php
		if($field2name[1]['formtype']=='box'){
			echo hc_box_match_name($field2name[1]['setting'],$simpinfo[$field2name[1]['field']]);
		}else{
			echo $simpinfo[$field2name[1]['field']];
		}
	?></td>
	<td  width="18%" align="center"><?php echo date("Y-m-d",$simpinfo['datetime']); ?></td>
	<td width="12%"  align="center"><input value="查看/隐藏 详情" type="button" onclick="displayinverse('formmore_<?php echo $simpinfo['dataid']; ?>')"></td>
	</tr>
	<tr id="formmore_<?php echo $simpinfo['dataid']; ?>" style="display:none;line-height:20px;">
	<td width="5%" align="center" colspan="1">详情：</td>
	<td colspan="5">
	<?php 
		foreach($field2name as $k=>$myfield){
			if($k<2){ continue; }
			//if($k!=2){ echo '<span style="color:#d5dfe8;">&nbsp&nbsp|&nbsp&nbsp</span>'; }
			if($k!=2){ echo '</br>'; }
			$myvalue = $simpinfo[$myfield['field']];
			if($myfield['formtype']=='box'){
				$myvalue = hc_box_match_name($myfield['setting'],$myvalue);
			}
			echo "<b>".$myfield['name']."</b>".$myvalue;
		} 
	?>
	</td></tr>
<?php 
	}
}
?>
    </tbody>
 <?php }else{ ?>
        <thead>
            <tr>
            <th width="10%"><input type="checkbox" value="" id="check_box" onclick="selectall('ids[]');"></th>
            <th width="5%"  align="left">ID</th>
			<th width="30%">标题</th>
            <th width="30%">时间</th>
            </tr>
        </thead>
    <tbody>
 <?php 
if(is_array($simpinfos)){
	foreach($simpinfos as $simpinfo){
?>   
	<tr>
	<td width="10%" align="center"><input class="inputcheckbox " name="ids[]" value="<?php echo $simpinfo['id']?>" type="checkbox"></td>
	<td width="5%"  align="left"><?php echo $simpinfo['id']?></td>
	<td  width="30%" align="center"><?php echo $simpinfo['title'];?></td>
	<td width="30%" align="center"><?php echo date('Y-m-d',$simpinfo['inputtime']);?></td>
	</tr>
<?php 
	}
}
?>
    </tbody>
 <?php } ?>
    </table>
   <?php if(!isset($field2name)){ ?>
    <div class="btn">
		<input type="hidden" name="modelid" value="<?php echo $modelid;?>">
		<input type="hidden" name="action" value="postcons">
		<?php if(!$this->jxscache['iscenter']){ ?><input type="hidden" name="data[jxsid]" value="1"><?php  } ?>
		<input type="button" class="button" value="采集" onclick="myform.action='?m=transinfos&c=index&a=dealsent';myform.submit();"/>
		<input style="display:none;" type="button" class="button" value="同步" onclick="myform.action='?m=transinfos&c=index&a=dealsent&data[update]=1';myform.submit();"/>
	</div><?php } ?></div>

 <div id="pages"> <?php echo $pages?></div>
</div>
</div>
</form>
</body>
<a href="javascript:edit(<?php echo $v['siteid']?>, '<?php echo $v['name']?>')">
</html>
<script type="text/javascript">
<!--
	function displayinverse(id){
		var inverser = $("#"+id);
		if(inverser.css("display")!="none"){
			inverser.css({display:"none"});
		}else{
			inverser.css({display:""});
		}
	}
	window.top.$('#display_center_id').css('display','none');
	function edit(id, name) {
	window.top.art.dialog({title:'<?php echo L('edit')?>--'+name, id:'edit', iframe:'?m=admin&c=position&a=edit&posid='+id ,width:'500px',height:'360px'}, 	function(){var d = window.top.art.dialog({id:'edit'}).data.iframe;
	var form = d.document.getElementById('dosubmit');form.click();return false;}, function(){window.top.art.dialog({id:'edit'}).close()});
}
//-->
</script>