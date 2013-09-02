<?php

/**
 * @return the value at $index in $array or $default if $index is not set.
 */
function idx(array $array, $key, $default = null) {
  return array_key_exists($key, $array) ? $array[$key] : $default;
}

function he($str) {
  return htmlentities($str, ENT_QUOTES, "UTF-8");
}

function isLocalhost () {
    if ($_SERVER['REMOTE_ADDR'] === '::1' || $_SERVER['REMOTE_ADDR'] === '127.0.0.1') {
        return true;
    } else {
        return false;
    }
}

function psqlConnectionStringFromDatabaseUrl () {
    extract(parse_url(getenv('DATABASE_URL')));
    return "user=$user password=$pass host=$host dbname=" . substr($path, 1) . ' sslmode=require';
}

function getFacebookAppToken () {
    $url = 'https://graph.facebook.com/oauth/access_token?'.
           'client_id=' . getenv('FACEBOOK_APP_ID') .
           '&client_secret=' . getenv('FACEBOOK_SECRET') .
           '&grant_type=client_credentials';
    $res = file_get_contents($url);
    list(, $token) = explode('=', $res);
    return $token;
}
