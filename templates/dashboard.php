<?php
// Initialize.
$reports_about_friends = array();
$reports_about_reported = array();

// Search for any reports against this user's friends.
if ($me && $me->getFriends()) {
    $sql_vals = array();
    $sql = 'SELECT * FROM incidents WHERE reportee_id IN (';
    $i = 1;
    foreach ($me->getFriends() as $friend) {
        $sql .= "\$$i"; // Bind query parameter position.
        if ($i !== count($me->getFriends())) {
            $sql .= ','; // Only add trailing comma if not last time through loop.
        }
        array_push($sql_vals, $friend['id']); // Add value to array.
        $i++;
    }
    $sql .= ') ORDER BY report_date DESC;';
    $result = pg_query_params($db->getHandle(),
        $sql,
        $sql_vals
    );
    if (pg_num_rows($result)) {
        while ($row = pg_fetch_assoc($result)) {
            $r = new PATIncident($row);
            $r->setReader($me);
            if ($r->isVisible()) {
                foreach ($me->getFriends() as $friend) {
                    if ($friend['id'] == $row['reportee_id']) {
                        $reports_about_friends[] = $r;
                    }
                }
            }
        }
    }
}

// Search for any reports filed by other people about people I've reported.
$sql_vals = array();
$sql  = 'SELECT * FROM incidents WHERE reportee_id IN (';
$sql .= 'SELECT reportee_id FROM incidents WHERE reporter_id=$1';
$sql .= ') AND reporter_id <> $1 ORDER BY report_date DESC';
$result = pg_query_params($db->getHandle(), $sql, array($user_id));
if (pg_num_rows($result)) {
    while ($row = pg_fetch_assoc($result)) {
        $r = new PATIncident($row);
        $r->setReader($me);
        if ($r->isVisible()) {
            $reports_about_reported[] = $r;
        }
    }
}
?>
<section id="MainContent">
    <h1>Take action</h1>
    <ul>
<!--
        <li>
            <a href="https://www.heroku.com/?utm_source=facebook&utm_medium=app&utm_campaign=fb_integration" target="_top" class="icon heroku">Heroku</a>
            <p>Learn more about <a href="https://www.heroku.com/?utm_source=facebook&utm_medium=app&utm_campaign=fb_integration" target="_top">Heroku</a>, or read developer docs in the Heroku <a href="https://devcenter.heroku.com/" target="_top">Dev Center</a>.</p>
        </li>
-->
        <li>
            <a href="reports.php?action=lookup" class="icon websites">Search</a>
            <p>Or <a href="reports.php?action=lookup&amp;mine">view information you shared</a>.</p>
        </li>
        <li>
            <a href="reports.php?action=new" class="icon apps-on-facebook">Share information</a>
            <p>Share your story related to a Facebook user's behavior.</p>
        </li>
        <li>
            <a href="https://userscripts.org/scripts/show/177813" target="_blank" class="icon mobile-apps">Get alerts</a>
            <p>Download the app to get alerts as you browse.</p>
        </li>
    </ul>
</section>

<section id="samples" class="clearfix">
    <h1>Information from within your network</h1>

    <div class="list">
        <h3>More information linked to people you've linked to</h3>
        <?php if ($reports_about_reported) { ?>
        <ul class="friends">
        <?php
        foreach ($reports_about_reported as $x) {
            reportListItem($x);
        }
        ?>
        </ul>
        <!--<p><a href="TK_LINK">See all</a></p>-->
        <?php } else { ?>
        <p>No information found.</p>
        <?php } ?>
    </div>

    <div class="list">
        <h3>More information linked to your Facebook friends</h3>
        <?php if ($reports_about_friends) { ?>
        <ul class="friends">
        <?php
        foreach ($reports_about_friends as $x) {
            reportListItem($x);
        }
        ?>
        </ul>
        <!--<p><a href="TK_LINK">See all</a></p>-->
        <?php } else { ?>
        <p>No information found.</p>
        <?php } ?>
    </div>
</section>
