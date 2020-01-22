<?php
require_once "./Models/UserModel.php";
require_once './Controllers/DomainController.php';
/**
 * Класс для работы с api telegram
*/
class Bot
{
    /**
     * @param string $messages Хранит сообщение клиента (Входящее)
     * @param string $responseMessage Хранит сообщение клиента для отправки
     * @param array $options Хранит массив опций для отправки
     * @param string $username Хранит имя клиента
     * @param string $chatid Хранит id чата клиента
     */
    private $messages = '';
    private $responseMessage = '';
    private $options =[];
    private $username = '';
    private $chatid = '';
    private  $userId;
    /**
     * Метод инициализации объекта
    */
    public function __construct ()
    {
        /**
         * @array Массив параметров, который пришел от бота
        */
        $response = json_decode(file_get_contents('php://input'), JSON_OBJECT_AS_ARRAY);
;
        if (isset($response['message'])) {
            $this->chatid = $response['message']['chat']['id'];
            $this->username = $response['message']['chat']['first_name'] ?? $response['message']['chat']['username'] ?? "Аноним";
            $this->messages = $response['message']['text'];
        }
        if (isset($response['callback_query'])) {
            $this->chatid = $response['callback_query']['message']['chat']['id'];
            $this->username = $response['callback_query']['message']['chat']['first_name'] ??  $response['callback_query']['message']['chat']['username'] ?? "Аноним";
            $this->messages = $response['callback_query']['data'];
        }

        /**
         * Создание пользователя
         * @param string username
         * @param string chatid
        */
        $this->userId = User::getId($this->username, $this->chatid);

        if (!$this->userId) {
            $this->options['text'] = 'Ошибка создания пользователя';
            $this->sendRequest();
            die();
        }

        $this->setAnswer();
        $this->options['chat_id'] = $this->chatid;
        $this->options['text'] = $this->responseMessage;
        $this->sendRequest();
        die();
    }
    /**
     * Установить ответ боту
     * @boolean
     */
    private function setAnswer()
    {
        if (empty($this->chatid) && empty($this->messages)) {
            return false;
        }
        /**
         * Команда /start
        */
        if ($this->messages == '/start') {
            $this->responseMessage = "Добро пожаловать!\n";
            $this->responseMessage .= "Меня зовут Доменыч.\n";
            $this->responseMessage .= "$this->username, я Ваш персональный помощник!\n";
            $button_command = array('text' => 'Команды', 'callback_data' => '/help');
            $keyboard = array('inline_keyboard' => array(array($button_command)));
            $this->options['reply_markup']= json_encode($keyboard, TRUE);
            return true;
        }
        /**
         * Команда /help
         */
        if ($this->messages == '/help') {
            $this->responseMessage = "Список всех команд:\n";
            $this->responseMessage.= "/addDomain (google.ru) - Добавить домен\n";
            $this->responseMessage.= "/destroyDomain (google.ru) - Удалить домен \n";

            $button_command = array('text' => 'Все Мои домены', 'callback_data' => '/allDomains');
            $keyboard = array('inline_keyboard' => array(array($button_command)));
            $this->options['reply_markup']= json_encode($keyboard, TRUE);
            return true;
        }
        /**
         * Команда /addDomain
         */
        if (preg_match("~/add[Dd]omain[\s]+(.+\..{2,10})$~", trim($this->messages), $matches)) {
            $domainName = $matches[1];
            /**
             * Получаем id домена в БД
             * @param string $domainName Имя домена
            */
            $domain_id = Domain::getId($domainName);

            if ($domain_id){
                /**
                 * Добавляем к пользователю домен
                */
                User::setDomainForeUser($domain_id, $this->userId);
                $this->responseMessage = "Доменное имя: $domainName привязано к вашей учетной записи. \n";
                return true;
            } else {
                $this->responseMessage = "Домен не привязался";
                return false;
            }
        }
        /**
         * Команда /allDomains
        */
        if (preg_match("~/all[Dd]omains~", trim($this->messages))) {

            $domains = User::getAllDomains($this->userId);

            if ($domains) {
                $this->responseMessage = "$this->username, вот Ваш список зарегистрированных доменов:\n\n";
                $this->responseMessage .= "****************************\n\n";
                $button_command = [];
                foreach ($domains as $domain) {
                    $this->responseMessage .= "Имя: ".$domain['domain']."\n";
                    $this->responseMessage .= "Действителен до: ".$domain['end']."\n\n";
                    $button_command[] = [['text' => 'Удалить домен '.$domain['domain'], 'callback_data' => '/destroyDomain '.$domain['domain']]];
                    $this->responseMessage .= "****************************\n\n";
                }
                $keyboard = array('inline_keyboard' => $button_command);
                $this->options['reply_markup']= json_encode($keyboard);
//                file_put_contents('test.txt', json_encode($keyboard));
            } else {
                $this->responseMessage = "$this->username , у Вас нет зарегистрированых доменов";
                return false;
            }
            return true;
        }
        /**
         * Команда /destroyDomain
        */
        if (preg_match("~/destroy[Dd]omain[\s]+(.+\..{2,10})$~", trim($this->messages),$matches)) {
            $domainName = $matches[1];
            $domainRemoved = User::destroyDomianForeUser($this->userId, $domainName);
            if ($domainRemoved){
                $this->responseMessage = "$this->username , домен $domainName успешно отвязан";
            } else {
                $this->responseMessage = "$this->username , нам не удалось отвязать домен $domainName";
                return false;
            }
            return true;
        }
        $this->responseMessage='Нет такой команды!';
        $button_command = array('text' => 'Команды', 'callback_data' => '/help');
        $keyboard = array('inline_keyboard' => array(array($button_command)));
        $this->options['reply_markup']= json_encode($keyboard, TRUE);
        return false;
    }
    /**
     * Отправляем ответ боту
     * @return array
     * @param string $method Метод для отправки боту, смотри Api телеграм
    */
    private function sendRequest($method = 'sendMessage')
    {
        $uri = 'https://api.telegram.org/bot' . TOKEN_BOT . '/';
        $myCurl = curl_init();

        /**
         * Необходимо прокси так как РКН блокирует Telegram
        */
        curl_setopt($myCurl, CURLOPT_PROXYTYPE, 7);
        curl_setopt($myCurl, CURLOPT_PROXY, "127.0.0.1:9050");

        if (!empty($this->options)) {
            curl_setopt($myCurl, CURLOPT_URL, $uri . $method . "?" . http_build_query($this->options));
        } else {
            curl_setopt($myCurl, CURLOPT_URL, $uri . $method);
        }
        $responses = curl_exec($myCurl);
        curl_close($myCurl);
        return json_decode(
            $responses,
            JSON_OBJECT_AS_ARRAY
        );
//        return $responses;
    }
}
