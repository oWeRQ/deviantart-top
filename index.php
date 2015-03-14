<?php

error_reporting(E_ALL);

require_once 'classes/autoload.php';

define('IS_ADMIN', Request::getUsername() === 'admin');
define('IS_GUEST', Request::getUsername() === 'guest');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	die();
}

$route = explode('/', Request::param('action', 'index'));
list($controller, $action) = (count($route) > 1) ? $route : array('index', $route[0]);

$controllerClass = ucfirst($controller).'Controller';
$actionMethod = 'action'.ucfirst($action);

call_user_func_array([new $controllerClass, $actionMethod], array_slice($route, 2));