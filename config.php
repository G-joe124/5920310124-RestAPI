<?php
//ob_start("ob_gzhandler");
//error_reporting(0);
header("Access-Control-Allow-Origin: *");  //this was added for allowing access form any domain
session_start();

/* DATABASE CONFIGURATION */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '5920310124');
define('DB_DATABASE', 'viabusinpsu');
// define("BASE_URL", "http://localhost/vaibusPSU/api/index.php");
define("BASE_URL", "http://172.20.10.2/vaibusPSU/api/index.php");
define("SITE_KEY", '2517');

date_default_timezone_set("Asia/Bangkok");

function getDBConnect() {
	
	$con = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);
		mysqli_query($con,"SET NAMES UTF8"); //to make Thai readable
	
		if($con){
			//echo "connect successful";
			return $con; 
		}else{ 
			echo "connection error";
		}

}
/* DATABASE CONFIGURATION END */

/* API key encryption */
function apiToken($session_uid)
{
	$key=md5(SITE_KEY.$session_uid);
	return hash('sha256', $key);
}

?>