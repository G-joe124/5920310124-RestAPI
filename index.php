<?php

//header("Access-Control-Allow-Origin: *"); //don't need here, already defined in config.php
require 'config.php';
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

$app->post('/login','login'); /* User login */
$app->post('/signup','signup'); /* User Signup  */
$app->post('/update_user','update_user'); /* update user  */
$app->post('/chgpwd','chgpwd'); /* change user pass word*/

$app->post('/update_img','update_img'); /* change user pass word*/

$app->post('/test','test'); /* change user pass word*/

// $app->post('/showbusstop','showbusstop');
$app->post('/showBusStop','showBusStop');
$app->post('/getbusdrvschedule','getbusdrvschedule');
$app->post('/showBus','showBus');
$app->post('/strdrive','strdrive');
$app->post('/stpdrive','stpdrive');
$app->post('/adduse','adduse');

$app->run();

/************************* USER LOGIN *************************************/
/* ### User login ### */

function test() {
	echo '{"test":{"text":"Test message"}}';
}

// function login() {
    
//     $request = \Slim\Slim::getInstance()->request();
//     $data = json_decode($request->getBody());
//     $email = $data->email;
// 	$pwd = $data->pwd;
//     try {
        
//         $db = getDBConnect(); //get the connection object from config.php
//         //$userData ='';
    
// 		$pwd = hash("sha256",$pwd); //hashing the pwd before comparing in select statement

// 		$sql = "SELECT * FROM app_users WHERE email='$email' and pwd='$pwd'";
//         $result = mysqli_query($db,$sql);
          
// 		$mainCount = mysqli_num_rows($result);
		
//         if($userData = mysqli_fetch_object($result)){

//             $userData = json_encode($userData);
            
//             echo '{"userData": ' .$userData . '}';
			
// 			$dte =  Date('Y-m-d H:i:s'); //record log	
// 			$sql2 = "UPDATE app_users SET tmlogs = (tmlogs+1), lastlog='$dte'
// 							WHERE email = '$email'
// 							and pwd = '$pwd'";
// 			mysqli_query($db,$sql2);
//         } else {
//                echo '{"error":{"text":"Bad request wrong username and password"}}';
//         } 
// 		$db = null;//means $db->close()
//     }
//     catch(PDOException $e) {
//         echo '{"error":{"text":'. $e->getMessage() .'}}';
//     }
// }

