<?php

namespace core\base\settings;

class Settings
{
    static private $_instance;
    private $routes = [
        "admin" => [
            "alias" => "admin",
            "path" => "core/admin/controller/",
            "hrURL" => false,

        ],
        "settings" => [
            "path" => "core/base/settings/",
        ],
        "plugins" => [
            "path" => "core/plugins/",
            "hrURL" => false,
            "dir" => false,
        ],
        "user" => [
            "path" => "core/user/controller/",
            "hrURL" => true,
            "routes" => [

            ],
        ],
        "default" => [
            "controller" => "indexController",
            "inputMethod" => "inputData",
            "outputMethod" => "outputData",
        ],
    ];


    private function __construct()
    {
    }

    private function __clone()
    {
    }

    static public function get($property)
    {
        return self::instance()->$property;
    }

    static public function instance()
    {
        if (self::$_instance instanceof self) {
            return self::$_instance;
        }
        return self::$_instance = new self;
    }

    public function clueProperties($class)
    {
        $baseProperties = [];
        foreach ($this as $name => $item) {
            $property = $class::get($name);
//            $baseProperies[$name] = $property;

            if (is_array($property) && is_array($item)) {
                $baseProperties[$name] = $this->arrayMergeRecursice($this->$name, $property);
                continue;
            }

            if (!$property) $baseProperties[$name] = $this->$name;
        }
    return $baseProperties;
    }

    public function arrayMergeRecursice()
    {
        $arrays = func_get_args();
        $base = array_shift($arrays);

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && is_array($base[$key])) {
                    $base[$key] = $this->arrayMergeRecursice($base[$key], $value);
                } else {
                    if (is_int($key)) {
                        if (!in_array($value, $base)) {
                            array_push($base, $value);
                            continue;
                        }
                        $base[$key] = $value;
                    }
                }
            }
        }
        return $base;
    }

}