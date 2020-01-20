<?php
    require './settings.php';
    require_once 'Controller.php';
    require_once 'Model.php';
    require_once  'routes.php';

    //Инициализация маршрутов
    Route::create();
//
//    CheckerController::create($db->id);


//TODO: Добавить авто создание таблиц sql createTables
//TODO: Добавить проверку даты (день, 2 дня, 7 дней)
//TODO: Настроить вывод уведомлений в telegram
