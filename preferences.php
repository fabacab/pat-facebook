<?php
require_once 'lib/pat-fb-init.php';

if (!$me) {
    header('Location: ' . AppInfo::getUrl($_SERVER['REQUEST_URI']));
    exit();
}
$prefs = $me->getPreferences();

if ($_REQUEST['submit']) {
    $new_prefs = array(
        'notify_on_same_reportee' => ($_REQUEST['notify_on_same_reportee']) ? true: false,
        'notify_on_friend_reported' => ($_REQUEST['notify_on_friend_reported']) ? true: false,
        'user_timezone_name' => (in_array($_REQUEST['user_timezone_name'], DateTimeZone::listIdentifiers()))
                                ? $_REQUEST['user_timezone_name'] : 'UTC',
    );
    if ($me->savePreferences($new_prefs)) {
        $prefs = $new_prefs;
    }
}

include 'templates/header.php';
?>
<section id="MainContent">
    <form>
        <?php if ($prefs === $new_prefs) : ?><div class="FlashMessage"><p>Your preferences have been saved.</p></div><?php endif;?>
        <p>Customize how <?php print he($FBApp->getAppName());?> behaves for you.</p>
        <fieldset><legend>Notification preferences</legend>
            <p><strong>Send me a Facebook notification whenever&hellip;</strong></p>
            <p><label><input type="checkbox" id="" name="notify_on_same_reportee"
                <?php if ($prefs['notify_on_same_reportee']) : ?>checked="checked"<?php endif;?>
                /> &hellip;someone shares information about the same person I've shared about.</label></p>
            <p><label><input type="checkbox" id="" name="notify_on_friend_reported"
                <?php if ($prefs['notify_on_friend_reported']) : ?>checked="checked"<?php endif;?>
                /> &hellip;someone shares information about one of my Facebook friends.</label></p>
        </fieldset>
        <fieldset><legend>Date and time preferences</legend>
            <p><label>I am usually located in the
                <select name="user_timezone_name">
                    <?php foreach (DateTimeZone::listIdentifiers() as $zone_name) : ?>
                    <option<?php if ($prefs['user_timezone_name'] === $zone_name) : ?> selected="selected"<?php endif;?>><?php print he($zone_name);?></option>
                    <?php endforeach; ?>
                </select> time zone.</label>
            </p>
        </fieldset>
        <input type="submit" name="submit" value="Save" />
    </form>
</section>
<?
include 'templates/footer.php';
