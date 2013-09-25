<?php
$reports_found = array();
if (is_numeric($_GET['id'])) {
    $report = new PATIncident(array('id' => $_GET['id']));
    $report->setReader($me);
    if ($report->isVisible()) {
        if ($report->reportee_id) {
            // Get information about the reportee.
            $url = "/{$report->reportee_id}?fields=name,picture.type(square),link";
            $reportee = getFacebookUserInfoFromApi($FB, $url);
            if ($reportee['picture']['data']['url']) {
                $reportee['picture'] = $reportee['picture']['data']['url'];
            }
            // Automatically search for any other reports against this user ID.
            $result = pg_query_params($db->getHandle(),
                'SELECT * FROM incidents WHERE reportee_id=$1 AND id <> $2 ORDER BY report_date DESC;',
                array($report->reportee_id, $report->id)
            );
            while ($row = pg_fetch_assoc($result)) {
                $r = new PATIncident($row);
                $r->setReader($me);
                if ($r->isVisible()) {
                    $reports_found[] = $r;
                }
            }
        }
    }
} else if (isset($_GET['mine'])) {
    $result = pg_query_params($db->getHandle(),
        'SELECT * FROM incidents WHERE reporter_id=$1 ORDER BY report_date DESC',
        array($user_id)
    );
    if (pg_num_rows($result)) {
        while ($row = pg_fetch_assoc($result)) {
            $r = new PATIncident($row);
            $r->setReader($me);
            if ($r->isVisible()) {
                $reports_found[] = $r;
            }
        }
    }
} else if (is_numeric($reportee_id)) {
    $reports_found = findReportsByReporteeId($reportee_id);
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
        // TODO: Anonymous report?
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
                'template' => "@[$user_id] viewed information you shared and has requested to know your identity.",
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
    <h1>Search</h1>
    <nav>
        <ul class="SectionNavigation">
            <li<?php if (isset($_GET['mine'])) : ?> class="active"<?php endif;?>><a href="<?php print $_SERVER['PHP_SELF'];?>?action=lookup&amp;mine">View info I shared</a></li>
            <li><a href="<?php print $_SERVER['PHP_SELF'];?>?action=export&amp;header">Download info I shared</a></li>
        </ul>
    </nav>
    <?php if ($reports_found && is_numeric($_GET['id'])) : ?>
    <div class="Alert">
        <p><strong>More information about this individual:</strong></p>
        <?php reportList($reports_found);?>
    </div>
    <?php endif;?>
    <?php if ($reports_found && isset($_GET['mine'])) { ?>
    <p>Your stories:</p><?php reportList($reports_found);?>
    <?php } else if ($reports_found && is_numeric($reportee_id)) { ?>
    <p>
        Information that has been shared about <a href="<?php print he($reportee_data['link']);?>"><?php print he($reportee_data['name']);?></a>:
    </p>
    <?php reportList($reports_found);?>
    <?php } else if ($report && $reportee) { ?>
    <p>
        <?php if ($report->reporter_id === $user_id) { ?>
        You
        <? } else if (!isset($_GET['who'])) { ?>
        <a href="<?php print he("{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}&who")?>" title="Learn who shared this story.">Someone else</a>
        <?php } else if ($reporter) { ?>
        <a href="<?php print he($reporter['link']);?>" target="_top"><img alt="" src="<?php print he($reporter['picture']['data']['url']);?>" /> <?php print he($reporter['name']);?></a>
        <?php if ($reporter['email']) : ?>(<a href="mailto:<?php print he($reporter['email']);?>">Send <?php print he($reporter['name']);?> an email about this incident</a>.)<?php endif;?>
        <?php } else { ?>
        The person who
        <?php } ?>
        shared this<?php if ($reporter_notified) : ?> has been notified of your interest. If they choose to do so, they'll send you a Facebook message. (You may want to double-check <a href="https://www.facebook.com/messages/other/">your "Other" mailbox</a> occasionally to ensure you don't miss their message.)<?php endif;?>.
    </p>
    <article id="pat-report-info">
        <h1><?php print he($report->report_title);?></h1>
        <?php if ($user_id == $report->reporter_id) : ?>
        <ul class="pat-report-meta">
            <li>
                Your identity is
                <?php
                switch ($report->contactable) {
                    case 'allowed':
                        print 'shared';
                        break;
                    case 'approval':
                        print 'hidden';
                        break;
                }
                ?>.
            </li>
            <li>
                Statement is
                <?php
                switch ($report->report_visibility) {
                    case 'public':
                        print 'visible to everyone';
                        break;
                    case 'friends':
                        print 'only visible to friends';
                        break;
                    case 'reporters':
                        print 'only visible to others who have shared about this person';
                        break;
                    case 'reporter_friends':
                        print 'only visible to friends who have shared about this person';
                        break;
                }
                ?>.
            </li>
        </ul>
        <?php endif;?>
        <p>This story is about: <a href="<?php print he($reportee['link']);?>" target="_blank"><img alt="" src="<?php print he($reportee['picture']);?>" /> <?php print he($reportee['name']);?></a>:</p>
        <blockquote><p><?php print he($report->report_text);?></p></blockquote>
    </article>
    <?php if ($requester) : ?>
    <p><img alt="" src="https://graph.facebook.com/<?php print he($_GET['requester']);?>/picture" /><a href="https://www.facebook.com/profile.php?id=<?php print he($_GET['requester']);?>"><?php print ($requester['name']) ? he($requester['name']) : "Facebook user $requester";?></a> would like to know who submitted this statement. If you feel comfortable identifying yourself to them, <a href="https://www.facebook.com/messages/<?php print he($_GET['requester']);?>" target="_top">click here to send them a message</a>.</p>
    <?php endif; ?>
    <?php } else if (($_REQUEST['submit'] || $_REQUEST['submit_clarification']) && empty($reportee_id)) { ?>
    <form id="pat-find-report-form" method="post" action="<?php print "{$_SERVER['PHP_SELF']}?action=lookup";?>">
        <input type="hidden" name="reportee_name" value="<?php print he($_REQUEST['reportee_name']);?>" />
        <?php
        clarifyReportee($search_results,
            array(
                'description' => "Please clarify who you're trying to find stories about.",
                'next' => $next_search_results_url
            )
        );
        ?>
    </form>
    <?php } else if ($_REQUEST['submit'] || $_REQUEST['submit_clarification']) { ?>
    <p>No information on this person could be found. Would you like to <a href="<?php print he(AppInfo::getUrl("/reports.php?action=new&reportee_id=$reportee_id"));?>">share some</a>?</p>
    <?php } ?>
    <form id="pat-find-report-form" method="post" action="<?php print "{$_SERVER['PHP_SELF']}?action=lookup";?>">
        <fieldset><legend>Reportee details</legend>
            <?php
            reporteeNameField(array(
                'label' => 'I want to know if there is any information about',
                'description_html' => 'Enter the name of any Facebook user. If you know their <a href="http://findmyfacebookid.com/" target="_blank">Facebook user ID number</a>, you can use that, too. (<a href="'.he(DOCUMENTATION_URL_BASE).'/User-Manual:Searching" target="_blank">help</a>)'
            ));?>
        </fieldset>
        <input type="submit" name="submit" value="Search" />
    </form>
</section>
