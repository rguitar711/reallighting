<?php
include 'vsadmin/db_conn_open.php';
include 'vsadmin/includes.php';
include 'vsadmin/inc/languageadmin.php';
include 'vsadmin/inc/incfunctions.php';


$sSQL  = "SELECT * FROM customers WHERE Email='rick@cbiz.com";
			if(mysql_num_rows(mysql_query($sSQL))<=0){
				echo 'works';

				}else{
                    echo 'not working';
                }

?>