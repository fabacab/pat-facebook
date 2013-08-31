<header class="clearfix">
    <?php if (isset($me)) { ?>
    <p id="user-picture" style="background-image: url(https://graph.facebook.com/<?php echo he($user_id); ?>/picture?type=normal)"></p>

    <div>
        <div id="share-app">
            <p>Help survivors by sharing <a href="<?php echo he(idx($app_info, 'link'));?>" target="_top"><?php echo he($app_name); ?></a> with friends:</p>
            <ul>
                <li>
                    <a href="#" class="facebook-button" id="postToWall" data-url="<?php echo AppInfo::getUrl(); ?>">
                        <span class="plus">Post to Wall</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="facebook-button speech-bubble" id="sendToFriends" data-url="<?php echo AppInfo::getUrl(); ?>">
                        <span class="speech-bubble">Send Message</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="facebook-button apprequests" id="sendRequest" data-message="This app fights rape culture by creating networks of support for survivors, by survivors! Please try it out and spread the word!">
                        <span class="apprequests">Send Requests</span>
                    </a>
                </li>
            </ul>
        </div>
        <h1>Welcome, <strong><?php echo he(idx($me, 'name')); ?></strong></h1>
        <ul>
            <li><a href="<?php print $FB->getLogoutUrl();?>" id="fb-logout-button" class="facebook-button">Log out of Facebook</a></li>
        </ul>
    </div>
  <?php } else { ?>
    <div>
        <h1>Welcome</h1>
        <div class="fb-login-button"></div>
    </div>
  <?php } ?>
</header>
