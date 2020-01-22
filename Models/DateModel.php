<?php

use http\Url;

class Date
{
    /**
     * Получить дуту регистрации домена
     * @param string $date дата
     * @return string
     */
    public function getRegistryDate($date)
    {
        preg_match('/Creation\sDate:\s(.*)\\r/', $date, $matches);
        if (!$matches[1]){
            preg_match('/created:\s*(.*)\\n/', $date, $matches);
        }
        $matches[1] = $this->formatDate($matches[1]);
        return $matches[1];
    }

    /**
     * Получить дуту Окончания регистрации домена
     * @param string $date дата
     * @return string
     */
    public function getExpirationDate($date)
    {
        preg_match('/Registry\sExpiry\sDate:\s(.*)\\r/', $date, $matches);
        if (!$matches[1]){
            preg_match('/paid-till:\s*(.*)\\n/', $date, $matches);
        }
        $matches[1] = $this->formatDate($matches[1]);
        return $matches[1];
    }

    /**
     * Добавить дату для домена
     * @param string $url uri домена без https
     * @param string $ans Ответ от сервера whois
     * @return integer domain_id
    */
    public function addExpAndRegDate($url, $ans, $db)
    {
        // Проверка на длину строки ответа
        if (strlen($ans)<=50) {
            return false;
        }

        $domain_id = self::getDomainId($url, $db);

        if ($domain_id) {
            return (int)$domain_id['domain_id'];
        }

        $reg = $this->getRegistryDate($ans);
        $exp = $this->getExpirationDate($ans);

        $insert = $db->prepare('INSERT INTO domains (domain_name, date_start, date_end) VALUES (:domain_name, :date_start, :date_end)');
        $insert->execute([':domain_name' => $url, ':date_start' => $reg, ':date_end' => $exp]);

        return self::addExpAndRegDate($url, $ans, $db);
    }

    /**
     * Найти домен в Бд
     * @param string $domain uri домена без https
     * @return integer
     */
    public static function getDomainId($domain, $db)
    {
        $sql= "SELECT domain_id FROM domains WHERE domain_name=?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$domain]);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        return $rows['domain_id'];
    }

    /**
     * Возвращает дату регистрации.
     * @param string $date дата
     * @return string
     */
    private function formatDate($date)
    {
        preg_match("/(\d{4}-\d{2}-\d{2})/", $date, $matches);
        return $matches[1];
    }

}