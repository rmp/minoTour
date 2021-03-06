<?php
ini_set('max_execution_time', 300);
header('Content-Type: application/json');
// checking for minimum PHP version
if (version_compare(PHP_VERSION, '5.3.7', '<')) {
    exit("Sorry, Simple PHP Login does not run on a PHP version smaller than 5.3.7 !");
} else if (version_compare(PHP_VERSION, '5.5.0', '<')) {
    // if you are using PHP 5.3 or PHP 5.4 you have to include the password_api_compatibility_library.php
    // (this library adds the PHP 5.5 password hashing functions to older versions of PHP)
    require_once("../libraries/password_compatibility_library.php");
}

// include the configs / constants for the database connection
require_once("../config/db.php");

// load the login class
require_once("../classes/Login.php");

// load the functions
require_once("../includes/functions.php");

// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process. in consequence, you can simply ...
$login = new Login();

// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true) {
    // the user is logged in. you can do whatever you want here.
    // for demonstration purposes, we simply show the "you are logged in" view.
    //include("views/index_old.php");*/
	if($_GET["prev"] == 1){
		$mindb_connection = new mysqli(DB_HOST,DB_USER,DB_PASS,$_SESSION['focusrun']);
	}else{
		$mindb_connection = new mysqli(DB_HOST,DB_USER,DB_PASS,$_SESSION['active_run_name']);
	}
	//echo cleanname($_SESSION['active_run_name']);;

	//echo '<br>';
	
	
	if (!$mindb_connection->connect_errno) {
		
		$jsonjobname="allqualities";
			
		$checkrow = "select name,json from jsonstore where name = '" . $jsonjobname . "' ;";
		$checking=$mindb_connection->query($checkrow);
		if ($checking->num_rows ==1){
			//echo "We have already run this!";
			foreach ($checking as $row){
				$jsonstring = $row['json'];
			}
		} else {
		//Check if we want to get a single read or all reads.
		if (isset($GET_["readname"])) {
			//Check if entry already exists in jsonstore table:
				$sql_template = "select qual,seqid from basecalled_template where basename = '" . $GET_['readname'] ."';";
				$sql_complement = "select qual,seqid from basecalled_complement where basename = '" . $GET_['readname'] ."';";
				$sql_2d = "select qual,seqid from basecalled_2d where basename = '" . $GET_['readname'] ."';";
			}else{
				$sql_template = "select qual,seqid from basecalled_template ORDER BY RAND() LIMIT 100;";
				$sql_complement = "select qual,seqid from basecalled_complement ORDER BY RAND() LIMIT 100;";
				$sql_2d = "select qual,seqid from basecalled_2d ORDER BY RAND() LIMIT 100;";
			}		
		
		$resultarray;
	
		$template=$mindb_connection->query($sql_template);
		$complement=$mindb_connection->query($sql_complement);
		$read2d=$mindb_connection->query($sql_2d);
		
		if ($template->num_rows >= 1){
			foreach ($template as $row) {
				$result = substr($row['qual'], 1, -1);
				$resultarray['template'][$row['seqid']]=$result;
			}
		}
		if ($complement->num_rows >= 1){
			foreach ($complement as $row) {
				$result = substr($row['qual'], 1, -1);
				$resultarray['complement'][$row['seqid']]=$result;
			}
		}
		if ($read2d->num_rows >= 1){
			foreach ($read2d as $row) {
				$result = substr($row['qual'], 1, -1);
				$resultarray['2d'][$row['seqid']]=$result;
			}
		}
		
		$qualityarray;
		
		foreach ($resultarray as $key => $value) {
			//echo $key."\n";
			foreach ($value as $key2 => $value2){
				//echo $value2."\n";
				$qualarray = str_split($value2);
				$counter = 1;
				foreach ($qualarray as $value3){
					//echo $key . "\t" . $counter . "\n";
					
					$qualityarray[$key][$counter]['value'] = $qualityarray[$key][$counter]['value']+(chr(ord($value3)-31));
					$qualityarray[$key][$counter]['number']++;
					$counter++;
				}
			}
		}
		
		//var_dump($resultarray);
		//echo json_encode($resultarray);
		$jsonstring;
		$jsonstring = $jsonstring . "[\n";
			foreach ($qualityarray as $key => $value){
				$jsonstring = $jsonstring .  "{\n";
				$jsonstring = $jsonstring .  "\"name\" : \"$key\",\n";
				$jsonstring = $jsonstring .  "\"data\": [";
				foreach ($value as $key2 => $value2) {
					$jsonstring = $jsonstring .  "[" . $key2 . "," . ($qualityarray[$key][$key2]['value']/$qualityarray[$key][$key2]['number']) . "],\n" ;
				}

				$jsonstring = $jsonstring .  "]\n},\n";
				

				
			}
			$jsonstring = $jsonstring .   "]\n";
			if ($_GET["prev"] == 1){
				//include 'savejson.php';
			}
		}
	
			
			
	$callback = $_GET['callback'];
	echo $callback.'('.$jsonstring.');';
	
	}
} else {
	echo "ERROR";
}
?>