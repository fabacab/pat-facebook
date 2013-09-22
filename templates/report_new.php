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
    <h1>Share a new story</h1>
    <?php if (
              (!isset($_REQUEST['submit']) && !isset($_REQUEST['submit_clarification']))
              ||
              (isset($_REQUEST['submit_clarification']) && $reportee_id)
              ||
              isset($report)
            ) { ?>
    <form id="pat-report-form" method="post" action="<?php print "{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}";?>">
        <p>Share a story.</p>
        <fieldset><legend>Story details</legend>
            <input type="hidden" id="reporter_id" name="reporter_id" value="<?php print he($user_id);?>" />
            <?php
            reporteeNameField(array(
                'label' => 'This story is about',
                'description_html' => 'If this is not already pre-filled, enter the name of the person you\'re sharing about. We\'ll look for a match and ask you to confirm. (If you know their <a href="http://findmyfacebookid.com/" target="_blank">Facebook user ID number</a>, you can use that, too.)'
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
                <span class="description">In your own words, describe what happened. The more detailed your story is, the better. If you can, please include the names of venues and witnesses, the time of day, locations, and any other details you can remember.</span>
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
                What's the single most important point about what happened?
                <span class="description">In as few words as possible, summarize the main take-away from your story. This is used like a "subject" line in email to display a brief headline for your story in the event there is more than one incident to display about a given individual.</span>
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
        <fieldset><legend>Communication preference</legend>
<!-- TODO: Do we want to allow anonymous reporting? -->
<!--
            <label>
                <input type="radio" name="communication_preference" value="remain_anonymous" /> I'd like to file this report anonymously.
                <span class="description">This option (filing anonymously) means you will not be able to view your report after you file it. It is <em>forever</em> out of your hands. Moreover, it does not necessarily mean that someone with sufficient knowledge about the events you are reporting would not be able to identify you. All it means is that <?php print he($FBApp->getAppName());?> will not make a note that your user account made this report.</span>
            </label>
-->
            <label>
                <input type="radio" name="communication_preference" value="approval" required="required"
                    <?php if (!isset($_REUQEST['communication_preference']) || $_REQUEST['communication_preference'] === 'approval') : ?>
                    checked="checked"
                    <?php endif;?>
                /> <!--I'd like to file this report under my name, but -->I do not want other people to learn that I wrote this story unless I approve of them knowing.
                <span class="description">This option (filing pseudonymously) means that <?php print he($FBApp->getAppName());?> will associate your story with your identity, but will only reveal your identity to others who ask about it after confirming with you if you are comfortable letting the other person know who wrote this story. (You'll get a notification letting you know someone's interested when that happens so you don't have to keep checking this site.)</span>
            </label>
            <label>
                <input type="radio" name="communication_preference" value="allowed"
                    <?php if ($_REQUEST['communication_preference'] === 'allowed') : ?>
                    checked="checked"
                    <?php endif;?>
                /> <!--I'd like to file this report under my name and -->I would like others to be able to contact me about this story as soon as they are interested in doing so.
                <span class="description">This option (filing non-anonymously) means that anyone who asks will be shown that you wrote this story. Only use this option if you are comfortable letting <em>everyone on the Internet</em> know that you shared this story.</span>
            </label>
        </fieldset>
        <fieldset><legend>Story visibility</legend>
            <label>
                <input type="radio" name="report_visibility" value="public" required="required"
                    <?php if ($_REQUEST['report_visibility'] === 'public') : ?>
                    checked="checked"
                    <?php endif;?>
                /> I want the entire Internet to be able to read this story.
                <span class="description">This option makes your story visible to the public. Everyone one the Internet will be able to find and read your story.</span>
            </label>
            <label>
                <input type="radio" name="report_visibility" value="friends"
                    <?php if (!isset($_REQUEST['report_visibility']) || $_REQUEST['report_visibility'] === 'friends') : ?>
                    checked="checked"
                    <?php endif;?>
                /> I only want my Facebook friends to be able to read this story.
                <span class="description">This option hides your story from everyone except your Facebook friends.</span>
            </label>
            <label>
                <input type="radio" name="report_visibility" value="reporters"
                    <?php if ($_REQUEST['report_visibility'] === 'reporters') : ?>
                    checked="checked"
                    <?php endif;?>
                /> I only want other people who have shared a story about this individual to be able to read this story.
                <span class="description">This option hides your story from everyone unless they, too, have shared a story about this individual.</span>
            </label>
            <label>
                <input type="radio" name="report_visibility" value="reporter_friends"
                    <?php if ($_REQUEST['report_visibility'] === 'reporter_friends') : ?>
                    checked="checked"
                    <?php endif;?>
                /> I only want my Facebook friends who have shared a story about this individual to be able to read this story.
                <span class="description">This option hides your story from everyone unless they are your Facebook friend <em>and</em> they, too, have shared a story about this individual.</span>
            </label>
        </fieldset>
        <input type="submit" name="submit" value="Share this story" />
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
