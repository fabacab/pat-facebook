<?php
require_once 'lib/pat-fb-init.php';

include 'templates/header.php';

if ($user_id) {
    include 'templates/dashboard.php';
} else {
    include 'templates/splash.php';
}

include 'templates/footer.php';
