<?php
require_once './library/Phois/Whois/Whois.php';
require_once './Models/DateModel.php';
require_once './Models/dbModel.php';

/**
 * Класс домена
 */
class Domain
{
    /**
     * Отдать id Домена (создав его если требуется)
     * @param string $url
     * @return integer domainId
    */
    public static function getId($url)
    {
        $db = new DB();
        $domain = new Whois($url);
        $whois_answer = $domain->info();
        $date = new Date();
        $domain_id =  $date->addExpAndRegDate($url, $whois_answer, $db->id);
        return $domain_id;
        ////todo: Добавить авто создание таблиц sql createTables
    }
}