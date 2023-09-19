<?php

namespace core\base\exceptions;

class RouteException extends \Exception
{
    protected $messages;
    use  \core\base\controllers\BaseMethods;

    public function __construct(string $message = "", int $code = 0)
    {
        parent::__construct($message, $code);
        $this->messages = include 'messages.php';
        $error = $this->getMessage() ? $this->getMessage() : $this->messages[$this->getCode()];
        $error .= "\r\n" . "file" . $this->getFile() . "\r\n" . "in line" . $this->getLine() . "\r\n";
        if($this->messages[$this->getCode()]){
            $this->message = $this->messages[$this->getCode()];
        }
        $this->writeLog($error);
    }

}