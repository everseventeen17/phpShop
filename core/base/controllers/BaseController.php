<?php

namespace core\base\controllers;

use core\base\exceptions\RouteException;

abstract class BaseController
{
    protected $page;
    protected $errors;

    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected $parametrs;

    public function route()
    {
        $controller = str_replace('/', '\\', $this->controller);
        try {
            $object = new \ReflectionMethod($controller, 'request');
            $args = [
                'parameters' => $this->parametrs,
                'inputMethod' => $this->inputMethod,
                'outputMethod' => $this->outputMethod,
            ];
            $object->invoke(new $controller, $args);

        } catch (\ReflectionException $e) {
            throw new RouteException($e->getMessage());
        }
    }

    public function request($args)
    {
        $this->parametrs = $args['parameters'];
        $inputData = $args['inputMethod'];
        $outputData = $args['outputMethod'];
        $this->$inputData();
        $this->page = $this->$outputData();
        if ($this->errors) {
            $this->writeLog('error');
        }
        $this->getPage();
    }

    protected function render($path = '', $parameters = [])
    {
        extract($parameters);
        if(!$path){
            $path = TEMPLATE . explode('controller', strtolower((new \ReflectionClass($this))->getShortName()))[0];
        }
        ob_start();
        if(!@include_once $path . '.php') throw new RouteException('Отсутсвтует шаблон для подключения - '  . $path);
        return ob_get_clean();
    }


    protected function getPage()
    {
        exit($this->page);
    }
}