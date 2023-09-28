<?php
$ip = '185.17.0.58'; #айпи / ip server
$port = 19132; #порт / port server
$status = 'null'; $txt = "🟠 Подключение к серверу $ip:$port";

#Отправка сообщений в Телеграм, ВК / Send message to Telegram, VK
function send(string $message = ''): void {
    $token = 'bot1234:TEST-test'; #токен бота / token bot
    $id = '-10071'; #айди чата / chat id

    $url = 'https://api.telegram.org/' . $token . '/sendMessage?chat_id=' . $id . '&text=' . urlencode($message);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    $url = 'https://api.vk.com/method/messages.send';
    $params = [
        'access_token' => 'vk1.a.', #токен / token
        'v' => '5.85'
    ];
    $params['message'] = $message;
    $params['chat_id'] = 1; #айди чата / id chat
    $ch = curl_init($url . '?' . http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
}

send($txt);
echo $txt;


#ЗАПУСК / START
while (true) {
    $socket = fsockopen("udp://" . gethostbyname($ip), $port);

    if (!$socket) {
        #ОШИБКА / ERROR
        if ($status === 'true' || $status === 'null') {
            $status = 'false';
            $txt = "[" . date('d-m-Y H:i:s') . "/$port] > Статус сервера изменился на 🔴 (socket)";
            send($txt);
            echo PHP_EOL . $txt;
        }
    } else {
        $request_packet = "\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78\x00\x00\x00\x00\x00\x00\x00\x00";
        stream_Set_Timeout($socket, 0, 100000);
        stream_Set_Blocking($socket, true);
        fwrite($socket, $request_packet, strlen($request_packet));
        $response = fread($socket, 4096);

        if (!$response) {
            #НЕДОСТУПЕН / NOT AVAILABLE
            if ($status === 'true' || $status === 'null') {
                $status = 'false';
                $txt = "[" . date('d-m-Y H:i:s') . "/$port] > Статус сервера изменился на 🔴 (response)";
                send($txt);
                echo PHP_EOL . $txt;
            }
        } else {
            $server_players = substr($response, 35);
            $server_players = explode(';', $server_players);
            fclose($socket);

            if (isset($server_players[4])) {
                $numplayers = $server_players[4];
            } else $numplayers = 0;
            
            #УСПЕШНО / SUCCESSFULLY
            if ($status === 'false' || $status === 'null') {
                $status = 'true';
                $txt = "[" . date('d-m-Y H:i:s') . "/$port] > Статус сервера изменился на 🟢\nОнлайн: " . $numplayers;
                send();
                echo PHP_EOL . $txt;
            }
        }
    }
    
    sleep(5); #время проверки / time check
}
?>
