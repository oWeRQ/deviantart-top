<?php

error_reporting(E_ALL);

require_once 'classes/autoload.php';

define('IS_ADMIN', Request::getUsername() === 'admin');

$actionName = 'action'.ucfirst(Request::param('action', 'index'));

(new IndexController)->$actionName();