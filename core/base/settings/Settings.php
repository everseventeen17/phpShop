<?php

namespace core\base\settings;

class Settings
{
    use \core\base\controllers\Singleton;
    private $templateArr = [
        'text' => ['name', 'phone', 'adress',],
        'textArea' => ['content',],
    ];
    private $routes = [
        "admin" => [
            "alias" => "admin",
            "path" => "core/admin/controllers/",
            "hrURL" => false,
            "routes" => [

            ],
        ],
        "settings" => [
            "path" => "core/base/settings/",
        ],
        "plugins" => [
            "path" => "core/plugins/",
            "hrURL" => false,
            "dir" => '',
        ],
        "user" => [
            "path" => "core/user/controllers/",
            "hrURL" => true,
            "routes" => [
            ],
        ],
        "default" => [
            "controller" => "IndexController",
            "inputMethod" => "inputData",
            "outputMethod" => "outputData",
        ],
    ];



    static public function get($property)
    {
        return self::instance()->$property;
    }

    public function clueProperties($class)
    {
        $baseProperties = [];
        foreach ($this as $name => $item) {
            $property = $class::get($name);
            $baseProperties[$name] = $property;
            if (is_array($property) && is_array($item)) {
                $baseProperties[$name] = $this->arrayMergeRecursive($this->$name, $property);
                continue;
            }
            if (!$property) {
                $baseProperties[$name] = $this->$name;
            }
        }
        return $baseProperties;
    }

    public function arrayMergeRecursive()
    {
        $arrays = func_get_args();
        $base = array_shift($arrays);
        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && is_array($base[$key])) {
                    $base[$key] = $this->arrayMergeRecursive($base[$key], $value);
                } else {
                    if (is_int($key)) {
                        if (!in_array($value, $base)) {
                            array_push($base, $value);
                            continue;
                        }
                    }
                    $base[$key] = $value;
                }
            }
        }
        return $base;
    }

}