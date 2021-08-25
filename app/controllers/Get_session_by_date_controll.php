<?php

namespace App\Controllers;

use App\Exceptions\AuthErrorException;
use App\Services\ActiveConnectionService;
use App\Services\UserService;
use App\Services\Date;
use DateTime;
use DateInterval;

class Get_session_by_date_controll
{
    protected $activeConnectionService;

    protected $userService;

    public function __construct()
    {
        $this->date  = new Date();
        $this->activeConnectionService = new ActiveConnectionService();
        date_default_timezone_set('Europe/Kiev');
    }

    public function Get_session_by_date()
    {
        $request    = request();
        $user_id    = $request['user_id'];
        $date_start = $request['date_start'];
        $date_end   = $request['date_end'];

        logger('Отримання сеансів по днях, id - ' . $user_id);

        if (empty($user_id)) {
            logger('Порожній ID');
            return [
                'response_code' => 4,
                'dates' => NULL
            ];
        }

        if (substr_count($user_id, '-') !== 4) {
            logger('Формат ID не коректний');
            return [
                'response_code' => 6,
                'dates' => NULL
            ];
        }

        $activeConnection = $this->activeConnectionService->is_id_work($user_id);
        if(!$activeConnection){
            logger('Строк дії id закінчився');
            return [
                'response_code' => 2,
                'dates' => NULL
            ];
        }

        if (!Date::Check_date($date_start) || !Date::Check_date($date_end) || !(strtotime($date_start)<=strtotime($date_end))) {
            logger('Дата старта періода('.$date_start.') чи кінця('.$date_end.'), не коректна.');
            return [
                'response_code' => 7,
                'dates' => NULL
            ];
        }
        //Масив, дат
        $period = new \DatePeriod(
            new DateTime($date_start),
            new DateInterval('P1D'),
            new DateTime($date_end)
       );
       $dates_arr = array();
       foreach ($period as $key => $value) {
            $date = $value->format('d.m.Y');
            $session_by_date = $this->activeConnectionService->get_session_by_date($date);
            array_push($dates_arr, $session_by_date);
        }
        //Виконуємо на останній день в періоді, бо він не входить в масив.
        $session_by_date = $this->activeConnectionService->get_session_by_date($date_end);
        array_push($dates_arr, $session_by_date);
        logger('Передача сеансів по датах.');
        return [
            'dates'         => $dates_arr,
            'response_code' => 1
        ];
    }
}
