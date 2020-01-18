<?php
require_once './Controllers/DomainController.php';

class User
{
    private static  $domain_id;
    private static  $chat_id;
    public static function setDomainForeUser ($url, $chat_id)
    {
        if (is_int(Domain::getId($url))) {

            self::$domain_id = Domain::getId($url);
            self::$chat_id = $chat_id;
            return self::setChatAndDomainId();

        } else {
            return false;
        }
    }
    private static function setChatAndDomainId()
    {
        $chat_id = (int)self::$chat_id;
        $domain_id = (int)self::$domain_id;

        if (empty($chat_id) || empty($domain_id)) {
            return false;
        }

        return true;
    }
}