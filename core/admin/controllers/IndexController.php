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
        $files['gallery_img'] = ['red.jpg', 'blue.jpg', 'black.jpg'];
        $files['img'] = 'main_img.jpg';
        $querry = $db->add($table, [
            'fields' => ['name' => 'Olga', 'text' => 'text'],
           'except' => ['name'],
            'files' => $files,
        ]);
        exit(print_r($querry));

    }
}