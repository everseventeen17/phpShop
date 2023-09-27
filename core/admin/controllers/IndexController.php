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
        $_POST['id'] = 3;
        $_POST['name'] = 'OKSANSA';
        $_POST['text'] = 'TOP TEXT IN THE WORLD';

        $querry = $db->edit($table);

        echo "<pre>";
        print_r($querry);
        echo "</pre>";

    }
}