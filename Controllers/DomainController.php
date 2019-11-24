<?php
require_once './library/Phois/Whois/Whois.php';
require_once './Models/DateModel.php';
class Domain extends Controller
{
    public static function check($db)
    {
        $domain = new Whois(LINK_URL);
        $whois_answer = $domain->info();
        $date = new Date();
        $date->addExpAndRegDate($whois_answer, $db);

        if ($domain->isAvailable()) {
            echo "Domain is available\n";
        } else {
            echo "Domain is registered\n";
        }
    }
}