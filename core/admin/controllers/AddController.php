<?php

namespace core\admin\controllers;

class AddController extends BaseAdmin
{

    protected function inputData()
    {
        if(!$this->userId){
            $this->execBase();
        }
        $this->createTableData();
        $this->createOutputData();

    }



}