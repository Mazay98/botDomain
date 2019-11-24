<?php

class Parser
{
    private function getStartAndEndTime ($db) {
        $sql = "SELECT time_start, time_end FROM parse_link_time WHERE site_url LIKE '%".Link::getCleanLink()."%'";
        $query = $db->query($sql);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getAverageCount ($db)
    {
        $sql = "SELECT average FROM parse_link_time WHERE site_url LIKE '%".Link::getCleanLink()."%'";
        $query = $db->query($sql);
        $result = $query->fetch();
        return (int)$result[0];
    }

    public function setTimeStart ($db)
    {
        $query = $db->prepare('INSERT INTO parse_link_time(site_url, time_start) VALUES (:site_url, :time_start)');
        $response = $query->execute(array(':site_url' => LINK_URL, ':time_start' => (int)time()));
        if ($response) {
            return true;
        } else {
            return false;
        }
    }

    public function updateTimeStart ($db)
    {
        // Проверяем существует ли таблица вообще
        if (AppModel::tableIsCreate($db, 'parse_link_time')) {
            // Проверяем есть ли в таблице данные
            if (AppModel::tableDataExist($db, 'parse_link_time')) {

                $sql = "UPDATE parse_link_time SET time_start=? WHERE site_url LIKE ?";
                $query = $db->prepare($sql);
                $response =$query->execute([(int)time(), "%".Link::getCleanLink()."%"]);
                // Проверяем update прошел или нет
                if ($response) {
                    return true;
                } else {
                    return 'error set time';
                }
            } else {
                $this->setTimeStart($db);
            }
        } else {
            return 'you need create table !';
        }
    }

    public function updateTimeEnd ($db)
    {
        $sql = "UPDATE parse_link_time SET time_end=? WHERE site_url LIKE ?";
        $query = $db->prepare($sql);
        $response =$query->execute([(int)time(), "%".Link::getCleanLink()."%"]);
        // Проверяем update прошел или нет
        if ($response) {
            return true;
        } else {
            return 'error update time end ';
        }
    }

    public function updateAverageCount ($db,$count)
    {
        $time = $this->getStartAndEndTime($db);
        $differenceTime = (int)$time['time_end'] - (int)$time['time_start'];

        if ($differenceTime <= 0) {
            $differenceTime = 1;
        } elseif ($differenceTime > 45) {
            $differenceTime = 45;
        }

        $oneLinkTimeParse = $differenceTime/$count;

        $average = round(45/$oneLinkTimeParse, 0);
        print_r('sr:'.$average);
        $sql = "UPDATE parse_link_time SET average=? WHERE site_url LIKE ?";
        $query = $db->prepare($sql);
        $response =$query->execute([$average, "%".Link::getCleanLink()."%"]);

        if ($response) {
            return true;
        } else {
            return false;
        }
    }

}
