<?php

namespace App\Exceptions;

use Exception;

class DatabaseConnectionError extends Exception
{
    public function __construct()
    {   
        //return 3;
        die('app not work');
    }
}
