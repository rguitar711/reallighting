<script src="//code.jquery.com/jquery-1.12.0.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#delete').on('click', function() {
    var choice = confirm('Do you really want to remove from location?.');
    if(choice === true) {
        return true;
    }
    return false;
    });
});
	
</script>

<script>
function myFunction() {
    var myWindow = window.open("", "", "width=200,height=100","target=_blank");
}
</script>

<?php




if(!@$GLOBALS['incfunctionsdefined']){
    print 'No incfunctions.php file';
    exit;}

if(trim(getget('addid'))==false && empty($_SESSION['addId'])){
     header( 'Location: stores.php');

}


$locIDForQuerystring = '';

//if(empty(getget('addid'))){
if(trim(getget('addid'))==false){
    $addId = $_SESSION['addId'];

}else
{
    $addId = getget('addid');
    $_SESSION['addId'] = $addId;

}


if(!empty($addId)){


$sql = "SELECT * FROM address WHERE addID =  '" . $addId . "'";
$result = ect_query($sql) or ect_error();
 while($rs=ect_fetch_assoc($result)){

$addName = $rs['addName'];
$addAddress = $rs['addAddress'];
$addCity = $rs['addCity'];
$addState = $rs['State'];
$addZip = $rs['addZip'];




 }

print '<p><h2>' .  $addName . '</h2></p>';
print '<p><h2>' .  $addAddress. " "  . $addCity . " " .  $addState . " " .  $addZip . '</h2></p>';
print '<hr>';






}


function insertlocation(){


    $productID = $_POST['productID'];
    $productLocation = $_POST['sel_location'];
    $thechekbox = $_POST['ck_homepage'];

//count the selection boxes.... will be equal to quantity fo producst

    $count = count($productLocation);
   
    for($i = 0; $i< $count; $i++){
  
        //$sql ="SELECT * FROM productandlocation WHERE clientID = '" .  $_SESSION['clientID'] . "' AND prodID = '" . $productID[$i]  . "'";
        $sql ="SELECT * FROM productandlocation WHERE clientID = '" .  $_SESSION['clientID'] . "' AND prodID = '" . $productID[$i]  . "' AND addID = '" . $_SESSION['addId'] . "'";
        $result = ect_query($sql) or ect_error();
        if(ect_num_rows($result) > 0)
        {
          
            //$sql ="UPDATE productandlocation SET locID = '" .  $productLocation[$i] . "' WHERE prodID = '".  $productID[$i] . "' AND clientID = '". $_SESSION['clientID'] . "'";
            $sql ="UPDATE productandlocation SET locID = '" .  $productLocation[$i] . "' WHERE prodID = '".  $productID[$i] . "' AND clientID = '". $_SESSION['clientID'] . "' AND addID = '" . $_SESSION['addId'] . "'";
            ect_query($sql) or ect_error();			
        }else{
           
            //$sql ="INSERT INTO productandlocation (clientID, locID, prodID) VALUES('" .  $_SESSION['clientID'] . "','" .$productLocation[$i] .  "','" .  $productID[$i] . "' ) ";
            $sql = "INSERT INTO productandlocation (clientID, locID, prodID, addID) VALUES('" .  $_SESSION['clientID'] . "','" .$productLocation[$i] .  "','" .  $productID[$i] .   "','" . $_SESSION['addId'] .  "' ) ";
            ect_query($sql) or ect_error();	
        }
    }  
}


 
 
$LocationTitle  = '';

