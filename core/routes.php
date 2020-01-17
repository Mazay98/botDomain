<?php
class Route
{
    public static function create()
    {
        $routes = explode('/', $_SERVER['REQUEST_URI']);

        if ($routes[1] === 'bot'){
            include "./Controllers/TelegramController.php";
            $bot = new Bot();
        } elseif ($routes[1] === 'check') {
            include "./Controllers/CheckerController.php";
        } else {
            Route::ErrorPage404();
        }
    }

    function ErrorPage404()
    {
        $host = 'http://'.$_SERVER['HTTP_HOST'].'/';
        header('HTTP/1.1 404 Not Found');
        header("Status: 404 Not Found");
        header('Location:'.$host.'404');
    }
}