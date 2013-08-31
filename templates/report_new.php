<?php
$reportee_id = ($_REQUEST['reportee_id']) ? $_REQUEST['reportee_id'] : '';
if ($reportee_id) {
    $reportee_data = $FB->api("/$reportee_id");
}
?>
<section id="MainContent">
    <h1>File a new report</h1>
    <?php if (!isset($_REQUEST['new_report_step']) || !isset($_REQUEST['submit'])) { ?>
    <form id="pat-report-form" method="post" action="<?php print "{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}";?>">
        <input type="hidden" id="new_report_step" name="new_report_step" value="confirm" />
        <fieldset><legend>Report details</legend>
            <input type="hidden" id="reporter_id" name="reporter_id" value="<?php print he($user_id);?>" />
            <input type="hidden" id="reportee_id" name="reportee_id" value="<?php print he($reportee_id);?>" />
            <label>
                This report is about <input list="friends-list" id="reportee_name" name="reportee_name" value="<?php print he($reportee_data['name']);?>" placeholder="Joe Shmo" required="required" />.
                <datalist id="friends-list">
                    <select><!-- For non-HTML5 fallback. -->
                        <?php foreach ($friends as $friend) : ?>
                        <option value="<?php print he($friend['id']);?>"><?php print he($friend['name']);?></option>
                        <?php endforeach;?>
                    </select>
                </datalist>
                <span class="description">If this is not already pre-filled, enter the name of the person you're reporting. We'll look for a match and ask you to confirm. (If you know their <a href="http://findmyfacebookid.com/">Facebook user ID number</a>, you can use that, too.)</span>
            </label>
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
                <textarea name="report_text" placeholder="Type your report here." required="required"></textarea>
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
                <input type="radio" name="communication_preference" value="do_not_contact" checked="checked" /> <!--I'd like to file this report under my name, but -->I do not want other people to learn that I wrote this report unless I approve of them knowing.
                <span class="description">This option (filing pseudonymously) means that <?php print he(idx($app_info, 'name'));?> will associate your report with your identity, but will only reveal your identity to others who ask about it after confirming with you if you are comfortable letting the other person know who wrote this report. (You'll get a notification letting you know someone's interested when that happens so you don't have to keep checking this site.)</span>
            </label>
            <label>
                <input type="radio" name="communication_preference" value="contact_allowed" /> <!--I'd like to file this report under my name and -->I would like others to be able to contact me about this report as soon as they are interested in doing so.
                <span class="description">This option (filing non-anonymously) means that anyone who asks will be shown that you wrote this report. Only use this option if you are comfortable letting <em>everyone on the Internet</em> know that you filed this report.</span>
            </label>
        </fieldset>
        <input type="submit" name="submit" value="File this report" />
    </form>
    <?php } else if ('confirm' === $_REQUEST['new_report_step']) { ?>
    <p>Confirming step</p>
    <?php var_dump($_REQUEST)?>
    <?php } ?>
</section>
