<?php
$GLOBALS['ectcartpage']='search';
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
		   <?php
			$GLOBALS['nobuyorcheckout']=TRUE; // Removes buy and checkout buttons
			$GLOBALS['noproductoptions']=TRUE;  // Removes product options
			$GLOBALS['showquantonproduct']=FALSE;  // Removes quantity box
			$GLOBALS['showproductsku']=""; // Removes SKU field
			$GLOBALS['manufacturerfield']=""; // Removes Manufacturer field
			$GLOBALS['showinstock']=FALSE; // Hides out of stock products
			$GLOBALS['showproductid']=FALSE; // Removes product ID
			$GLOBALS['shortdescriptionlimit']=0; // Removes short description
			$GLOBALS['noshowdiscounts']=TRUE; // Hides discount text
			$GLOBALS['orprodsperpage']=9; // Sets number of products per page
			include "vsadmin/inc/incsearch.php";?>
		</div> 
	 <div class="clearfix"></div>
</div> 
<?php get_footer(); ?>