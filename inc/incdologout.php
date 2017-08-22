<?php	$_SESSION['loggedon']='';
		setcookie('WRITECKL', '', (time() - 2592000), '/', '', 0);
		setcookie('WRITECKP', '', (time() - 2592000), '/', '', 0);
		print '<meta http-equiv="Refresh" content="3; URL=admin.php">';
?>  
  <div class="row centerit">
	<div class="login_message">
		<h2 class="centerit"><?php print $yyLogOut?></h2>
		<p><?php print $yyLOMes?></p>
	</div>
  </div>