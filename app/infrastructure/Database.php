<?php

namespace App\Infrastructure;

use App\Exceptions\DatabaseConnectionError;

class Database
{
    private $connection;

    private static $connections = [
       'main'   => null,
       'server' => null,
    ];

    /**
     * @throws \App\Exceptions\DatabaseConnectionError
     */
    private function __construct($connectionName)
    {
        $config = getConfig();
        $connection = ibase_connect(
            $config->get("databases.".$connectionName.".host"),
            $config->get("databases.".$connectionName.".user"),
            $config->get("databases.".$connectionName.".pswd"),
            $config->get("databases.".$connectionName.".charset")
        );
        if (! $connection) {
            //echo "2";
            throw new DatabaseConnectionError();
        }
        //var_dump($connection);
        $this->connection                   = $connection;
        self::$connections[$connectionName] = $this;
    }

    public function __destruct()
    {
        foreach (self::$connections as $connection) {
            if($connection){
                //var_dump($connection->connection);
                ibase_close($connection->connection);
            }
            
        }
    }

    /**
     * @throws \App\Exceptions\DatabaseConnectionError
     */
    public static function getConnection(string $name)
    {
        if (! array_key_exists($name, self::$connections)) {
            throw new DatabaseConnectionError();
        }

        if (! is_null(self::$connections[$name])) {
            return self::$connections[$name];
        }
        //var_dump(new self($name));
        return new self($name);
    }



    public function runQuery(string $sql)
    {
        return ibase_query($this->connection, $sql);
    }

    public function runQueryWithResult(string $sql)
        {
            //var_dump($sql);
            //var_dump(ibase_query($this->connection, $sql));
            return ibase_fetch_assoc(ibase_query($this->connection, $sql));

        }


    public function runQueryWithMultyResult(string $sql)
        {
            //var_dump($sql);
            $s = ibase_query($this->connection, $sql);
            $arr = array();
            $i = 0;
            while ($row = ibase_fetch_assoc ($s)) { 
                $i++;
                array_push($arr, $row);
            }
            if($i == 0){
                return false;
            }
           return $arr;
        }
}
