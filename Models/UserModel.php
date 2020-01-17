<?php
require_once './Controllers/DomainController.php';

class User
{
    public static function addDomainForeUser ($url)
    {
        return Domain::check($url);
    }
}