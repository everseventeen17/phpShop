<?php

define("VG_ACCESS", true);
header('Content-Type:text/html;charset=utf-8');
session_start();

require_once 'config.php';
require_once 'core/base/settings/internal_settings.php';
require_once 'core/base/controllers/RouteController.php';

use core\base\exceptions\RouteException;
use core\base\controllers\RouteController;
use core\base\settings\ShopSettings;

try {
    RouteController::getInstance()->route();

} catch (RouteException $e) {
    echo "<pre>";
    print_r($e->getMessage());
    echo "</pre>";
}