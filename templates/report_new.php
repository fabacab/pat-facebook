<?php
// We're ready to save?
if (isset($_REQUEST['submit']) && !empty($_REQUEST['reportee_id'])) {
    $report = new PATIncident(array(
        'reporter_id' => $user_id,
        'reportee_id' => $_REQUEST['reportee_id'],
        'report_title' => $_REQUEST['report_title'],
        'report_text' => $_REQUEST['report_text'],
        'report_visibility' => $_REQUEST['report_visibility'],
        'contactable' => $_REQUEST['communication_preference']
    ));
    if ($report->fieldsValidate()) {
        if ($rid = $report->save()) {
            // get list of other people who have reported this reportee.
            $result = pg_query_params($db->getHandle(),
                'SELECT DISTINCT reporter_id FROM incidents WHERE reportee_id = $1 AND reporter_id <> $2',
                array($report->reportee_id, $report->reporter_id)
            );
            // Check those other people's notification preference.
            while ($row = pg_fetch_assoc($result)) {
                $usr = new PATFacebookUser($FB, $row['reporter_id']);
                $prefs = $usr->getPreferences();
                // If their notification preference is on,
                if ($prefs['notify_on_same_reportee']) {
                    $report->setReader($usr);
                    if ($report->isVisible()) {
                        // send each of them a notification.
                        $FB->setAccessToken(getFacebookAppToken());
                        $FB->api("/{$usr->getId()}/notifications", 'post', array(
                            'template' => 'Another story was shared in the ' . $FBApp->getAppName() . ' about someone you previously shared a story about; click here for more information.',
                            'href' => "reports.php?action=lookup&id=$rid"
                        ));
                    }
                }
            }
            header('Location: ' . AppInfo::getUrl($_SERVER['PHP_SELF'] . "?action=lookup&id=$rid"));
            exit();
        }
    }
}
?>
<section id="MainContent">
    <h1>Share</h1>
    <?php if (
              (!isset($_REQUEST['submit']) && !isset($_REQUEST['submit_clarification']))
              ||
              (isset($_REQUEST['submit_clarification']) && $reportee_id)
              ||
              isset($report)
            ) { ?>
    <form id="pat-report-form" method="post" action="<?php print "{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}";?>">
        <fieldset><legend>Details (<a href="<?php print he(DOCUMENTATION_URL_BASE);?>/User-Manual:Talk-About-It#how-to-contribute-information" target="_blank">help</a>)</legend>
            <input type="hidden" id="reporter_id" name="reporter_id" value="<?php print he($user_id);?>" />
            <?php if ($report && $report->getValidationErrors('reporter_id')) : ?>
            <ul class="errors">
            <?php foreach ($report->getValidationErrors('reporter_id') as $error_message) : ?>
                <li><?php print he($error_message);?></li>
            <?php endforeach;?>
            </ul>
            <?php endif;?>
            <?php
            reporteeNameField(array(
                'label' => 'This is information about',
                'description_html' => 'Enter the name of any Facebook user. If you know their <a href="http://findmyfacebookid.com/" target="_blank">Facebook user ID number</a>, you can use that, too.'
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
                <span class="description">Tell others in your social network about your experience with this person. Share as many or as few details as you feel comfortable with. It's also okay to provide information about bad experiences your friends have had with this person.</span>
                <?php if ($report && $report->getValidationErrors('report_text')) : ?>
                <ul class="errors">
                <?php foreach ($report->getValidationErrors('report_text') as $error_message) : ?>
                    <li><?php print he($error_message);?></li>
                <?php endforeach;?>
                </ul>
                <?php endif; ?>
                <textarea name="report_text" placeholder="Type your story here." required="required"><?php print he($_REQUEST['report_text']);?></textarea>
            </label>
            <label>
                Short description:
                <span class="description">A brief headline that will appear on your friends' Dashboards or when your statement shows up in a search.</span>
                <?php if ($report && $report->getValidationErrors('report_title')) : ?>
                <ul class="errors">
                <?php foreach ($report->getValidationErrors('report_title') as $error_message) : ?>
                    <li><?php print he($error_message);?></li>
                <?php endforeach;?>
                </ul>
                <?php endif; ?>
                <input id="report_title" name="report_title" placeholder="Keywords/summary of main point" value="<?php print he($_REQUEST['report_title']);?>" />
            </label>
<!-- TODO: Should we add "when/where" questions, too? -->
        </fieldset>
        <fieldset><legend>Your identity (<a href="<?php print he(DOCUMENTATION_URL_BASE);?>/User-Manual:Decide-Who-Knows#your-identity" target="_blank">help</a>)</legend>
<!-- TODO: Do we want to allow anonymous reporting? -->
<!--
            <label>
                <input type="radio" name="communication_preference" value="remain_anonymous" /> I'd like to file this report anonymously.
                <span class="description">This option (filing anonymously) means you will not be able to view your report after you file it. It is <em>forever</em> out of your hands. Moreover, it does not necessarily mean that someone with sufficient knowledge about the events you are reporting would not be able to identify you. All it means is that <?php print he($FBApp->getAppName());?> will not make a note that your user account made this report.</span>
            </label>
-->
            <label>
                <input type="radio" name="communication_preference" value="allowed"
                    <?php if ($_REQUEST['communication_preference'] === 'allowed') : ?>
                    checked="checked"
                    <?php endif;?>
                /> Share my identity.
                <span class="description">People viewing your statement will be able to see that it was written by you.</span>
            </label>
            <label>
                <input type="radio" name="communication_preference" value="approval" required="required"
                    <?php if (!isset($_REUQEST['communication_preference']) || $_REQUEST['communication_preference'] === 'approval') : ?>
                    checked="checked"
                    <?php endif;?>
                /> Keep my identity hidden.
                <span class="description">People viewing your statement will not be able to see that it was written by you. If they request to know the author's identity, you will get a notification and can decide on a case-by-case basis about who to share your identity with.</span>
            </label>
        </fieldset>
        <fieldset><legend>Statement visibility (<a href="<?php print he(DOCUMENTATION_URL_BASE);?>/User-Manual:Decide-Who-Knows#statement-visibility" target="_blank">help</a>)</legend>
            <label>
                <input type="radio" name="report_visibility" value="public" required="required"
                    <?php if ($_REQUEST['report_visibility'] === 'public') : ?>
                    checked="checked"
                    <?php endif;?>
                /> Public
                <span class="description">Anyone on the Internet can find and read this information.</span>
            </label>
            <label>
                <input type="radio" name="report_visibility" value="friends"
                    <?php if (!isset($_REQUEST['report_visibility']) || $_REQUEST['report_visibility'] === 'friends') : ?>
                    checked="checked"
                    <?php endif;?>
                /> Friends only
                <span class="description">Hide this information from everyone except my Facebook friends.</span>
            </label>
            <label>
                <input type="radio" name="report_visibility" value="reporters"
                    <?php if ($_REQUEST['report_visibility'] === 'reporters') : ?>
                    checked="checked"
                    <?php endif;?>
                /> Only other people who have shared
                <span class="description">Hide this information from everyone except users who have also shared information with PAT-Facebook about this same person.</span>
            </label>
            <label>
                <input type="radio" name="report_visibility" value="reporter_friends"
                    <?php if ($_REQUEST['report_visibility'] === 'reporter_friends') : ?>
                    checked="checked"
                    <?php endif;?>
                /> Only friends who have shared
                <span class="description">Hide this information from everyone except my Facebook friends who have also shared information with PAT-Facebook about this same person.</span>
            </label>
        </fieldset>
        <input type="submit" name="submit" value="Share" />
        <p><span class="description">Information submitted to PAT-Facebook can not be edited or deleted. Please keep in mind that even if you choose to keep your identity hidden, details you share here could be used to identify you. Put your own safety first.</span></p>
    </form>
    <?php } else if (empty($reportee_id)) { ?>
    <form id="pat-report-form" method="post" action="<?php print "{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}";?>">
        <input type="hidden" id="reporter_id" name="reporter_id" value="<?php print he($user_id);?>" />
        <input type="hidden" name="reportee_name" value="<?php print he($_REQUEST['reportee_name']);?>" />
        <input type="hidden" name="report_title" value="<?php print he($_REQUEST['report_title']);?>" />
        <input type="hidden" name="report_text" value="<?php print he($_REQUEST['report_text']);?>" />
        <input type="hidden" name="communication_preference" value="<?php print he($_REQUEST['communication_preference']);?>" />
        <input type="hidden" name="report_visibility" value="<?php print he($_REQUEST['report_visibility']);?>" />
        <?php
        clarifyReportee($search_results,
            array(
                'description' => "Please clarify who you're sharing this story about. It's important this field is accurate, so double-check just to be sure!",
                'next' => $next_search_results_url
            )
        );
        ?>
    </form>
    <?php } ?>
</section>
