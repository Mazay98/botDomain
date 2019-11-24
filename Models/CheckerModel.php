<?php
class Checker extends  Model
{
    public function check ($db)
    {
        $sql= "SELECT url,date_end FROM dates WHERE date_end = CURDATE() OR date_end = CURDATE() + INTERVAL 2 DAY OR date_end = CURDATE() + INTERVAL 7 DAY";
        $stmt = $db->prepare($sql);
        $stmt->execute([LINK_URL]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($rows) {
            return $rows;
        } else {
            return false;
        }
    }

}