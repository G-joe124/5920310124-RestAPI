<?php
//header('Access-Control-Allow-Origin: *'); //this command is already headed in config.php
require 'config.php';

$target_path = "usrpics/";
 
$target_path = $target_path . basename( $_FILES['file']['name']);
 
if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
    echo "Upload and move success";
} else {
echo $target_path;
    echo "There was an error uploading the file, please try again!";
}

//connect and update target_path of the picture to db        
$db = getDBConnect(); //get the connection object from config.php
$uid = $_GET['uid'];

mysqli_query($db,"UPDATE app_users SET usrpic = '$target_path'
					WHERE uid = $uid");
					
//$sql="UPDATE app_users SET pic = '$target_path' WHERE uid = $uid";				
//$result = mysqli_query($db,$sql);

?>