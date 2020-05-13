<?php

//header("Access-Control-Allow-Origin: *"); //don't need here, already defined in config.php
require 'config.php';
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

$app->post('/login','login'); /* User login */
$app->post('/signup','signup'); /* User Signup  */
$app->post('/getEvents','getEvents'); /* get events  */
$app->post('/getRights','getRights'); /* get person  */
$app->post('/update_user','update_user'); /* update user  */
$app->post('/chgpwd','chgpwd'); /* change user pass word*/
$app->post('/getAffectedPerson','getAffectedPerson'); /* get affected person  */
$app->post('/getNEvtByYrPlc','getNEvtByYrPlc'); ////get number of events by year and place with the given year (from 2547)
$app->post('/getNEvtByYr','getNEvtByYr');//get number of events by year (from 2547)
$app->post('/getNEvtByPlc','getNEvtByPlc');//get number of events by place 
$app->post('/getNEvtByPlcNyr','getNEvtByPlcNyr');//get number of events by place and year (from 2547)
$app->post('/getNPByYrNlostyp','getNPByYrNlostyp');//get number of affected people year and lost-type
$app->post('/getNPByProvNyr','getNPByProvNyr');//get number of affected people province and year
$app->run();

/************************* USER LOGIN *************************************/
/* ### User login ### */

