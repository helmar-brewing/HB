<?php

function code_timer ($name) {

    $mtime = explode(' ',microtime());
    $time = $mtime[1] + $mtime[0];

    //determine if we're starting the timer or ending it
    if ($_SESSION["timer-$name"]) {
      $stime=$_SESSION["timer-$name"];
      unset($_SESSION["timer-$name"]);
      return ($time - $stime);
    } else {
      $_SESSION["timer-$name"]=$time;
      return(true);
    }
}

code_timer ('a');



    // Set the token, eventually this should be moved to the keys.php file we have in the 'inc' folder

    $ebay_auth_token = '';



    // Set the start and end times, this should be changed to code to set it dynamically

    date_default_timezone_set( "UTC" );
    $date = date('Y-m-d\Th:i:s\Z', time());

    $end_time = $date;


    $date2 = strtotime(date('Y-m-d H:i:s') . ' -1 week');
    $date2 = date('Y-m-d\Th:i:s\Z', $date2);

    $start_time = $date2;








    $http_headers = array(

        'Content-Type: text/xml',

        'X-EBAY-API-COMPATIBILITY-LEVEL:903',

        'X-EBAY-API-DEV-NAME:39b1e0dd-982e-461c-8791-d14dd5043ce6',

        'X-EBAY-API-APP-NAME:RobertRu-b5e0-414c-b76e-82c06bd8e5c4',

        'X-EBAY-API-CERT-NAME:9d2bf8d8-25dc-4491-a2e5-dfc69819dbed',

        'X-EBAY-API-SITEID:0',

        'X-EBAY-API-CALL-NAME:GetSellerList'

    );


//ReturnAll


    $xml_request = '


            <?xml version="1.0" encoding="utf-8"?>
            <GetSellerListRequest xmlns="urn:ebay:apis:eBLBaseComponents">

            <RequesterCredentials>

                <eBayAuthToken>'.$ebay_auth_token.'</eBayAuthToken>

            </RequesterCredentials>

            <StartTimeFrom>'.$start_time.'</StartTimeFrom>

            <StartTimeTo>'.$end_time.'</StartTimeTo>

               <ErrorLanguage>en_US</ErrorLanguage>
               <WarningLevel>High</WarningLevel>
               <GranularityLevel>Coarse</GranularityLevel>
               <IncludeWatchCount>true</IncludeWatchCount>
               <Pagination>
                   <EntriesPerPage>'.$_GET["entries"].'</EntriesPerPage>
                  <PageNumber>'.$_GET["pagenum"].'</PageNumber>
               </Pagination>




        </GetSellerListRequest>

    ';





    // Open a curl session for making the call

    $curl = curl_init('https://api.ebay.com/ws/api.dll');



    // Tell curl to use HTTP POST

    curl_setopt($curl, CURLOPT_POST, true);

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);



    // Tell curl not to return headers, but do return the response

    curl_setopt($curl, CURLOPT_HEADER, false);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);



    // Set the HTTP headers, including the ones ebay that ebay requires

    curl_setopt($curl, CURLOPT_HTTPHEADER, $http_headers);



    // Set the POST arguments to pass on

    curl_setopt($curl, CURLOPT_POSTFIELDS, $xml_request);



    // Make the REST call, returning the result

    $xml_response = curl_exec($curl);



    // Close the connection

    curl_close( $curl );



    // Convert the XML into an object

    $ebay = new SimpleXMLElement($xml_response);



    // Test for success

    if($ebay->Ack == 'Success'){

        $date = date('Y-m-d\Th:i:s\Z', time());

        $date2 = strtotime(date('Y-m-d H:i:s') . ' -1 week');
        $date2 = date('Y-m-d\Th:i:s\Z', $date2);

    //    print '
    //    <p>'.$ebay->PaginationResult->TotalNumberOfPages.'</p>
    //            <p>'.$ebay->PaginationResult->TotalNumberOfEntries.'</p>
    //            <p>'.$ebay->HasMoreItems.'</p>
    //            <p>'.$date.'</p>
    //            <p>'.$date2.'</p>
    //
    //
    //        ';


        // print a link to prior/next page
        if ($_GET["pagenum"] == 1){
            print '<a href="ebay3.php?entries=8&pagenum='.$ebay->PaginationResult->TotalNumberOfPages.'">Prior Page   </a>';
        } else {
            $n = $_GET["pagenum"];
            $n = $n - 1;
            print '<a href="ebay3.php?entries=8&pagenum='.$n.'">Prior Page   </a>';
        }

        if ($_GET["pagenum"] == $ebay->PaginationResult->TotalNumberOfPages){
            print '<a href="ebay3.php?entries=8&pagenum=1">Next Page</a>';
        } else {
            $n = $_GET["pagenum"];
            $n = $n + 1;
            print '<a href="ebay3.php?entries=8&pagenum='.$n.'">Next Page</a>';
        }





        //run a loop on the items and display them however you want

        $i = 1;
        print '<div class="container-fluid">';

        foreach($ebay->ItemArray->Item as $listing){
            // create row div for 4 items per row
            if ($i == 1) {
                print '<div class="row">';

            }


            print '


                <div class="col-sm-3">

                    <a href="'.$listing->ListingDetails->ViewItemURL.'" target="new">
                    <img src="'.$listing->PictureDetails->PictureURL.'"  max-width:100%;/></a>

                </div>



            ';

            //style="max-height:300px;"

            // making 4 col, when get to 4th item, end row div, else increment i
            if ($i==4){
                $i = 1;
                print '</div>';
            } else {
                $i = $i + 1;
            }

        }
        // need to add end of row div if there is not a full row. if i = 1 when listins end, then it was full row, else we need to close out row div
        if ($i<>1){
            print '</div>';
        }

        // end container fluid div
        print '</div>';



        // print link for next page



    }else{

        print 'There was an error getting listings from ebay.';

    }









    // for debugging this needs to be removed

   // print '<h1>raw response</h1><hr><pre>';

   // var_dump($ebay);

    //print '</pre>';

print '<br><br>';
echo "page generated in " . code_timer('a');


?>
