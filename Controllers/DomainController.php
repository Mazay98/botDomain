<?php
require_once './library/Phois/Whois/Whois.php';
require_once './Models/DateModel.php';
require_once './Models/dbModel.php';

class Domain
{
    public static function getId($url)
    {

        $db = new DB();
        $domain = new Whois($url);
        $whois_answer = $domain->info();
        $date = new Date();
        return $date->addExpAndRegDate($url, $whois_answer, $db->id);

////todo: Добавить createTables
//        if ($domain->isAvailable()) {
//            return $domain->info();
//        } else {
//            return $domain->info();
//        }
    }
}