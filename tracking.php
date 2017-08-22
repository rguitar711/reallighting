<?php
$GLOBALS['ectcartpage']='tracking';
require('./wp-blog-header.php');
get_header();
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
			<?php include "vsadmin/inc/inctracking.php";?>
		</div> <!-- #main-area -->
	 <div class="clearfix"></div>
</div> <!-- #content-area -->
<?php get_footer(); ?>