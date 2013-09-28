<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes" />

    <title><?php echo he($FBApp->getAppName()); ?></title>
    <link rel="stylesheet" href="stylesheets/screen.css" media="Screen" type="text/css" />
    <link rel="stylesheet" href="stylesheets/mobile.css" media="handheld, only screen and (max-width: 480px), only screen and (max-device-width: 480px)" type="text/css" />

    <!--[if IEMobile]>
    <link rel="stylesheet" href="mobile.css" media="screen" type="text/css"  />
    <![endif]-->

    <!-- These are Open Graph tags.  They add meta data to your  -->
    <!-- site that facebook uses when your content is shared     -->
    <!-- over facebook.  You should fill these tags in with      -->
    <!-- your data.  To learn more about Open Graph, visit       -->
    <!-- 'https://developers.facebook.com/docs/opengraph/'       -->
    <meta property="og:title" content="<?php echo he($FBApp->getAppName()); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo AppInfo::getUrl(); ?>" />
    <meta property="og:image" content="<?php echo AppInfo::getUrl('/logo.png'); ?>" />
    <meta property="og:site_name" content="<?php echo he($FBApp->getAppName()); ?>" />
    <meta property="og:description" content="Supporting survivors of sexual assault helps break the silence about their own experiences, and the stronger our community responses to sexual violence become." />
    <meta property="fb:app_id" content="<?php echo AppInfo::appID(); ?>" />

    <script type="text/javascript" src="//code.jquery.com/jquery-1.7.1.min.js"></script>

    <script type="text/javascript" src="javascript/pat-fb.js"></script>

    <!--[if IE]>
      <script type="text/javascript">
        var tags = ['header', 'section'];
        while(tags.length)
          document.createElement(tags.pop());
      </script>
    <![endif]-->
  </head>
  <body>
    <div id="fb-root"></div>
    <script type="text/javascript">
      window.fbAsyncInit = function() {
        FB.init({
          appId      : '<?php echo AppInfo::appID(); ?>', // App ID
          channelUrl : '//<?php echo $_SERVER["HTTP_HOST"]; ?>/channel.html', // Channel File
          status     : true, // check login status
          cookie     : true, // enable cookies to allow the server to access the session
          xfbml      : true // parse XFBML
        });

        // Listen to the auth.login which will be called when the user logs in
        // using the Login button
        FB.Event.subscribe('auth.login', function(response) {
          // We want to reload the page now so PHP can read the cookie that the
          // Javascript SDK sat. But we don't want to use
          // window.location.reload() because if this is in a canvas there was a
          // post made to this page and a reload will trigger a message to the
          // user asking if they want to send data again.
          window.location = window.location;
        });

        FB.Canvas.setAutoGrow();
      };

      // Load the SDK Asynchronously
      (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js";
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));
    </script>

<header class="clearfix">
    <?php if (isset($me)) { ?>
    <p id="user-picture" style="background-image: url(https://graph.facebook.com/<?php echo he($user_id); ?>/picture?type=normal)"></p>

    <div>
        <div id="share-app">
            <p>Help survivors by sharing <a href="<?php echo he($FBApp->getAppLink());?>" target="_top"><?php echo he($FBApp->getAppName()); ?></a> with friends:</p>
            <ul>
                <li>
                    <a href="#" class="facebook-button" id="postToWall" data-url="<?php echo AppInfo::getUrl(); ?>" data-caption="<?php print he($FBApp->getAppName());?>" data-description="Made by survivors for survivors, <?php print he($FBApp->getAppName());?> helps survivors of sexual violence connect with other survivors in their social networks and start conversations about consent with friends.">
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
        <h1><a href="/" title="View your dashboard"><strong><?php echo he($me->name); ?></strong></a></h1>
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
