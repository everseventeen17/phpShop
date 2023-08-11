<?php

define("VG_ACCESS", true);
header('Content-Type:text/html:charset=utf-8');

session_start();

require_once 'config.php';
require_once 'core/base/settings/internal_settings.php';
//require_once 'core/base/controllers/RouteController.php';
use core\base\exceptions\RouteExceptions;
use core\base\controllers\RouteController;

try {

    RouteController::getInstance();


} catch (RouteException $e) {
    exit($e->getMessage());
}