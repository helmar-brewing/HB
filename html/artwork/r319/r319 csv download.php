<?php
 
ob_start();
 
// do all the stuff from other pages, like connect to database and check for login etc. - Robert: copied most from other php 319 code
 /* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* WHICH DATABASES DO WE NEED */
$db2use = array(
	'db_auth' 	=> TRUE,
	'db_main'	=> TRUE
);

/* GET KEYS TO SITE */ require($path_to_keys);

/* LOAD FUNC-CLASS-LIB */
require_once('classes/phnx-user.class.php');

// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(1);


 
 
// remember to handle no being logged in somehow, including closing the database connects and calling ob_end_flush();
 
 /*
 
 if not logged in then
 	give message?
	ob_end_flush();
	end this script
	
*/



// you should rename these variables to make sense for whatever you are doing
 
// set the name of the files to be downloaded
$file = 'r319-checklist.csv';
 
// open a file on the server to be written
$fp = fopen($file, 'w');
 
// set the headers for the spreadsheet,
$csvheaders = array('Card Number','Player Name','Position','Team Name');
 
// write the headers to the file
fputcsv($fp, $csvheaders);
 
 
// write the file with a while loop. you could also instead build an array of your data with a while loop, up before you set the filename.
// that would allow you to make sure that there were no errors, then use a for each loop down where to write the data.
// it would also allow you to change the format of the info form the database.
// for example, in this dataset perhaps 'vote' is a booleen in the database, but you want to display it as 'yes', or 'no' in the CSV.
$result = $db_main->query("SELECT * FROM cardList WHERE series = 'R319-Helmar'");
if($result != FALSE){
	$result->dataseek(0);
	while($object = $result->fetch_object()){
		// your csvline needs to be a simple array
		$csvline = array($object->cardnum,$object->player,$object->description,$object->team);
		fputcsv($fp, $csvline);
	}
}
 
// close the connection to file you are writign to
fclose($fp);
 
// close database connections
$db->close();
 
// send the HTTP headers
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.basename($file));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($file));
 
// clear the output buffer
ob_clean();
ob_end_flush();
flush();
 
// send the file to the browser
readfile($file);
 
// delete the file from the server
unlink($file);
 
?>