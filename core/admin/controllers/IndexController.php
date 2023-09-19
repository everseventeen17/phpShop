<?php


namespace core\admin\controllers;

use core\base\controllers\BaseController;
use core\admin\models\Model;

class IndexController extends BaseController
{
    protected function inputData()
    {
        $db = Model::instance();




        $table = 'teachers';
        $res = $db->get($table, [
            'fields' => ['id', 'name'],
            'where' => ['name' => 'Masha, Olya, Sveta','name' => 'Masha', 'Surname'=>'Sergeevna'],
            'operand' => [ 'IN', '<>' ],
            'condition'=> ['AND'],
            'order' => ['fio', 'name'],
            'order_direction' => ['DESC'],
            'limit' => '1',
        ]);
        $res = $db->query($querry);
    }
}