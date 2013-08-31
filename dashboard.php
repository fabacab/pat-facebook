<?php
// Main dashboard.
// Show some options of what can be done.

// * Suggest action of filing a report against someone with no reports.
// * Show list of my friends against whom there ARE reports.

// Initialize.
$lists = array();

// Search for any reports which have friends as their reportee.
if ($results) {
    $lists['reported_friends'] = array();
    foreach ($results as $reportee) {
        $lists['reported_friends'][] = $reportee;
    }
}
?>
<section id="guides">
    <h1>Take action</h1>
    <ul>
<!--
        <li>
            <a href="https://www.heroku.com/?utm_source=facebook&utm_medium=app&utm_campaign=fb_integration" target="_top" class="icon heroku">Heroku</a>
            <p>Learn more about <a href="https://www.heroku.com/?utm_source=facebook&utm_medium=app&utm_campaign=fb_integration" target="_top">Heroku</a>, or read developer docs in the Heroku <a href="https://devcenter.heroku.com/" target="_top">Dev Center</a>.</p>
        </li>
-->
        <li>
            <a href="report.php?action=lookup" class="icon websites">Lookup reports</a>
            <p>Search for reports that have already been filed.</p>
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
            <a href="report.php?action=new" class="icon apps-on-facebook">File report</a>
            <p>File a new report to alert others of potential danger.</p>
        </li>
    </ul>
</section>

<section id="samples" class="clearfix">
    <h1>Information from within your social network</h1>

    <div class="list">
        <h3>Friends without reports</h3>
        <ul class="friends">
        <?php
        foreach ($friends as $friend) {
        // Extract the pieces of info we need from the requests above
        $id = idx($friend, 'id');
        $name = idx($friend, 'name');
        ?>
            <!--          <li>
                <a href="https://www.facebook.com/<?php echo he($id); ?>" target="_top">
                    <img src="https://graph.facebook.com/<?php echo he($id) ?>/picture?type=square" alt="<?php echo he($name); ?>">
                    <?php echo he($name); ?>
                </a>
            </li>-->
        <?php
        }
        ?>
        </ul>
        <p><a href="TK_LINK">See all</a></p>
    </div>

    <div class="list">
        <h3>Friends with reports</h3>
        <?php if ($lists['reported_friends']) { ?>
        <ul class="friends">
        <?php
        foreach ($lists['reported_friends'] as $friend) :
        // Extract the pieces of info we need from the requests above
//        $id = idx($friend, 'id');
//        $name = idx($friend, 'name');
            $id = $friend;
        ?>
            <li>
                <img src="https://graph.facebook.com/<?php echo he($id) ?>/picture?type=square" alt="<?php echo he($id); ?>">
            </li>
        <?php endforeach;?>
        </ul>
        <p><a href="TK_LINK">See all</a></p>
        <?php } else { ?>
        <p>No reports found.</p>
        <?php } ?>
    </div>
</section>
