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
    protected $menu;
    protected $title;

    protected function inputData()
    {
        $this->init(true);
        $this->title = 'Engine';
        if (empty($this->model)) @$this->model = Model::instance();
        if (empty($this->menu)) @$this->menu = Settings::get('projectTables');
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

    protected function execBase()
    {
        self::inputData();
    }

    protected function createTableData()
    {
        if (empty($this->table)) {
            if (!empty($this->parametrs)) $this->table = array_keys($this->parametrs[0]);
            else $this->table = @Settings::get('defaultTable');
        }
        $this->table = Settings::get('dafaultTable')['table'];
        $this->columns = $this->model->showColumns($this->table);
        if (empty($this->columns)) new RouteException('Не найдены поля в таблице -' . $this->table, 2);
    }

    protected function createData($arr = [], $add = true)
    {
        $fields = [];
        $order = [];
        $order_direction = [];
        if (!empty($add)) {
            if (empty($this->columns['id_row'])) return $this->data = [];
            $fields[] = $this->columns['id_row'] . ' as id';
            if ($this->columns['name']) $fields['name'] = 'name';
            if ($this->columns['img']) $fields['img'] = 'img';
            if (count($fields) < 3) {
                foreach ($this->columns as $key => $item) {
                    if (!empty($fields['name']) and strpos($key, 'name') !== false) {
                        $fields['name'] = $key . ' as name';
                    }
                    if (!empty($fields['img']) and strpos($key, 'img') !== 0) {
                        $fields['img'] = $key . ' as img';
                    }
                }
            }
            if (!empty($arr['fields'])) {
                $fields = Settings::instance()->arrayMergeRecursive($fields, $arr['fields']);
            }
            if (!empty($this->columns['parent_id'])) {
                if (!in_array('parent_id', $fields)) $fields[] = 'parent_id';
                $order[] = 'parent_id';
            }
            if (!empty($this->columns['menu_position'])) $order[] = 'menu_position';
            elseif ($this->columns['date']) {

                if ($order) $order_direction = ['ASC', 'DESC'];
                else $order_direction[] = ['DESC'];

                $order[] = 'date';
            }
            if (!empty($arr['order'])) {
                $order = Settings::instance()->arrayMergeRecursive($order, $arr['order']);
            }
            if (!empty($arr['order_direction'])) {
                $order_direction = Settings::instance()->arrayMergeRecursive($order_direction, $arr['order_direction']);
            }
        } else {
            if (empty($arr)) return $this->data = [];
            $fields = $arr['fields'];
            $order = $arr['fields'];
            $order_direction = ['order_direction'];
        }
        $this->data = $this->model->get($this->table, [
            'fields' => $fields,
            'order' => $order,
            'order_direction' => $order_direction,
        ]);
        exit();

    }

}