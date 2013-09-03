<?php
// Main dashboard.
// Show some options of what can be done.

// * Suggest action of filing a report against someone with no reports.
// * Show list of my friends against whom there ARE reports.

// Initialize.
$db = new PATFacebookDatabase();
$reports_about_friends = array();
$reports_about_reported = array();

// Search for any reports against this user's friends.
$sql_vals = array();
$sql = 'SELECT id, reportee_id FROM incidents WHERE reportee_id IN (';
$i = 1;
foreach ($friends as $friend) {
    $sql .= "\$$i"; // Bind query parameter position.
    if ($i !== count($friends)) {
        $sql .= ','; // Only add trailing comma if not last time through loop.
    }
    array_push($sql_vals, $friend['id']); // Add value to array.
    $i++;
}
$sql .= ');';
$result = pg_query_params($db->connect(psqlConnectionStringFromDatabaseUrl()),
    $sql,
    $sql_vals
);
if (pg_num_rows($result)) {
    while ($row = pg_fetch_object($result)) {
        foreach ($friends as $friend) {
            if ($friend['id'] == $row->reportee_id) {
                $friend['pat_report_id'] = $row->id;
                $reports_about_friends[] = $friend;
            }
        }
    }
}

// Search for any reports filed by other people about people I've reported.
$sql_vals = array();
$sql  = 'SELECT id, reportee_id FROM incidents WHERE reportee_id IN (';
$sql .= 'SELECT reportee_id FROM incidents WHERE reporter_id=$1';
$sql .= ') AND reporter_id <> $1';
$result = pg_query_params($db->getHandle(), $sql, array($user_id));
if (pg_num_rows($result)) {
    while ($row = pg_fetch_object($result)) {
        $reports_about_reported[] = $row;
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
            <a href="reports.php?action=lookup" class="icon websites">Find reports</a>
            <p>Search for reports, or <a href="reports.php?action=lookup&amp;mine">view reports you filed</a>.</p>
        </li>
<!--
        <li>
            <a href="https://developers.facebook.com/docs/guides/mobile/" target="_top" class="icon mobile-apps">Mobile Apps</a>
            <p>
            Integrate with our core experience by building apps
            that operate within Facebook.
            </p>
        </li>
-->
        <li>
            <a href="reports.php?action=new" class="icon apps-on-facebook">File report</a>
            <p>File a report about another Facebook user's behavior.</p>
        </li>
    </ul>
</section>

<section id="samples" class="clearfix">
    <h1>Information from within your network</h1>

    <div class="list">
        <h3>Reports filed by others about people you've reported</h3>
        <?php if ($reports_about_reported) { ?>
        <ul class="friends">
        <?php foreach ($reports_about_reported as $x) : ?>
            <li>
                <a href="reports.php?action=lookup&amp;id=<?php print he($x->id);?>"><img alt="" src="https://graph.facebook.com/<?php print he($x->reportee_id);?>/picture?type=square" /> View this report.<a/>
            </li>
        <?php endforeach; ?>
        </ul>
        <!--<p><a href="TK_LINK">See all</a></p>-->
        <?php } else { ?>
        <p>No reports found.</p>
        <?php } ?>
    </div>

    <div class="list">
        <h3>Reports filed about your Facebook friends</h3>
        <?php if ($reports_about_friends) { ?>
        <ul class="friends">
        <?php foreach ($reports_about_friends as $x) : ?>
            <li>
                <a href="reports.php?action=lookup&amp;id=<?php print he($x['pat_report_id']);?>"><img src="https://graph.facebook.com/<?php echo he($x['id']) ?>/picture?type=square" alt=""> View this report about <?php print he($x['name']);?></a>
            </li>
        <?php endforeach;?>
        </ul>
        <!--<p><a href="TK_LINK">See all</a></p>-->
        <?php } else { ?>
        <p>No reports found.</p>
        <?php } ?>
    </div>
</section>
