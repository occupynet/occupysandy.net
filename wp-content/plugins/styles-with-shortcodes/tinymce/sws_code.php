<?php
require_once('../api.php');

$post_id = isset($_REQUEST['post_id'])?intval(trim($_REQUEST['post_id'])):false;
$code_id = isset($_REQUEST['code_id'])?intval(trim($_REQUEST['code_id'])):false;

if($post_id===false||$code_id===false){
	die('Missing parameter.');
}

$meta = "sws_code_".$code_id;

if ( !current_user_can( 'edit_post', $post_id ) ){
	die();
}
$sys_msg="";
if(isset($_REQUEST['sws_code'])){
	$sws_code = get_post_meta($post_id,'sws_code',true);
	$sws_code = is_array($sws_code)?$sws_code:array();
	$sws_code[$code_id]=$_REQUEST['sws_code'];
	update_post_meta($post_id,'sws_code',$sws_code);
	$sys_msg="Code updated.";
}

$sws_code = get_post_meta($post_id,'sws_code',true);
$sws_code = is_array($sws_code)?$sws_code:array();

$content = isset($sws_code[$code_id])?$sws_code[$code_id]:'';


?>
<html>
<head>
<style>
html,body {
	margin:0;
	padding:0;
}
textarea {
	width:100%;
	height:100%;
	padding:0;
	margin:0;
}
</style>
<?php wp_print_scripts('jquery');?>
<script>
jQuery(document).ready(function($){  

});
</script>
</head>
<body>
<?php
//echo "<PRE>";
//print_r($_REQUEST);
//echo "</PRE>";
?>
<form name="sform" method="post" action="<?php echo $_REQUEST['REQUEST_URI']?>">

<input type="hidden" name="code" value="<?php echo $post_id.':'.$code_id?>" />
<table width="100%" height="95%" padding="0" spacing="0">
	<tr>
		<td height="30"><input type="submit" value="Save" />&nbsp;<?php echo $sys_msg?></td>
	</tr>
	<tr>
		<td><textarea id="sws_code" name="sws_code" cols=50 rows=10><?php echo $content?></textarea></td>
	</tr>
</table>


</form>
</body>
</html>
