<?php

error_reporting(E_ALL);

require_once 'classes/autoload.php';

define('IS_ADMIN', Request::getUsername() === 'admin');
define('IS_GUEST', Request::getUsername() === 'guest');

$route = explode('/', Request::param('action', 'index'));
$controller = count($route) > 1 ? $route[0] : 'index';
$action = count($route) > 1 ? $route[1] : $route[0];

$controllerClass = ucfirst($controller).'Controller';
$actionMethod = 'action'.ucfirst($action);

call_user_func_array([new $controllerClass, $actionMethod], array_slice($route, 2));