<?php
require_once './Models/CheckerModel.php';
require_once './Controllers/TelegramController.php';

class CheckDomain extends Bot
{
    public function __construct()
    {

        $checker = new Checker();
        $rows = $checker->check();

        if(empty($rows)){
           return false;
        }
        $option=[];

        foreach ($rows as $row) {
            $option['text'] = $row['user_name'].", Срок действия домена ".$row['domain_name'].", подходит к концу.\nДомен действителен до ".$row['date_end'];
            $option['chat_id'] = $row['chat_id'];
            parent::sendRequest($option);
        }

        return true;
    }

}