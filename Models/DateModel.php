<?php
class Date
{
    public function getRegistryDate($date)
    {
        preg_match('/Creation\sDate:\s(.*)\\r/', $date, $matches);
        if (!$matches[1]){
            preg_match('/created:\s*(.*)\\n/', $date, $matches);
        }
        $matches[1] = $this->formatDate($matches[1]);
        return $matches[1];
    }

    public function getExpirationDate($date)
    {
        preg_match('/Registry\sExpiry\sDate:\s(.*)\\r/', $date, $matches);
        if (!$matches[1]){
            preg_match('/paid-till:\s*(.*)\\n/', $date, $matches);
        }
        $matches[1] = $this->formatDate($matches[1]);
        return $matches[1];
    }

    public function addExpAndRegDate($ans ,$db)
    {
        $reg = $this->getRegistryDate($ans);
        $exp = $this->getExpirationDate($ans);

//        $cd=date('d-m-Y');
//        $d1 = strtotime($cd);
//        $d2 = strtotime($exp);
//        $diff = $d2-$d1;
//        $diff = $diff/(60*60*24);

        $sql= "SELECT url FROM dates WHERE url=?";
        $stmt = $db->prepare($sql);
        $stmt->execute([LINK_URL]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) {
            $insert = $db->prepare('INSERT INTO dates (url, date_start, date_end) VALUES (:url, :date_start, :date_end)');
            $insert->execute([':url' => LINK_URL, ':date_start' => $reg, ':date_end' => $exp]);
        }
    }

    private function formatDate($date)
    {
        preg_match("/(\d{4}-\d{2}-\d{2})/", $date, $matches);
        return $matches[1];
    }

}