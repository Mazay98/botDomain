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
//todo: Добавить таблицу с данными domains(domain_id, domain_name, date_start, date_end)
//todo: Добавить таблицу с данными users(user_id, domain_users_id, user_name, chat_id)
//todo: Добавить таблицу с данными domain_users(domain_users_id, chat_id, domain_id)
        if ($domain->isAvailable()) {
            return "DisA";
        } else {
            return "DisR";
        }
    }
}