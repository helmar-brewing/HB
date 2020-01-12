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


// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(1);

// check user subscription status
$user->checksub();



if(isset($user)){
   if( $user->login() == 1 || $user->login() == 2 ){
   /* do this code if user is logged in */
   		// grab userType
       $R_cards2 = $db_main->query("
       SELECT userType
       FROM users
       WHERE userid ='".$user->id."'
           "
       );
       $R_cards2->data_seek(0);
       while($card = $R_cards2->fetch_object()){
           $userType = $card->userType;
       }
       $R_cards2->free();

       if ($userType === 'admin') {
           // where all the code goes if you're admin!

       // set the name of the files to be downloaded
       $file = 'User_Report.csv';

       // open a file on the server to be written
       $fp = fopen($file, 'w');


               // set the headers for the spreadsheet,
               $csvheaders = array('eBay ID','Name','Spend');

               // write the headers to the file
               fputcsv($fp, $csvheaders);



               // write the file with a while loop. you could also instead build an array of your data with a while loop, up before you set the filename.
               // that would allow you to make sure that there were no errors, then use a for each loop down where to write the data.
               // it would also allow you to change the format of the info form the database.
               // for example, in this dataset perhaps 'vote' is a booleen in the database, but you want to display it as 'yes', or 'no' in the CSV.
               $result = $db_main->query("
               SELECT ebayID, buyerName, sum(auctionAmount) as SumAmount
               FROM completed_auctions
               GROUP BY ebayID, buyerName
               ORDER BY Sum(auctionAmount) DESC
                   "
               );
               if($result != FALSE){
                 $result->data_seek(0);
                 while($object = $result->fetch_object()){
                   // your csvline needs to be a simple array
                   $csvline = array($object->ebayID,$object->buyerName,$object->SumAmount);
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


            } else{
              // not admin
              print '<meta http-equiv="Refresh" content="0; url=https://helmarbrewing.com/">';
          }


   /* END code if user is logged in */
   }else{
   /* do this if user is not logged in */
   print '<meta http-equiv="Refresh" content="0; url=https://helmarbrewing.com/">';
  }
}else{
   /* do this if user is not logged in */
   print '<meta http-equiv="Refresh" content="0; url=https://helmarbrewing.com/">';
}

// close database connections
$db_main->close();
$db_auth->close();

?>
