<?php

namespace App\Services;

use App\Entities\User;
use App\Infrastructure\Database;

class UserService
{
    protected $database;

    public function __construct()
    {
        //echo "sss";
        $this->database = Database::getConnection('main');
    }

    public function getUserById(int $id): ?User
    {
        $sql    = "SELECT * from users where users.id='$id'";
        
        $result = $this->database->runQueryWithResult($sql);

        if (isset($result['LOGIN']) && isset($result['PASS'])) {
            return new User($result['LOGIN'], $result['PASS']);
        }

        return null;
    }
}
