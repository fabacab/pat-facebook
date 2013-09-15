<?php
require_once 'lib/pat-fb-init.php';

// Search for any visible reports
$reports_found = findReportsByReporteeId((int) $_GET['fbid']);
if ($reports_found) {
    print json_encode(array(
        'reportee_id' => (int) $_GET['fbid'],
        'reports' => count($reports_found)
    ));
} else {
    print json_encode(false);
}
