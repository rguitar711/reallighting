<?php
$GLOBALS['ectcartpage']='sorry';
require('./wp-blog-header.php');
get_header();
?>    

<?php if(get_option('wpg_sidebar_opt') =="left"){ ?>
    <div id="content-area" class=" leftsidebar">
	<?php get_sidebar(); ?>        
	<?php }else{?>
	
	<div id="content-area" class=" rightsidebar"><?php } ?>	
	
	<div id="main-area">		
	<br /><?php include "vsadmin/inc/incsorry.php";?>	
	
	
	</div> <!-- #main-area -->    
	
	<?php if(get_option('wpg_sidebar_opt') =="left"){ ?>
	<?php }else{?><?php get_sidebar(); ?><?php } ?>	 
	<div class="clearfix"></div>
	</div> <!-- #content-area -->
	<?php get_footer(); ?>