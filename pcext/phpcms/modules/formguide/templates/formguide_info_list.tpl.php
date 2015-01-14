<?php 
defined('IN_ADMIN') or exit('No permission resources.');
include $this->admin_tpl('header', 'admin');
?>

<div class="pad-lr-10">
<form name="myform" id="myform" action="?m=formguide&c=formguide_info&a=delete" method="post">
<div class="table-list">
<table width="100%" border="0" cellpadding="10">
  <tr >
    <td width="35">&nbsp;</td>
    <td><a href="?m=formguide&c=formguide_info&a=public_add&formid=<?php echo $formid?>"><?php echo L('add')?></a></td>
  </tr>
</table>
<table width="100%" cellspacing="0">
  <thead>
            <tr>
            <th width="35" align="center"><input type="checkbox" value="" id="check_box" onclick="selectall('ids[]');"></th>
			<?php foreach($fields as $key=>$v){ $k++; if($k<3){ ?><th width='250' align="center"><?php echo $v['name'];?> </th><?php }}?>
			<th width='250' align="center"><?php echo L('times')?></th>
			<th width="250" align="center"><?php echo L('operation')?></th>
            </tr>
        </thead>
    <tbody>
 <?php 
if(is_array($datas)){

	foreach($datas as $kk=>$d){			
?>   
	<tr>
	<td align="center">
	<input type="checkbox" name="ids[]" value="<?php echo $d['dataid']?>">
	</td>
	<td><?php echo htmlspecialchars_decode(str_replace("\\","",$d[$fary[0]]));?> </td>
    <?php if($d[$fary[1]]){ ?> <td><?php echo htmlspecialchars_decode(str_replace("\\","",$d[$fary[1]]));?> </td><?php } ?>
	<td align="center"><?php echo date('Y-m-d', $d['datetime'])?></td>
	<td align="center"><a href="javascript:check('<?php echo $formid?>', '<?php echo $d['dataid']?>', '<?php echo safe_replace($d['username'])?>');void(0);"><?php echo L('check')?></a> |<a href="?m=formguide&c=formguide_info&a=public_edit&formid=<?php echo $formid?>&did=<?php echo $d['dataid']?>"><?php echo L('edit')?></a>| <a href="?m=formguide&c=formguide_info&a=public_delete&formid=<?php echo $formid?>&did=<?php echo $d['dataid']?>" onClick="return confirm('<?php echo L('confirm', array('message' => L('delete')))?>')"><?php echo L('del')?></a></td>
	</tr>
<?php 

	}
}
?>
</tbody>
    </table>
  
        <div class="btn"><label for="check_box"><?php echo L('selected_all')?>/<?php echo L('cancel')?></label>
		<input name="button" type="button" class="button" value="<?php echo L('remove_all_selected')?>" onClick="document.myform.action='?m=formguide&c=formguide_info&a=public_delete&formid=<?php echo $formid?>';ckids();return confirm('<?php echo L('affirm_delete')?>');">&nbsp;&nbsp;
		<?php $jxsself = pc_base::load_config('jxsself'); if(in_array($formid,array('16','21'))&&$jxsself['iscenter']) { ?>
		<input type="hidden" name="modelid" value="<?php echo $formid;?>">
        <?php if(!$jxsself['iscenter']){ ?><input type="hidden" name="data[jxsid]" value="<?php echo $jxsself['default']['jxs_id']; ?>"><?php  } ?>
		<input type="button" class="button" value="推送给经销商" onclick="myform.action='?m=transinfos&c=index&a=dealsent&action=postcons';ckids();"/>
		<input type="button" class="button" value="同步到经销商" onclick="myform.action='?m=transinfos&c=index&a=dealsent&action=postcons&data[update]=1';ckids();"/>
		<?php if($jxsself['iscenter']){ ?><input type="button" class="button" value="同步删除已推送内容" onclick="javascript:jxsdelids('?m=transinfos&c=index&a=dealsent&action=deleteids');"/><?php }?>
		<input type="button" class="button" value="选择要推送经销商" onclick="javascript:displayjxs('jxs');"/>
		<?php }?>
		</div>
        <?php if($jxsself['iscenter']){ ?>
		<div class="btn" id="jxs" style="display:none;">
		<?php 
			$jxsdb = pc_base::load_model('sitemodel_field_model');
			$jxsdb->change_table("form_jxsgl");
			$jxses = $jxsdb->select(array(), '*', '100', '`dataid` DESC');
			foreach($jxses as $jxs){
		?>
		<input type="button" class="button" value="反选" onclick="inversesselect('jxsids[]');" ><input class="inputcheckbox " id="jxs<?php echo $jxs['jxs_id'];?>" name="jxsids[]" checked="checked" value="<?php echo $jxs['jxs_id'];?>" type="checkbox"> <?php echo $jxs['jxs_name'];?>
		<?php 
			}
		?>
		</div>
        <?php }?>
 <div id="pages"><?php echo $pages;?></div>
</form>
</div>
</body>
</html>
<script type="text/javascript">
function inversesselect(name){
	$("input[name='"+name+"']").each(function(){
		if(this.checked==false){
			this.checked=true;
		}else{
			this.checked=false;
		}
	});
}
function ckids(){
	var num = 0;
	$("input[name='ids[]']").each(function(){
		if(this.checked==true){
			num++;
		}
	})
	if(num==0){
		alert("请选择要操作信息！");
	}else{
		document.getElementById("myform").submit();
	}
}
function jxsdelids(url){
	var cfm = confirm("删除将无法恢复，您确定删除吗？");
	if(cfm==true){
		document.getElementById("myform").action=url;
		document.getElementById("myform").submit();
	}else{
		return true;
	}
}
function displayjxs(id) {
	var jxs = $('#'+id);
	if(jxs.css('display')=='none'){
		jxs.css('display','');
	}else{
		jxs.css('display','none');
	}
}
function check(id, did, title) {
	window.top.art.dialog({id:'check'}).close();
	window.top.art.dialog({title:'<?php echo L('check'); ?>--'+title+'<?php echo L('submit_info'); ?>', id:'edit', iframe:'?m=formguide&c=formguide_info&a=public_view&formid='+id+'&did='+did ,width:'700px',height:'500px'}, function(){window.top.art.dialog({id:'check'}).close()});
}
</script>