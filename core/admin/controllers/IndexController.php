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
        $res = $db->delete($table, [
            'where' => ['id' => 3],
            'join' => [
                'students' => [
                    'on' => ['student_id', 'id' ],
                ]
            ],
        ]);


    }
}