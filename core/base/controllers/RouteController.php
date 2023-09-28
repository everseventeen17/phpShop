<?php

namespace core\base\controllers;

use core\base\exceptions\RouteException;
use core\base\settings\Settings;

class RouteController extends BaseController
{
    use Singleton;
    protected $routes;

    private function __construct()
    {
        $adress_str = $_SERVER["REQUEST_URI"];
        $path = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'index.php'));
        if ($path === PATH) {
            if (strrpos($adress_str, '/') === strlen($adress_str) - 1 and strrpos($adress_str, '/') !== strlen(PATH) - 1) {
                $this->redirect(rtrim($adress_str, '/'), 301);
            }
            $this->routes = Settings::get('routes');
            if (!$this->routes) throw new RouteException('Не описаны routes в классе Settings', 1);
            $url = explode('/', substr($adress_str, strlen(PATH)));
            if (!empty($url[0]) and $url[0] === $this->routes['admin']['alias']) { //админка
                array_shift($url);
                if (!empty($url[0]) and is_dir($_SERVER['DOCUMENT_ROOT'] . PATH . $this->routes['plugins']['path'] . $url[0])) { // если плагин
                    $plugin = array_shift($url);
                    $pluginSettings = $this->routes['settings']['path'] . ucfirst($plugin . 'Settings');
                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . PATH . $pluginSettings . '.php')) {
                        $pluginSettings = str_replace('/', '\\', $pluginSettings);
                        $this->routes = $pluginSettings::get('routes');
                    }
                    $dir = $this->routes['plugins']['dir'] ? '/' . $this->routes['plugins']['dir'] . '/' : '/';
                    $dir = str_replace('//', '/', $dir);
                    $this->controller = $this->routes['plugins']['path'] . $plugin . $dir;
                    $hrURL = $this->routes['plugins']['hrURL'];
                    $route = 'plugins';
                } else { // если НЕ плагин
                    $this->controller = $this->routes['admin']['path'];
                    $hrURL = $this->routes['admin']['hrURL'];
                    $route = 'admin';
                }
            } else { // обычный пользователь
                $url = explode('/', substr($adress_str, strlen(PATH)));
                $hrUrl = $this->routes['user']['hrURL'];
                $this->controller = $this->routes['user']['path'];
                $route = 'user';
            }
            $this->createRoute($route, $url);
            if (!empty($url[1])) {
                $count = count($url);
                $key = '';
                if (!$hrUrl) {
                    $i = 1;
                } else {
                    $this->parametrs['alias'] = $url[1];
                    $i = 2;
                }
                for (; $i < $count; $i++) {
                    if (!$key) {
                        $key = $url[$i];
                        $this->parametrs[$key] = '';
                    } else {
                        $this->parametrs[$key] = $url[$i];
                        $key = '';
                    }
                }
            }
        } else {
            throw new RouteException('Не корректная дирректория сайта', 1);
        }
    }

    private function createRoute($var, $arr)
    {
        $route = [];
        if (!empty($arr[0])) {
            if (@$this->routes[$var]['routes'][$arr[0]]) {
                $route = explode('/', $this->routes[$var]['routes'][$arr[0]]);
                $this->controller .= ucfirst($route[0] . 'Controller');
            } else {
                $this->controller .= ucfirst($arr[0] . 'Controller');
            }
        } else {
            $this->controller .= $this->routes['default']['controller'];
        }
        $this->inputMethod = !empty($route[1]) ? $route[1] : $this->routes['default']['inputMethod'];
        $this->outputMethod = !empty($route[2]) ? $route[2] : $this->routes['default']['outputMethod'];
        return;
    }

}