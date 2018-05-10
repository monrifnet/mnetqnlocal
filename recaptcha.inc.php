<?php

# Utility for google reCaptcha
# https://www.google.com/recaptcha/admin#site/338669455
# https://developers.google.com/recaptcha/docs/invisible
# https://developers.google.com/recaptcha/docs/verify

define('MNETQNLOCAL_RECAPTCHA_KEY', '6LePry8UAAAAANr2Cjg5D-kCu1rDoAXvONh0zt1d');
define('MNETQNLOCAL_RECAPTCHA_SECRET', '6LePry8UAAAAANjmpqLPn5roNltavt_4BvPO4qmF');
define('MNETQNLOCAL_RECAPTCHA_FIELD', 'g-recaptcha-response');

function mnetqnlocal_recaptcha_verify($response) {
    if (!$response) return FALSE;
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $ip = @$_SERVER['REMOTE_ADDR'];
    $custom_opts = array(
        CURLOPT_POST => TRUE,
        CURLOPT_POSTFIELDS => array(
            'secret' => MNETQNLOCAL_RECAPTCHA_SECRET,
            'response' => $response,
        ),
    );
    if ($ip) $custom_opts[CURLOPT_POSTFIELDS]['remoteip'] = $ip;
    $api = mnetqnlocal_get_curl($url, TRUE, $custom_opts);
    if ($api && $api["payload"] && $api["status"] == 200) {
        $json = json_decode($api["payload"], TRUE);
        return @$json["success"] == TRUE;
    }
    return FALSE;
}
