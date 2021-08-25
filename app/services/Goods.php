<?php

namespace App\Services;

use App\Infrastructure\Database;

class GOODS
{
    protected $database;

    /**
     * @throws \App\Exceptions\DatabaseConnectionError
     */
    public function __construct()
    {
        $this->database = Database::getConnection('main');
    }

    public function get_article_from_tariff(int $id_tariff)
    {
        $result = $this->database->runQueryWithResult("select id_articles from tariff where id=$id_tariff;");
        return $result['ID_ARTICLES'];
    }
    public function get_article_info_from_id(int $id, string $id_type)
    {
        $result = $this->database->runQueryWithMultyResult("
            SELECT T_ARTICLES.NAME, T_BAR.PRICE, T_ARTICLES.FULLNAME, T_ARTICLES.TAXGROUP, T_BAR.ID_BAR, T_BAR.BAR,
            T_ARTICLES.IS_FISCAL, T_BAR.ID_MEASURE FROM T_BAR INNER JOIN T_ARTICLES ON T_BAR.ID_ARTICLES = 
            T_ARTICLES.ID_ARTICLES WHERE t_articles.is_active!=0 and t_bar.".$id_type."=$id;"
        );
        //var_dump($result);
        return $result;
    }

    public function check_tickets_id(array $tickets)
    {
        foreach ($tickets as $key => $value) {
            $result = $this->get_article_info_from_id($value['id'], 'ID_ARTICLES');
            //var_dump($value['quantity']);
            if (is_string($value['quantity'])){
                return false;
            }
            if (!$result) {
                return false;
            }
        }
        //var_dump($result);
        return true;
    }
}
