<?php
require_once './Models/CheckerModel.php';

class CheckerController
{
    public static function create($db)
    {
        $checker = new Checker();
        $rows = $checker->check($db);

    }
}