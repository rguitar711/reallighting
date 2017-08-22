<?php
$GLOBALS['ectcartpage']='clientlogin';
require('./wp-blog-header.php');
get_header();
?>    
<?php

if($_SESSION['clientID'] != '' && $_POST['location']) {

$sql = "INSERT INTO productlocation (clientid, location) values ('" . escape_string($_SESSION['clientID']) . "','" . escape_string($_POST['location']) . "')";
echo '<script type="javascript">alert($sql);</script>';
exit;

ect_query($sql) or ect_error();



}






?>

<?php if(get_option('wpg_sidebar_opt') =="left"){ ?>
		<div class="left_side_bar">
			<?php get_sidebar(); ?>
		</div>	
		<div class="right_side_bar">
        <?php }else{ ?>
		<div class="left_side_bar" style="float:right !important;">
			<?php get_sidebar(); ?>
		</div>	
		<div class="right_side_bar">
		<?php }?>


<form action="" method="post">
<table>
<tr><td> Add Location for product
</td></tr>
<tr><td>
			<input type = "text" name="location">
</td></tr>

<tr><td>		<input type="submit" value="submit"> 
</td></tr>
<tr><td>		<select name="select_location"><option value="0">Locations:</select>
</td></tr>
</table>

	</form>
		
			
		</div> <!-- #main-area -->
	 <div class="clearfix"></div>
</div> <!-- #content-area -->
<?php get_footer(); ?>