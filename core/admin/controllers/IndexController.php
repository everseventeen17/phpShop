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
        $color = ['red', 'blue', 'black'];
        $res = $db->get($table, [
            'fields' => ['id', 'name'],
            'where' => ['name' => 'Masha', 'surname'=>'Sergeevna', 'fio' => 'Andrei', 'car'=>'Porshe', 'color'=> $color],
            'operand' => [ 'IN', 'LIKE', '<>', '=', 'NOT IN' ],
            'condition'=> ['OR', 'AND'],
            'order' => ['fio', 'name'],
            'order_direction' => ['DESC'],
            'limit' => '1',
        ]);
        $res = $db->query($querry);
    }
}