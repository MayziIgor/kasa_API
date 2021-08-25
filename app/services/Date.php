<?php

namespace App\Services;

class Date
{

    public function __construct()
    {
        
        //$this->database = Database::getConnection('server');
    }


    public static function Check_date(string $date)
    {
        logger('Перевірка на те що дата введено кореткно.'.$date);
        $test_data_ar = explode('.', $date);
        //если дата введена в корректном формате d.m.Y (checkdate(месяц, день, год))
        if(@checkdate($test_data_ar[1], $test_data_ar[0], $test_data_ar[2])) {
            return true;
        }
        else return false;
        
    }
}
