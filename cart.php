<?php
$GLOBALS['ectcartpage']='cart';
require('./wp-blog-header.php');
get_header();
?>    
		<div class="cart_page">  	
			<?php include "vsadmin/inc/inccart.php";?>
		</div> <!-- #main-area -->
	 <div class="clearfix"></div>
</div> <!-- #content-area -->
<?php get_footer(); ?>