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
		<a class="add fb" href=""><em><?php echo $recieves['jxsname']; ?>·可采集栏目列表</em></a>　  
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
        <thead>
            <tr>
            <th width="10%"  align="center">栏目ID</th>
			<th width="40%">点击获取本栏目信息列表</th>
            <th width="20%"></th>
            </tr>
        </thead>
    <tbody>
 <?php 
if(is_array($catinfos)){
	foreach($catinfos as $cat){
?>   
	<tr>
	<td width="10%"  align="center"><?php echo $cat['catid']?></td>
	<td  width="40%" align="center">
    	<a href="<?php echo $cat['url4cats']; ?>" target="_self">点击获取<strong style="color:#930;"><?php echo $cat['catname'];?></strong>信息列表</a>
    </td>
	<td width="20%" align="center"></td>
	</tr>
<?php 
	}
}
?>
    </tbody>
    </table>

 <div id="pages"> <?php echo $pages?></div>
</div>
</div>
</form>
</body>
<a href="javascript:edit(<?php echo $v['siteid']?>, '<?php echo $v['name']?>')">
</html>
<script type="text/javascript">
<!--
	window.top.$('#display_center_id').css('display','none');
	function edit(id, name) {
	window.top.art.dialog({title:'<?php echo L('edit')?>--'+name, id:'edit', iframe:'?m=admin&c=position&a=edit&posid='+id ,width:'500px',height:'360px'}, 	function(){var d = window.top.art.dialog({id:'edit'}).data.iframe;
	var form = d.document.getElementById('dosubmit');form.click();return false;}, function(){window.top.art.dialog({id:'edit'}).close()});
}
//-->
</script>