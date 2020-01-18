<?php
include "./Models/UserModel.php";

class Bot
{
    private $messages = '';
    private $responseMessage = '';
    private $options =[];
    protected $username = '';
    protected $chatid = '';
    protected $domain = '';

    public function __construct ()
    {
        $response = json_decode(file_get_contents('php://input'), JSON_OBJECT_AS_ARRAY);
        if (isset($response['message'])) {
            $this->chatid = $response['message']['chat']['id'];
            $this->username = $response['message']['chat']['username'];
            $this->messages = $response['message']['text'];
        }
        if (isset($response['callback_query'])) {
            $this->chatid = $response['callback_query']['message']['chat']['id'];
            $this->username = $response['callback_query']['message']['chat']['username'];
            $this->messages = $response['callback_query']['data'];
        }
        $user = User::create($this->username, $this->chatid);

        if (!$user) {
            $this->options['text'] = 'Ошибка создания пользователя';
            $this->sendRequest();
            die();
        }

        $this->setAnswer();
        $this->options['chat_id'] = $this->chatid;
        $this->options['text'] = $this->responseMessage;
        $this->sendRequest();
    }
    /**
     * @boolean
     * @descriprion =  'return true or false answer'
     */
    private function setAnswer()
    {
        if (empty($this->chatid) && empty($this->messages)) {
            return false;
        }
        if ($this->messages == '/start') {
            $this->responseMessage = "Добро пожаловать!\n";
            $this->responseMessage .= "Меня зовут Доменыч.\n";
            $this->responseMessage .= "Я ваш персональный помощник!\n";
            $button_command = array('text' => 'Команды', 'callback_data' => '/help');
            $keyboard = array('inline_keyboard' => array(array($button_command)));
            $this->options['reply_markup']= json_encode($keyboard, TRUE);
            return true;
        }
        if ($this->messages == '/help') {
            $this->responseMessage = "Список всех команд:\n";
            $this->responseMessage.= "/addDomain [Доменное имя (google.ru)]";
            return true;
        }
        if (preg_match("~/add[Dd]omain[\s]+(.+\..{2,10})$~", trim($this->messages), $matches)) {

            $this->domain = $matches[1];
            if ($this->setDomainForUser()){
                $this->responseMessage = "Доменное имя: $this->domain привязано к вашей учетной записи. \n";
                return true;
            } else {
                $this->responseMessage = "Домен доступен для покупки!";
                return false;
            }
			
//			$this->responseMessage = $this->setDomainForUser();
//			return true;
        }
        $this->responseMessage='Нет такой команды!';
        return false;
    }

    private function setDomainForUser()
    {
		return User::setDomainForeUser($this->domain);
    }

    private function sendRequest($method = 'sendMessage')
    {
        $uri = 'https://api.telegram.org/bot' . TOKEN_BOT . '/';
        $myCurl = curl_init();
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