<?php

namespace App;

class Application extends \ManaPHP\Mvc\Application
{
    public function authenticate()
    {
        // parent::authenticate();
    }

    public function authorize()
    {
        // parent::authorize();
    }

    public function handleException($exception)
    {
        if ($this->dispatcher->getArea() === 'Api') {
            $this->_di->getShared('ManaPHP\Rest\ErrorHandler')->handle($exception);
        } else {
            $this->_di->getShared('ManaPHP\Mvc\ErrorHandler')->handle($exception);
        }
    }
}
