<?php

namespace App\Services;

use App\Entities\ActiveConnection;
use App\Infrastructure\Database;
use App\Services\BOOKED_TICKET;
use App\Services\Goods;
use Noodlehaus\Config;

class ActiveConnectionService
{
    protected $database;
    protected $active_connection;

    public function __construct()
    {
        $this->BOOKED_TICKET = new BOOKED_TICKET();
        $this->goods         = new GOODS();
        $this->database      = Database::getConnection('main');
    }

    public function create(string $id)
    {
        $start_time = time() + 3600 * 3;
        $this->database->runQuery(
            "INSERT INTO ACTIVE_CONN (ID, GUID, START_USERS_SESSION, IS_ACTIVE) VALUES (NULL, '$id', $start_time, 1);"
        );
    }

    public function get(string $id): ?ActiveConnection
    {
        //echo "sfsfsf";
        $result = $this->database->runQueryWithResult(
            "SELECT * from active_conn where active_conn.GUID='$id' and active_conn.is_active=1"
        );

        if (isset($result['START_USERS_SESSION'])) {
            return new ActiveConnection($result['START_USERS_SESSION']);
        }

        return null;
    }

    public function disable(string $id)
    {
        $this->database->runQuery("update active_conn set active_conn.is_active=0 where active_conn.guid='$id'");
    }
    
    
    public function is_id_work(string $id)
    {
        $result = $this->database->runQueryWithResult(
            "SELECT * from active_conn where active_conn.GUID='$id' and active_conn.is_active=1"
        );
        if(is_array($result )){
            $timeStart  = $result['START_USERS_SESSION'];
            $time_now = time() + 3600 * 3;
            $diff       = $time_now - $timeStart;
            if ($diff > 900) {
                return false;
            }
            return true;
        }
        return false;
    }


    public function get_session_by_date(string $date)
    {
        $result = $this->database->runQueryWithMultyResult(
            "SELECT * from sessions where sessions.\"DATE\"='$date';"
        );
        if(is_array($result)){
            return $this->create_session($result, $date);
        }
        else {
            if(date("N", strtotime($date)) >5){
                $date_type = 9;
            }
            else{ $date_type = 8; }
            $result = $this->database->runQueryWithMultyResult(
                "SELECT * from sessions where sessions.DAY_TYPE=$date_type;"
            );

            if(is_array($result)){
                return $this->create_session($result, $date);
            }
        }
        
    }
    function create_session(array $result, string $date)
    {
        $arr = array();
        $row = array();
        foreach ($result as $key => $value) {
            $booket_ticket = $this->BOOKED_TICKET->get_count_booked_ticket($value['ID_SESSION'], $date);
            $empty_seats   = intval(getConfig()->get('max_count_of_visitor')) - $booket_ticket;
            if ($empty_seats < 0) {
                $empty_seats = 0;
            }
            $session= 
            [
                'id'                 => $value['ID_SESSION'],
                'time_session_start' => $value['TIME_SESSIONS_START'],
                'session_length'     => $value['SESSIONS_LENGTH'],
                'empty_seats'        => $empty_seats 
            ];
            array_push($row, $session);   
        }
        $arr =[
            'date' => $date,
            'session' => $row
        ];
        return $arr;
    }
    public function get_tickets_by_session(int $id_session)
    {
        $result = $this->database->runQueryWithResult(
            "select tariff from sessions where sessions.id_session=$id_session;"
        );
        $tariff = explode(',', $result['TARIFF']);
        $tick_arr = array();
        foreach ($tariff as $key => $value) {
            $id_articles   = $this->goods->get_article_from_tariff($value);
            $articles_info = $this->goods->get_article_info_from_id($id_articles, "id_articles");
            if($articles_info == ""){
                continue;
            }
            foreach ($articles_info as $key_articles_info => $value_articles_info) {
                $tickeet= 
                [
                    'id'    => $value_articles_info['ID_BAR'],
                    'price' => $value_articles_info['PRICE'],
                    'name'  => $value_articles_info['NAME'],
                ];
                array_push($tick_arr, $tickeet);   
            }
        }
        return $tick_arr;       
    }
}
