<?php

namespace core\admin\controllers;

use core\admin\models\Model;
use core\base\controllers\BaseController;
use core\base\exceptions\RouteException;
use core\base\settings\Settings;

abstract class BaseAdmin extends BaseController
{
    protected $model;
    protected $table;
    protected $columns;
    protected $data;
    protected $adminPath;
    protected $menu;
    protected $title;
    protected $translate;
    protected $blocks = [];

    protected function inputData()
    {
        $this->init(true);
        $this->title = 'Engine';
        if (empty($this->model)) @$this->model = Model::instance();
        if (empty($this->menu)) @$this->menu = Settings::get('projectTables');
        if (empty($this->adminPath)) @$this->adminPath = PATH . Settings::get('routes')['admin']['alias'] . '/';

        $this->sendNoCacheHeaders();
    }

    protected function outputData()
    {
        if(!$this->content){
            $args = func_get_arg(0);
            $vars = $args ? $args : [];
           // if (empty($this->template)) $this->template = ADMIN_TEMPLATE . 'show';
            $this->content = $this->render($this->template, $vars);
        }
        $this->header = $this->render(ADMIN_TEMPLATE . 'include/header');
        $this->footer = $this->render(ADMIN_TEMPLATE . 'include/footer');
        return $this->render(ADMIN_TEMPLATE . 'layout/default');
    }

    protected function sendNoCacheHeaders()
    {
        header('Last-Modified:  ' . gmdate("D, d m Y H:i:s ") . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Cache-Control:  max - age = 0');
        header('Cache-Control:  post - check = 0, pre - check = 0');
    }

    protected function execBase()
    {
        self::inputData();
    }

    protected function createTableData($settings = false)
    {
        if (empty($this->table)) {
            if (!empty($this->parametrs)) $this->table = array_keys($this->parametrs)[0];
            else {
                if(!$settings) $settings = Settings::instance();
                $this->table = $settings::get('defaultTable')['table'];
            }
        }
        $this->columns = $this->model->showColumns($this->table);
        if (empty($this->columns)) new RouteException('Не найдены поля в таблице - ' . $this->table, 2);
    }


    protected function expansion($args = [], $settings = false)
    {
        $fileName = explode('_', $this->table);
        $className = '';
        foreach ($fileName as $item) {
            $className .= ucfirst($item);
        }
        if (!$settings) {
            $path = Settings::get('expansion')['path'];
        } elseif (is_object($settings)) {
            $path = $settings::get('expansion')['path'];
        } else {
            $path = $settings;
        }

        $class = $path . $className . 'Expansion';

        if (is_readable($_SERVER['DOCUMENT_ROOT'] . PATH . $class . ' . php')) {
            $class = str_replace(' / ', '\\', $class);
            $exp = $class::instance();

            foreach ($this as $name => $value) {
                $exp->$name = $this->$name;
            }
            return $exp->expansion($args);
        } else {
            $file = $_SERVER['DOCUMENT_ROOT'] . PATH . $path . $this->table . ' . php';
            extract($args);
            if (is_readable($file)) return include $file;
        }
        return false;
    }

    protected function createOutputData($settings = false)
    {
        if(!$settings) $settings = Settings::instance();
        $blocks = $settings::get('blockNeedle');
        $this->translate = $settings::get('translate');

        if(!empty($blocks) || is_array($blocks)){
            foreach($this->columns as $name => $value){

                if($name === 'id_row') continue;
                if(!isset($this->translate[$name])){
                    $this->translate[$name][] = $name;
                }
                $this->blocks[0][] = $name;
            }
        }
        $default = array_keys($blocks)[0];
        foreach($this->columns as $name => $item){
            if($name === 'id_row') continue;
            $insert = false;
            foreach($blocks as $block => $value){
                if(!array_key_exists($block, $this->blocks)) $this->blocks[$block] = [];
                if(in_array($name, $value)){
                    $this->blocks[$block][] = $name;
                    $insert = true;
                    break;
                }
            }
            if(!$insert) $this->blocks[$default][] = $name;
        }
    }

}