if($_SESSION['clientID'] == ''){?>
    <div class="ectdiv ectclientlogin">
    <div class="ectdivhead"><?php print 'My Home Page' ?></div>
    <div class="ectmessagescreen">
    <div><?php print $GLOBALS['xxMusLog']?></div>
    <div><?php print imageorbutton(@$imglogin,$GLOBALS['xxLogin'],'login',(@$forceloginonhttps?$pathtossl:'')."cart.php?mode=login&amp;refurl=".urlencode(@$_SERVER['PHP_SELF']),FALSE)?></div>
    </div>
	</div>
    <?php 

} else {




//GET ALL LOCATIONS FOR THE CLIENT FOR THE MAIN DROPDOWN


    $sql = "SELECT id,  location FROM productlocation  WHERE clientID ='" . $_SESSION['clientID'] . "' AND addID = '" . $_SESSION['addId'] . "' order by location asc";
    $result=ect_query($sql) or ect_error();
    print '<div style="padding-bottom:50px;">';
    print '<p><h4>Choose your location:  </h4></p>';
    print '<form name="locationForm" action="" method="POST">';
    print '<select name="sel_location_home">';
    print '<option value="0">Select Location</option>';
    print '<option value="0">All Locations</option>';

    while($rs=ect_fetch_assoc($result)){

            $location = $rs['location'];
            $locId = $rs['id'];
            if($locId == $_POST['sel_location_home']){
            //if($locId == $_SESSION[selected_area]){
                print '<option value="' . $locId . '" selected >' . $location . '</option>';
            }
            else{
                    print '<option value="' . $locId . '">' . $location . '</option>';
            }
    }

    print '</select>';
    print '&nbsp;&nbsp;';
    print '<input type="submit" name="submit" value="Submit">';
    print '</form>';
    print '</div>';

    if(isset($_POST['delete'])){
        if(isset($_POST['removeProduct'])){
            foreach($_POST['removeProduct'] as $cbvalue )
            {
            $sqlDelete =  "DELETE FROM productandlocation WHERE clientID = '" .  $_SESSION['clientID'] . "' AND prodID = '" .$cbvalue . "' AND addID = '" . $_SESSION['addId'] . "'";
                ect_query($sqlDelete) or ect_error();
                header("Location:myhomepage.php");
            }
        }
    }
//chceck if To Cart button was clicked
    if(isset($_POST['tocart'])){
        $addToCartCount = 0;
        //count the checkboxess
        $addToCartCount= $_POST['thecounter'];
        for($i = 0; $i<=  $addToCartCount; $i++){
                    //check if the checkbox is checked
            if(isset($_POST['ck_tocart-'.$i])){
                $quantity = $_POST['txt_quantity-'.$i];//$_POST['ck_tocart'][$i];
                $aiprice = $_POST['txt_price-'.$i];//$_POST['txt_price'][$i];
                $ainame = $_POST['txt_productname-'.$i];//$_POST['txt_productname'][$i];
                $theid = $_POST['ck_tocart-'.$i];
                $sSQL='INSERT INTO cart (cartSessionID,cartClientID,cartProdID,cartOrigProdID,cartQuantity,cartCompleted,cartProdName,cartProdPrice,cartOrderID,cartDateAdded,cartListID) VALUES (' .
                    "'" . escape_string($thesessionid) . "','" . (@$_SESSION['clientID']!='' ? escape_string($_SESSION['clientID']) : 0) . "','" . escape_string($theid) . "',''," . $quantity . ",0,'" . escape_string(strip_tags($ainame)) . "','" . round($aiprice,2) . "',0,'" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "',0)";
                ect_query($sSQL) or ect_error();
            }
        }
        header("Location:cart.php");
    }

    if(isset($_POST['updateLocation']))
    {
        insertlocation();
    }                    
    

 
            $page = 0;
            $rec_limit = 10;
            $left_rec = 0;
            $rec_count = 0;

//THIS IS FOR THE SUBMIT BUTTON TO GET THE PRODUCTS PER LOCATION FROM THE MAIN DROPDOWN
    if(isset($_POST['submit'])){
//GET TITLE OF LOCATION WHEN SELECTED
        $sqlGetTitle = "SELECT location from productlocation WHERE id = '" . $_POST['sel_location_home'] . "'";
        $result2=ect_query($sqlGetTitle) or ect_error();

        while($rs2=ect_fetch_assoc($result2)){
                $LocationTitle = $rs2['location'];
        }


       

        if(isset($_POST['sel_location_home']) && $_POST['sel_location_home'] != "0" ){

            $locIDForQuerystring = $_POST['sel_location_home'];
            // $_SESSION['locationID'] =  $locIDForQuerystring;


            //$sqlImage = "SELECT locID,prodID,imageSrc,pLongdescription,pPrice,pName FROM productandlocation JOIN productimages ON productandlocation.prodID = productimages.imageProduct JOIN products ON productandlocation.prodID = products.pID WHERE productandlocation.clientid =   '" . $_SESSION['clientID'] . "' AND productandlocation.locID = '" . $_POST['sel_location_home'] . "'";

             $sqlImage = "SELECT locID,prodID,imageSrc,pLongdescription,pPrice,pWholesalePrice,pName FROM productandlocation JOIN productimages ON productandlocation.prodID = productimages.imageProduct JOIN products ON productandlocation.prodID = products.pID WHERE productandlocation.clientid =   '" . $_SESSION['clientID'] . "' AND productandlocation.locID = '" . $_POST['sel_location_home'] . "' AND productandlocation.addID = '" . $_SESSION['addId'] ."'";
                

   

      
        }else{
      
               // unset($_SESSION['locationID']);


        //THE DROPDOWN WAS NOT SELECTED AND ALL LOCATIONS AND PRODUCTS SHOW
        //$sqlImage = "SELECT locID,prodID,imageSrc,pLongdescription,pPrice,pName FROM productandlocation JOIN productimages ON productandlocation.prodID = productimages.imageProduct JOIN products ON productandlocation.prodID = products.pID WHERE productandlocation.clientid =   '" . $_SESSION['clientID'] ."'";
        $sqlImage = "SELECT locID,prodID,imageSrc,pLongdescription,pPrice,pWholesalePrice,pName FROM productandlocation JOIN productimages ON productandlocation.prodID = productimages.imageProduct JOIN products ON productandlocation.prodID = products.pID WHERE productandlocation.clientid =   '" . $_SESSION['clientID'] ."'  AND productandlocation.addID = '" . $_SESSION['addId'] ."'";
     
        }

    }else{
 
//$sqlImage = "SELECT locID,prodID,imageSrc,pLongdescription,pPrice,pName FROM productandlocation JOIN productimages ON productandlocation.prodID = productimages.imageProduct JOIN products ON productandlocation.prodID = products.pID WHERE productandlocation.clientid =   '" . $_SESSION['clientID'] ."'";
$sqlImage = "SELECT locID,prodID,imageSrc,pLongdescription,pPrice,pWholesalePrice,pName FROM productandlocation JOIN productimages ON productandlocation.prodID = productimages.imageProduct JOIN products ON productandlocation.prodID = products.pID WHERE productandlocation.clientid =   '" . $_SESSION['clientID'] ."' AND productandlocation.addID = '" . $_SESSION['addId'] ."'";

    }



//$locIDForQuerystring = getget('location');
 //$locIDForQuerystring = $_SESSION['locationID'] ;
//if(!empty($locIDForQuerystring))
//{
     // $sqlImage = "SELECT locID,prodID,imageSrc,pLongdescription,pPrice,pName FROM productandlocation JOIN productimages ON productandlocation.prodID = productimages.imageProduct JOIN products ON productandlocation.prodID = products.pID WHERE productandlocation.clientid =   '" . $_SESSION['clientID'] . "' AND productandlocation.locID = '" . $locIDForQuerystring . "'";
     // $sqlImage = "SELECT locID,prodID,imageSrc,pLongdescription,pPrice,pWholesalePrice, pName FROM productandlocation JOIN productimages ON productandlocation.prodID = productimages.imageProduct JOIN products ON productandlocation.prodID = products.pID WHERE productandlocation.clientid =   '" . $_SESSION['clientID'] . "' AND productandlocation.locID = '" . $locIDForQuerystring . "'  AND productandlocation.addID = '" . $_SESSION['addId'] ."'";
//echo $sqlImage;

//}





    $resultCount=ect_query($sqlImage) or ect_error();

    $rec_count = ect_num_rows($resultCount);

    if($rec_count >  $rec_limit){

        if( isset($_GET['page'])) {
            $page = getget('page');
            $page = $page + 1;
            $offset = $rec_limit * $page ;
         }else {
            $page = 0;
            $offset = 0;
         }

    $left_rec = $rec_count - ($page * $rec_limit);


    $sqlImage = $sqlImage .  " LIMIT " . $offset . " , " .  $rec_limit;
   
    }


    $result1 = ect_query($sqlImage) or ect_error();

    if(ect_num_rows($result1) > 0){
        print '<form name="form" action="" method="POST">';
        print '<h2>' . $LocationTitle . '</h2>';
        print '</br>';
        print '<table class="products" width="800" border="1" bordercolor="#43403C" cellspacing="0" cellpadding="3" align="center">
                <tr style="height:35">
                <td width="147" align="center" style="background:#ADC431; font-size:14px; color:#43403C" ><strong>Image</strong></td>
                <td width="302" align="center" style="background:#ADC431; font-size:14px; color:#43403C" ><strong>Product Name</strong></td>
                <td width="421" align="center" style="background:#ADC431; font-size:14px; color:#43403C"><strong>Products/Descriptions</strong></td>
                <td width="332" align="center" style="background:#ADC431; font-size:14px; color:#43403C"  ><strong> Price</strong></td>
                <td width="50" align="center" style="background:#ADC431; font-size:14px; color:#43403C"  ><strong> Quantity</strong></td>
                <td width="200" align="center" class="prodimage" style="background:#ADC431; font-size:14px; color:#43403C"><strong>#</strong></td>
                <td width="50" align="center" style="background:#ADC431; font-size:14px; color:#43403C"  ><strong>Location</strong></td>
                <td width="200" align="center" class="prodimage" style="background:#ADC431; font-size:14px; color:#43403C"><strong>Delete</strong></td>
                </tr>';

        $windowname = 'cart';
        $counter = 0;

	    while($rs1=ect_fetch_assoc($result1)){

            if($_SESSION['clientActions'] == 8){
                 $number = $rs1['pWholesalePrice'];
            }else{
                $number = $rs1['pPrice'];
            }
            
            setlocale(LC_MONETARY,"en_US");
            $myproduct = str_replace('/','%2F',$rs1['prodID']);
            print '<tr>';
            print '<td>';
            print '<a href="proddetail.php?prod='.  $myproduct . '" onclick="myFunction" >';
            print '<img src="' . $rs1['imageSrc'] . '" height="100" width="100" >';
            print '</a>';
            print '</td><td>';
            print '<h4>' . $rs1['pName'] . '</h4></td>';
            print '</td><td>';
            print $rs1['pLongdescription'];
            print '</td><td>';
            print  money_format('%i',$number);
            print '</td><td align="center">'; 
            print '<input type="text" name="txt_quantity-' . $counter . '" size="2" maxlength="5" value="1" </td>';
            print '</td><td align="center">';
            print '<input type="checkbox" name="ck_tocart-' . $counter . '"  value ="' . $rs1['prodID'] . '" >';
            print '</td>';
            print  '<td align="center">';
            print  '<select name="sel_location[]">';
            print '<option value="">No Location</option>';

            $sql = "SELECT id,  location FROM productlocation  WHERE clientID ='" . $_SESSION['clientID'] . "' AND addID = ' " . $_SESSION['addId'] . "' order by location asc";
            $result=ect_query($sql) or ect_error();
            while($rs=ect_fetch_assoc($result)){
                $location = $rs['location'];
                $locId = $rs['id'];
                if($locId == $rs1['locID']){
                    print '<option value="' . $locId . '" selected >' . $location . '</option>';
                    }else
                    {
                    print '<option value="' . $locId . '">' . $location . '</option>';
                    }
            }
            print '</select>';
            print '</td>';
            print '<td align="center">';
            print '<input type="checkbox" name="removeProduct[]" value ="' . $rs1['prodID'] . '" >';
            print '</td></tr>';
            print '<input type="hidden" name="txt_price-' . $counter . '" value="' . $rs1['pPrice'] . '">';
            print '<input type="hidden" name="txt_productname-' . $counter . '" value="' . $rs1['pName'] . '">';
            print '<input type="hidden" name="productID[]" value="' . $rs1['prodID'] . '">';
            $countAll = $counter + 1;
            print '<input type="hidden" name="thecounter" value="' . $countAll. '">';
            $counter = $counter + 1;
        }






    print '</table>';





if($rec_count > 10){
         if( $page > 0 && ($left_rec > $rec_limit)) {
            $last = $page - 2;
            echo "<a href = \"$_PHP_SELF?page=$last&location=$locIDForQuerystring\" >Last 10 Records</a> |";
            echo "<a href = \"$_PHP_SELF?page=$page&location=$locIDForQuerystring\">Next 10 Records</a>";
         }else if ( $left_rec < $rec_limit ) {
            $last = $page-2;
            echo "<a href = \"$_PHP_SELF?page=$last&location=$locIDForQuerystring\">Last 10 Records</a>";
         }else   if( $page == 0  ) {
            echo "<a href = \"$_PHP_SELF?page=$page&location=$locIDForQuerystring\">Next 10 Records</a>";
         }
         
         
}
echo "<br>";
       echo "There are " .  $rec_count . " records";   
       
         
         





    print '<br>';
    print '<br>';
    print '<table style="float:right;">';
    print '<tr>';
    print '<td><input type="submit" name="updateLocation" value="Update Location" id="updateLocation"></td>';
    print '<td><input type="submit" name="delete" value="Delete" id="delete" ></td></tr>';
    print '</table>';
    print '<br>';
    print '<br>';
    print '<input type="submit" name="tocart" value="Add To Cart" id="tocart" style = "float:right; margin-bottom:10px;" >';
  
  
    print "</div>"; 

    }




       
    
      
         
        










print '</form>';
}

?>


