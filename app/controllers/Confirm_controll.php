<?php

namespace App\Controllers;

use App\Exceptions\AuthErrorException;
use App\Services\ActiveConnectionService;
use App\Services\Date;
use App\Services\BOOKED_TICKET;


class Confirm_controll
{
    protected $activeConnectionService;


    public function __construct()
    {
        $this->date                    = new Date();
        $this->activeConnectionService = new ActiveConnectionService();
        $this->BOOKED_TICKET           = new BOOKED_TICKET();
        date_default_timezone_set('Europe/Kiev');
    }

    public function Confirm()
    {
        $request       = request();
        $user_id       = $request['user_id'];
        $booked_name   = $request['booked_name'];
        $booked_phone  = $request['booked_phone'];
        $booked_email  = $request['booked_email'];
        $booked_id     = $request['booked_id'];
        $booked_tickets= $request['booked_tickets'];

        $booked_id = intval(mb_substr($booked_id, iconv_strlen(getConfig()->get('booked_id_prefix')),null));
        logger('Підтвердження квитків, id - ' . $user_id. ". Номер замовлення:" .$booked_id);

        if (empty($user_id)) {
            logger('Порожній ID');
            return [
                'response_code' => 4
            ];
        }

        if (substr_count($user_id, '-') !== 4) {
            logger('Формат ID не коректний');
            return [
                'response_code' => 6
            ];
        }

        $activeConnection = $this->activeConnectionService->is_id_work($user_id);
        if(!$activeConnection){
            logger('Строк дії id закінчився');
            return [
                'response_code' => 2
            ];
        }

        if (!is_int($booked_id)) {
            logger('Номер замовлення: '.$booked_id.', не коректний.');
            return [
                'response_code' => 0
            ];
        }
         //var_dump($id_session);
         if (!$this->BOOKED_TICKET->check_booked_tickets_id($booked_tickets, $booked_id)) { 
            logger('Один або всі заброньовані квитки не коректні');
            return [
                'response_code' => 5
            ];
        }        
       
        if (!$this->BOOKED_TICKET->confirm_booking($booked_name, $booked_phone, $booked_email, $booked_id, $booked_tickets)) { 
            logger('Помилка підтвердження замовлення: '.$booked_id);
            return [
                'response_code' => 0
            ];
        }   
        logger('Замовлення:'.$booked_id.' підтверджене.');
        return [
            'response_code' => 1
        ];
    }
}
