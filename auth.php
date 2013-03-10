<?php

error_reporting(E_ALL);

require_once 'deviantart.class.php';

$devianart = new Devianart;

$resp = $devianart->sendPost('https://www.deviantart.com/users/login', array(
	'username' => $argv[1],
	'password' => $argv[2],
	'ref' => 'http://www.deviantart.com/',
	'reusetoken' => '1',
));