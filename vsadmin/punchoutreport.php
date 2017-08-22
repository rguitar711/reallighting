<?php
session_cache_limiter('none');
session_start();

include "db_conn_open.php"; 
include "includes.php";
include "inc/languageadmin.php";
include "inc/incfunctions.php";
include 'inc/incloginfunctions.php';

if(@$_SESSION["loggedon"] != "virtualstore"){
	if(@$_SERVER["HTTPS"] == "on" || @$_SERVER["SERVER_PORT"] == "443")$prot='https://';else $prot='http://';
	exit;
}
$message = '';

//$sSQL = "SELECT id, Date_format(ordersent_timestamp,'%c/%e/%Y')as 'Order Sent', orderid as 'Order ID', DATE_Format( orderconfirmation_timestamp,'%c/%e/%Y' ) as 'Order Confirmation', ponumber as PO FROM PunchOutLog";
if(isset($_POST['start_date']) &&  !empty($_POST['start_date']) && isset($_POST['end_date']) && !empty($_POST['end_date']) && isset($_POST['vendor']) && !empty($_POST['vendor'])){
$vendor = $_POST['vendor'];
$start_date =  date('m/d/Y',strtotime($_POST['start_date']));
$end_date =  date('m/d/Y',strtotime($_POST['end_date']));


$sSQL = "SELECT a.ordName, b.id, Date_format(b.ordersent_timestamp,'%m/%d/%Y')as 'Order Sent', b.orderid as 'Order ID', DATE_Format( b.orderconfirmation_timestamp,'%m/%d/%Y' ) as 'Order Confirmation', b.ponumber as PO FROM orders as a inner join PunchOutLog as b on a.OrdId = b.OrderId WHERE Date_format(b.ordersent_timestamp,'%m/%d/%Y') >= '$start_date' and Date_format(b.ordersent_timestamp,'%m/%d/%Y')<= '$end_date' and vendor = '$vendor' ORDER BY b.ordersent_timestamp  DESC";



	$rs= ect_query($sSQL) or ect_error();	
	$num_f=ect_num_rows($rs);
	$message = "<p>There are ".  $num_f . " records. </p>";

}
else
{
$message = '<br />Need date range and/or vendor selection<br />';

/*$sSQL = "SELECT a.ordName, b.id, Date_format(b.ordersent_timestamp,'%c/%e/%Y')as 'Order Sent', b.orderid as 'Order ID', DATE_Format( b.orderconfirmation_timestamp,'%c/%e/%Y' ) as 'Order Confirmation', b.ponumber as PO FROM orders as a inner join PunchOutLog as b on a.OrdId = b.OrderId";
*/
}


	
	//$row=mysql_fetch_array($rs);
	


if(isset($_POST['print'])){



header("Location:incexport_excel.php?startdate=$start_date&enddate=$end_date");


}

?>




<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="adminstyle.css"/>
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css"  />
<title>Report</title>
<script type="text/javascript">

$(function() {
    $( "#start_date" ).datepicker();
	$("#end_date").datepicker();
  });
</script>
<style type="text/css">

body{
	font-family:Arial, Helvetica, sans-serif;
	font-size:12px;
	}
	
#wrapper{
	
	float:right;
	
	}
	
.category
{ font-weight:bold;
}	
table th
{ background-color:#666666;
color:#FFFFFF;
}

</style>
</head>

<body <?php if($isprinter) print 'class="printbody"'?>>
<?php if(! $isprinter){ ?>

<!-- Header section -->
<?php adminheader() ?>

<!-- Left menus -->
<?php adminnavigation(); ?>

<?php } ?>
<body>

<div id="main">
<form name="form1" action="" method="post" >
    <table><tr><td class="category">Vendor:</td><td><select name="vendor">
                           <option value="birchstreet.com" <?php if(isset($_POST['vendor']) && $_POST['vendor'] == 'birchstreet.com'){ echo ' selected';} ?>>Birchstreet</option>
                    <option value="payablesnexus.com" <?php if(isset($_POST['vendor']) && $_POST['vendor'] == 'payablesnexus.com'){ echo 'selected';} ?>>Nexus</option>
        </td></tr>
<tr><td class="category">Order Date:</td><td><input type="text" name="start_date" id="start_date" value="<?php echo $start_date; ?>"  /></td><td class="category">Order Date</td><td><input type="text" name="end_date" id="end_date" value="<?php echo $end_date; ?>" /></td><td><input type="submit" value="Search" /></td><td><input type="submit" value="Export to Excel" name="print" /></td></tr></table>
</form>


<?php echo $message; ?>



<table border="1" cellpadding="3" cellspacing="3" style="border-collapse:collapse">
<tr><th>Client</th><th>Order Sent</th><th>Order Confirmed</th><th>Order Id</th><th>PO Number</th><th>Days Since Order</th></tr>
<?php for($i =0; $i<$num_f; $i++){

$row=ect_fetch_assoc($rs);
if($i % 2)
{
$RowColor="style='background-color:#BFC9E0'";
}
else
{

$RowColor="style='background-color:#ffffff'";

}

//find difference between dates
$sent = new DateTime($row['Order Sent']);
if(is_null($row['Order Confirmation'])){
$confirmation = new DateTime();
}
else
{
$confirmation = new Datetime($row['Order Confirmation']);
}
$interval = $confirmation->diff($sent);


echo "<tr ".$RowColor . ">";
echo "<td>";
echo $row['ordName'];
echo "</td>";
echo "<td>";
echo $row['Order Sent'];
echo "</td>";
echo "<td>";
echo $row['Order Confirmation'];
echo "</td>";
echo "<td>";
echo $row['Order ID'];
echo "</td>";
echo "<td>";
echo $row['PO'];
echo "</td>";
echo "<td>";
echo $interval->format('%a');
echo "</td>";
echo "</tr>";
}
?>
</table>

</div>



<!-- Footer -->
<?php if(! $isprinter) adminfooter() ?>

</body>
</html>

