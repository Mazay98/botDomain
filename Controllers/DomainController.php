<?php
require_once './library/Phois/Whois/Whois.php';
require_once './Models/DateModel.php';
require_once './Models/dbModel.php';

class Domain
{
    public static function check($url)
    {
        $db = new DB();
        $domain = new Whois($url);
//        $whois_answer = $domain->info();
//        $date = new Date();
//        $date->addExpAndRegDate($whois_answer, $db);
//todo: Добавить createTables
        if ($domain->isAvailable()) {
            return "DisA";
        } else {
            return "DisR";
        }
    }
}