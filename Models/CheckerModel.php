<?php
require_once './Models/dbModel.php';
class Checker
{
    private $db;

    public function __construct()
    {
        $db = new DB();
        $this->db = $db->id;
    }
    /**
     * Находит домены, у которых срок действия подходит к концу.
     * @return array
    */
    public function checkDomains ()
    {
        $db = $this->db;

        $sql= "
            SELECT user_name, chat_id, domain_name, date_end 
            FROM domain_users JOIN users USING (user_id)
            JOIN domains USING (domain_id)
            WHERE (
                domains.date_end = CURDATE() OR 
                domains.date_end = CURDATE() + INTERVAL 2 DAY OR 
                domains.date_end = CURDATE() + INTERVAL 7 DAY  OR 
                domains.date_end = CURDATE() + INTERVAL 30 DAY
            )
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows ? $rows : false;
    }
    /**
     * Находит домены, у которых срок действия ssl сертификата  подходит к концу.
     * @return array
     */
    public function checkSsl()
    {
        $db = $this->db;

        $sql= "
            SELECT user_name, chat_id, domain_name, date_end_ssl
            FROM domain_users JOIN users USING (user_id)
            JOIN domains USING (domain_id)
            WHERE (
                domains.date_end_ssl = CURDATE() OR 
                domains.date_end_ssl = CURDATE() + INTERVAL 2 DAY OR 
                domains.date_end_ssl = CURDATE() + INTERVAL 7 DAY  OR 
                domains.date_end_ssl = CURDATE() + INTERVAL 30 DAY
            )
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows ? $rows : false;
    }
}