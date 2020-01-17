<?php
    require './settings.php';
    require_once 'Controller.php';
    require_once 'Model.php';
    require_once  'routes.php';

    Route::create();
//
//    CheckerController::create($db->id);


//TODO: Добавить работу с ботом telegram -> php
    //TODO: Добавить команды : /start, /addDomain [domain name]
//TODO: Добавить team_id
//TODO: Присвоить team_id из telegram
//TODO: Добавить id пользователя добавившего доменный адрес в team_id
//TODO: Добавить проверку даты (день, 2 дня, 7 дней)
//TODO: Настроить вывод уведомлений в telegram
