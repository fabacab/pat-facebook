<?php
// We're ready to save?
if (isset($_REQUEST['submit']) && !empty($_REQUEST['reportee_id'])) {
    $report = new PATIncident(array(
        'reporter_id' => $user_id,
        'reportee_id' => $_REQUEST['reportee_id'],
        'report_title' => $_REQUEST['report_title'],
        'report_text' => $_REQUEST['report_text'],
        'contactable' => $_REQUEST['communication_preference']
    ));
    if ($report->fieldsValidate()) {
        if ($result = $report->save()) {
            header('Location: ' . AppInfo::getUrl($_SERVER['PHP_SELF'] . "?action=lookup&id={$result->id}"));
            exit();
        }
    }
}
?>
<section id="MainContent">
    <h1>File a new report</h1>
    <?php if (!isset($_REQUEST['submit']) || isset($_REQUEST['submit_clarification']) || isset($report)) { ?>
    <form id="pat-report-form" method="post" action="<?php print "{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}";?>">
        <p>Report an incident.</p>
        <fieldset><legend>Report details</legend>
            <input type="hidden" id="reporter_id" name="reporter_id" value="<?php print he($user_id);?>" />
            <?php
            reporteeNameField(array(
                'label' => 'This report is about',
                'description_html' => 'If this is not already pre-filled, enter the name of the person you\'re reporting. We\'ll look for a match and ask you to confirm. (If you know their <a href="http://findmyfacebookid.com/" target="_blank">Facebook user ID number</a>, you can use that, too.)'
            ));?>
<!--
            <label>
                I feel this violation was
                <select name="severity" required="required">
                    <option>severe</option>
                    <option>moderate</option>
                    <option>minor</option>
                </select>.
            </label>
-->
            <label>
                What happened?
                <span class="description">In your own words, describe what happened. The more detailed your report is, the better. If you can, please include the names of venues and witnesses, the time of day, locations, and any other details you can remember.</span>
                <?php if ($report && $report->getValidationErrors('report_text')) : ?>
                <ul class="errors">
                <?php foreach ($report->getValidationErrors('report_text') as $error_message) : ?>
                    <li><?php print he($error_message);?></li>
                <?php endforeach;?>
                </ul>
                <?php endif; ?>
                <textarea name="report_text" placeholder="Type your report here." required="required"><?php print he($_REQUEST['report_text']);?></textarea>
            </label>
            <label>
                What's the single most important point about what happened?
                <span class="description">In as few words as possible, summarize the main take-away from your report. This is used like a "subject" line in email to display a brief headline for your report in the event there is more than one incident to display about a given individual.</span>
                <?php if ($report && $report->getValidationErrors('report_title')) : ?>
                <ul class="errors">
                <?php foreach ($report->getValidationErrors('report_title') as $error_message) : ?>
                    <li><?php print he($error_message);?></li>
                <?php endforeach;?>
                </ul>
                <?php endif; ?>
                <input id="report_title" name="report_title" placeholder="Keywords/summary of main point" required="required" value="<?php print he($_REQUEST['report_title']);?>" />
            </label>
<!-- TODO: Should we add "when/where" questions, too? -->
        </fieldset>
        <fieldset><legend>Communication preference</legend>
<!-- TODO: Do we want to allow anonymous reporting? -->
<!--
            <label>
                <input type="radio" name="communication_preference" value="remain_anonymous" /> I'd like to file this report anonymously.
                <span class="description">This option (filing anonymously) means you will not be able to view your report after you file it. It is <em>forever</em> out of your hands. Moreover, it does not necessarily mean that someone with sufficient knowledge about the events you are reporting would not be able to identify you. All it means is that <?php print he(idx($app_info, 'name'));?> will not make a note that your user account made this report.</span>
            </label>
-->
            <label>
                <input type="radio" name="communication_preference" value="approval"
                    <?php if (!isset($_REUQEST['communication_preference']) || $_REQUEST['communication_preference'] === 'do_not_contact') : ?>
                    checked="checked"
                    <?php endif;?>
                /> <!--I'd like to file this report under my name, but -->I do not want other people to learn that I wrote this report unless I approve of them knowing.
                <span class="description">This option (filing pseudonymously) means that <?php print he(idx($app_info, 'name'));?> will associate your report with your identity, but will only reveal your identity to others who ask about it after confirming with you if you are comfortable letting the other person know who wrote this report. (You'll get a notification letting you know someone's interested when that happens so you don't have to keep checking this site.)</span>
            </label>
            <label>
                <input type="radio" name="communication_preference" value="allowed"
                    <?php if ($_REQUEST['communication_preference'] === 'contact_allowed') : ?>
                    checked="checked"
                    <?php endif;?>
                /> <!--I'd like to file this report under my name and -->I would like others to be able to contact me about this report as soon as they are interested in doing so.
                <span class="description">This option (filing non-anonymously) means that anyone who asks will be shown that you wrote this report. Only use this option if you are comfortable letting <em>everyone on the Internet</em> know that you filed this report.</span>
            </label>
        </fieldset>
        <input type="submit" name="submit" value="File this report" />
    </form>
    <?php } else if (empty($_REQUEST['reportee_id'])) { ?>
    <form id="pat-report-form" method="post" action="<?php print "{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}";?>">
        <input type="hidden" id="reporter_id" name="reporter_id" value="<?php print he($user_id);?>" />
        <input type="hidden" name="report_title" value="<?php print he($_REQUEST['report_title']);?>" />
        <input type="hidden" name="report_text" value="<?php print he($_REQUEST['report_text']);?>" />
        <input type="hidden" name="communication_preference" value="<?php print he($_REQUEST['communication_preference']);?>" />
        <?php clarifyReportee($search_results, array('description' => "Please clarify who you're filing this report about. It's important this field is accurate, so double-check just to be sure!"));?>
    </form>
    <?php } ?>
</section>
