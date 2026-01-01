<?php
require_once 'config.php';

$oauthProvider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => GITHUB_CLIENT_ID,
    'clientSecret'            => GITHUB_CLIENT_SECRET,
    'redirectUri'             => REDIRECT_URI,
    'urlAuthorize'            => 'https://github.com/login/oauth/authorize',
    'urlAccessToken'          => 'https://github.com/login/oauth/access_token',
    'urlResourceOwnerDetails' => 'https://api.github.com/user'
]);