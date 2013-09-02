<?php
if (is_numeric($_GET['id'])) {
    $report = new PATIncident(array('id' => $_GET['id']));
    if ($report->reportee_id) {
        $reportee = $FB->api("/{$report->reportee_id}?fields=name,picture.type(square),link");
        if ($reportee['picture']['data']['url']) {
            $reportee['picture'] = $reportee['picture']['data']['url'];
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
    <h1>Lookup a report</h1>
    <?php if ($report && $reportee) { ?>
    <p>
        <?php if ($report->reporter_id === $user_id) { ?>
        You
        <? } else if (!isset($_GET['who'])) { ?>
        <a href="<?php print he("{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}&who")?>" title="Learn who filed this report.">Someone else</a>
        <?php } else if ($reporter) { ?>
        <a href="<?php print he($reporter['link']);?>" target="_top"><img alt="" src="<?php print he($reporter['picture']['data']['url']);?>" /> <?php print he($reporter['name']);?></a> filed this report.
        <?php if ($reporter['email']) : ?>(<a href="mailto:<?php print he($reporter['email']);?>">Send <?php print he($reporter['name']);?> an email about this incident</a>.)<?php endif;?>
        <?php } else { ?>
        The person who
        <?php } ?>
        filed this report<?php if ($reporter_notified) : ?> has been notified of your interest. If they choose to do so, they'll send you a Facebook message. (You may want to double-check <a href="https://www.facebook.com/messages/other/">your "Other" mailbox</a> occasionally to ensure you don't miss their message.)<?php endif;?>.
    </p>
    <ul id="report-info">
        <li>This report is about: <a href="<?php print he($reportee['link']);?>" target="_blank"><img alt="" src="<?php print he($reportee['picture']);?>" /> <?php print he($reportee['name']);?></a></li>
        <li>
            Reported incident:
            <blockquote><p><?php print he($report->report_text);?></p></blockquote>
        </li>
    </ul>
    <?php if ($requester) : ?>
    <p><img alt="" src="https://graph.facebook.com/<?php print he($_GET['requester']);?>/picture" /><a href="https://www.facebook.com/profile.php?id=<?php print he($_GET['requester']);?>"><?php print ($requester['name']) ? he($requester['name']) : "Facebook user $requester";?></a> would like to know that you wrote this report. If you feel comfortable doing so, you can <a href="https://www.facebook.com/messages/<?php print he($_GET['requester']);?>">click here to send them a message</a>.</p>
    <?php endif; ?>
    <?php } else { ?>
    <p>No report matching this description could be found. Maybe you want to <a href="<?php print he(AppInfo::getUrl('/reports.php?action=new'));?>">file one</a>?</p>
    <?php } ?>
</section>
