<?php

error_reporting(E_ALL);

require_once 'classes/autoload.php';

$deviantart = new Deviantart;

$resp = $deviantart->sendGet('https://www.deviantart.com/users/login');

//file_put_contents('auth1.html', $resp);

preg_match('/name="validate_token" value="(\w+)"/', $resp, $match);
$validate_token = $match[1];

preg_match('/name="validate_key" value="(\w+)"/', $resp, $match);
$validate_key = $match[1];

echo "validate_token: $validate_token\n";
echo "validate_key: $validate_key\n";

sleep(2);

$resp = $deviantart->sendPost('https://www.deviantart.com/users/login', array(
	'username' => $deviantart->config('account.user'),
	'password' => $deviantart->config('account.pass'),
	'ref' => 'https://www.deviantart.com/users/loggedin',
	'reusetoken' => '1',
	'remember_me' => '1',
	'validate_token' => $validate_token,
	'validate_key' => $validate_key,
));

if ($resp) {
	preg_match('/deviantART\.deviant\s*=\s*(\{.*\});/', $resp, $deviantMatch);
	$deviant = json_decode($deviantMatch[1], true);
	print_r($deviant);
	file_put_contents('tmp/auth2.html', $resp);
} else {
	echo "auth error\n";
}
