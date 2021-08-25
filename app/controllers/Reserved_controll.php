<?php

namespace App\Controllers;

use App\Exceptions\AuthErrorException;
use App\Services\ActiveConnectionService;
use App\Services\Date;
use App\Services\Goods;
use App\Services\BOOKED_TICKET;


class Reserved_controll
{
    protected $activeConnectionService;


    public function __construct()
    {
        $this->goods                   = new GOODS();
        $this->date                    = new Date();
        $this->activeConnectionService = new ActiveConnectionService();
        $this->BOOKED_TICKET           = new BOOKED_TICKET();
        date_default_timezone_set('Europe/Kiev');
    }

    public function Reserved()
    {
        $request    = request();
        $user_id    = $request['user_id'];
        $date       = $request['date'];
        $id_session = intval($request['id_session']);
        $tickets    = $request['tickets'];

        logger('Резервуванян квитків, id - ' . $user_id);

        if (empty($user_id)) {
            logger('Порожній ID');
            return [
                'response_code' => 4,
                'booked_id' => NULL
            ];
        }

        if (substr_count($user_id, '-') !== 4) {
            logger('Формат ID не коректний');
            return [
                'response_code' => 6,
                'booked_id' => NULL
            ];
        }

        $activeConnection = $this->activeConnectionService->is_id_work($user_id);
        if(!$activeConnection){
            logger('Строк дії id закінчився');
            return [
                'response_code' => 2,
                'booked_id' => NULL
            ];
        }

        if (!Date::Check_date($date)) {
            logger('Дата'.$date.', не коректна.');
            return [
                'response_code' => 7,
                'booked_id' => NULL
            ];
        }
        //var_dump($id_session);
        if (!is_int($id_session)) {
            logger('Номер сесії: '.$id_session.', не коректний.');
            return [
                'response_code' => 0,
                'booked_id' => NULL
            ];
        }
        if (!$this->goods->check_tickets_id($tickets)) {
            logger('Один або всі квитки не коректні.');
            return [
                'response_code' => 5,
                'booked_id' => NULL
            ];
        }  

        $booket_ticket = $this->BOOKED_TICKET->get_count_booked_ticket($id_session, $date);
        $empty_seats   = intval(getConfig()->get('max_count_of_visitor')) - $booket_ticket;
        $booked_seats  = 0;
        foreach ($tickets as $key => $value) {
            $booked_seats = $booked_seats + $value['quantity'];
        }
        if($empty_seats < $booked_seats){
            logger('Замовлено('.$booked_seats.') більше квитків ніж доступно('.$empty_seats.')');
            return [
                'response_code' => 8,
                'booked_id' => NULL
            ];
        }
      
        $booked_id       = $this->BOOKED_TICKET->add_booking($id_session, $date);
        $booked_tickets  = $this->BOOKED_TICKET->add_booking_tickets($tickets, $booked_id);
        $booked_id       = intval(getConfig()->get('booked_id_prefix')).$booked_id;
        logger('Резервування готове.');
        return [
            'booked_id'     => $booked_id,
            'booked_tikets' => $booked_tickets,
            'response_code' => 1
        ];
    }
}
