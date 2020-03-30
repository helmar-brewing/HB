<?php

require("../lib/Input.php");

require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');
forcehttps();
$db2use = array(
    'db_auth' 	=> TRUE,
    'db_main'	=> TRUE
);
require($path_to_keys);
ob_start();

require_once('classes/phnx-user.class.php');
require_once('libraries/stripe/init.php');
\Stripe\Stripe::setApiKey($apikey['stripe']['secret']);

$searchTypes = array('buying' => 'marketWishlist', 'selling' => 'marketSale');

$user = new phnx_user;
$user->checklogin(1);

$code = 200;
$json = array();

/*
 * Returns the necessary where clauses for an arbitrary search term. For example, searching Det Tiger with searching columns, series, team & properName would return:
 * (series LIKE '%Det%' AND series LIKE '%Tiger%') OR (team LIKE '%Det%' AND team LIKE '%Tiger%') OR (properName LIKE '%Det%' AND properName LIKE '%Tiger%')
 * Additionally, any filters will be key value pair checked.
 */
function get_where_clause($search, $filters = array()) {
    $searchColumns = array('cardList.series', 'team', 'properName');
    $whereClauses = array();

    $searchTerms = explode(" ", $search);
    $searchTermsPrepared = array_map(function($term) {
        return "%".$term."%";
    }, $searchTerms);

    foreach ($searchColumns as $column) {
        $whereSubClauses = array();

        foreach ($searchTermsPrepared as $term) {
            array_push($whereSubClauses, "{$column} LIKE '{$term}'");
        }

        array_push($whereClauses, "(". implode(" AND ", $whereSubClauses).")");
    }

    $filterSubClauses = array();

    foreach ($filters as $col => $val) {
        if (isset($val) && $val !== '') {
            array_push($filterSubClauses, "{$col} = '{$val}'");
        }
    }

    $whereClause = "(".implode(" OR ", $whereClauses).")";

    if (count($filterSubClauses) > 0) {
        $filterSubClause = implode(" AND ", $filterSubClauses);
        $whereClause .= " AND ".$filterSubClause;
    }

    return $whereClause;
}

function validSearchType($type) {
    return $type === 'buying' || $type === 'selling';
}

function get_query($selectColumns, $search, $filters, $searchType) {
    global $searchTypes;

    $whereClause = get_where_clause($search, $filters);
    $selectStatement = implode(", ", $selectColumns);
    $searchTable = $searchTypes[$searchType];
    global $user;
    return "
        SELECT 
            {$selectStatement}
        from 
            {$searchTable}
        INNER JOIN 
        (
            SELECT userid, max(endDate) as maxEndDate FROM {$searchTable} GROUP BY userid
        ) ordering ON ordering.userid = {$searchTable}.userid
        INNER JOIN
            users
        ON
            users.userid = {$searchTable}.userid
        INNER JOIN
            cardList
        ON cardList.series = {$searchTable}.series AND cardList.cardNum = {$searchTable}.cardnum
        INNER JOIN
            series_info
        ON series_info.series_tag = cardList.series
        WHERE
            {$searchTable}.expired = 'N' AND {$user->id} <> {$searchTable}.userid
            AND  
            {$whereClause}
        ORDER BY series_info.sort ASC, ordering.maxEndDate DESC
    ";
}

function search_cards_meta($search, $filters, $searchType) {
    global $db_main;

    $query = get_query(array("COUNT(*) as card_count"), $search, $filters, $searchType);
    $response = $db_main->query($query);

    if ($response->num_rows !== 1) {
        return 0;
    } else {
        return $response->fetch_object();
    }
}

function search_cards($search, $filters, $limit, $offset, $searchType) {
    global $db_main;
    global $site;
    global $protocol;
    global $searchTypes;

    $searchTable = $searchTypes[$searchType];
    $selects = array(
        "users.*",
        "cardList.*",
        "{$searchTable}.*",
        "series_info.series_name",
    );

    $query = get_query($selects, $search, $filters, $searchType);
    if (isset($limit) && isset($offset)) {
        $query.=  "LIMIT {$limit} OFFSET {$offset}";
    }

    $response = $db_main->query($query);

    $results = array();

    while ($row = $response->fetch_object()) {

        $frontLargePicture = '/images/cardPics/large/' . $row->series . '_' . $row->cardnum . '_Front.jpg';
        $backLargePicture = '/images/cardPics/large/' . $row->series . '_' . $row->cardnum . '_Back.jpg';
        $picturePrefix = $protocol.$site;
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$frontLargePicture)) {
            $row->frontPicture = $picturePrefix.$frontLargePicture;
        }

        if (file_exists($_SERVER['DOCUMENT_ROOT'].$backLargePicture)) {
            $row->backPicture = $picturePrefix.$backLargePicture;
        }

        array_push($results, $row);
    }

    return $results;
}

function get_series_names() {
    global $db_main;

    $query = "
        SELECT
            DISTINCT series_name, series_tag
        FROM
            series_info
        WHERE series_status = 'active' AND live_status = 'live'
        ORDER BY sort ASC
    ";

    $response = $db_main->query($query);
    $results = array();

    while ($row = $response->fetch_object()) {
        array_push($results, $row);
    }

    return $results;
}

function get_card_info($cardId, $series) {
    global $db_main;
    $query = "
        SELECT 
            cardList.*,
            series_info.series_name
        FROM 
            cardList 
        INNER JOIN
            series_info
        ON series_info.series_tag = cardList.series
        WHERE cardList.cardNum='{$cardId}' AND cardList.series='{$series}' LIMIT 1";
    // echo $query;
    $response = $db_main->query($query);
    if ($response->num_rows !== 1) {
        return false;
    }

    return $response->fetch_object();
}

$action = Input::get("action");
$cardNum = Input::get("cardNum");
$series = Input::get("series");
$query = Input::get("q");
$limit = Input::get("limit");
$offset = Input::get("offset");
$filters = Input::get("filters");
$searchType = Input::get("type");

switch ($action) {
    case 'single':
        $cardInfo = get_card_info($cardNum, $series);
        if ($cardInfo) {
            $json = array('success' => true, 'cardInfo' => $cardInfo);
        } else {
            $json = array('success' => false, 'error' => 'Card not found.');
            $code = 404;
        }
        break;

    case 'search-meta':

        if (!validSearchType($searchType)) {
            $json = array('success' => false, 'error' => 'Invalid type: '.$searchType);
            $code = 400;
            break;
        }

        $metaInfo = search_cards_meta($query, $filters, $searchType);
        if ($metaInfo) {
            $json = array('success' => true, 'meta' => $metaInfo);
        } else {
            $json = array('success' => false);
            $code = 400;
        }
        break;

    case 'search':
        if (!validSearchType($searchType)) {
            $json = array('success' => false, 'error' => 'Invalid type: '.$searchType);
            $code = 400;
            break;
        }

        if (Input::has("q")) {
            $results = search_cards($query, $filters, $limit, $offset, $searchType);
            $json = array('success' => true, 'cards' => $results);
        } else {
            $json = array('success' => false, 'error' => 'q, limit & offset parameters required');
            $code = 400;
        }
        break;

    case 'series':
        $json = array('success' => true, 'series' => get_series_names());
        break;

    default:
        $code = 400;
        $json = array('success' => false, 'error' => 'invalid action: valid actions are single, search-meta & search.');
        break;
}

header_remove();
http_response_code($code);
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
header('Status: '.$code);
echo json_encode($json);
ob_end_flush();

?>
