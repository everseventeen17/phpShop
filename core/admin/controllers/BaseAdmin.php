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
    protected $menu;
    protected $title;

    protected function inputData()
    {
        $this->init(true);
        $this->title = 'Engine';
        if (!empty($this->model)) $this->model = Model::instance();
        if (!empty($this->menu)) $this->menu = Settings::get('projectTables');
        $this->sendNoCacheHeaders();
    }

    protected function outputData()
    {

    }

    protected function sendNoCacheHeaders()
    {
        header('Last-Modified:  ' . gmdate("D, d m Y H:i:s ") . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Cache-Control:  max-age=0');
        header('Cache-Control:  post-check=0, pre-check=0');
    }

    protected function exectBase()
    {
        self::inputData();
    }

    protected function createTableData()
    {
        if (empty($this->table)) {
            if (!empty($this->parametrs)) $this->table = array_keys($this->parametrs[0]);
            else $this->table = Settings::get('defaultTable');
        }
        $this->table = Settings::get('dafaultTable')['table'];
        $this->columns = $this->model->showColumns($this->table);
        if (empty($this->columns)) new RouteException('Не найдены поля в таблице -' . $this->table, 2);
    }

}