<?php
require_once './Controllers/DomainController.php';
require_once './Models/dbModel.php';

/**
 * Класс для работы с пользователями
*/
class User
{
    /**
     * @param string $domain_id Хранит id Домена
     * @param integer $chat_id Хранит id Чата клиента
     * @param integer $user_id Хранит id клиента
     * @param string $name Хранит имя клиента
     * @param string $db Хранит экземпляр БД
     */
    private static $domain_id;
    private static $chat_id;
    public static $user_id;
    private static $name;
    private static $db;

    /**
     * Возвращает id пользователя
     * @param string $name Логин клиента
     * @param string $chatId Id чата клиента
     * @return integer
     */
    public static function getId($name, $chatId)
    {
        if (empty($chatId) || empty($name)) {
            return false;
        }

        $db = new DB();
        self::$db = $db->id;
        self::$name = $name;
        self::$chat_id = $chatId;

        if (!self::getUser()) {
            self::userCreate();
        }

        $user_id = (int)self::getUser();
        return $user_id;
    }
    /**
     * Связать домен и пользователя
     * @param integer $domainId id Домена
     * @param integer $userId id Пользователя
     * @return boolean
    */
    public static function setDomainForeUser($domainId, $userId)
    {
        self::$domain_id = $domainId;
        self::$user_id = $userId;

        self::setChatAndDomainId();

        return true;
    }

    /**
     * Вывести список всех доменов пользователя
     * @param integer $userId id пользователя
     * @return array
    */
    public static function getAllDomains($userId)
    {
        $db = new DB();
        $db = $db->id;

        $sql= "
            SELECT domain_name 'domain', date_end 'end'
            FROM (
                users JOIN domain_users
                USING(user_id))
            JOIN domains 
            USING (domain_id)
            WHERE user_id = ?
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows){
            return false;
        }
        return $rows;
    }

    /**
     * Удалить домен у пользователя
     * @param  integer $userId id Пользователя
     * @param  string $domainName Доменное имя
     * @return boolean
    */
    public static function destroyDomianForeUser($userId, $domainName)
    {
        if (!$userId || !$domainName){
            return  false;
        }
        $db = new DB();
        $db = $db->id;

        $sql= "
            DELETE du 
            FROM ( 
                domain_users du 
                JOIN users 
                USING(user_id) 
            )
            JOIN domains 
            USING (domain_id) 
            WHERE users.user_id = ? AND domains.domain_name = ?
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId, $domainName]);

        if (!$stmt->rowCount()){
            return false;
        }

        return true;
    }

    /**
     * Связать пользователя и домен
    */
    private static function setChatAndDomainId()
    {
        $domain_id = self::$domain_id;
        $user_id = self::$user_id;

        if (empty($user_id) || empty($domain_id)) {
            return false;
        }
        if (empty(self::getDomainUser())){
            self::setDomainUser();
        }
        return true;
    }

    /**
     * Создать пользователь
     * @return boolean
    */
    private function userCreate()
    {
        $sql = 'INSERT INTO users (user_name, chat_id) VALUES (:user_name, :chat_id)';
        $insert = self::$db->prepare($sql);
        $insert->execute([':user_name' => self::$name, ':chat_id' => self::$chat_id]);
        return true;
    }

    /**
     * Найти пользователя и получить его id
     * @return integer
    */
    private static function getUser()
    {
        $sql= "SELECT user_id FROM users WHERE chat_id=?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([self::$chat_id]);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$rows['user_id'];
    }

    /**
     * Найти домен привязанный к пользователю
     * @return array
    */
    private static function getDomainUser()
    {
        $sql= "SELECT id FROM domain_users WHERE user_id=? AND domain_id=?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([self::$user_id,self::$domain_id]);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        return $rows['id'];
    }

    /**
     * Добавить пользователю домен
     * @return boolean
     */
    private static function setDomainUser()
    {
        $sql = 'INSERT INTO domain_users (user_id, domain_id) VALUES (:user_id, :domain_id)';
        $insert = self::$db->prepare($sql);
        $insert->execute([':user_id' => self::$user_id, ':domain_id' => self::$domain_id]);
        return true;
    }

}