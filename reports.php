<?php
require_once 'lib/pat-fb-init.php';

$reportee_id = ($_REQUEST['reportee_id']) ? $_REQUEST['reportee_id'] : '';
$search_results = array();
// TODO: All these forms should start implementing CSRF protections?
if ($_REQUEST['submit_clarification'] && $_REQUEST['next_page']) {
    // Remove the reportee_id value if user selected one but clicked "show more".
    if ('Show more' === substr($_REQUEST['submit_clarification'], 0, 9)) {
        unset($reportee_id);
    }
    // We're in the middle of paging, and just asked for another page.
    $p = parse_url($_REQUEST['next_page']);
    $url = "{$p['path']}?{$p['query']}";
    $x = processFacebookSearchResults($FB->api(urldecode($url)));
    $search_results = $x['search_results'];
    $next_search_results_url = $x['next_page'];
}

if (is_numeric($reportee_id)) {
    $reportee_data = getFacebookUserInfoFromApi($FB, $reportee_id);
} else if (empty($reportee_id) && !empty($_REQUEST['reportee_name']) && empty($_REQUEST['submit_clarification'])) {
    // If the "name" is numeric or doesn't have spaces, assume it's an ID or an
    // unique username, so do that search first.
    if (is_numeric($_REQUEST['reportee_name']) || (false === strpos($_REQUEST['reportee_name'], ' '))) {
        $search_results[] = getFacebookUserInfoFromApi($FB, $_REQUEST['reportee_name']);
    }
    // But then always do a Graph Search, too.
    $x = processFacebookSearchResults($FB->api(
        '/search?type=user&q=' . urlencode($_REQUEST['reportee_name']) .
        '&fields=id,name,picture.type(square),gender,bio,birthday,link'
    ));
    $search_results = array_merge($search_results, $x['search_results']);
    $next_search_results_url = $x['next_page'];
    if (empty($search_results) && false !== strpos($_REQUEST['reportee_name'], ' ')) {
        $x = guessFacebookUsername($_REQUEST['reportee_name']);
        if ($x) {
            $search_results[] = $x;
        }
    }
}

// Offer a tab separated values download of the user's own reports.
if ('export' === $_GET['action']) {
    // Learn column placements to strip incident ID, ensure only own reports are exported.
    $result = pg_query_params($db->getHandle(),
        'SELECT column_name FROM information_schema.columns WHERE table_name=$1',
        array('incidents')
    );
    $pos_id = 0;
    $pos_reporter_id = 0;
    $i = 0;
    $field_headings = array();
    while ($row = pg_fetch_object($result)) {
        $field_headings[] = $row->column_name;
        switch ($row->column_name) {
            case 'id':
                $pos_id = $i;
                break;
            case 'reporter_id':
                $pos_reporter_id = $i;
                break;
        }
        $i++;
    }
    array_splice($field_headings, $pos_id, 1);
    if ($data = pg_copy_to($db->getHandle(), 'incidents', "\t")) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="My PAT Facebook reports.tsv"');
        header('Pragma: no-cache');
        if (isset($_GET['header'])) {
            print implode("\t", $field_headings) . "\n";
        }
        foreach ($data as $line) {
            $fields = explode("\t", $line);
            if ($user_id == $fields[$pos_reporter_id]) {
                array_splice($fields, $pos_id, 1);
                print implode("\t", $fields);
            }
        }
    }
    exit();
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
