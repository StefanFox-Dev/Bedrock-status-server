<?php
$ip = '185.17.0.58'; //ip server
$port = 19132; // port server
$status = 'null';

function send(string $message = ''): void {
 $botTGtoken = 'bot65:aa'; //token bot Telegram
 $botVKtoken = 'vk1.71'; //token bot VK
 $cTGid = '1234'; //chat id Telegram
 $cVKid = 1; //chat id VK

    $url = 'https://api.telegram.org/' . $botTGtoken . '/sendMessage?chat_id=' . $cTGid . '&text=' . urlencode($message);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    
    $url = 'https://api.vk.com/method/messages.send';
    $params = [
        'access_token' => $botVKtoken,
        'v' => '5.85'
    ];
    $params['message'] = $message;
    $params['chat_id'] = $cVKid;
    $ch = curl_init($url . '?' . http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
}

echo "🟠 Подключение к серверу $ip:$port";
while (true) {
    $socket = fsockopen("udp://" . gethostbyname($ip), $port);

    if (!$socket) {
        if ($status === 'true' || $status === 'null') {
            $status = 'false';
            send("[" . date('d-m-Y H:i:s') . "/$port] > Статус сервера изменился на 🔴 (socket)");
            echo PHP_EOL . date('d-m-Y H:i:s') . "/$port 🔴 (socket)";
        }
    } else {
        $request_packet = "\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78\x00\x00\x00\x00\x00\x00\x00\x00";
        stream_Set_Timeout($socket, 0, 100000);
        stream_Set_Blocking($socket, true);
        fwrite($socket, $request_packet, strlen($request_packet));
        $response = fread($socket, 4096);

        if (!$response) {
            if ($status === 'true' || $status === 'null') {
                $status = 'false';
                send("[" . date('d-m-Y H:i:s') . "/$port] > Статус сервера изменился на 🔴 (response)");
                echo PHP_EOL . date('d-m-Y H:i:s') . "/$port 🔴 (response)";
            }
        } else {
            $server_players = substr($response, 35);
            $server_players = explode(';', $server_players);
            fclose($socket);

            if (isset($server_players[4])) {
                $numplayers = $server_players[4];
            } else $numplayers = 0;

            if ($status === 'false' || $status === 'null') {
                $status = 'true';
                send("[" . date('d-m-Y H:i:s') . "/$port] > Статус сервера изменился на 🟢\nОнлайн: " . $numplayers);
                echo PHP_EOL . date('d-m-Y H:i:s') . "/$port 🟢\nОнлайн: " . $numplayers;
            }
        }
    }
    sleep(5);
}
?>
