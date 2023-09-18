<?php

namespace core\user\controllers;

use core\base\controllers\BaseController;

class IndexController extends BaseController
{
protected $name;
    protected function inputData()
    {
        $name = "Masha";
        $content = $this->render('', compact('name'));
        $header = $this->render(TEMPLATE.'header');
        $footer = $this->render(TEMPLATE.'footer');
        return compact('header', 'content', 'footer');
    }
}