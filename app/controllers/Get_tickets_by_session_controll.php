<?php

namespace App\Controllers;

use App\Exceptions\AuthErrorException;
use App\Services\ActiveConnectionService;
use App\Services\Date;

class Get_tickets_by_session_controll
{
    protected $activeConnectionService;


    public function __construct()
    {
        $this->date                    = new Date();
        $this->activeConnectionService = new ActiveConnectionService();
        date_default_timezone_set('Europe/Kiev');
    }

    public function Get_tickets_by_session()
    {
        $request    = request();
        $user_id    = $request['user_id'];
        $date       = $request['date'];
        $id_session = intval($request['id_session']);

        logger('Отримання Квитків по сеансах, id - ' . $user_id);

        if (empty($user_id)) {
            logger('Порожній ID');
            return [
                'response_code' => 4,
                'tickets' => NULL
            ];
        }

        if (substr_count($user_id, '-') !== 4) {
            logger('Формат ID не коректний');
            return [
                'response_code' => 6,
                'tickets' => NULL
            ];
        }

        $activeConnection = $this->activeConnectionService->is_id_work($user_id);
        if(!$activeConnection){
            logger('Строк дії id закінчився');
            return [
                'response_code' => 2,
                'tickets' => NULL
            ];
        }

        if (!Date::Check_date($date)) {
            logger('Дата'.$date.', не коректна.');
            return [
                'response_code' => 7,
                'tickets' => NULL
            ];
        }
        //var_dump($id_session);
        if (!is_int($id_session)) {
            logger('Номер сесії: '.$id_session.', не коректний.');
            return [
                'response_code' => 0,
                'tickets' => NULL
            ];
        }
        $tickets_by_session = $this->activeConnectionService->get_tickets_by_session($id_session);
        logger('Передача квитків по сеансах.');
        return [
            'tickets'       => $tickets_by_session,
            'response_code' => 1
        ];
    }
}
