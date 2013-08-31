<?php
require_once 'lib/pat-fb-init.php';

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
