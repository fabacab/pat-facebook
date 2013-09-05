<?php
$db = new PATFacebookDatabase();
$db->connect(psqlConnectionStringFromDatabaseUrl());
$reports_found = array();
if (is_numeric($_GET['id'])) {
    $report = new PATIncident(array('id' => $_GET['id']));
    if ($report->reportee_id) {
        // Get information about the reportee.
        $reportee = $FB->api("/{$report->reportee_id}?fields=name,picture.type(square),link");
        if ($reportee['picture']['data']['url']) {
            $reportee['picture'] = $reportee['picture']['data']['url'];
        }
        // Automatically search for any other reports against this user ID.
        $result = pg_query_params($db->getHandle(),
            'SELECT id, report_date FROM incidents WHERE reportee_id=$1 AND id <> $2 ORDER BY report_date DESC;',
            array($report->reportee_id, $report->id)
        );
        while ($row = pg_fetch_object($result)) {
            $reports_found[] = $row;
        }
    }
} else if (isset($_GET['mine'])) {
    $result = pg_query_params($db->getHandle(),
        'SELECT * FROM incidents WHERE reporter_id=$1 ORDER BY report_date DESC',
        array($user_id)
    );
    if (pg_num_rows($result)) {
        while ($row = pg_fetch_object($result)) {
            $reports_found[] = $row;
        }
    }
} else if (is_numeric($_REQUEST['reportee_id'])) {
    // Search for reports about this person.
    $result = pg_query_params($db->getHandle(),
        'SELECT * FROM incidents WHERE reportee_id=$1',
        array($_REQUEST['reportee_id'])
    );
    if (pg_num_rows($result)) {
        while ($row = pg_fetch_object($result)) {
            $reports_found[] = $row;
        }
    }
}
if (is_numeric($_GET['requester'])) {
    try {
        $requester = $FB->api("/{$_GET['requester']}?fields=name,picture.type(square),link,email");
    } catch (FacebookApiExcetion $e) {
        // TODO: Deal with any errors.
        $requester = $_GET['requester'];
    }
}
if (isset($_GET['who'])) {
    if (!$report->reporter_id) {
        // Anonymous report.
        $reporter = 'Anonymous';
    } else if ($report->contactable === 'allowed') {
        // If the reporter allows contact, let the requester view their identity.
        // TODO: Currently, we ONLY store the reporter's Facebook ID. This means we
        //       rely on their having a Graph API-searchable profile to retrive the
        //       info about them. Maybe an incident report should also include some
        //       fields for METHODS of contact beyond PREFERENCE of contact-ability?
        try {
            $reporter = $FB->api("/{$report->reporter_id}?fields=name,picture.type(square),link,email");
        } catch (Exception $e) {
            // TODO: Deal with any errors.
        }
    } else if ($report->contactable === 'approval') {
        // If the reporter asks for approval for contact, notify the reporter
        // but keep the reporter's identity anonymous to the requester.
        $FB->setAccessToken(getFacebookAppToken());
        try {
            $FB->api("/{$report->reporter_id}/notifications", 'post', array(
                'template' => "@[$user_id] wants to learn that you wrote a PAT-FB report. Click here to review the report.",
                'href' => "reports.php?action=lookup&id={$report->id}&requester=$user_id"
            ));
            $reporter_notified = true;
        } catch (FacebookApiException $e) {
            // TODO: Deal with any errors.
        }
    }
}
?>
<section id="MainContent">
    <h1>Find a report</h1>
    <nav>
        <ul class="SectionNavigation">
            <li<?php if (isset($_GET['mine'])) : ?> class="active"<?php endif;?>><a href="<?php print $_SERVER['PHP_SELF'];?>?action=lookup&amp;mine">View reports I filed</a></li>
            <li><a href="<?php print $_SERVER['PHP_SELF'];?>?action=export&amp;header">Download reports I filed</a></li>
        </ul>
    </nav>
    <?php if ($reports_found && is_numeric($_GET['id'])) : ?>
    <div class="Alert">
        <p><strong>There have been additional incidents reported about this individual.</strong></p>
        <?php reportList($reports_found);?>
    </div>
    <?php endif;?>
    <?php if ($reports_found && isset($_GET['mine'])) { ?>
    <p>Your reports:</p><?php reportList($reports_found);?>
    <?php } else if ($reports_found && is_numeric($_REQUEST['reportee_id'])) { ?>
    <p>The following reports have been found:</p><?php reportList($reports_found);?>
    <?php } else if ($report && $reportee) { ?>
    <p>
        <?php if ($report->reporter_id === $user_id) { ?>
        You
        <? } else if (!isset($_GET['who'])) { ?>
        <a href="<?php print he("{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}&who")?>" title="Learn who filed this report.">Someone else</a>
        <?php } else if ($reporter) { ?>
        <a href="<?php print he($reporter['link']);?>" target="_top"><img alt="" src="<?php print he($reporter['picture']['data']['url']);?>" /> <?php print he($reporter['name']);?></a>
        <?php if ($reporter['email']) : ?>(<a href="mailto:<?php print he($reporter['email']);?>">Send <?php print he($reporter['name']);?> an email about this incident</a>.)<?php endif;?>
        <?php } else { ?>
        The person who
        <?php } ?>
        filed this report<?php if ($reporter_notified) : ?> has been notified of your interest. If they choose to do so, they'll send you a Facebook message. (You may want to double-check <a href="https://www.facebook.com/messages/other/">your "Other" mailbox</a> occasionally to ensure you don't miss their message.)<?php endif;?>.
    </p>
    <ul id="report-info">
        <li>This report is about: <a href="<?php print he($reportee['link']);?>" target="_blank"><img alt="" src="<?php print he($reportee['picture']);?>" /> <?php print he($reportee['name']);?></a></li>
        <li>
            Report title:
            <blockquote><p><?php print he($report->report_title);?></p></blockquote>
        </li>
        <li>
            Reported incident:
            <blockquote><p><?php print he($report->report_text);?></p></blockquote>
        </li>
    </ul>
    <?php if ($requester) : ?>
    <p><img alt="" src="https://graph.facebook.com/<?php print he($_GET['requester']);?>/picture" /><a href="https://www.facebook.com/profile.php?id=<?php print he($_GET['requester']);?>"><?php print ($requester['name']) ? he($requester['name']) : "Facebook user $requester";?></a> would like to know that you wrote this report. If you feel comfortable doing so, you can <a href="https://www.facebook.com/messages/<?php print he($_GET['requester']);?>">click here to send them a message</a>.</p>
    <?php endif; ?>
    <?php } else if ($_REQUEST['submit'] && empty($_REQUEST['reportee_id'])) { ?>
    <form id="pat-find-report-form" method="post" action="<?php print "{$_SERVER['PHP_SELF']}?action=lookup";?>">
        <?php clarifyReportee($search_results, array('description' => "Please clarify who you're trying to find reports about."));?>
    </form>
    <?php } else if ($_REQUEST['submit']) { ?>
    <p>No report matching this description could be found. Maybe you want to <a href="<?php print he(AppInfo::getUrl('/reports.php?action=new'));?>">file one</a>?</p>
    <?php } ?>
    <form id="pat-find-report-form" method="post" action="<?php print "{$_SERVER['PHP_SELF']}?action=lookup";?>">
        <p>Search for a report.</p>
        <fieldset><legend>Reportee details</legend>
            <?php
            reporteeNameField(array(
                'label' => 'I want to know if there are any reports about',
                'description_html' => 'Enter the name of the person you\'d like to find reports about. We\'ll look for a match and ask you to confirm. (If you know their <a href="http://findmyfacebookid.com/">Facebook user ID number</a>, you can use that, too.)'
            ));?>
        </fieldset>
        <input type="submit" name="submit" value="Find reports" />
    </form>
</section>
