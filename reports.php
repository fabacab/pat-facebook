<?php
require_once 'lib/pat-fb-init.php';

$reportee_id = ($_REQUEST['reportee_id']) ? $_REQUEST['reportee_id'] : '';
if (is_numeric($reportee_id)) {
    try {
        $reportee_data = $FB->api("/$reportee_id");
    } catch (Exception $e) {
        // TODO: Figure out why I can't seem to catch this error if the $reportee_id
        //       is a user who the current user has blocked.
        // If the user we're looking up was blocked, we should get an Exception.
        // In that case, ask the user to triple-check that this is the correct
        // user ID number for the user they wish to report.
    }
} else if (empty($reportee_id) && !empty($_REQUEST['reportee_name'])) {
    if (is_numeric($_REQUEST['reportee_name'])) {
        $reportee_data = $FB->api("/{$_REQUEST['reportee_name']}");
    } else {
        $x = $FB->api(
            '/search?type=user&q=' . urlencode($_REQUEST['reportee_name']) .
            '&fields=id,name,picture.type(square),gender,bio,birthday,link'
        );
        if ($x['data']) {
            $search_results = $x['data'];
        }
    }
}

ob_start(); // Sometimes we put headers in bad places. :P
include 'templates/header.php';
switch ($_GET['action']) {
    case 'new':
        include 'templates/report_new.php';
        break;
    case 'lookup':
    default:
        include 'templates/report_lookup.php';
        break;
}
include 'templates/footer.php';
ob_end_flush();
