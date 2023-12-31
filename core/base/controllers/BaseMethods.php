<?php

namespace core\base\controllers;

trait BaseMethods
{
    protected function clear($str)
    {
        if (is_array($str)) {
            foreach ($str as $key => $value) {
                $str[$key] = trim(strip_tags($value));
                return $str;
            }
        } else {
            return trim(strip_tags($str));
        }
    }

    protected function clearNum($num)
    {
        return $num * 1;
    }

    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    protected function redirect($http = false, $code = false)
    {
        if ($code) {
            $codes = [
                '301' => 'HTTP/1.1 301 Move Permanently'
            ];
            if ($codes[$code]) {
                header($codes[$code]);
            }
        }
        if ($http) {
            $redirect = $http;
        } else {
            $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : PATH;
        }
        header("Location: $redirect");
    }

    protected function writeLog($message, $file = 'log.txt', $event = 'Fault')
    {
        $nowDateTime = new \DateTime();
        $str = $event . ': ' . $nowDateTime->format('d-m-Y H:i:s') . ' - ' . $message . "\r\n";
        file_put_contents('log/' . $file, $str, FILE_APPEND);

    }

}