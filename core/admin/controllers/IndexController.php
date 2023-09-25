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
        $querry = $db->get($table, [
            'fields' => ['id', 'name'],
            'where' => ['name' => "Masha"],
//            'operand' => ['IN', '<>'],
//            'condition' => ['AND', 'OR'],
            'order' => ['id'],
            'order_direction' => ['DESC'],
//            'limit' => '1',
            'join' => [
                1 => [
                    'table' => 'join_table1',
                    'fields' => ['id as j_id', 'name as J_name'],
                    'type' => 'left',
                    'where' => ['name' => 'Sasha'],
                    'operand' => ['<>'],
                    'condition' => ['AND'],
                    'on' =>[
                        'table'=> 'teachers',
                        'fields'=>['id', 'parent_id']
                    ]
                ],
                2 => [
                    'table' => 'join_table2',
                    'fields' => ['id as j_id', 'name as J_name'],
                    'type' => 'left',
                    'where' => ['name' => 'Sasha'],
                    'operand' => ['<>'],
                    'condition' => ['AND'],
                    'on' =>[
                        'table'=> 'teachers',
                        'fields'=>['id', 'parent_id']
                    ]
                ]
            ]
        ]);

    }
}