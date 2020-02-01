<?php
header('Content-Type: text/plain; charset=UTF-8');
require_once(dirname(__FILE__).'/class/DSSOClient.class.php');

define('APP_ID', '');
define('SECRET_KEY', '');
define('REDIRECT_URI', 'http://localhost/example.php');

$SSO = new DSSOClient('id.dscitech.com');
$SSO->config(REDIRECT_URI, APP_ID, SECRET_KEY);

print_r($SSO->authorize());