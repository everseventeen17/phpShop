<?php

namespace core\admin\controllers;

class ShowController extends BaseAdmin
{
    protected function inputData()
    {
        $this->exectBase();
        $this->createTableData();
        exit();
    }

    protected function outputData()
    {

    }

}