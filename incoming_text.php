<?php
  
  //Start session
	session_start();
	
	//Include database connection details
	require_once('/home/mattbils/config.php');
	
	//Array to store validation errors
	$errmsg_arr = array();
	
	//Validation error flag
	$errflag = false;
	
	//Connect to mysql server
	$link = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);
	if(!$link) {
		die('Failed to connect to server: ' . mysql_error());
	}
	
	//Select database
	$db = mysql_select_db(DB_DATABASE);
	if(!$db) {
		die("Unable to select database");
	}
  
  //Function to sanitize values received from the form. Prevents SQL injection
	function clean($str) {
		$str = @trim($str);
		if(get_magic_quotes_gpc()) {
			$str = stripslashes($str);
		}
		return mysql_real_escape_string($str);
	}
  
  $msg = $_REQUEST['text'];
  $sender = $_REQUEST['msisdn'];
  echo "You said: ";
  $text = htmlentities($msg);
    $cell = htmlentities($sender);
    
    //check database of blocked numbers to see if we should proceed
    //SELECT * FROM `blocked` WHERE `cell` = 'ADMIN_CELL_NUMBER_HERE'
    	$insert_data = mysql_real_escape_string($cell);
$qry="SELECT * FROM `blocked` WHERE `cell` = ('$insert_data')";
    $result=mysql_query($qry);	
echo(mysql_num_rows($result));

//if the user isn't blocked
if(mysql_num_rows($result) == 0) {

//now analyze the message

//see if it contains "next" as the first 4 letters (ignore case)



$msgLower = strtolower ( $text );

$posNext = strpos($msgLower, "next");

$posHttp = strpos($msgLower, "http");

//only if next is the first word and no http

if($posNext == 0 && $posHttp === false){
echo("maybe");
//see if user has already voted next
	$insert_data = mysql_real_escape_string($cell);
$qry="SELECT * FROM `next` WHERE `cell` = ('$insert_data')";
    $result=mysql_query($qry);	
echo(mysql_num_rows($result));
echo($cell);
//if admin or hasn't voted...add next
if(mysql_num_rows($result) == 0 || $cell === "ADMIN_CELL_NUMBER_HERE"){
echo("conidtion");

//add cell to next list
$insert_data = mysql_real_escape_string($cell);
$qry="INSERT INTO mattbils_snotunes.next (cell) VALUES ('$insert_data')";

$result=mysql_query($qry);	
}else{
echo("name is already");
}
}else if($posHttp !== false){

echo("looking for link");

$youtubeURL = substr($text,$posHttp);

//$youtubeURL = $explodeURL[1];
//var_dump($explodeURL);

echo($youtubeURL);

//now check to see if user has already requested a song today
$insert_data = mysql_real_escape_string($cell);
$qry="SELECT * FROM `users` WHERE `number` = ('$insert_data')";
    $result=mysql_query($qry);	
    if(mysql_num_rows($result) == 0 || $cell === "ADMIN_CELL_NUMBER_HERE"){
    
    echo("valid request");
    
    //now add song to playlist
    $insert_data = mysql_real_escape_string($youtubeURL);
$qry="INSERT INTO mattbils_snotunes.requests (song) VALUES ('$insert_data')";

$result=mysql_query($qry);	

//add number to list of users
$insert_data = mysql_real_escape_string($cell);
$qry="INSERT INTO mattbils_snotunes.users (number) VALUES ('$insert_data')";

$result=mysql_query($qry);
    }


}
//add the cell to the next table

var_dump($result);
   $send= sprintf('%s %s %s',$text,$cell,$result);

  //Sanitize the POST values

	
	 
	  
	  //If there are input validations, redirect back to the login form
	if($errflag) {
		$_SESSION['ERRMSG_ARR'] = $errmsg_arr;
		session_write_close();
		echo "fail";
		exit();
	}

}else{
echo("fail user on blocked list");
}
	  
?>