function login() {
    
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $email = $data->email;
	$pwd = $data->pwd;
    try {
        
        $db = getDBConnect(); //get the connection object from config.php
        //$userData ='';
    
		$pwd = hash("sha256",$pwd); //hashing the pwd before comparing in select statement

		$sql = "SELECT * FROM driver WHERE email='$email' and pwd='$pwd'";
        $result = mysqli_query($db,$sql);
          
		$mainCount = mysqli_num_rows($result);
		
        if($userData = mysqli_fetch_object($result)){

            $userData = json_encode($userData);
            
            echo '{"userData": ' .$userData . '}';
			
			$dte =  Date('Y-m-d H:i:s'); //record log	
			$sql2 = "UPDATE driver SET tmlogs = (tmlogs+1), lastlog='$dte'
							WHERE email = '$email'
							and pwd = '$pwd'";
			mysqli_query($db,$sql2);
        } else {
               echo '{"error":{"text":"Bad request wrong username and password"}}';
        } 
		$db = null;//means $db->close()
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function chgpwd() {
    
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $drvid = $data->drvid;
    $oldpwd = $data->oldpwd;
    $newpwd = $data->newpwd;
    // $conpwd = $data->conpwd;
   try {
        
        $db = getDBConnect(); //get the connection object from config.php
        $newpwd = hash("sha256",$newpwd);
        $oldpwd = hash("sha256",$oldpwd);
        // $conpwd = hash("sha256",$conpwd);

        $sql = "SELECT * FROM driver WHERE drvid='$drvid' and pwd='$oldpwd'";
        $result = mysqli_query($db,$sql);
          
        $mainCount = mysqli_num_rows($result);

        if($mainCount==1){
            $sql2 = "UPDATE driver SET pwd = '$newpwd' WHERE drvid = '$drvid'";
            if(mysqli_query($db,$sql2)){
                echo '{"success":{"text":"completed"}}';                      
            } else { echo '{"success":{"text":"not completed"}}';}

        }else{
            echo '{"error":{"text":"Invalid old password"}}';
        }
    } catch (PDOException $e){ //TODO: recheck and change exception error
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
    
}

function signup() {
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
	
    $email = $data->email;
    $nme = $data->nme;
    $snme =$data->snme;
    $pwd = $data->pwd;
	
	try {     
        //$username_check = preg_match('~^[A-Za-z0-9_]{3,20}$~i', $username);
        $email_check = preg_match('~^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$~i', $email);
        $password_check = preg_match('~^[A-Za-z0-9!@#$%^&*()_]{6,20}$~i', $pwd);
         
        if ( strlen(trim($pwd))>0 && strlen(trim($email))>0 && $email_check>0)
        {
            $db = getDBConnect();
 			$sql = "SELECT * FROM app_users WHERE email='$email'";
            $result = mysqli_query($db,$sql);
             
			$mainCount=mysqli_num_rows($result);
			
            $created=time();
			
			$reg_dte = date("Y-m-d");
			$status = "1";
							
            if($mainCount==0)
            {
                $pwd = hash("sha256",$pwd); //call a method to encript password
 
                $sql2="INSERT INTO app_users(nme, snme, email, pwd, reg_dte, status)
							 VALUES('$nme', '$snme', '$email', '$pwd', '$reg_dte', '$status')";
				$result = mysqli_query($db,$sql2);
				
                $userData=internalUserDetails($email);
				
                if($userData){
					$userData = json_encode($userData);
					echo '{"userData": ' .$userData . '}';
				} else {
					echo '{"error":{"text":"Sign up not completed"}}';
				}  
            }else{
				echo '{"error":{"text":"Email specified have been registered"}}';
			}
            $db = null;			
        }
        else{
            echo '{"error":{"text":"Invalid data"}}';
        }
   }
   catch(PDOException $e) {
       echo '{"error":{"text":'. $e->getMessage() .'}}';
   }
}

// function update_user() {	
	
// 	$request = \Slim\Slim::getInstance()->request();
//     $data = json_decode($request->getBody());
// 	$uid = $data->uid;
//     $email = $data->email;
//     $nme = $data->nme;
//     $snme =$data->snme;
// 	$dob = $data->dob;
// 	$gender = $data->gender;
//     //$pwd = $data->pwd; //pass shall be changed by another method
	
// 	try {
//         $db = getDBConnect();
//         //$username_check = preg_match('~^[A-Za-z0-9_]{3,20}$~i', $username);
//         $email_check = preg_match('~^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$~i', $email);
//         //$password_check = preg_match('~^[A-Za-z0-9!@#$%^&*()_]{6,20}$~i', $pwd);
//         //TODO: above shall be used for checking 
// 		$sql = "SELECT * FROM app_users WHERE uid != '$uid' and email='$email'";
//         $result = mysqli_query($db,$sql);        
// 		$mainCount=mysqli_num_rows($result);
		
// 		if($mainCount>0){
// 			echo '{"error":{"text":"The email specified have been registered"}}';
// 		}else{

//             mysqli_query($db,"UPDATE app_users SET nme = '$nme', snme='$snme', gender='$gender', dob='$dob', email='$email'
// 							WHERE uid = $uid");
							
// 			$userData=internalUserDetails($email);	
			
// 			$userData = json_encode($userData);
//             echo '{"userData": ' .$userData . '}';			
// 		}
//    }
//    catch(PDOException $e) {
//        echo '{"error":{"text":'. $e->getMessage() .'}}';
//    }
// }

function update_user() {	
	
	$request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
	$drvid = $data->drvid;
    $fnme = $data->fnme;
    $lnme =$data->lnme;
    $tel = $data->tel;
    $licenNo = $data->licenNo;
    $email = $data->email;
    // $regisdte = $data->regisdte;
	$gender = $data->gender;
    //$pwd = $data->pwd; //pass shall be changed by another method
	
	try {
        $db = getDBConnect();
        //$username_check = preg_match('~^[A-Za-z0-9_]{3,20}$~i', $username);
        $email_check = preg_match('~^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$~i', $email);
        //$password_check = preg_match('~^[A-Za-z0-9!@#$%^&*()_]{6,20}$~i', $pwd);
        //TODO: above shall be used for checking 
		$sql = "SELECT * FROM driver WHERE drvid <> '$drvid' and email='$email'";
        $result = mysqli_query($db,$sql);        
		$mainCount=mysqli_num_rows($result);
		
		if($mainCount>0){
			echo '{"error":{"text":"The email specified have been registered"}}';
		}else{

            mysqli_query($db,"UPDATE driver SET fnme = '$fnme', lnme='$lnme', gender='$gender', tel='$tel', licenNo='$licenNo', email='$email'
							WHERE drvid = $drvid");
							
			$userData=internalUserDetails($email);	
			
			$userData = json_encode($userData);
            echo '{"userData": ' .$userData . '}';			
		}
   }
   catch(PDOException $e) {
       echo '{"error":{"text":'. $e->getMessage() .'}}';
   }
}

function update_img() {
    
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $uid = $data->uid;
   
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
    $uid ='46'; // $uid = $_GET['uid'];
    
    mysqli_query($db,"UPDATE app_users SET usrpic = '$target_path'
                        WHERE uid = $uid");
    
}

/* ### internal Username Details ### */
function internalUserDetails($input) { //case driver
    
    try {
        $db = getDBConnect();
        //  $sql = "SELECT * FROM app_users WHERE email='$input'";
         $sql = "SELECT * FROM driver WHERE email='$input'";
        /*       
		$sql = "SELECT user_id, name, email, username FROM users WHERE username=:input or email=:input";
		$stmt = $db->prepare($sql);
        $stmt->bindParam("input", $input,PDO::PARAM_STR);
        $stmt->execute();
        $userDetails = $stmt->fetch(PDO::FETCH_OBJ);
        $userDetails->token = apiToken($userDetails->uid);
        $db = null;
        return $userDetails;
		*/
		$result = mysqli_query($db,$sql);
		
		if($userDetails = mysqli_fetch_object($result)){	
			$db = null;
			
			$userDetails->token = apiToken($userDetails->drvid);
			return $userDetails;
		}
        
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
    
}
function dateEng_stored_format($dt){
	
    //input date $dt 2018-01-12
	//output is 25611201
	
	if(substr($dt,0,2)<25)
		$yyyy = substr($dt,0,4)+ 543;
	else $yyyy = substr($dt,0,4);
	$mm = substr($dt,5,2);
	$dd = substr($dt,8,2);
	return ($yyyy.$mm.$dd);
}

function showBusStop() {
   
    try {
        $db = getDBConnect();
 		$sql = "SELECT * FROM bus_stop";
       
		// $result = mysqli_query($db,$sql);
        // $mainCount=mysqli_num_rows($result);
        // if($mainCount > 0){
        //     $bstp = json_encode($result);
        //     // $bstp->token = apiToken($bstp->bus_stopno);
        //     echo '{'.$mainCount .'}';
        //     echo '{"bus_stop": ' .$bstp . '}';
        // 
        $result = mysqli_query($db,$sql);//run the query

        $bstp = array(); 
        
        while($row=mysqli_fetch_object($result)){ 
            //fetch each row to to array events 		
            $bstp[]=$row;
        }
        //echo '{"events":'.json_encode($events).'}'; //return without checking
        if($bstp){
            echo '{"bus_stop": ' .json_encode($bstp). '}'; //return the array of events satisfied	
            //echo json_encode($events); //this is another way of sending data without hearder as the line above
        } else {
            echo '{"error": {"text":"No more record found"}}';
        } 
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function showBus() {
   
    try {
        $db = getDBConnect();
 		$sql = "SELECT * FROM bus WHERE statuss = 1";
       
		// $result = mysqli_query($db,$sql);
        // $mainCount=mysqli_num_rows($result);
        // if($mainCount > 0){
        //     $bstp = json_encode($result);
        //     // $bstp->token = apiToken($bstp->bus_stopno);
        //     echo '{'.$mainCount .'}';
        //     echo '{"bus_stop": ' .$bstp . '}';
        // 
        $result = mysqli_query($db,$sql);//run the query

        $bus = array(); 
        
        while($row=mysqli_fetch_object($result)){ 
            //fetch each row to to array events 		
            $bus[]=$row;
        }
        //echo '{"events":'.json_encode($events).'}'; //return without checking
        if($bus){
            echo '{"bus": ' .json_encode($bus). '}'; //return the array of events satisfied	
            //echo json_encode($events); //this is another way of sending data without hearder as the line above
        } else {
            echo '{"error": {"text":"No more record found"}}';
        } 
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function strdrive() {
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $busno = $data->busno;
    $drvid = $data->drvid;

    try {
        // $time=time();
        $time=date("H:i:s");
        $dte = date("Y-m-d");
        // $dte = date("h:i:s", time());

        $db = getDBConnect();
 		// $sql = "SELECT * FROM bus WHERE statuss = 1";
        $sql = "UPDATE bus SET statuss = 1 WHERE busno = '$busno'";
        $result = mysqli_query($db,$sql);//run the query
        $sql2="INSERT INTO bususe(busno, drvid, dte, timestr, timestp)
                VALUES('$busno', '$drvid', '$dte', '$time', '00:00:00')";
        $result = mysqli_query($db,$sql2);
		// $result = mysqli_query($db,$sql);
        // $mainCount=mysqli_num_rows($result);
        // if($mainCount > 0){
        //     $bstp = json_encode($result);
        //     // $bstp->token = apiToken($bstp->bus_stopno);
        //     echo '{'.$mainCount .'}';
        //     echo '{"bus_stop": ' .$bstp . '}';
        // 
        // $bus = array(); 
        if(mysqli_query($db,$sql)){
            if(mysqli_query($db,$sql2)){
                 echo '{"success":{"text":"completed"}}';  
            }else { echo '{"success":{"text":"not completed"}}';}
        } else { echo '{"success":{"text":"not completed"}}';}
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function stpdrive() {
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $busno = $data->busno;
    $drvid = $data->drvid;

    try {
        // $time=date("H:i:s");
        $dte = date("Y-m-d");

        $db = getDBConnect();
        $sql = "UPDATE bus SET statuss = 2 WHERE busno = '$busno'";
        $result = mysqli_query($db,$sql);//run the query
        $time=date("H:i:s");
        $sql2 = "UPDATE bususe SET timestp = $time WHERE busno = '$busno' AND drvid = '$drvid' AND dte = '$dte' AND timestp = '00:00:00' ";
        $result = mysqli_query($db,$sql2);
        if(mysqli_query($db,$sql)){
            if(mysqli_query($db,$sql2)){
                echo '{"success":{"text":"completed"}}';  
            }else { echo '{"success":{"text":"not completed"}}';}               
        } else { echo '{"success":{"text":"not completed"}}';}
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}


function getbusdrvschedule() {
   
    try {
        $db = getDBConnect();
 		$sql = "SELECT * FROM busdrvsschedule WHERE dte = CURDATE()";
       
		// $result = mysqli_query($db,$sql);
        // $mainCount=mysqli_num_rows($result);
        // if($mainCount > 0){
        //     $bstp = json_encode($result);
        //     // $bstp->token = apiToken($bstp->bus_stopno);
        //     echo '{'.$mainCount .'}';
        //     echo '{"bus_stop": ' .$bstp . '}';
        // 
        $result = mysqli_query($db,$sql);//run the query

        $bsscd = array(); 
        
        while($row=mysqli_fetch_object($result)){ 
            //fetch each row to to array events 		
            $bsscd[]=$row;
        }
        //echo '{"events":'.json_encode($events).'}'; //return without checking
        if($bsscd){
            echo '{"schedule": ' .json_encode($bsscd). '}'; //return the array of events satisfied	
            //echo json_encode($events); //this is another way of sending data without hearder as the line above
        } else {
            echo '{"error": {"text":"No more record found"}}';
        } 
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function adduse() {
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
	
    $busno = $data->busno;
    $drvid = $data->drvid;
    $amount =$data->amount;
    
    try {
        $dte = date("Y-m-d");
        $db = getDBConnect();
        $sql = "SELECT * FROM dayuse WHERE busno='$busno' AND drvid='$drvid' AND dte='$dte'";
        $result = mysqli_query($db,$sql);//run the query
        $mainCount=mysqli_num_rows($result);
        if($mainCount==0){
            $dte = date("Y-m-d");
            $sql2="INSERT INTO dayuse(busno, drvid, dte, amount)
							 VALUES('$busno', '$drvid', '$dte', '$amount')";
            $result = mysqli_query($db,$sql2);
            if(mysqli_query($db,$sql2)){
                echo '{"success":{"text":"completed"}}';  
            }else { echo '{"success":{"text":"not completed"}}';} 
        }else {
            $dte = date("Y-m-d");
            $sql3 = "UPDATE dayuse SET amount = $amount WHERE busno='$busno' AND drvid='$drvid' AND dte='$dte'";
            $result = mysqli_query($db,$sql3);
            if(mysqli_query($db,$sql3)){
                echo '{"success":{"text":"completed"}}';  
            }else { echo '{"success":{"text":"not completed"}}';} 
            }
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

?>