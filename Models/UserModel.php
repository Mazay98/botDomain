<?php
require_once './Controllers/DomainController.php';

class User
{
    private static  $domain_id;
    public static function setDomainForeUser ($url)
    {
        if (is_int(Domain::getId($url))) {
            return true;
        } else {
            return false;
        }
    }
}