function login() {
    
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $email = $data->email;
	$pwd = $data->pwd;
    try {
        
        $db = getDBConnect(); //get the connection object from config.php
        //$userData ='';
    
		$pwd = hash("sha256",$pwd); //hashing the pwd before comparing in select statement

		$sql = "SELECT * FROM app_users WHERE email='$email' and pwd='$pwd'";
        $result = mysqli_query($db,$sql);
          
		$mainCount = mysqli_num_rows($result);
		
        if($userData = mysqli_fetch_object($result)){
            $userData = json_encode($userData);
            echo '{"userData": ' .$userData . '}';
			
			$dte =  Date('Y-m-d H:i:s'); //record log	
			$sql2 = "UPDATE app_users SET tmlogs = (tmlogs+1), lastlog='$dte'
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
    $uid = $data->uid;
    $oldpwd = $data->oldpwd;
	$newpwd = $data->newpwd;
   try {
        
        $db = getDBConnect(); //get the connection object from config.php
        $newpwd = hash("sha256",$newpwd);
        $oldpwd = hash("sha256",$oldpwd);

        $sql = "SELECT * FROM app_users WHERE uid='$uid' and pwd='$oldpwd'";
        $result = mysqli_query($db,$sql);
          
        $mainCount = mysqli_num_rows($result);

        if($mainCount==1){
            $sql2 = "UPDATE app_users SET pwd = '$newpwd' WHERE uid = '$uid'";
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

function update_user() {	
	
	$request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
	$uid = $data->uid;
    $email = $data->email;
    $nme = $data->nme;
    $snme =$data->snme;
	$dob = $data->dob;
	$gender = $data->gender;
    //$pwd = $data->pwd; //pass shall be changed by another method
	
	try {
        $db = getDBConnect();
        //$username_check = preg_match('~^[A-Za-z0-9_]{3,20}$~i', $username);
        $email_check = preg_match('~^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$~i', $email);
        //$password_check = preg_match('~^[A-Za-z0-9!@#$%^&*()_]{6,20}$~i', $pwd);
        //TODO: above shall be used for checking 
		$sql = "SELECT * FROM app_users WHERE uid != '$uid' and email='$email'";
        $result = mysqli_query($db,$sql);        
		$mainCount=mysqli_num_rows($result);
		
		if($mainCount>0){
			echo '{"error":{"text":"The email specified have been registered"}}';
		}else{

            mysqli_query($db,"UPDATE app_users SET nme = '$nme', snme='$snme', gender='$gender', dob='$dob', email='$email'
							WHERE uid = $uid");
							
			$userData=internalUserDetails($email);	
			
			$userData = json_encode($userData);
            echo '{"userData": ' .$userData . '}';			
		}
   }
   catch(PDOException $e) {
       echo '{"error":{"text":'. $e->getMessage() .'}}';
   }
}

function getEvents(){
	
	$request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());

	$kdte1 = $data->kdte1;//$_GET['kdte1'];
    $kdte2 = $data->kdte2;//$_GET['kdte2'];
	$kevnname = $data->kname;//$_GET['kname'];
	$kevnplc = $data->kplc;//$_GET['kplc'];
	$kevnplcgen =$data->kplcgen;// $_GET['kplcgen'];
	$pg = $data->pg;//$_GET['pg'];
	
	//$kdte1 = dateEng_stored_format($kdte1);
	//$kdte2 = dateEng_stored_format($kdte2);
	
	//echo $kdte1." ".$kdte2;
	
	$db = getDBConnect();
	
    //seting starting record to read, based on number records per page (p_size)
	$p_size = 10;	
    $start = $p_size*($pg-1);  
    
	$sql = "SELECT event.evnid as evnid, event.evnnme as evnname, event.evndte as evndate,
                       event.evntme as evntime, event.evnplc as evnplc, event.evndes as des, 
                       const_plc.plc_lat as lat, const_plc.plc_lng as lng,
                       const_plc.plcnme as evnplcgen,
                       event.dop_endorse as dop,
                       event.mil_endorse as mil,
                       event.pol_endorse as pol
            FROM event INNER JOIN const_plc
                  ON event.evnplcidgen = const_plc.plcidgen
			WHERE event.evnnme like '%$kevnname%' ";
		if($kdte1!=""){
            $kdte1 = dateEng_stored_format($kdte1);
			 $sql = $sql." and event.evndte >= '$kdte1'";
        }
        if($kdte2!=""){
            $kdte2 = dateEng_stored_format($kdte2);
             $sql = $sql." and event.evndte <= '$kdte2'";	
        }					   
		if($kevnplc!="")
			 $sql = $sql." and event.evnplc like '%$kevnplc%'";	
		if($kevnplcgen!="")
			 $sql = $sql." and const_plc.plcnme like '%$kevnplcgen%'";	
		$sql = $sql." ORDER BY evndate DESC LIMIT $start, $p_size";
 
    $qr = mysqli_query($db,$sql);//run the query

    $events = array(); 
    
	while($row=mysqli_fetch_object($qr)){ 
        //fetch each row to to array events 		
		$events[]=$row;
	}
    //echo '{"events":'.json_encode($events).'}'; //return without checking
    if($events){
        echo '{"events": ' .json_encode($events). '}'; //return the array of events satisfied	
        //echo json_encode($events); //this is another way of sending data without hearder as the line above
    } else {
        echo '{"error": {"text":"No more record found"}}';
    }
}
function getAffectedPerson(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());

    $evnid = $data->evnid; //$_GET['evnid'];

    $db = getDBConnect();

	$qr = mysqli_query($db,"SELECT person.nme as fname, person.surnme as lname, 
		person.pid as piden, person.adr as peraddr, person.perid as perid, yylosofper.approved as approved,
		const_perlostyp.perlostypnme as pertype, const_losbody.losbodynme as lostype
		FROM yylosofper, person,const_perlostyp,const_losbody
		WHERE 	yylosofper.evnid = $evnid and
			yylosofper.perid = person.perid and
			yylosofper.perlostypid = const_perlostyp.perlostypid and
			yylosofper.losbodytyp = const_losbody.losbody"
            );

    $persons=array();
	while($row=mysqli_fetch_object($qr)){
		$persons[]=$row;
    }
    if($persons){
        echo '{"persons": ' .json_encode($persons). '}'; //return the array of affected person	
        //echo json_encode($persons); 
    } else {
        echo '{"error": {"text":"No affected person found"}}';
    }

}//end of getAffected person

function getRights(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
  
    
	$lbid = $data->lbid; //$_GET['lbid'];
	$ptid = $data->ptid;//$_GET['ptid'];
    $hcase = $data->hcase;//$_GET['hcase'];

    $db = getDBConnect();
    $qr = mysqli_query($db,"SELECT const_org.ORGANIZATION as orgnme, const_helptype.helptype as helptype,
		const_helpcond.agreement as agreement, const_rights.helptypeid as htypid, const_paytype.paytype as paytype, const_rights.tmoney as tmoney,
		const_rights.minmoney as tmin,const_rights.maxmoney as tmax, const_rights.des2shw as des2shw
		FROM const_rights, const_helptype, const_paytype, const_org, const_helpcond 
        where const_rights.active = 1
            and const_rights.losbodyid = '$lbid'
			and const_rights.pertypeid = '$ptid'
            and const_rights.hcase = '$hcase'
			and const_rights.orgid = const_org.ORGID
			and const_rights.helptypeid = const_helptype.helptypeid
			and const_rights.agreeid = const_helpcond.agreeid
            and const_rights.paytypeid = const_paytype.paytypeid ORDER BY htypid DESC");
            
	$rights =array();
	while($row=mysqli_fetch_object($qr)){
		$rights[]=$row;
    }
    if($rights){
        echo '{"rights": '.json_encode($rights).'}';
       // echo '{"persons": ' .json_encode($persons). '}'; 
    }else{
        echo '{"error": {"text":"No rights found"}}';
    }
}
/**
 * get the number of events occurred by year
 * based on the year 2547-present
 */
function getNEvtByYr(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
  
    $db = getDBConnect();
    $sql = "SELECT SUBSTRING(evndte, 1, 4) as yyyy, count(*) as ne FROM `event` WHERE SUBSTRING(evndte, 1, 4)>=2547 AND SUBSTRING(evndte, 1, 4)<=3000 GROUP BY yyyy";
    $qr = mysqli_query($db,$sql);
            
    $nevents =array();
    //$tot = 0;
	while($row=mysqli_fetch_object($qr)){
        $nevents[]=$row;
        //$tot = $tot + $row->ne;
    }
    if($nevents){
        echo '{"nevents": '.json_encode($nevents).'}';
        //echo '{"total": ' .json_encode($tot). '}'; 
    }else{
        echo '{"error": {"text":"No records found"}}';
    }
}
/**
 * get the number of event by province with the given year yyyy
 */
function getNEvtByYrPlc(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $yyyy = $data->yyyy; //of year

    $db = getDBConnect();
    $sql = "SELECT SUBSTRING(evnplcidgen,1,2) as provid, 
                    const_plcnmegen.prvnmegen as prov, count(*) as ne 
            FROM `event` LEFT JOIN const_plcnmegen ON event.evnplcidgen=const_plcnmegen.plcidgen
            WHERE SUBSTRING(evndte, 1, 4)='$yyyy' GROUP BY prov";
    $qr = mysqli_query($db,$sql);
            
    $nevents =array();
    //$tot = 0;
	while($row=mysqli_fetch_object($qr)){
        $nevents[]=$row;
       // $tot = $tot + $row->ne;
    }
    if($nevents){
        echo '{"nevents": '.json_encode($nevents).'}';
      //  echo '{"total": ' .json_encode($tot). '}'; 
    }else{
        echo '{"error": {"text":"No records found"}}';
    }
}
/**
 * get the number of event by province and year
 */
function getNEvtByPlcNyr(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    //$yyyy = $data->yyyy; //of year

    $db = getDBConnect();
    $sql = "SELECT SUBSTRING(evnplcidgen,1,2) as provid, 
    const_plcnmegen.prvnmegen as prov,
    SUBSTRING(evndte, 1, 4) as yyyy, count(*) as ne 
    FROM `event` LEFT JOIN const_plcnmegen ON event.evnplcidgen=const_plcnmegen.plcidgen
    GROUP BY provid, yyyy";
    $qr = mysqli_query($db,$sql);
            
    $nevents =array();
    //$tot = 0;
	while($row=mysqli_fetch_object($qr)){
        $nevents[]=$row;
       // $tot = $tot + $row->ne;
    }
    if($nevents){
        echo '{"nevents": '.json_encode($nevents).'}';
      //  echo '{"total": ' .json_encode($tot). '}'; 
    }else{
        echo '{"error": {"text":"No records found"}}';
    }
}
/**
 * get the number of event by province and year
 */
function getNEvtByPlc(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    //$yyyy = $data->yyyy; //of year

    $db = getDBConnect();
    $sql = "SELECT SUBSTRING(evnplcidgen,1,2) as prov, count(*) as ne 
            FROM `event` GROUP BY prov";
    $qr = mysqli_query($db,$sql);
            
    $nevents =array();
    //$tot = 0;
	while($row=mysqli_fetch_object($qr)){
        $nevents[]=$row;
       // $tot = $tot + $row->ne;
    }
    if($nevents){
        echo '{"nevents": '.json_encode($nevents).'}';
      //  echo '{"total": ' .json_encode($tot). '}'; 
    }else{
        echo '{"error": {"text":"No records found"}}';
    }
}
/**
 * SELECT SUBSTRING(evndte,1,4) as yyyy, yylosofper.losbodytyp as lbtyp,
 * const_losbody.losbodynme as lostyp, count(*) as np 
 * FROM `yylosofper` LEFT JOIN event ON event.evnid = yylosofper.evnid 
 * RIGHT JOIN const_losbody ON yylosofper.losbodytyp = const_losbody.losbody 
 * WHERE SUBSTRING(evndte,1,4)>=2547 GROUP BY yyyy,lbtyp ORDER BY yyyy, lbtyp
 *
 */

function getNPByYrNlostyp(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    //$yyyy = $data->yyyy; //of year

    $db = getDBConnect();
    $sql = "SELECT SUBSTRING(evndte,1,4) as yyyy, yylosofper.losbodytyp as lbtyp,
            const_losbody.losbodynme as lostyp, count(*) as np 
            FROM `yylosofper` LEFT JOIN event ON event.evnid = yylosofper.evnid 
            RIGHT JOIN const_losbody ON yylosofper.losbodytyp = const_losbody.losbody 
            WHERE SUBSTRING(evndte,1,4)>=2547 GROUP BY yyyy,lbtyp ORDER BY yyyy, lbtyp";
    $qr = mysqli_query($db,$sql);
            
    $npYrPtyp =array();
    //$tot = 0;
	while($row=mysqli_fetch_object($qr)){
        $npYrPtyp[]=$row;
       // $tot = $tot + $row->ne;
    }
    if($npYrPtyp){
        echo '{"nevents": '.json_encode($npYrPtyp).'}';
      //  echo '{"total": ' .json_encode($tot). '}'; 
    }else{
        echo '{"error": {"text":"No records found"}}';
    }
}
/**
 * get the number of effected persion by provice and year, sine 2547 to present
 * 
 * SELECT SUBSTRING(evnplcidgen,1,2) as provid, const_plcnmegen.prvnmegen as prov,
 *           SUBSTRING(evndte, 1, 4) as yyyy, count(*) as ne 
 *           FROM `event` LEFT JOIN const_plcnmegen ON event.evnplcidgen=const_plcnmegen.plcidgen
 *           GROUP BY provid, yyyy
 */

function getNPByProvNyr(){
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    //$yyyy = $data->yyyy; //of year

    $db = getDBConnect();
    $sql = "SELECT SUBSTRING(evnplcidgen,1,2) as provid, const_plcnmegen.prvnmegen as prov,
               SUBSTRING(evndte, 1, 4) as yyyy, count(*) as ne 
               FROM `event` LEFT JOIN const_plcnmegen ON event.evnplcidgen=const_plcnmegen.plcidgen
               GROUP BY provid, yyyy";
    $qr = mysqli_query($db,$sql);
            
    $npYrPtyp =array();
    //$tot = 0;
	while($row=mysqli_fetch_object($qr)){
        $npYrPtyp[]=$row;
       // $tot = $tot + $row->ne;
    }
    if($npYrPtyp){
        echo '{"nevents": '.json_encode($npYrPtyp).'}';
      //  echo '{"total": ' .json_encode($tot). '}'; 
    }else{
        echo '{"error": {"text":"No records found"}}';
    }
}
/* ### internal Username Details ### */
function internalUserDetails($input) {
    
    try {
        $db = getDBConnect();
 		$sql = "SELECT * FROM app_users WHERE email='$input'";
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
			
			$userDetails->token = apiKey($userDetails->uid);
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
/* ### User registration ### */
/*
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
        
       // echo $email_check.'<br/>'.$email;
        
        if ( strlen(trim($pwd))>0 && strlen(trim($email))>0 && $email_check>0 && $password_check>0)
        {
         //   echo 'here';
            $db = getDBConnect();
            $userData = '';
            //$sql = "SELECT uid FROM users WHERE username=:username or email=:email";
			$sql = "SELECT * FROM app_users WHERE email='$email'";
            $stmt = $db->prepare($sql);
            //$stmt->bindParam("username", $username,PDO::PARAM_STR);
            //$stmt->bindParam(":email", $email,PDO::PARAM_STR);
            $stmt->execute();
            $mainCount=$stmt->rowCount();
            $created=time();
			
			$dte = date("Y-m-d");

							
            if($mainCount==0)
            {
    
                $sql1="INSERT INTO app_users(nme, snme, email, pwd, reg_dte, status)
							 VALUES(:nme, :snme, :email, :pwd, :reg_dte, :status)";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam(":nme", $nme,PDO::PARAM_STR);
				$stmt1->bindParam(":snme", $snme,PDO::PARAM_STR);
				$stmt1->bindParam(":email", $email,PDO::PARAM_STR);
                $pwd = hash('2517',$data->pwd);
                $stmt1->bindParam(":pwd", $pwd, PDO::PARAM_STR);
                $stmt1->bindParam(":reg_dte", $dte);
				$stmt1->bindParam(":status", '1');
                $stmt1->execute();
                
                $userData=internalUserDetails($email);
                
            }
            
            $db = null;
         
            if($userData){
               $userData = json_encode($userData);
                echo '{"userData": ' .$userData . '}';
            } else {
                echo '{"error":{"text":"Enter valid data"}}';
            }
           
        }
        else{
            echo '{"error":{"text":"Enter valid data"}}';
        }
    }
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
*/
/*
function email() {
    $request = \Slim\Slim::getInstance()->request();
    $data = json_decode($request->getBody());
    $email=$data->email;
    try {
       
        $email_check = preg_match('~^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.([a-zA-Z]{2,4})$~i', $email);
       
        if (strlen(trim($email))>0 && $email_check>0)
        {
            $db = getDBConnect();
            $userData = '';
            $sql = "SELECT user_id FROM emailUsers WHERE email=:email";
            $stmt = $db->prepare($sql);
            $stmt->bindParam("email", $email,PDO::PARAM_STR);
            $stmt->execute();
            $mainCount=$stmt->rowCount();
            $created=time();
            if($mainCount==0)
            {
                $sql1="INSERT INTO emailUsers(email)VALUES(:email)";
                $stmt1 = $db->prepare($sql1);
                $stmt1->bindParam("email", $email,PDO::PARAM_STR);
                $stmt1->execute();
                 
            }
            $userData=internalEmailDetails($email);
			
            $db = null;
            if($userData){
               $userData = json_encode($userData);
                echo '{"userData": ' .$userData . '}';
            } else {
               echo '{"error":{"text":"Enter valid dataaaa"}}';
            }
        }
        else{
            echo '{"error":{"text":"Enter valid data"}}';
        }
    }
    
    catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
*/

?>