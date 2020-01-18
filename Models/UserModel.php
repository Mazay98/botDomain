<?php
require_once './Controllers/DomainController.php';
require_once './Models/dbModel.php';

class User
{
    private static $domain_id;
    private static $chat_id;
    public static $user_id;
    private static $name;
    private static $db;

    public static function setDomainForeUser ($url)
    {
        if (is_int(Domain::getId($url))) {

            self::$domain_id = Domain::getId($url);
            self::setChatAndDomainId();

        } else {
            return false;
        }
        return true;
    }
    private static function setChatAndDomainId()
    {
//        $chat_id = (int)self::$chat_id;
        $domain_id = (int)self::$domain_id;
        $user_id = (int)self::$user_id;

        if (empty($user_id) || empty($domain_id)) {
            return false;
        }

        return true;
    }
    public static function create($name, $chat_id)
    {
        if (empty($chat_id) || empty($name)) {
            return false;
        }
        $db = new DB();
        self::$db = $db->id;
        self::$name = $name;
        self::$chat_id = $chat_id;

        if (!self::userCreated()){
            return false;
        }
        self::$user_id = self::getUserId();
        return true;
    }

    private function userCreated()
    {
        if (!empty(self::getUserId())){
            return true;
        }

        $sql = 'INSERT INTO users (user_name, chat_id) VALUES (:user_name, :chat_id)';
        $insert = self::$db->prepare($sql);
        $insert->execute([':user_name' => self::$name, ':chat_id' => self::$chat_id]);

        return self::userCreated();
    }

    private function getUserId()
    {
        $sql= "SELECT user_id FROM users WHERE chat_id=?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([self::$chat_id]);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        return $rows['user_id'];
    }

}