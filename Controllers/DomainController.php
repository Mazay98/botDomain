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
        if ($date->addExpAndRegDate($url, $whois_answer, $db->id)) {
            $domain_id = (int)Date::getDomainId($url,$db->id);
            return $domain_id;
        }
       return false;
    }
}