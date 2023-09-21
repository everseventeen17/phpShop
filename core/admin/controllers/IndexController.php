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
            'where' => ['name' => "O'Riley"],
            'operand' => ['IN', '<>'],
            'condition' => ['OR', 'AND'],
            'order' => ['fio', 'name'],
            'order_direction' => ['DESC'],
            'limit' => '1',
            'join' => [
                'join_table' => [
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
//                'join_table2' => [
//                    'table' => 'join_table2',
//                    'fields' => ['id as j_id', 'name as J_name'],
//                    'type' => 'left',
//                    'where' => ['name' => 'Sasha'],
//                    'operand' => ['<>'],
//                    'condition' => ['AND'],
//                    'on' =>[
//                        'table'=> 'teachers',
//                        'fields'=>['id', 'parent_id']
//                    ]
//                ]
            ]
        ]);
//        $res = $db->query($querry);
    }
}