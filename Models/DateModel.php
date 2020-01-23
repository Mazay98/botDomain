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

        $ssl_date = $this->setDomainDateSSL($url) ? $this->setDomainDateSSL($url) : '';
        $reg = $this->getRegistryDate($ans);
        $exp = $this->getExpirationDate($ans);

        $insert = $db->prepare('INSERT INTO domains (domain_name, date_start, date_end, date_end_ssl) VALUES (:domain_name, :date_start, :date_end, :date_end_ssl)');
        $insert->execute([':domain_name' => $url, ':date_start' => $reg, ':date_end' => $exp, ':date_end_ssl'  => $ssl_date]);

        return true ? $insert->rowCount() : false;
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

    /**
     * Возвращает до какого числа зарегистрирован ssl сертификат
     * @param string $url url сертификата
     * @return string
     */
    private function setDomainDateSSL($url)
    {
        $getDomainSSL = shell_exec("echo | openssl s_client -servername $url -connect $url:443 2>/dev/null | openssl x509 -noout -dates");
        preg_match('~notAfter=(\w+)\s+(\d+)\s.+\s(\d+)~', $getDomainSSL, $matches);
        $date =  $matches[2].$matches[1].$matches[3];
        $date =  date("Y-m-d", strtotime($date));
        return $date;
    }
}