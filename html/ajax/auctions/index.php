<?php

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ forcehttps();

/* WHICH DATABASES DO WE NEED */
$db2use = array(
	'db_auth' 	=> FALSE,
	'db_main'	=> FALSE
);

/* GET KEYS TO SITE */ require($path_to_keys);

ob_start();


// Set the token
$ebay_auth_token = $apikey['ebay']['auth_token'];



// Set the start and end times, this should be changed to code to set it dynamically
date_default_timezone_set( "UTC" );
$date = date('Y-m-d\Th:i:s\Z', time());
$end_time = $date;
$date2 = strtotime(date('Y-m-d H:i:s') . ' -1 week');
$date2 = date('Y-m-d\Th:i:s\Z', $date2);
$start_time = $date2;

// set ebay headers
$http_headers = $apikey['ebay']['headers'];


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
            <EntriesPerPage>8</EntriesPerPage>
            <PageNumber>'.$_GET['pagenum'].'</PageNumber>
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

    $error = 0;

    //set next page
    if($_GET['pagenum'] == $ebay->PaginationResult->TotalNumberOfPages){
        $nextpage = FALSE;
    }else{
        $nextpage = $_GET['pagenum'] + 1;
    }

    //run a loop on the items and display them however you want
    foreach($ebay->ItemArray->Item as $listing){
        $html .='<li><a style="background:url(\''.$listing->PictureDetails->PictureURL.'\'); background-size: cover; background-position: center center;background-repeat: repeat;" href="'.$listing->ListingDetails->ViewItemURL.'"><span><figure style="background:url(\''.$listing->PictureDetails->PictureURL.'\'); background-size: contain;background-position: center center;background-repeat: no-repeat;"></figure></span></a></li>';
    }
}else{
    $error = 1;
}


$json = array(
    'error'     => $error,
    'content'   => $html,
    'nextpage'  => $nextpage
);


header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
print json_encode($json);
ob_end_flush();




?>
