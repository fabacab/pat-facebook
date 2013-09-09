<?php
// Provides access to app specific values such as your app id and app secret.
// Defined in 'AppInfo.php'
require_once(dirname(__FILE__) . '/../AppInfo.php');

// This provides access to helper functions defined in 'utils.php'
require_once(dirname(__FILE__) . '/../utils.php');

// Enforce https on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && !isLocalhost()) {
  header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  exit();
}

// Load the Facebook PHP SDK
require_once('facebook/src/facebook.php');

// Load our own libraries.
require 'pat-fb/PATFacebookUser.class.php';
require 'pat-fb/PATIncident.class.php';
require 'pat-fb/template_functions.inc.php';

$FB = new Facebook(array(
  'appId'  => AppInfo::appID(),
  'secret' => AppInfo::appSecret(),
  'sharedSession' => true,
  'trustForwarded' => true,
));
$user_id = $FB->getUser();

if ($user_id) {
    try {
        // Fetch the viewer's basic information
        $me = new PATFacebookUser($FB);
        $me->loadFriends('id,name,gender,picture.type(square),bio,installed');
    } catch (FacebookApiException $e) {
        // If the call fails we check if we still have a user. The user will be
        // cleared if the error is because of an invalid accesstoken
        if (!$FB->getAccessToken()) {
            header('Location: '. AppInfo::getUrl($_SERVER['REQUEST_URI']));
            exit();
        }
    }
}

// Fetch the basic info of the app that they are using
$FBApp = new AppInfo($FB->api('/' . AppInfo::appID()));

// Some global settings.
date_default_timezone_set('UTC');
