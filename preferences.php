<?php
require_once 'lib/pat-fb-init.php';

try {
    $prefs = $me->getPreferences();
} catch (Exception $e) {
    header('Location: ' . AppInfo::getUrl());
}

if ($_REQUEST['submit']) {
    $new_prefs = array(
        'notify_on_same_reportee' => ($_REQUEST['notify_on_same_reportee']) ? true: false,
        'notify_on_friend_reported' => ($_REQUEST['notify_on_friend_reported']) ? true: false,
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
                /> &hellip;someone shares a story about someone I've shared a story about.</label></p>
            <p><label><input type="checkbox" id="" name="notify_on_friend_reported"
                <?php if ($prefs['notify_on_friend_reported']) : ?>checked="checked"<?php endif;?>
                /> &hellip;someone shares a story about one of my Facebook friends.</label></p>
        </fieldset>
        <input type="submit" name="submit" value="Save" />
    </form>
</section>
<?
include 'templates/footer.php';
