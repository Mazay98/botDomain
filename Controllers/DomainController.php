<?php
require_once './library/Phois/Whois/Whois.php';
require_once './Models/DateModel.php';
class Domain
{
    public static function check($url)
    {
        $domain = new Whois($url);
//        $whois_answer = $domain->info();
//        $date = new Date();
//        $date->addExpAndRegDate($whois_answer, $db);

        if ($domain->isAvailable()) {
            return "DisA";
        } else {
            return "DisR";
        }
    }
}