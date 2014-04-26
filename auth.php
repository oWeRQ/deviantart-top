<?php

error_reporting(E_ALL);

require_once 'classes/Deviantart.php';

$deviantart = new Deviantart;

$resp = $deviantart->sendGet('https://www.deviantart.com/users/login');

sleep(2);

//file_put_contents('auth1.html', $resp);

preg_match('/name="validate_token" value="(\w+)"/', $resp, $match);
$validate_token = $match[1];

preg_match('/name="validate_key" value="(\w+)"/', $resp, $match);
$validate_key = $match[1];

$resp = $deviantart->sendPost('https://www.deviantart.com/users/login', array(
	'username' => $argv[1],
	'password' => $argv[2],
	'ref' => 'https://www.deviantart.com/users/loggedin',
	'reusetoken' => '1',
	'remember_me' => '1',
	'validate_token' => $validate_token,
	'validate_key' => $validate_key,
));

//file_put_contents('auth2.html', $resp);