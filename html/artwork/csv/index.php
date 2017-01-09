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
require_once('libraries/stripe/init.php');
\Stripe\Stripe::setApiKey($apikey['stripe']['secret']);

// Card Series Variables
$series_id = $_GET['series'];

// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(1);

// check user subscription status
$user->checksub();



if(isset($user)){
   if( $user->login() == 1 || $user->login() == 2 ){
   /* do this code if user is logged in */


       $series_sql = $db_main->query("SELECT * FROM series_info WHERE series_id='".$series_id."' LIMIT 1");
       if($series_sql !== FALSE){
           $series_sql->data_seek(0);
           while($seriesinfo = $series_sql->fetch_object()){
             $series_tag = $seriesinfo->series_tag;
             $series_name = $seriesinfo->series_name;
           }
       } else {
         // need error handling - don't load page at all!
       }
       $series_sql->free();



       // set the name of the files to be downloaded
       $file = $series_tag.'-checklist.csv';

       // open a file on the server to be written
       $fp = fopen($file, 'w');

       // query to pull from cardList series
      $R_cards = $db_main->query("SELECT * FROM cardList WHERE series = '".$series_tag."'");

               // set the headers for the spreadsheet,
               $csvheaders = array('Series','Card Number','Player Name','Position','Team Name','Last Sold','Max Sell');

               // write the headers to the file
               fputcsv($fp, $csvheaders);



               // write the file with a while loop. you could also instead build an array of your data with a while loop, up before you set the filename.
               // that would allow you to make sure that there were no errors, then use a for each loop down where to write the data.
               // it would also allow you to change the format of the info form the database.
               // for example, in this dataset perhaps 'vote' is a booleen in the database, but you want to display it as 'yes', or 'no' in the CSV.
               $result = $db_main->query("SELECT * FROM cardList WHERE series = '".$series_tag."'");
               if($result != FALSE){
                 $result->data_seek(0);
                 while($object = $result->fetch_object()){
                   // your csvline needs to be a simple array
                   $csvline = array($series_name,$object->cardnum,$object->player,$object->description,$object->team,$object->lastsold,$object->maxSold);
                   fputcsv($fp, $csvline);
                 }
               }



             // this code is common if user has subscription or not, but logged in


             // close the connection to file you are writign to
             fclose($fp);

             // close database connections
             $result->free();


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





   /* END code if user is logged in */
   }else{
   /* do this if user is not logged in */
   print 'To view Checklist, please log in or create an account';    }
}else{
   /* do this if user is not logged in */
   print 'To view Checklist, please log in or create an account';
}

// close database connections
$db_main->close();
$db_auth->close();

?>
