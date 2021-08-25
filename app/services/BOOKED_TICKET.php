<?php

namespace App\Services;

use App\Infrastructure\Database;
use DateTime;
use App\Services\Goods;

class BOOKED_TICKET
{
    protected $database;

    /**
     * @throws \App\Exceptions\DatabaseConnectionError
     */
    public function __construct()
    {
        $this->database = Database::getConnection('main');
        $this->goods    = new GOODS();
    }

    public function get_count_booked_ticket(int $id_session, string $date)
    {
        $result = $this->database->runQueryWithResult("
            SELECT sum(booked_ticket_content.quantity)
            from BOOKED_TICKET join booked_ticket_content on booked_ticket.id=booked_ticket_content.id_booked_ticket
            where BOOKED_TICKET.id_session=$id_session and booked_ticket.session_date='$date'  and booked_ticket.status!=0
            and booked_ticket_content.is_active=1"
        );
        //var_dump($result);
        return $result['SUM'];
    }

    public function add_booking(int $id_session, string $date)
    {
        $date_time = new DateTime;
        $DATE_TIME = $date_time->format('d.m.Y H:i:s');
        $result = $this->database->runQueryWithResult("
            insert into BOOKED_TICKET (ID, DATE_TIME, SOURCE, ID_SESSION, SESSION_DATE, RECEIPT_NUMBER, STATUS, NAME, PHONE, EMAIL)
            VALUES (NULL, '$DATE_TIME', 1, $id_session, '$date', NULL, 2, NULL, NULL, NULL) returning id;"
        );
        return $result['ID'];
    }

    public function add_booking_tickets(array $tickets, int $booked_id)
    {
        $arr = array();
        foreach ($tickets as $key => $value) {
            $id         = $value['id'];
            $quantity   = $value['quantity'];
            $goods_info = $this->goods->get_article_info_from_id($id, 'ID_BAR');
            //var_dump($goods_info);
            $price      = $goods_info[0]['PRICE'];
            $result = $this->database->runQueryWithResult("
                INSERT INTO BOOKED_TICKET_CONTENT (ID, ID_BOOKED_TICKET, ID_BAR, QUANTITY, PRICE, PAYMENT_STATUS, IS_ACTIVE)
                VALUES (NULL, $booked_id, $id, $quantity, $price, 1, 1) returning id;
            ");
            $booked_tickets= 
            [
                'id'               => $id,
                'id_booked_tikets' => $result['ID']
            ];
            array_push($arr, $booked_tickets);  

        }
        //var_dump($arr);
        return $arr;
    }

    public function check_booked_tickets_id(array $booked_tickets, int $booked_id)
    {
        //var_dump($booked_tickets);
        foreach ($booked_tickets as $key => $value) {
            if(!$this->is_booked_tickets_id_true($value['id_booked_tikets'],$booked_id)){
                return false;
            }
        }
        return true;
    }

    public function is_booked_tickets_id_true(int $booked_tickets_id, int $booked_id)
    {
        $result = $this->database->runQueryWithMultyResult("
            select * from booked_ticket_content where booked_ticket_content.id=$booked_tickets_id 
            and booked_ticket_content.id_booked_ticket=$booked_id;
        ");
        if(!is_array($result)){
            return false;
        }
        return true;
    }
    
    public function confirm_booking(string $booked_name,string $booked_phone,string $booked_email,int $booked_id, array $booked_tickets)
    {
        $result = $this->database->runQuery("
            update booked_ticket set STATUS=1, NAME='$booked_name', PHONE='$booked_phone', EMAIL='$booked_email' where ID=$booked_id
        ");
        if($result === false){
            return 0;
        }
        //var_dump($booked_id);
        foreach ($booked_tickets as $key => $value) {
            $status = $value['pay_status'];
            $id     = $value['id_booked_tikets'];
            $result = $this->database->runQuery("
                update booked_ticket_content set PAYMENT_STATUS=$status where ID=$id  
            ");
            if($result === false){
                return 0;
            }
        }
        return 1;
    }
}
